<?php
/**
 * A aplicação vive dentro de /public. Este arquivo só existe para que o acesso
 * à raiz do projeto (ex.: http://localhost/troca-plantao/) leve o visitante
 * até lá, mesmo em servidores sem mod_rewrite habilitado.
 */
header('Location: public/', true, 302);
exit;
