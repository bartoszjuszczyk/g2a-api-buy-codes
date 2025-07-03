#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * File: cmd.php
 *
 * @author Bartosz Juszczyk <b.juszczyk@bjuszczyk.pl>
 * @copyright Copyright (C) 2025
 */
require __DIR__.'/vendor/autoload.php';

$app = new \Symfony\Component\Console\Application('G2A API Code Buy', '1.0.0');

$app->add(new \Juszczyk\Console\BuyCommand());

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['API_EMAIL', 'API_DOMAIN', 'API_HASH', 'API_CUSTOMER_SECRET']);


$app->run();