<?php
/**
 * Configurações gerais da aplicação
 */
return [
    'nome'            => 'Sistema de Troca e Passagem de Plantão',
    'timezone'        => 'America/Sao_Paulo',
    'session_name'    => 'trocaplantao_sess',
    'session_lifetime'=> 60 * 60 * 8, // 8 horas
    'base_url'        => '', // preenchido automaticamente pelo bootstrap se necessário
    'login_max_tentativas' => 5,
    'login_bloqueio_minutos' => 15,
];
