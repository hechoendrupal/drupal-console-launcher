<?php

use DrupalFinder\DrupalFinder;
use Drupal\Console\Launcher\Utils\Launcher;

set_time_limit(0);

$autoloaders = [
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../vendor/autoload.php'
];
foreach ($autoloaders as $file) {
    if (file_exists($file)) {
        $autoloader = $file;
        break;
    }
}
if (isset($autoloader)) {
    require_once $autoloader;
}
else {
    echo 'You must set up the project dependencies using `composer install`' . PHP_EOL;
    exit(1);
}

$root = getcwd();
$source = null;
$target = null;
foreach ($argv as $value) {
    if (substr($value, 0, 7) == "--root=") {
        $root = substr($value, 7);
    }
}

$drupalFinder = new DrupalFinder();
$drupalFinder->locateRoot($root);
$composerRoot = $drupalFinder->getComposerRoot();
$drupalRoot = $drupalFinder->getDrupalRoot();

if ($composerRoot && $drupalRoot) {
    $launcher = new Launcher();
    if ($launcher->launch($composerRoot)) {
        exit(0);
    }
}

echo 'Could not find Drupal in the current path.' . PHP_EOL;
exit(1);
