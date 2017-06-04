<?php

use DrupalFinder\DrupalFinder;
use Drupal\Console\Launcher\Utils\Colors;
use Drupal\Console\Launcher\Utils\Launcher;
use Drupal\Console\Launcher\Command\SelfUpdateCommand;

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
$version = '1.0.0-rc20';
$showVersion = false;
$debug = false;

if ($argc>1) {
    $command = $argv[1];
}

foreach ($argv as $value) {
    if (substr($value, 0, 7) == "--root=") {
        $root = substr($value, 7);
    }
    if (substr($value, 0, 9) == "--version") {
        $showVersion = true;
    }
    if (substr($value, 0, 7) == "--debug") {
        $debug = true;
    }
}
if ($showVersion || $debug) {
    echo Colors::GREEN . 'Drupal Console Launcher' . Colors::NONE . ' version ' . Colors::YELLOW . $version . Colors::NONE . PHP_EOL;
}
if ($debug) {
    echo Colors::GREEN . 'Launcher path: ' . Colors::YELLOW . $argv[0] . Colors::NONE . PHP_EOL . PHP_EOL;
}

$drupalFinder = new DrupalFinder();
$drupalFinder->locateRoot($root);
$composerRoot = $drupalFinder->getComposerRoot();
$drupalRoot = $drupalFinder->getDrupalRoot();
$isValidDrupal = ($composerRoot && $drupalRoot)?true:false;

if ($command === 'self-update' || $command === 'selfupdate') {
    $selfUpdateCommand = new SelfUpdateCommand();
    $selfUpdateCommand->run($version, $isValidDrupal, $composerRoot);
}

if ($isValidDrupal) {
    $launcher = new Launcher();
    if ($launcher->launch($drupalFinder)) {
        exit(0);
    }
    echo 'Could not find DrupalConsole in the current site (' . $root . ').' .
        PHP_EOL;
    echo 'Please execute: composer require drupal/console:~1.0' . PHP_EOL;
    exit(1);
}

if (file_exists($root.'/composer.json')) {
    echo 'Seems like there is an error with your composer.json file,' . PHP_EOL;
    echo 'Please execute: composer validate' . PHP_EOL;
} else {
    echo 'The drupal command should be run from within a Drupal project.' . PHP_EOL;
    echo 'See the documentation page about the Launcher:' . PHP_EOL;
    echo 'https://docs.drupalconsole.com/en/getting/launcher.html' . PHP_EOL . PHP_EOL;
}
exit(1);
