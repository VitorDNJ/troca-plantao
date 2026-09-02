<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PassagemRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO passagens
                (solicitacao_id, quem_passou_id, quem_recebeu_id, data, hora_inicial, hora_final, turno)
                VALUES (:solicitacao_id, :quem_passou_id, :quem_recebeu_id, :data, :hora_inicial, :hora_final, :turno)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);
        return (int)$this->db->lastInsertId();
    }

    public function buscarPorSolicitacao(int $solicitacaoId): ?array
    {
        $sql = "SELECT ps.*, u.nome AS recebeu_nome, u.matricula AS recebeu_matricula, s.nome AS recebeu_setor_nome
                FROM passagens ps
                JOIN usuarios u ON u.id = ps.quem_recebeu_id
                JOIN setores s ON s.id = u.setor_id
                WHERE ps.solicitacao_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $solicitacaoId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
