<?php

declare(strict_types=1);

/**
 * Escapa saída para HTML (proteção XSS).
 */
function h(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Monta URL relativa à raiz pública do sistema.
 */
function url(string $path = ''): string
{
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
    // Normaliza quando estamos dentro de /public
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function old(string $key, $default = '')
{
    $old = \App\Core\Session::flash('_old');
    return h($old[$key] ?? $default);
}

function setOld(array $data): void
{
    \App\Core\Session::flash('_old', $data);
}

function flashMessage(string $tipo, string $mensagem): void
{
    \App\Core\Session::flash('_msg', ['tipo' => $tipo, 'mensagem' => $mensagem]);
}

function getFlashMessage(): ?array
{
    return \App\Core\Session::flash('_msg');
}

function formatarData(?string $data): string
{
    if (!$data) return '-';
    $ts = strtotime($data);
    return $ts ? date('d/m/Y', $ts) : '-';
}

function formatarDataHora(?string $data): string
{
    if (!$data) return '-';
    $ts = strtotime($data);
    return $ts ? date('d/m/Y H:i', $ts) : '-';
}

function formatarHora(?string $hora): string
{
    if (!$hora) return '-';
    return substr($hora, 0, 5);
}

function ipCliente(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
}

function inputPost(string $key, $default = null)
{
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default;
}

function inputGet(string $key, $default = null)
{
    return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default;
}

/**
 * Renderiza uma view dentro do layout padrão.
 */
function view(string $viewPath, array $dados = []): void
{
    $root = dirname(__DIR__, 2);
    extract($dados, EXTR_SKIP);
    $conteudoView = $root . '/views/' . $viewPath . '.php';

    if (!is_file($conteudoView)) {
        http_response_code(500);
        die('View não encontrada: ' . h($viewPath));
    }

    require $root . '/views/partials/header.php';
    require $conteudoView;
    require $root . '/views/partials/footer.php';
}

function viewSemLayout(string $viewPath, array $dados = []): void
{
    $root = dirname(__DIR__, 2);
    extract($dados, EXTR_SKIP);
    require $root . '/views/' . $viewPath . '.php';
}

function statusBadge(string $status): string
{
    $label = \App\Helpers\StatusSolicitacao::label($status);
    $cor = \App\Helpers\StatusSolicitacao::corBadge($status);
    return '<span class="badge bg-' . $cor . '">' . h($label) . '</span>';
}

function jsonResponse($dados, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}
