<?php

use Packyderm\Frontend\Frontend;
use Packyderm\Frontend\ImageBuilder;

require __DIR__ . '/vendor/autoload.php';

// grpc throws some deprecations on PHP 8.2
error_reporting(E_ALL ^ E_DEPRECATED);

Frontend::run(function (ImageBuilder $builder) : void {

    $config = $builder->fetchConfigFile();
    echo "Config file:\n$config\n";

    // do some processing here

    $builder->buildImage('FROM ubuntu');
    echo "Built image ok\n";
});
