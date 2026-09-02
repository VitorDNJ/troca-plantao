<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PeriodoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function listarTodos(): array
    {
        return $this->db->query("SELECT * FROM periodos_controle ORDER BY data_inicial DESC")->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM periodos_controle WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Identifica o período de controle ao qual uma determinada data pertence.
     * Regra fundamental: o período da solicitação é definido pela DATA DO PLANTÃO.
     */
    public function buscarPorData(string $data): ?array
    {
        $sql = "SELECT * FROM periodos_controle
                WHERE :data BETWEEN data_inicial AND data_final
                AND status IN ('ATIVO','FUTURO')
                ORDER BY data_inicial LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['data' => $data]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function periodoAtivo(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM periodos_controle WHERE status = 'ATIVO' ORDER BY data_inicial DESC LIMIT 1");
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO periodos_controle
                (nome, data_inicial, data_final, limite_trocas, limite_passagens, status, regra_troca_entre_periodos, observacao, criado_por)
                VALUES (:nome, :data_inicial, :data_final, :limite_trocas, :limite_passagens, :status, :regra, :observacao, :criado_por)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);
        return (int)$this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): void
    {
        $sql = "UPDATE periodos_controle SET nome=:nome, data_inicial=:data_inicial, data_final=:data_final,
                limite_trocas=:limite_trocas, limite_passagens=:limite_passagens, status=:status,
                regra_troca_entre_periodos=:regra, observacao=:observacao WHERE id=:id";
        $dados['id'] = $id;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);
    }

    public function possuiSolicitacoesVinculadas(int $id): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM solicitacoes WHERE periodo_id = :id");
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
