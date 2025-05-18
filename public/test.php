<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

// Загружаем .env
(new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

// Создаем и запускаем ядро
$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$kernel->boot();

// Получаем контейнер после запуска ядра
$container = $kernel->getContainer();

// Получаем Redis-кэш (по умолчанию `cache.app`)
$cache = $container->get('cache.app');

$item = $cache->getItem('manual_key');
if (!$item->isHit()) {
    $item->set('Stored manually at ' . time());
    $cache->save($item);
    echo "Сохранили в Redis: " . $item->get();
} else {
    echo "Получено из Redis: " . $item->get();
}
