<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class NotificacaoRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function criar(int $usuarioId, string $mensagem, ?string $link = null): void
    {
        $stmt = $this->db->prepare("INSERT INTO notificacoes (usuario_id, mensagem, link) VALUES (:u, :m, :l)");
        $stmt->execute(['u' => $usuarioId, 'm' => $mensagem, 'l' => $link]);
    }

    public function listarRecentes(int $usuarioId, int $limite = 10): array
    {
        $stmt = $this->db->prepare("SELECT * FROM notificacoes WHERE usuario_id = :u ORDER BY criado_em DESC LIMIT :lim");
        $stmt->bindValue('u', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue('lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function contarNaoLidas(int $usuarioId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificacoes WHERE usuario_id = :u AND lida = 0");
        $stmt->execute(['u' => $usuarioId]);
        return (int)$stmt->fetchColumn();
    }

    public function marcarTodasComoLidas(int $usuarioId): void
    {
        $stmt = $this->db->prepare("UPDATE notificacoes SET lida = 1 WHERE usuario_id = :u");
        $stmt->execute(['u' => $usuarioId]);
    }
}
