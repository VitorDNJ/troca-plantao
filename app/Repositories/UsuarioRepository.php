<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class UsuarioRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function buscarPorMatricula(string $matricula): ?array
    {
        $sql = "SELECT u.*, p.codigo AS perfil_codigo, p.nome AS perfil_nome, s.nome AS setor_nome
                FROM usuarios u
                JOIN perfis p ON p.id = u.perfil_id
                JOIN setores s ON s.id = u.setor_id
                WHERE u.matricula = :matricula
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['matricula' => $matricula]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT u.*, p.codigo AS perfil_codigo, p.nome AS perfil_nome, s.nome AS setor_nome
                FROM usuarios u
                JOIN perfis p ON p.id = u.perfil_id
                JOIN setores s ON s.id = u.setor_id
                WHERE u.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function registrarTentativaFalha(int $id, int $tentativas, ?string $bloqueadoAte): void
    {
        $sql = "UPDATE usuarios SET tentativas_login = :t, bloqueado_ate = :b WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['t' => $tentativas, 'b' => $bloqueadoAte, 'id' => $id]);
    }

    public function atualizarSenha(int $id, string $hash, bool $forcarTroca = false): void
    {
        $sql = "UPDATE usuarios SET senha_hash = :h, trocar_senha_primeiro_acesso = :f WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['h' => $hash, 'f' => $forcarTroca ? 1 : 0, 'id' => $id]);
    }

    /** Pesquisa colaboradores por nome ou matrícula (para seleção em trocas/passagens). */
    public function pesquisar(string $termo, ?int $excluirId = null, int $limite = 15): array
    {
        // Placeholders nomeados não podem se repetir quando EMULATE_PREPARES = false,
        // por isso :termoNome e :termoMatricula em vez de um único :t reutilizado.
        $sql = "SELECT u.id, u.nome, u.matricula, s.nome AS setor_nome, u.funcao
                FROM usuarios u JOIN setores s ON s.id = u.setor_id
                WHERE u.status = 'ATIVO' AND (u.nome LIKE :termoNome OR u.matricula LIKE :termoMatricula)";
        if ($excluirId) {
            $sql .= " AND u.id != :excluirId";
        }
        $sql .= " ORDER BY u.nome LIMIT :lim";

        $stmt = $this->db->prepare($sql);
        $like = '%' . $termo . '%';
        $stmt->bindValue('termoNome', $like, PDO::PARAM_STR);
        $stmt->bindValue('termoMatricula', $like, PDO::PARAM_STR);
        if ($excluirId) {
            $stmt->bindValue('excluirId', $excluirId, PDO::PARAM_INT);
        }
        $stmt->bindValue('lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function listarTodos(?int $setorId = null, ?string $busca = null): array
    {
        $sql = "SELECT u.*, p.nome AS perfil_nome, p.codigo AS perfil_codigo, s.nome AS setor_nome
                FROM usuarios u
                JOIN perfis p ON p.id = u.perfil_id
                JOIN setores s ON s.id = u.setor_id
                WHERE 1=1";
        $params = [];
        if ($setorId) {
            $sql .= " AND u.setor_id = :setorId";
            $params['setorId'] = $setorId;
        }
        if ($busca) {
            $sql .= " AND (u.nome LIKE :buscaNome OR u.matricula LIKE :buscaMatricula OR u.cpf LIKE :buscaCpf)";
            $like = '%' . $busca . '%';
            $params['buscaNome'] = $like;
            $params['buscaMatricula'] = $like;
            $params['buscaCpf'] = $like;
        }
        $sql .= " ORDER BY u.nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function listarPerfis(): array
    {
        return $this->db->query("SELECT * FROM perfis ORDER BY id")->fetchAll();
    }

    public function matriculaOuCpfExiste(string $matricula, string $cpf, ?int $ignorarId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE (matricula = :m OR cpf = :c)";
        $params = ['m' => $matricula, 'c' => $cpf];
        if ($ignorarId) {
            $sql .= " AND id != :id";
            $params['id'] = $ignorarId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function criar(array $dados): int
    {
        $sql = "INSERT INTO usuarios (matricula, nome, cpf, email, setor_id, funcao, perfil_id, status, senha_hash, trocar_senha_primeiro_acesso)
                VALUES (:matricula, :nome, :cpf, :email, :setor_id, :funcao, :perfil_id, :status, :senha_hash, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);
        return (int)$this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): void
    {
        $sql = "UPDATE usuarios SET nome=:nome, cpf=:cpf, email=:email, setor_id=:setor_id,
                funcao=:funcao, perfil_id=:perfil_id, status=:status WHERE id=:id";
        $dados['id'] = $id;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($dados);
    }

    public function alternarStatus(int $id, string $novoStatus): void
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET status = :s WHERE id = :id");
        $stmt->execute(['s' => $novoStatus, 'id' => $id]);
    }

    /** Setores vinculados a um coordenador (além do setor principal). */
    public function setoresDoCoordenador(int $usuarioId): array
    {
        $sql = "SELECT s.id, s.nome FROM usuarios_setores us
                JOIN setores s ON s.id = us.setor_id
                WHERE us.usuario_id = :id
                UNION
                SELECT s.id, s.nome FROM usuarios u JOIN setores s ON s.id = u.setor_id WHERE u.id = :id2
                ORDER BY nome";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $usuarioId, 'id2' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public function vincularSetor(int $usuarioId, int $setorId): void
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO usuarios_setores (usuario_id, setor_id) VALUES (:u, :s)");
        $stmt->execute(['u' => $usuarioId, 's' => $setorId]);
    }

    public function desvincularTodosSetores(int $usuarioId): void
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios_setores WHERE usuario_id = :u");
        $stmt->execute(['u' => $usuarioId]);
    }
}
