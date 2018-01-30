<?php

use Drupal\Console\Core\Bootstrap\DrupalConsoleCore;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Core\Utils\ArgvInputReader;
use Drupal\Console\Core\Utils\ConfigurationManager;
use Drupal\Console\Core\Utils\DrupalFinder;
use Drupal\Console\Launcher\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

set_time_limit(0);
error_reporting(-1);

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
$target = $argvInputReader->get('target', null);
$root = $argvInputReader->get('root', getcwd());
$debug = $argvInputReader->get('debug', false);
$command = $argvInputReader->get('command', false);

$drupalFinder = new DrupalFinder();
$drupalFinder->locateRoot($root);
$composerRoot = $drupalFinder->getComposerRoot();
$drupalRoot = $drupalFinder->getDrupalRoot();
$isValidDrupal = ($composerRoot && $drupalRoot)?true:false;

$drupalConsole = new DrupalConsoleCore($pharRoot, null, $drupalFinder);
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

if ($target = $argvInputReader->get('target')) {
    $configurationManager->loadConfiguration($drupalFinder->getComposerRoot());
    $configurationManager->getSites();
    $options = $configurationManager->readTarget($target);
    if ($options) {
        if ($options['type'] != 'local') {
            $launcherType = 'console.launcher_' . $options['type'];
            if ($container->has($launcherType)) {
                $launcher = $container->get($launcherType);
                $launch = $launcher->launch($options);
                exit(0);
            }
        } else {
            $root = $options['root'];
            $drupalFinder = new DrupalFinder();
            $drupalFinder->locateRoot($root);
            $composerRoot = $drupalFinder->getComposerRoot();
            $drupalRoot = $drupalFinder->getDrupalRoot();
            $isValidDrupal = ($composerRoot && $drupalRoot)?true:false;
        }
    }
}

if ($debug || ($isValidDrupal && $command == 'list')) {
    $io->writeln(
        sprintf(
            '<info>%s</info> version <comment>%s</comment>',
            Application::NAME,
            Application::VERSION
        )
    );
}

if ($debug) {
    $io->writeln(
        sprintf(
            '<info>Launcher path:</info> <comment>%s</comment>',
            $argv[0]
        )
    );
}

if ($isValidDrupal) {
    $launcher = $container->get('console.launcher_local');
    $launch = $launcher->launch($drupalFinder);

    if (!$launch) {
        $message = sprintf(
            $translator->trans('application.site.errors.not-installed'),
            PHP_EOL . $drupalFinder->getComposerRoot()
        );
        $io->error($message);

        $io->info(
            $translator->trans('application.site.errors.execute-composer')
        );

        $io->commentBlock(
            'composer require drupal/console:~1.0 --prefer-dist --optimize-autoloader'
        );

        exit(1);
    }

    exit(0);
}

$argvInputReader->restoreOriginalArgvValues();
$application = new Application($container);
$application->run();
