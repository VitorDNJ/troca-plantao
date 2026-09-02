<?php

namespace App\Services;

use App\Repositories\PeriodoRepository;
use App\Repositories\SolicitacaoRepository;
use App\Repositories\ExcecaoRepository;

/**
 * Serviço único responsável por toda a regra de cálculo de limites.
 *
 * LIMITE EFETIVO = LIMITE PADRÃO DO PERÍODO + EXCEÇÕES AUTORIZADAS
 * DISPONÍVEL     = LIMITE EFETIVO - SOLICITAÇÕES UTILIZADAS OU RESERVADAS
 *
 * Trocas e passagens possuem limites INDEPENDENTES.
 * O limite geral do período NUNCA é alterado por uma exceção individual —
 * a exceção soma apenas ao limite efetivo daquele colaborador específico.
 */
class LimiteService
{
    private PeriodoRepository $periodoRepo;
    private SolicitacaoRepository $solicitacaoRepo;
    private ExcecaoRepository $excecaoRepo;

    public const TROCA = 'TROCA';
    public const PASSAGEM = 'PASSAGEM';

    public function __construct()
    {
        $this->periodoRepo = new PeriodoRepository();
        $this->solicitacaoRepo = new SolicitacaoRepository();
        $this->excecaoRepo = new ExcecaoRepository();
    }

    /**
     * Identifica o período de controle a partir da DATA DO PLANTÃO (não da data de criação).
     */
    public function identificarPeriodoPorData(string $dataPlantao): ?array
    {
        return $this->periodoRepo->buscarPorData($dataPlantao);
    }

    /**
     * Calcula a situação de limite de um usuário para um tipo (TROCA/PASSAGEM) em um período.
     *
     * @return array{
     *   limite_padrao:int, extra_autorizado:int, limite_efetivo:int,
     *   utilizadas:int, disponivel:int, atingiu_limite:bool, em_excecao:bool
     * }
     */
    public function calcular(int $usuarioId, string $tipo, int $periodoId, ?int $ignorarSolicitacaoId = null): array
    {
        $periodo = $this->periodoRepo->buscarPorId($periodoId);
        if (!$periodo) {
            throw new \InvalidArgumentException('Período de controle não encontrado.');
        }

        $limitePadrao = $tipo === self::TROCA ? (int)$periodo['limite_trocas'] : (int)$periodo['limite_passagens'];
        $extraAutorizado = $this->excecaoRepo->totalExtraAutorizado($usuarioId, $tipo, $periodoId);
        $limiteEfetivo = $limitePadrao + $extraAutorizado;

        $utilizadas = $this->solicitacaoRepo->contarAtivasPorUsuarioTipoPeriodo($usuarioId, $tipo, $periodoId, $ignorarSolicitacaoId);
        $disponivel = max(0, $limiteEfetivo - $utilizadas);

        return [
            'periodo_id'       => $periodoId,
            'tipo'             => $tipo,
            'limite_padrao'    => $limitePadrao,
            'extra_autorizado' => $extraAutorizado,
            'limite_efetivo'   => $limiteEfetivo,
            'utilizadas'       => $utilizadas,
            'disponivel'       => $disponivel,
            'atingiu_limite'   => $utilizadas >= $limiteEfetivo,
            'em_excecao'       => $utilizadas > $limitePadrao,
        ];
    }

    /**
     * Verifica se o usuário PODE criar uma nova solicitação do tipo informado no período.
     * Toda validação crítica deve ser refeita aqui no backend, mesmo que o frontend já tenha checado.
     */
    public function podeSolicitar(int $usuarioId, string $tipo, int $periodoId, ?int $ignorarSolicitacaoId = null): bool
    {
        $situacao = $this->calcular($usuarioId, $tipo, $periodoId, $ignorarSolicitacaoId);
        return $situacao['disponivel'] > 0;
    }

    /**
     * Resumo dos dois tipos (troca/passagem) para exibição no painel do colaborador.
     */
    public function resumoUsuarioPeriodo(int $usuarioId, int $periodoId): array
    {
        return [
            'troca' => $this->calcular($usuarioId, self::TROCA, $periodoId),
            'passagem' => $this->calcular($usuarioId, self::PASSAGEM, $periodoId),
        ];
    }
}
