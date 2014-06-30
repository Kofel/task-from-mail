<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = Symfony\Component\Yaml\Yaml::parse(__DIR__ . '/../config/config.yml');

$smartTask = new Redmine\SmartTask(
    new Redmine\Client(
        $config['redmine']['url'],
        $config['redmine']['token']
    )
);

var_dump($smartTask->create('Level Up Finance - zgłoszenia', 'Test ' . date('Y-m-d H:i'), 'Lorem ipsum hehe', 'admin@bergsystem.pl'));