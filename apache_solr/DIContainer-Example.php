<?php

/**
 * The bootstrap file creates and returns the container.
 */
require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\SearchController;
use App\Services\SolrService;

$container = new Pimple();

$container['config'] = $container->share(function () {
        return require(__DIR__ . '/config.php');
});

$container['Twig_Environment'] = $container->share(function () {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../src/Views');
        return new Twig_Environment($loader);
});

//services
$container['SolrService'] = $container->share(function ($c) {
    return new SolrService($c['config']);
});

//controllers
$container['App\Controllers\HomeController'] = $container->share(function ($c) {
    return new HomeController($c['Twig_Environment'],$c['SolrService']);
});

$container['App\Controllers\SearchController'] = $container->share(function ($c) {
    return new SearchController($c['SolrService']);
});

return $container;
