<?php

use Drupal\Console\Utils\ArgvInputReader;
use Drupal\Console\Utils\ConfigurationManager;
use Drupal\Console\Utils\DrupalConsoleLauncher;
use Drupal\Console\Utils\DrupalChecker;

set_time_limit(0);

$pharRoot = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
$pharAutoload = $pharRoot.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

if (file_exists($pharAutoload)) {
    $autoload = include_once $pharAutoload;
} else {
    echo 'Something goes wrong with your drupal.phar archive.'.PHP_EOL.
         'Try downloading again by executing from your terminal:'.PHP_EOL.
         'curl https://drupalconsole.com/installer -L -o drupal.phar'.PHP_EOL;
    exit(1);
}

$argvInputReader = new ArgvInputReader();
$configurationManager = new ConfigurationManager();
$configuration = $configurationManager->getConfiguration();

if ($options = $configuration->get('application.options')?:[]) {
    $argvInputReader->setOptionsFromConfiguration($options);
}

if ($target = $argvInputReader->get('target')) {
    $targetConfig = $configurationManager->readTarget($target);
    $argvInputReader->setOptionsFromTargetConfiguration($targetConfig);
}

$argvInputReader->setOptionsAsArgv();

//var_export($_SERVER['argv']);
//var_export($argvInputReader->getAll());

$currentDirectory = getcwd() . DIRECTORY_SEPARATOR;

/* validate if this is a valid drupal root */
$drupalChecker = new DrupalChecker();
$isValidDrupal = $drupalChecker->isValidRoot($argvInputReader->get('root'), true);

if ($argvInputReader->get('remote', false)) {
    /* execute command via ssh */
    exit(0);
}

if ($isValidDrupal) {
    $drupalConsoleLauncher = new DrupalConsoleLauncher();
    $drupalConsoleLauncher->launch($argvInputReader->get('root'));

    exit(0);
}
