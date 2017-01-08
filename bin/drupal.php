<?php

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use DrupalFinder\DrupalFinder;
use Drupal\Console\Core\Bootstrap\DrupalConsoleCore;
use Drupal\Console\Launcher\Application;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Core\Utils\ArgvInputReader;
use Drupal\Console\Core\Utils\ConfigurationManager;
use Drupal\Console\Launcher\Utils\Remote;

set_time_limit(0);

$pharRoot = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
$pharAutoload = $pharRoot.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

if (file_exists($pharAutoload)) {
    $autoload = include_once $pharAutoload;
} else {
    echo ' Something is wrong with your drupal.phar archive.'.PHP_EOL.
         ' Try downloading again by executing from your terminal:'.PHP_EOL.
         ' curl https://drupalconsole.com/installer -L -o drupal.phar'.PHP_EOL;

    exit(1);
}

$argvInputReader = new ArgvInputReader();
$drupalConsole = new DrupalConsoleCore($pharRoot);
$container = $drupalConsole->boot();

/* @var ConfigurationManager  $configurationManager */
$configurationManager = $container->get('console.configuration_manager');

$configuration = $configurationManager->getConfiguration();

$translator = $container->get('console.translator_manager');

if ($options = $configuration->get('application.options') ?: []) {
    $argvInputReader->setOptionsFromConfiguration($options);
}
$targetConfig = [];
if ($target = $argvInputReader->get('target')) {
    $targetConfig = $container->get('console.configuration_manager')
        ->readTarget($target);
    $argvInputReader->setOptionsFromTargetConfiguration($targetConfig);
}

$argvInputReader->setOptionsAsArgv();

$output = new ConsoleOutput();
$input = new ArrayInput([]);
$io = new DrupalStyle($input, $output);

if ($argvInputReader->get('remote', false)) {
    $commandInput = new ArgvInput();

    /* @var Remote $remote */
    $remote = $container->get('console.remote');
    $commandName = $argvInputReader->get('command', false);

    $remoteSuccess = $remote->executeCommand(
        $io,
        $commandName,
        $target,
        $targetConfig,
        $commandInput->__toString(),
        $configurationManager->getHomeDirectory()
    );

    exit($remoteSuccess?0:1);
}

$root = $argvInputReader->get('root');
if (!$root) {
    $root = getcwd();
}
$drupalFinder = new DrupalFinder();
$drupalFinder->locateRoot($root);
$composerRoot = $drupalFinder->getComposerRoot();
$drupalRoot = $drupalFinder->getDrupalRoot();

if ($composerRoot && $drupalRoot) {
    $drupalConsoleLauncher = $container->get('console.launcher');
    $launch = $drupalConsoleLauncher->launch($composerRoot, $autoload);

    if (!$launch) {
        $message = sprintf(
            $translator->trans('application.site.errors.not-installed'),
            $argvInputReader->get('root')
        );
        $io->error($message);

        $io->info(
            $translator->trans('application.site.errors.execute-composer')
        );

        $io->commentBlock(
            $configuration->get('application.composer.install-console')
        );

        exit(1);
    }

    exit(0);
}

$argvInputReader->restoreOriginalArgvValues();
$application = new Application($container);
$application->setDefaultCommand('about');
$application->run();
