<?php

namespace App\Services;

use App\Repositories\NotificacaoRepository;

class NotificacaoService
{
    private NotificacaoRepository $repo;

    public function __construct()
    {
        $this->repo = new NotificacaoRepository();
    }

    public function enviar(int $usuarioId, string $mensagem, ?string $link = null): void
    {
        $this->repo->criar($usuarioId, $mensagem, $link);
    }
}
