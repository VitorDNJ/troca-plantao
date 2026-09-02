<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

Auth::requirePerfil(['ADMIN']);

$db = Database::connection();

$totais = $db->query("SELECT
    SUM(tipo='TROCA') AS total_trocas,
    SUM(tipo='PASSAGEM') AS total_passagens,
    SUM(status IN ('PENDENTE_ACEITE','ACEITA','AGUARDANDO_COORDENADOR')) AS pendentes,
    SUM(status = 'APROVADA') AS aprovadas,
    SUM(status IN ('RECUSADA','REPROVADA')) AS recusadas,
    SUM(possui_excecao = 1) AS com_excecao,
    SUM(flit_status = 'PENDENTE_FLIT') AS pendentes_flit,
    SUM(flit_status = 'LANCADA_FLIT') AS lancadas_flit
    FROM solicitacoes")->fetch();

$porSetor = $db->query("SELECT st.nome AS setor, COUNT(*) AS total
    FROM solicitacoes s JOIN usuarios u ON u.id = s.solicitante_id JOIN setores st ON st.id = u.setor_id
    GROUP BY st.nome ORDER BY total DESC")->fetchAll();

$porPeriodo = $db->query("SELECT p.nome AS periodo, COUNT(*) AS total
    FROM solicitacoes s JOIN periodos_controle p ON p.id = s.periodo_id
    GROUP BY p.nome ORDER BY p.data_inicial")->fetchAll();

$topColaboradores = $db->query("SELECT u.nome, COUNT(*) AS total
    FROM solicitacoes s JOIN usuarios u ON u.id = s.solicitante_id
    GROUP BY u.nome ORDER BY total DESC LIMIT 5")->fetchAll();

view('dashboard/admin', compact('totais', 'porSetor', 'porPeriodo', 'topColaboradores'));
