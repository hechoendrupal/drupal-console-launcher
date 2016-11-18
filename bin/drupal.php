<?php

use DrupalFinder\DrupalFinder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Drupal\Console\Bootstrap\DrupalConsoleCore;
use Drupal\Console\LauncherApplication;
use Drupal\Console\Style\DrupalStyle;
use Drupal\Console\Utils\ArgvInputReader;
use Drupal\Console\Utils\Remote;


set_time_limit(0);

$pharRoot = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
$pharAutoload = $pharRoot.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

if (file_exists($pharAutoload)) {
    $autoload = include_once $pharAutoload;
} else {
    echo ' Something goes wrong with your drupal.phar archive.'.PHP_EOL.
         ' Try downloading again by executing from your terminal:'.PHP_EOL.
         ' curl https://drupalconsole.com/installer -L -o drupal.phar'.PHP_EOL;

    exit(1);
}

$drupalConsole = new DrupalConsoleCore($pharRoot);
$container = $drupalConsole->boot();

$argvInputReader = new ArgvInputReader();

$configurationManager = $container->get('console.configuration_manager');

$configuration = $configurationManager->getConfiguration();

$translator = $container->get('console.translator_manager');

if ($options = $configuration->get('application.options') ?: []) {
    $argvInputReader->setOptionsFromConfiguration($options);
}

if ($target = $argvInputReader->get('target')) {
    $targetConfig = $container->get('console.configuration_manager')
        ->readTarget($target);
    $argvInputReader->setOptionsFromTargetConfiguration($targetConfig);
}

$argvInputReader->setOptionsAsArgv();

$output = new ConsoleOutput();

if ($argvInputReader->get('remote', false)) {

    $input = new ArgvInput();

    $remote = $container->get('console.remote');
    $commandName = $argvInputReader->get('command', false);

    $remoteResult = $remote->executeCommand(
        $commandName,
        $target,
        $targetConfig,
        $input->__toString(),
        $configurationManager->getHomeDirectory()
    );

    $output->writeln($remoteResult);
    /*
     *  Execute command via ssh
     *  Relocate remote execution to this project
     */
    exit(0);
}


$input = new ArrayInput([]);
$io = new DrupalStyle($input, $output);

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
    $launch = $drupalConsoleLauncher->launch($composerRoot);

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
$application = new LauncherApplication($container);
$application->setDefaultCommand('about');
$application->run();
