<?php

namespace App\Helpers;

/**
 * Status centralizados das solicitações de troca/passagem.
 * Nunca espalhar strings de status pelo sistema — usar sempre estas constantes.
 */
class StatusSolicitacao
{
    public const PENDENTE_ACEITE = 'PENDENTE_ACEITE';
    public const ACEITA = 'ACEITA';
    public const RECUSADA = 'RECUSADA';
    public const AGUARDANDO_COORDENADOR = 'AGUARDANDO_COORDENADOR';
    public const APROVADA = 'APROVADA';
    public const REPROVADA = 'REPROVADA';
    public const PENDENTE_FLIT = 'PENDENTE_FLIT';
    public const LANCADA_FLIT = 'LANCADA_FLIT';
    public const CANCELADA = 'CANCELADA';

    /** Status que RESERVAM/CONTAM para o limite do período. */
    public const CONTAM_LIMITE = [
        self::PENDENTE_ACEITE,
        self::ACEITA,
        self::AGUARDANDO_COORDENADOR,
        self::APROVADA,
        self::PENDENTE_FLIT,
        self::LANCADA_FLIT,
    ];

    /** Status finais que NÃO contam para o limite (liberam a vaga). */
    public const NAO_CONTAM_LIMITE = [
        self::RECUSADA,
        self::REPROVADA,
        self::CANCELADA,
    ];

    public const LABELS = [
        self::PENDENTE_ACEITE        => 'Pendente de aceite',
        self::ACEITA                 => 'Aceita pelo colaborador',
        self::RECUSADA               => 'Recusada pelo colaborador',
        self::AGUARDANDO_COORDENADOR => 'Aguardando coordenador',
        self::APROVADA               => 'Aprovada pelo coordenador',
        self::REPROVADA              => 'Reprovada pelo coordenador',
        self::PENDENTE_FLIT          => 'Pendente de lançamento no FLIT',
        self::LANCADA_FLIT           => 'Lançada no FLIT',
        self::CANCELADA              => 'Cancelada',
    ];

    public const CORES_BADGE = [
        self::PENDENTE_ACEITE        => 'secondary',
        self::ACEITA                 => 'info',
        self::RECUSADA               => 'danger',
        self::AGUARDANDO_COORDENADOR => 'warning',
        self::APROVADA               => 'primary',
        self::REPROVADA              => 'danger',
        self::PENDENTE_FLIT          => 'warning',
        self::LANCADA_FLIT           => 'success',
        self::CANCELADA              => 'dark',
    ];

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? $status;
    }

    public static function corBadge(string $status): string
    {
        return self::CORES_BADGE[$status] ?? 'secondary';
    }

    public static function contaParaLimite(string $status): bool
    {
        return in_array($status, self::CONTAM_LIMITE, true);
    }
}
