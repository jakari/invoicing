<?php

$loader = require __DIR__.'/../autoload.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

$container = $kernel->getContainer();

return [
    'environments' => [
        'default_database' => 'dev',
        'dev' => [
            'adapter' => 'pgsql',
            'host' => $container->getParameter('database_host'),
            'name' => $container->getParameter('database_name'),
            'user' => $container->getParameter('database_user'),
            'pass' => $container->getParameter('database_password'),
        ],
        'test' => [
            'adapter' => 'pgsql',
            'host' => $container->getParameter('database_host'),
            'name' => $container->getParameter('test_database_name'),
            'user' => $container->getParameter('database_user'),
            'pass' => $container->getParameter('database_password'),
        ]
    ],
    'paths' => ['migrations' => realpath(__DIR__ . '/../migrations')]
];
