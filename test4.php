<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$req = \Illuminate\Http\Request::create('/api/penjualan/123', 'PUT', ['_method' => 'PUT', 'lampiran' => 'file']);
$route = new \Illuminate\Routing\Route('PUT', '/api/penjualan/{id}', []);
$route->bind($req);
$req->setRouteResolver(function() use ($route) { return $route; });
var_dump($req->all());
