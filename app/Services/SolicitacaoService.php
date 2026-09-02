<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\StatusSolicitacao;
use App\Repositories\SolicitacaoRepository;
use App\Repositories\TrocaRepository;
use App\Repositories\PassagemRepository;
use App\Repositories\HistoricoRepository;
use App\Repositories\PeriodoRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\AuditoriaRepository;

class LimiteExcedidoException extends \RuntimeException {}
class RegraPeriodoException extends \RuntimeException {}

class SolicitacaoService
{
    private SolicitacaoRepository $solicitacaoRepo;
    private TrocaRepository $trocaRepo;
    private PassagemRepository $passagemRepo;
    private HistoricoRepository $historicoRepo;
    private PeriodoRepository $periodoRepo;
    private UsuarioRepository $usuarioRepo;
    private AuditoriaRepository $auditoriaRepo;
    private LimiteService $limiteService;
    private CodigoService $codigoService;
    private NotificacaoService $notificacaoService;

    public function __construct()
    {
        $this->solicitacaoRepo = new SolicitacaoRepository();
        $this->trocaRepo = new TrocaRepository();
        $this->passagemRepo = new PassagemRepository();
        $this->historicoRepo = new HistoricoRepository();
        $this->periodoRepo = new PeriodoRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->auditoriaRepo = new AuditoriaRepository();
        $this->limiteService = new LimiteService();
        $this->codigoService = new CodigoService();
        $this->notificacaoService = new NotificacaoService();
    }

    private function validarPeriodo(array $periodo): void
    {
        if (in_array($periodo['status'], ['ENCERRADO', 'INATIVO'], true)) {
            throw new RegraPeriodoException('O período correspondente a esta data está encerrado/inativo e não aceita novas solicitações.');
        }
    }

    /**
     * Cria uma solicitação de TROCA. Toda a validação crítica (período e limite) é
     * refeita aqui no backend, independentemente do que o formulário já validou no navegador.
     */
    public function criarTroca(int $solicitanteId, array $dados): array
    {
        $meuPeriodo = $this->limiteService->identificarPeriodoPorData($dados['meu_data']);
        if (!$meuPeriodo) {
            throw new RegraPeriodoException('Não existe período de controle configurado para a data do seu plantão.');
        }
        $this->validarPeriodo($meuPeriodo);

        $outroPeriodo = $this->limiteService->identificarPeriodoPorData($dados['outro_data']);
        if (!$outroPeriodo) {
            throw new RegraPeriodoException('Não existe período de controle configurado para a data do plantão do outro colaborador.');
        }

        $entrePerodosDiferentes = $meuPeriodo['id'] !== $outroPeriodo['id'];
        if ($entrePerodosDiferentes && $meuPeriodo['regra_troca_entre_periodos'] === 'PROIBIDA') {
            throw new RegraPeriodoException('Este período não permite trocas entre plantões de períodos diferentes.');
        }

        if (!$this->limiteService->podeSolicitar($solicitanteId, LimiteService::TROCA, $meuPeriodo['id'])) {
            throw new LimiteExcedidoException('Limite de trocas atingido para este período.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $codigo = $this->codigoService->gerar('TRC');

            $solicitacaoId = $this->solicitacaoRepo->criar([
                'codigo' => $codigo,
                'tipo' => 'TROCA',
                'solicitante_id' => $solicitanteId,
                'periodo_id' => $meuPeriodo['id'],
                'status' => StatusSolicitacao::PENDENTE_ACEITE,
                'motivo' => $dados['motivo'] ?? null,
                'observacao' => $dados['observacao'] ?? null,
            ]);

            $this->trocaRepo->criar([
                'solicitacao_id' => $solicitacaoId,
                'meu_data' => $dados['meu_data'],
                'meu_turno' => $dados['meu_turno'],
                'outro_usuario_id' => $dados['outro_usuario_id'],
                'outro_data' => $dados['outro_data'],
                'outro_turno' => $dados['outro_turno'],
                'periodo_outro_id' => $outroPeriodo['id'],
            ]);

            if ($entrePerodosDiferentes) {
                $db->prepare("UPDATE solicitacoes SET autorizado_entre_periodos = 1 WHERE id = :id")
                   ->execute(['id' => $solicitacaoId]);
            }

            $this->historicoRepo->registrar($solicitacaoId, $solicitanteId, 'Solicitação de troca criada', null, StatusSolicitacao::PENDENTE_ACEITE);
            $this->auditoriaRepo->registrar($solicitanteId, 'CRIAR_TROCA', 'solicitacoes', $solicitacaoId, null, $dados);

            $outro = $this->usuarioRepo->buscarPorId((int)$dados['outro_usuario_id']);
            $solicitante = $this->usuarioRepo->buscarPorId($solicitanteId);
            $this->notificacaoService->enviar(
                (int)$dados['outro_usuario_id'],
                "Você recebeu uma solicitação de troca de {$solicitante['nome']} (código {$codigo}).",
                'trocas_ver.php?id=' . $solicitacaoId
            );

            $db->commit();
            return ['id' => $solicitacaoId, 'codigo' => $codigo];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Cria uma solicitação de PASSAGEM.
     */
    public function criarPassagem(int $solicitanteId, array $dados): array
    {
        $periodo = $this->limiteService->identificarPeriodoPorData($dados['data']);
        if (!$periodo) {
            throw new RegraPeriodoException('Não existe período de controle configurado para a data deste plantão.');
        }
        $this->validarPeriodo($periodo);

        if (!$this->limiteService->podeSolicitar($solicitanteId, LimiteService::PASSAGEM, $periodo['id'])) {
            throw new LimiteExcedidoException('Limite de passagens atingido para este período.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $codigo = $this->codigoService->gerar('PAS');

            $solicitacaoId = $this->solicitacaoRepo->criar([
                'codigo' => $codigo,
                'tipo' => 'PASSAGEM',
                'solicitante_id' => $solicitanteId,
                'periodo_id' => $periodo['id'],
                'status' => StatusSolicitacao::PENDENTE_ACEITE,
                'motivo' => $dados['motivo'] ?? null,
                'observacao' => $dados['observacao'] ?? null,
            ]);

            $this->passagemRepo->criar([
                'solicitacao_id' => $solicitacaoId,
                'quem_passou_id' => $solicitanteId,
                'quem_recebeu_id' => $dados['quem_recebeu_id'],
                'data' => $dados['data'],
                'hora_inicial' => $dados['hora_inicial'],
                'hora_final' => $dados['hora_final'],
                'turno' => $dados['turno'],
            ]);

            $this->historicoRepo->registrar($solicitacaoId, $solicitanteId, 'Solicitação de passagem criada', null, StatusSolicitacao::PENDENTE_ACEITE);
            $this->auditoriaRepo->registrar($solicitanteId, 'CRIAR_PASSAGEM', 'solicitacoes', $solicitacaoId, null, $dados);

            $solicitante = $this->usuarioRepo->buscarPorId($solicitanteId);
            $this->notificacaoService->enviar(
                (int)$dados['quem_recebeu_id'],
                "Você recebeu uma solicitação de passagem de plantão de {$solicitante['nome']} (código {$codigo}).",
                'passagens_ver.php?id=' . $solicitacaoId
            );

            $db->commit();
            return ['id' => $solicitacaoId, 'codigo' => $codigo];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
