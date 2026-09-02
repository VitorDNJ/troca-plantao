<?php

namespace App\Services;

use App\Core\Database;
use App\Helpers\StatusSolicitacao;
use App\Repositories\SolicitacaoRepository;
use App\Repositories\HistoricoRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\AuditoriaRepository;

class TransicaoInvalidaException extends \RuntimeException {}

/**
 * Orquestra as transições de status das solicitações:
 * PENDENTE_ACEITE -> ACEITA/RECUSADA -> AGUARDANDO_COORDENADOR -> APROVADA/REPROVADA
 * -> PENDENTE_FLIT -> LANCADA_FLIT (ou CANCELADA a qualquer momento antes da aprovação)
 *
 * Toda regra crítica é validada aqui no backend.
 */
class FluxoAprovacaoService
{
    private SolicitacaoRepository $solicitacaoRepo;
    private HistoricoRepository $historicoRepo;
    private UsuarioRepository $usuarioRepo;
    private AuditoriaRepository $auditoriaRepo;
    private NotificacaoService $notificacaoService;

    public function __construct()
    {
        $this->solicitacaoRepo = new SolicitacaoRepository();
        $this->historicoRepo = new HistoricoRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->auditoriaRepo = new AuditoriaRepository();
        $this->notificacaoService = new NotificacaoService();
    }

    /** Aceite pelo colaborador que recebeu a troca/passagem. */
    public function aceitar(int $solicitacaoId, int $usuarioLogadoId): void
    {
        $sol = $this->solicitacaoRepo->buscarPorId($solicitacaoId);
        if (!$sol || $sol['status'] !== StatusSolicitacao::PENDENTE_ACEITE) {
            throw new TransicaoInvalidaException('Esta solicitação não está mais pendente de aceite.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->solicitacaoRepo->atualizarStatus($solicitacaoId, StatusSolicitacao::AGUARDANDO_COORDENADOR);
            $this->historicoRepo->registrar($solicitacaoId, $usuarioLogadoId, 'Colaborador aceitou a solicitação', StatusSolicitacao::PENDENTE_ACEITE, StatusSolicitacao::AGUARDANDO_COORDENADOR);
            $this->auditoriaRepo->registrar($usuarioLogadoId, 'ACEITAR_SOLICITACAO', 'solicitacoes', $solicitacaoId);

            $this->notificacaoService->enviar((int)$sol['solicitante_id'], "Sua solicitação {$sol['codigo']} foi aceita e enviada ao coordenador.", $this->linkPara($sol));

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Recusa pelo colaborador que recebeu a troca/passagem — libera a vaga do limite automaticamente. */
    public function recusar(int $solicitacaoId, int $usuarioLogadoId, ?string $observacao = null): void
    {
        $sol = $this->solicitacaoRepo->buscarPorId($solicitacaoId);
        if (!$sol || $sol['status'] !== StatusSolicitacao::PENDENTE_ACEITE) {
            throw new TransicaoInvalidaException('Esta solicitação não está mais pendente de aceite.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->solicitacaoRepo->atualizarStatus($solicitacaoId, StatusSolicitacao::RECUSADA);
            $this->historicoRepo->registrar($solicitacaoId, $usuarioLogadoId, 'Colaborador recusou a solicitação', StatusSolicitacao::PENDENTE_ACEITE, StatusSolicitacao::RECUSADA, $observacao);
            $this->auditoriaRepo->registrar($usuarioLogadoId, 'RECUSAR_SOLICITACAO', 'solicitacoes', $solicitacaoId);

            $this->notificacaoService->enviar((int)$sol['solicitante_id'], "Sua solicitação {$sol['codigo']} foi recusada.", $this->linkPara($sol));

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Aprovação pelo coordenador — move para pendente de lançamento no FLIT. */
    public function aprovar(int $solicitacaoId, int $coordenadorId): void
    {
        $sol = $this->solicitacaoRepo->buscarPorId($solicitacaoId);
        if (!$sol || $sol['status'] !== StatusSolicitacao::AGUARDANDO_COORDENADOR) {
            throw new TransicaoInvalidaException('Esta solicitação não está aguardando aprovação do coordenador.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->solicitacaoRepo->atualizarStatus($solicitacaoId, StatusSolicitacao::APROVADA);
            $this->historicoRepo->registrar($solicitacaoId, $coordenadorId, 'Coordenador aprovou a solicitação', StatusSolicitacao::AGUARDANDO_COORDENADOR, StatusSolicitacao::APROVADA);

            $this->solicitacaoRepo->definirFlitPendente($solicitacaoId);
            $this->solicitacaoRepo->atualizarStatus($solicitacaoId, StatusSolicitacao::PENDENTE_FLIT);
            $this->historicoRepo->registrar($solicitacaoId, $coordenadorId, 'Solicitação encaminhada para lançamento no FLIT', StatusSolicitacao::APROVADA, StatusSolicitacao::PENDENTE_FLIT);

            $this->auditoriaRepo->registrar($coordenadorId, 'APROVAR_SOLICITACAO', 'solicitacoes', $solicitacaoId);

            $this->notificacaoService->enviar((int)$sol['solicitante_id'], "Sua solicitação {$sol['codigo']} foi aprovada pelo coordenador.", $this->linkPara($sol));

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Reprovação pelo coordenador — motivo obrigatório, libera a vaga do limite. */
    public function reprovar(int $solicitacaoId, int $coordenadorId, string $motivo): void
    {
        if (trim($motivo) === '') {
            throw new TransicaoInvalidaException('Motivo da reprovação é obrigatório.');
        }
        $sol = $this->solicitacaoRepo->buscarPorId($solicitacaoId);
        if (!$sol || $sol['status'] !== StatusSolicitacao::AGUARDANDO_COORDENADOR) {
            throw new TransicaoInvalidaException('Esta solicitação não está aguardando aprovação do coordenador.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->solicitacaoRepo->atualizarStatus($solicitacaoId, StatusSolicitacao::REPROVADA, $motivo);
            $this->historicoRepo->registrar($solicitacaoId, $coordenadorId, 'Coordenador reprovou a solicitação', StatusSolicitacao::AGUARDANDO_COORDENADOR, StatusSolicitacao::REPROVADA, $motivo);
            $this->auditoriaRepo->registrar($coordenadorId, 'REPROVAR_SOLICITACAO', 'solicitacoes', $solicitacaoId, null, ['motivo' => $motivo]);

            $this->notificacaoService->enviar((int)$sol['solicitante_id'], "Sua solicitação {$sol['codigo']} foi reprovada pelo coordenador.", $this->linkPara($sol));

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Cancelamento pelo próprio solicitante, permitido apenas antes da aprovação do coordenador. */
    public function cancelar(int $solicitacaoId, int $usuarioLogadoId): void
    {
        $sol = $this->solicitacaoRepo->buscarPorId($solicitacaoId);
        if (!$sol) {
            throw new TransicaoInvalidaException('Solicitação não encontrada.');
        }
        if ((int)$sol['solicitante_id'] !== $usuarioLogadoId) {
            throw new TransicaoInvalidaException('Apenas o solicitante pode cancelar esta solicitação.');
        }
        if (!in_array($sol['status'], [StatusSolicitacao::PENDENTE_ACEITE, StatusSolicitacao::ACEITA, StatusSolicitacao::AGUARDANDO_COORDENADOR], true)) {
            throw new TransicaoInvalidaException('Esta solicitação não pode mais ser cancelada.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $statusAnterior = $sol['status'];
            $this->solicitacaoRepo->atualizarStatus($solicitacaoId, StatusSolicitacao::CANCELADA);
            $this->historicoRepo->registrar($solicitacaoId, $usuarioLogadoId, 'Solicitante cancelou a solicitação', $statusAnterior, StatusSolicitacao::CANCELADA);
            $this->auditoriaRepo->registrar($usuarioLogadoId, 'CANCELAR_SOLICITACAO', 'solicitacoes', $solicitacaoId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Marca uma solicitação como lançada no FLIT — nunca permite lançar duas vezes. */
    public function marcarLancadaFlit(int $solicitacaoId, int $usuarioLogadoId): void
    {
        $sol = $this->solicitacaoRepo->buscarPorId($solicitacaoId);
        if (!$sol || $sol['flit_status'] !== 'PENDENTE_FLIT') {
            throw new TransicaoInvalidaException('Esta solicitação não está pendente de lançamento no FLIT (pode já ter sido lançada).');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->solicitacaoRepo->marcarLancadaFlit($solicitacaoId, $usuarioLogadoId);
            $this->historicoRepo->registrar($solicitacaoId, $usuarioLogadoId, 'Coordenador marcou como lançado no FLIT', StatusSolicitacao::PENDENTE_FLIT, StatusSolicitacao::LANCADA_FLIT);
            $this->auditoriaRepo->registrar($usuarioLogadoId, 'LANCAR_FLIT', 'solicitacoes', $solicitacaoId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /** Lançamento em lote — cada item é processado individualmente; falhas isoladas não interrompem o lote. */
    public function marcarLoteLancadoFlit(array $solicitacaoIds, int $usuarioLogadoId): array
    {
        $sucesso = [];
        $falhas = [];
        foreach ($solicitacaoIds as $id) {
            try {
                $this->marcarLancadaFlit((int)$id, $usuarioLogadoId);
                $sucesso[] = $id;
            } catch (\Throwable $e) {
                $falhas[$id] = $e->getMessage();
            }
        }
        return ['sucesso' => $sucesso, 'falhas' => $falhas];
    }

    private function linkPara(array $sol): string
    {
        return $sol['tipo'] === 'TROCA' ? 'trocas_ver.php?id=' . $sol['id'] : 'passagens_ver.php?id=' . $sol['id'];
    }
}
