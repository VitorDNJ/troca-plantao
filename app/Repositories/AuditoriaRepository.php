<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class AuditoriaRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function registrar(?int $usuarioId, string $acao, string $entidade, ?int $entidadeId, $dadosAnteriores = null, $dadosNovos = null): void
    {
        $sql = "INSERT INTO logs_auditoria (usuario_id, acao, entidade, entidade_id, dados_anteriores, dados_novos, ip)
                VALUES (:u, :acao, :ent, :eid, :da, :dn, :ip)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'u' => $usuarioId,
            'acao' => $acao,
            'ent' => $entidade,
            'eid' => $entidadeId,
            'da' => $dadosAnteriores !== null ? json_encode($dadosAnteriores, JSON_UNESCAPED_UNICODE) : null,
            'dn' => $dadosNovos !== null ? json_encode($dadosNovos, JSON_UNESCAPED_UNICODE) : null,
            'ip' => ipCliente(),
        ]);
    }

    public function listar(array $filtros = [], int $limite = 200): array
    {
        $sql = "SELECT l.*, u.nome AS usuario_nome FROM logs_auditoria l
                LEFT JOIN usuarios u ON u.id = l.usuario_id WHERE 1=1";
        $params = [];
        if (!empty($filtros['entidade'])) {
            $sql .= " AND l.entidade = :entidade";
            $params['entidade'] = $filtros['entidade'];
        }
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND l.usuario_id = :usuario_id";
            $params['usuario_id'] = $filtros['usuario_id'];
        }
        $sql .= " ORDER BY l.criado_em DESC LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue('lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
