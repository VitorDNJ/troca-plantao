<?php

namespace App\Repositories;

use App\Core\Database;
use App\Helpers\StatusSolicitacao;
use PDO;

class SolicitacaoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO solicitacoes
                (codigo, tipo, solicitante_id, periodo_id, status, motivo, observacao)
                VALUES (:codigo, :tipo, :solicitante_id, :periodo_id, :status, :motivo, :observacao)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);
        return (int)$this->db->lastInsertId();
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT s.*, u.nome AS solicitante_nome, u.matricula AS solicitante_matricula,
                       st.nome AS solicitante_setor_nome, u.funcao AS solicitante_funcao,
                       p.nome AS periodo_nome, p.data_inicial AS periodo_data_inicial, p.data_final AS periodo_data_final
                FROM solicitacoes s
                JOIN usuarios u ON u.id = s.solicitante_id
                JOIN setores st ON st.id = u.setor_id
                JOIN periodos_controle p ON p.id = s.periodo_id
                WHERE s.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function atualizarStatus(int $id, string $status, ?string $motivoReprovacao = null): void
    {
        $sql = "UPDATE solicitacoes SET status = :status, motivo_reprovacao = :motivo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status, 'motivo' => $motivoReprovacao, 'id' => $id]);
    }

    public function marcarExcecao(int $id, int $excecaoId): void
    {
        $stmt = $this->db->prepare("UPDATE solicitacoes SET possui_excecao = 1, excecao_id = :exc WHERE id = :id");
        $stmt->execute(['exc' => $excecaoId, 'id' => $id]);
    }

    public function definirFlitPendente(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE solicitacoes SET flit_status = 'PENDENTE_FLIT' WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function marcarLancadaFlit(int $id, int $usuarioId): void
    {
        $sql = "UPDATE solicitacoes
                SET flit_status = 'LANCADA_FLIT', flit_lancado_por = :u, flit_lancado_em = NOW(), status = :statusLancada
                WHERE id = :id AND flit_status != 'LANCADA_FLIT'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['u' => $usuarioId, 'id' => $id, 'statusLancada' => StatusSolicitacao::LANCADA_FLIT]);
    }

    /** Conta solicitações de um usuário/tipo/período que reservam limite (não finais negativas). */
    public function contarAtivasPorUsuarioTipoPeriodo(int $usuarioId, string $tipo, int $periodoId, ?int $ignorarId = null): int
    {
        $placeholders = implode(',', array_fill(0, count(StatusSolicitacao::CONTAM_LIMITE), '?'));
        $sql = "SELECT COUNT(*) FROM solicitacoes
                WHERE solicitante_id = ? AND tipo = ? AND periodo_id = ?
                AND status IN ($placeholders)";
        $params = array_merge([$usuarioId, $tipo, $periodoId], StatusSolicitacao::CONTAM_LIMITE);
        if ($ignorarId) {
            $sql .= " AND id != ?";
            $params[] = $ignorarId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function listarMinhas(int $usuarioId, ?string $tipo = null): array
    {
        $sql = "SELECT s.*, p.nome AS periodo_nome FROM solicitacoes s
                JOIN periodos_controle p ON p.id = s.periodo_id
                WHERE s.solicitante_id = :id";
        $params = ['id' => $usuarioId];
        if ($tipo) {
            $sql .= " AND s.tipo = :tipo";
            $params['tipo'] = $tipo;
        }
        $sql .= " ORDER BY s.criado_em DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Solicitações onde o usuário é o "outro colaborador" (aguardando resposta dele). */
    public function listarRecebidas(int $usuarioId): array
    {
        $sql = "SELECT s.*, p.nome AS periodo_nome, u.nome AS solicitante_nome, 'TROCA' AS origem
                FROM solicitacoes s
                JOIN trocas t ON t.solicitacao_id = s.id
                JOIN periodos_controle p ON p.id = s.periodo_id
                JOIN usuarios u ON u.id = s.solicitante_id
                WHERE t.outro_usuario_id = :id
                UNION ALL
                SELECT s.*, p.nome AS periodo_nome, u.nome AS solicitante_nome, 'PASSAGEM' AS origem
                FROM solicitacoes s
                JOIN passagens ps ON ps.solicitacao_id = s.id
                JOIN periodos_controle p ON p.id = s.periodo_id
                JOIN usuarios u ON u.id = s.solicitante_id
                WHERE ps.quem_recebeu_id = :id2
                ORDER BY criado_em DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $usuarioId, 'id2' => $usuarioId]);
        return $stmt->fetchAll();
    }

    /** Fila de aprovação do coordenador: status AGUARDANDO_COORDENADOR, filtrando por setores. */
    public function listarAguardandoCoordenador(array $setorIds): array
    {
        if (empty($setorIds)) return [];
        $in = implode(',', array_fill(0, count($setorIds), '?'));
        $sql = "SELECT s.*, u.nome AS solicitante_nome, u.matricula AS solicitante_matricula, st.nome AS setor_nome
                FROM solicitacoes s
                JOIN usuarios u ON u.id = s.solicitante_id
                JOIN setores st ON st.id = u.setor_id
                WHERE s.status = 'AGUARDANDO_COORDENADOR' AND st.id IN ($in)
                ORDER BY s.criado_em ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($setorIds);
        return $stmt->fetchAll();
    }

    public function listarPendentesFlit(array $setorIds): array
    {
        if (empty($setorIds)) return [];
        $in = implode(',', array_fill(0, count($setorIds), '?'));
        $sql = "SELECT s.*, u.nome AS solicitante_nome, u.matricula AS solicitante_matricula, st.nome AS setor_nome
                FROM solicitacoes s
                JOIN usuarios u ON u.id = s.solicitante_id
                JOIN setores st ON st.id = u.setor_id
                WHERE s.flit_status = 'PENDENTE_FLIT' AND st.id IN ($in)
                ORDER BY s.criado_em ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($setorIds);
        return $stmt->fetchAll();
    }

    /** Filtro amplo usado nos relatórios. */
    public function filtrar(array $filtros): array
    {
        $sql = "SELECT s.*, u.nome AS solicitante_nome, u.matricula AS solicitante_matricula,
                       st.nome AS setor_nome, p.nome AS periodo_nome
                FROM solicitacoes s
                JOIN usuarios u ON u.id = s.solicitante_id
                JOIN setores st ON st.id = u.setor_id
                JOIN periodos_controle p ON p.id = s.periodo_id
                WHERE 1=1";
        $params = [];

        if (!empty($filtros['periodo_id'])) {
            $sql .= " AND s.periodo_id = :periodo_id";
            $params['periodo_id'] = $filtros['periodo_id'];
        }
        if (!empty($filtros['setor_id'])) {
            $sql .= " AND st.id = :setor_id";
            $params['setor_id'] = $filtros['setor_id'];
        }
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND u.id = :usuario_id";
            $params['usuario_id'] = $filtros['usuario_id'];
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND s.tipo = :tipo";
            $params['tipo'] = $filtros['tipo'];
        }
        if (!empty($filtros['status'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $filtros['status'];
        }
        if (isset($filtros['flit_status']) && $filtros['flit_status'] !== '') {
            $sql .= " AND s.flit_status = :flit_status";
            $params['flit_status'] = $filtros['flit_status'];
        }
        if (isset($filtros['excecao']) && $filtros['excecao'] !== '') {
            $sql .= " AND s.possui_excecao = :excecao";
            $params['excecao'] = (int)$filtros['excecao'];
        }
        if (!empty($filtros['setor_ids_permitidos'])) {
            $in = implode(',', array_fill(0, count($filtros['setor_ids_permitidos']), '?'));
            $sql .= " AND st.id IN ($in)";
        }

        $sql .= " ORDER BY s.criado_em DESC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        if (!empty($filtros['setor_ids_permitidos'])) {
            $i = count($params) + 1;
            foreach ($filtros['setor_ids_permitidos'] as $sid) {
                $stmt->bindValue($i++, $sid);
            }
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function resumoPorUsuario(int $usuarioId, int $periodoId): array
    {
        $sql = "SELECT tipo, COUNT(*) as total FROM solicitacoes
                WHERE solicitante_id = :u AND periodo_id = :p
                AND status IN ('" . implode("','", StatusSolicitacao::CONTAM_LIMITE) . "')
                GROUP BY tipo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['u' => $usuarioId, 'p' => $periodoId]);
        $out = ['TROCA' => 0, 'PASSAGEM' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $out[$row['tipo']] = (int)$row['total'];
        }
        return $out;
    }

    public function contadoresDashboard(array $setorIds): array
    {
        if (empty($setorIds)) {
            return ['aguardando' => 0, 'pendentes_flit' => 0, 'trocas_periodo' => 0, 'passagens_periodo' => 0];
        }
        $in = implode(',', array_fill(0, count($setorIds), '?'));

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM solicitacoes s JOIN usuarios u ON u.id=s.solicitante_id
            WHERE s.status='AGUARDANDO_COORDENADOR' AND u.setor_id IN ($in)");
        $stmt->execute($setorIds);
        $aguardando = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM solicitacoes s JOIN usuarios u ON u.id=s.solicitante_id
            WHERE s.flit_status='PENDENTE_FLIT' AND u.setor_id IN ($in)");
        $stmt->execute($setorIds);
        $pendentesFlit = (int)$stmt->fetchColumn();

        return ['aguardando' => $aguardando, 'pendentes_flit' => $pendentesFlit];
    }
}
