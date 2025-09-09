<?php
require __DIR__.'/../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(dirname(__DIR__))->load();

$c = new Illuminate\Database\Capsule\Manager;
$c->addConnection([
  'driver'=>'mysql','host'=>env('DB_HOST','mysql'),'port'=>env('DB_PORT',3306),
  'database'=>env('DB_DATABASE','energeX'),'username'=>env('DB_USERNAME','app'),
  'password'=>env('DB_PASSWORD','app'),'charset'=>'utf8mb4','collation'=>'utf8mb4_unicode_ci',
]);
$c->setAsGlobal(); $c->bootEloquent();

foreach (glob(__DIR__.'/*.php') as $f) if (basename($f)!=='migrate.php') require $f;
echo "Migrations executed.\n";
