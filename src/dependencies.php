<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Medoo\Medoo;
use Slim\Views\PhpRenderer;

return function (App $app) {
    $container = $app->getContainer();

    // Khai báo dịch vụ Database (Medoo)
    $container->set('db', function (ContainerInterface $c) {
        require __DIR__ . '/../../config.php'; // Nạp file config CSDL
        require __DIR__ . '/../../Medoo.php';  // Nạp thư viện Medoo

        return new Medoo([
            'type'      => 'mysql',
            'host'      => 'localhost',
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASS,
            'charset'   => DB_CHARSET,
            'collation' => DB_COLLATION,
            'port'      => DB_PORT,
        ]);
    });

    // Khai báo dịch vụ View (để render file .phtml)
    $container->set('view', function (ContainerInterface $c) {
        return new PhpRenderer(__DIR__ . '/../templates/');
    });
};
