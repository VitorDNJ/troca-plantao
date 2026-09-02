<?php

namespace App\Services;

use App\Core\Database;
use App\Repositories\ExcecaoRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\AuditoriaRepository;

class ExcecaoService
{
    private ExcecaoRepository $excecaoRepo;
    private UsuarioRepository $usuarioRepo;
    private AuditoriaRepository $auditoriaRepo;
    private CodigoService $codigoService;
    private NotificacaoService $notificacaoService;

    public function __construct()
    {
        $this->excecaoRepo = new ExcecaoRepository();
        $this->usuarioRepo = new UsuarioRepository();
        $this->auditoriaRepo = new AuditoriaRepository();
        $this->codigoService = new CodigoService();
        $this->notificacaoService = new NotificacaoService();
    }

    public function solicitar(int $usuarioId, int $setorId, int $periodoId, string $tipo, string $justificativa, ?int $solicitacaoOrigemId = null): array
    {
        $db = Database::connection();
        $db->beginTransaction();
        try {
            $codigo = $this->codigoService->gerar('EXC');
            $id = $this->excecaoRepo->criar([
                'codigo' => $codigo,
                'usuario_id' => $usuarioId,
                'periodo_id' => $periodoId,
                'setor_id' => $setorId,
                'tipo' => $tipo,
                'quantidade_extra' => 1,
                'justificativa' => $justificativa,
                'solicitacao_origem_id' => $solicitacaoOrigemId,
            ]);
            $this->auditoriaRepo->registrar($usuarioId, 'SOLICITAR_EXCECAO', 'excecoes_limite', $id);
            $db->commit();
            return ['id' => $id, 'codigo' => $codigo];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function autorizar(int $excecaoId, int $coordenadorId): void
    {
        $exc = $this->excecaoRepo->buscarPorId($excecaoId);
        if (!$exc || $exc['status'] !== 'PENDENTE') {
            throw new \RuntimeException('Esta solicitação de exceção não está mais pendente.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->excecaoRepo->autorizar($excecaoId, $coordenadorId);
            $this->auditoriaRepo->registrar($coordenadorId, 'AUTORIZAR_EXCECAO', 'excecoes_limite', $excecaoId, ['status' => 'PENDENTE'], ['status' => 'AUTORIZADA']);
            $this->notificacaoService->enviar((int)$exc['usuario_id'], "Sua solicitação de exceção {$exc['codigo']} foi autorizada.");
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function negar(int $excecaoId, int $coordenadorId, string $motivo): void
    {
        $exc = $this->excecaoRepo->buscarPorId($excecaoId);
        if (!$exc || $exc['status'] !== 'PENDENTE') {
            throw new \RuntimeException('Esta solicitação de exceção não está mais pendente.');
        }

        $db = Database::connection();
        $db->beginTransaction();
        try {
            $this->excecaoRepo->negar($excecaoId, $coordenadorId, $motivo);
            $this->auditoriaRepo->registrar($coordenadorId, 'NEGAR_EXCECAO', 'excecoes_limite', $excecaoId, ['status' => 'PENDENTE'], ['status' => 'NEGADA', 'motivo' => $motivo]);
            $this->notificacaoService->enviar((int)$exc['usuario_id'], "Sua solicitação de exceção {$exc['codigo']} foi negada.");
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
