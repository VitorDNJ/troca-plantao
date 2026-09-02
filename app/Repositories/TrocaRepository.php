<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class TrocaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO trocas
                (solicitacao_id, meu_data, meu_turno,
                 outro_usuario_id, outro_data, outro_turno, periodo_outro_id)
                VALUES (:solicitacao_id, :meu_data, :meu_turno,
                        :outro_usuario_id, :outro_data, :outro_turno, :periodo_outro_id)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);
        return (int)$this->db->lastInsertId();
    }

    public function buscarPorSolicitacao(int $solicitacaoId): ?array
    {
        $sql = "SELECT t.*, u.nome AS outro_nome, u.matricula AS outro_matricula, s.nome AS outro_setor_nome
                FROM trocas t
                JOIN usuarios u ON u.id = t.outro_usuario_id
                JOIN setores s ON s.id = u.setor_id
                WHERE t.solicitacao_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $solicitacaoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
