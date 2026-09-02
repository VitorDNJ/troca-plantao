<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ExcecaoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO excecoes_limite
                (codigo, usuario_id, periodo_id, setor_id, tipo, quantidade_extra, justificativa, solicitacao_origem_id, status)
                VALUES (:codigo, :usuario_id, :periodo_id, :setor_id, :tipo, :quantidade_extra, :justificativa, :solicitacao_origem_id, 'PENDENTE')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);
        return (int)$this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT e.*, u.nome AS usuario_nome, u.matricula AS usuario_matricula,
            a.nome AS autorizador_nome
            FROM excecoes_limite e
            JOIN usuarios u ON u.id = e.usuario_id
            LEFT JOIN usuarios a ON a.id = e.autorizado_por
            WHERE e.id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Soma de extras já AUTORIZADOS para um usuário/tipo/período (não altera limite geral do período). */
    public function totalExtraAutorizado(int $usuarioId, string $tipo, int $periodoId): int
    {
        $sql = "SELECT COALESCE(SUM(quantidade_extra),0) FROM excecoes_limite
                WHERE usuario_id = :u AND tipo = :t AND periodo_id = :p AND status = 'AUTORIZADA'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['u' => $usuarioId, 't' => $tipo, 'p' => $periodoId]);
        return (int)$stmt->fetchColumn();
    }

    public function listarPendentesPorSetores(array $setorIds): array
    {
        if (empty($setorIds)) return [];
        $in = implode(',', array_fill(0, count($setorIds), '?'));
        $sql = "SELECT e.*, u.nome AS usuario_nome, u.matricula AS usuario_matricula, s.nome AS setor_nome
                FROM excecoes_limite e
                JOIN usuarios u ON u.id = e.usuario_id
                JOIN setores s ON s.id = e.setor_id
                WHERE e.status = 'PENDENTE' AND e.setor_id IN ($in)
                ORDER BY e.criado_em ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($setorIds);
        return $stmt->fetchAll();
    }

    public function autorizar(int $id, int $autorizadorId): void
    {
        $sql = "UPDATE excecoes_limite SET status = 'AUTORIZADA', autorizado_por = :a, autorizado_em = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['a' => $autorizadorId, 'id' => $id]);
    }

    public function negar(int $id, int $autorizadorId, string $motivo): void
    {
        $sql = "UPDATE excecoes_limite SET status = 'NEGADA', autorizado_por = :a, autorizado_em = NOW(), motivo_negativa = :m WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['a' => $autorizadorId, 'm' => $motivo, 'id' => $id]);
    }

    public function relatorio(array $filtros): array
    {
        $sql = "SELECT e.*, u.nome AS usuario_nome, u.matricula AS usuario_matricula, s.nome AS setor_nome,
                       p.nome AS periodo_nome, a.nome AS autorizador_nome
                FROM excecoes_limite e
                JOIN usuarios u ON u.id = e.usuario_id
                JOIN setores s ON s.id = e.setor_id
                JOIN periodos_controle p ON p.id = e.periodo_id
                LEFT JOIN usuarios a ON a.id = e.autorizado_por
                WHERE e.status = 'AUTORIZADA'";
        $params = [];
        if (!empty($filtros['periodo_id'])) {
            $sql .= " AND e.periodo_id = :periodo_id";
            $params['periodo_id'] = $filtros['periodo_id'];
        }
        if (!empty($filtros['setor_id'])) {
            $sql .= " AND e.setor_id = :setor_id";
            $params['setor_id'] = $filtros['setor_id'];
        }
        $sql .= " ORDER BY e.autorizado_em DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
