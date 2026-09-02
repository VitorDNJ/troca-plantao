<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class HistoricoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function registrar(int $solicitacaoId, ?int $usuarioId, string $acao, ?string $statusAnterior, ?string $statusNovo, ?string $observacao = null): void
    {
        $sql = "INSERT INTO historico_solicitacoes
                (solicitacao_id, usuario_id, acao, status_anterior, status_novo, observacao, ip)
                VALUES (:sid, :uid, :acao, :ant, :novo, :obs, :ip)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'sid' => $solicitacaoId,
            'uid' => $usuarioId,
            'acao' => $acao,
            'ant' => $statusAnterior,
            'novo' => $statusNovo,
            'obs' => $observacao,
            'ip' => ipCliente(),
        ]);
    }

    public function listarPorSolicitacao(int $solicitacaoId): array
    {
        $sql = "SELECT h.*, u.nome AS usuario_nome
                FROM historico_solicitacoes h
                LEFT JOIN usuarios u ON u.id = h.usuario_id
                WHERE h.solicitacao_id = :id
                ORDER BY h.criado_em ASC, h.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $solicitacaoId]);
        return $stmt->fetchAll();
    }
}
