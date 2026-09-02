<?php
require_once __DIR__ . '/../app/Core/bootstrap.php';
(new \App\Controllers\AuthController())->logout();
