<?php

use DrupalFinder\DrupalFinder;
use Drupal\Console\Launcher\Utils\Launcher;
use Drupal\Console\Launcher\Command\SelfUpdate;

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
    include_once $autoloader;
} else {
    echo 'You must set up the project dependencies using `composer install`' . PHP_EOL;
    exit(1);
}

$root = getcwd();
$source = null;
$target = null;
$command = null;
$version = '1.0.0-rc18';

if ($argc>1) {
    $command = $argv[1];
}

foreach ($argv as $value) {
    if (substr($value, 0, 7) == "--root=") {
        $root = substr($value, 7);
    }
}

$drupalFinder = new DrupalFinder();
$drupalFinder->locateRoot($root);
$composerRoot = $drupalFinder->getComposerRoot();
$drupalRoot = $drupalFinder->getDrupalRoot();
$isValidDrupal = ($composerRoot && $drupalRoot)?true:false;

if ($command === 'self-update' || $command === 'selfupdate') {
    $selfUpdate = new SelfUpdate();
    $selfUpdate->run($version, $isValidDrupal, $composerRoot);
}

if ($isValidDrupal) {
    $launcher = new Launcher();
    if ($launcher->launch($composerRoot)) {
        exit(0);
    }
    echo 'Could not find DrupalConsole in the current site (' . $root . ').' .
      PHP_EOL;
    echo 'Please execute: composer require drupal/console:~1.0' . PHP_EOL;
    exit(1);
}
echo 'Drupal Console Launcher version ' . $version . PHP_EOL;
echo 'Path: ' . $argv[0] . PHP_EOL . PHP_EOL;
if (file_exists($root.'/composer.json')) {
    echo 'Seems like there is an error with your composer.json file,' . PHP_EOL;
    echo 'Please execute: composer validate' . PHP_EOL;
} else {
    echo 'The drupal command should be run from within a Drupal project.' . PHP_EOL;
    echo 'See the documentation page about the Launcher:' . PHP_EOL;
    echo 'https://docs.drupalconsole.com/en/getting/launcher.html' . PHP_EOL . PHP_EOL;
}
exit(1);
