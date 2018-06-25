<?php

require __DIR__ . '/../vendor/autoload.php';


use Api\Models\Offering;
use Api\Models\Branch;
use Api\Models\ClassModel;

use Api\Services\SearchService AS Searcher;

use Api\Controllers\HomeController;
use Api\Controllers\OfferingController;
use Api\Controllers\BranchController;
use Api\Controllers\SearchController;
use Api\Controllers\ClassController;
use Api\Controllers\CategoryController;

$container = new Pimple();

$container['config'] = $container->share(function () {
       return require(__DIR__ . '/config/config.php');
});

$container['db'] = $container->share(function ($c) 
{
  $dsn = "mysql:dbname={$c['config']['connection']['database']};host={$c['config']['connection']['hostname']}";
  $user = $c['config']['connection']['username'];
  $password = $c['config']['connection']['password'];
  try {
      $db = new PDO($dsn, $user, $password);
  } catch (PDOException $e) {
      echo 'Connection failed: ' . $e->getMessage();
  }
  return $db;
});

$container['Api\Models\Offering'] = $container->share(function ($c) {
    return new Offering($c['db']);
});

$container['Api\Models\Branch'] = $container->share(function ($c) {
    return new Branch($c['db']);
});

$container['Api\Models\ClassModel'] = $container->share(function ($c) {
    return new ClassModel($c['db']);
});

/* SERVICES */

$container['Api\Services\SearchService'] = $container->share(function ($c) {
    return new Searcher($c['config']['search'], array("offerings" => $c['Api\Models\Offering']));
});


/* CONTROLLERS */

$container['Api\Controllers\HomeController'] = $container->share(function ($c) {
    return new HomeController();
});

$container['Api\Controllers\OfferingController'] = $container->share(function ($c) {
    return new OfferingController($c['Api\Models\Offering']);
});

$container['Api\Controllers\BranchController'] = $container->share(function ($c) {
    return new BranchController($c['Api\Models\Branch']);
});

$container['Api\Controllers\ClassController'] = $container->share(function ($c) {
    return new ClassController($c['Api\Models\ClassModel']);
});

$container['Api\Controllers\CategoryController'] = $container->share(function ($c) {
    return new CategoryController($c['Api\Models\Offering']);
});

$container['Api\Controllers\SearchController'] = $container->share(function ($c) {
    return new SearchController($c['Api\Services\SearchService']);
});

return $container;
