<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class SetorRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function listarTodos(bool $apenasAtivos = false): array
    {
        $sql = "SELECT * FROM setores";
        if ($apenasAtivos) $sql .= " WHERE status = 'ATIVO'";
        $sql .= " ORDER BY nome";
        return $this->db->query($sql)->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM setores WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function criar(string $nome): int
    {
        $stmt = $this->db->prepare("INSERT INTO setores (nome, status) VALUES (:nome, 'ATIVO')");
        $stmt->execute(['nome' => $nome]);
        return (int)$this->db->lastInsertId();
    }

    public function atualizar(int $id, string $nome): void
    {
        $stmt = $this->db->prepare("UPDATE setores SET nome = :nome WHERE id = :id");
        $stmt->execute(['nome' => $nome, 'id' => $id]);
    }

    public function alternarStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare("UPDATE setores SET status = :s WHERE id = :id");
        $stmt->execute(['s' => $status, 'id' => $id]);
    }

    /** IDs dos setores sob responsabilidade de um coordenador. */
    public function idsPorCoordenador(int $usuarioId): array
    {
        $sql = "SELECT DISTINCT s.id FROM setores s
                LEFT JOIN usuarios_setores us ON us.setor_id = s.id AND us.usuario_id = :id
                LEFT JOIN usuarios u ON u.setor_id = s.id AND u.id = :id2
                WHERE us.usuario_id IS NOT NULL OR u.id IS NOT NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $usuarioId, 'id2' => $usuarioId]);
        return array_map('intval', array_column($stmt->fetchAll(), 'id'));
    }
}
