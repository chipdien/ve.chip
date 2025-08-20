<?php
require_once __DIR__ . '/init.php';

die("Xin chào " . ($_SESSION['user']['nickname'] ?? 'Khách'));
// dump($_SESSION['user'] ?? null);