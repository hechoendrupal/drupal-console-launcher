<?php

use Drupal\Console\Core\Bootstrap\DrupalConsoleCore;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Core\Utils\ArgvInputReader;
use Drupal\Console\Core\Utils\ConfigurationManager;
use Drupal\Console\Core\Utils\TranslatorManager;
use Drupal\Console\Core\Utils\DrupalFinder;
use Drupal\Console\Launcher\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

set_time_limit(0);
error_reporting(-1);

$pharRoot = dirname(__DIR__).DIRECTORY_SEPARATOR;
$pharAutoload = $pharRoot.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

if (file_exists($pharAutoload)) {
    $autoload = include_once $pharAutoload;
} else {
    echo ' Something is wrong with your drupal.phar archive.'.PHP_EOL.
        ' Try downloading again by executing from your terminal:'.PHP_EOL.
        ' curl https://drupalconsole.com/installer -L -o drupal.phar'.PHP_EOL;
    exit(1);
}

$launcherType = [
    'local' => new Drupal\Console\Launcher\Utils\LauncherLocal(),
    'ssh' => new Drupal\Console\Launcher\Utils\LauncherSsh(),
    'container' => new Drupal\Console\Launcher\Utils\LauncherContainer()
];

$output = new ConsoleOutput();
$input = new ArrayInput([]);
$io = new DrupalStyle($input, $output);

$argvInputReader = new ArgvInputReader();
$target = $argvInputReader->get('target', null);
$root = $argvInputReader->get('root', getcwd());
$debug = $argvInputReader->get('debug', false);
$command = $argvInputReader->get('command', false);

$drupalFinder = new DrupalFinder();
$drupalFinder->locateRoot($root);
$composerRoot = $drupalFinder->getComposerRoot();
$drupalRoot = $drupalFinder->getDrupalRoot();

$configurationManager = new ConfigurationManager();
$configurationManager->loadConfiguration($drupalFinder->getComposerRoot());
$configuration = $configurationManager->getConfiguration();

if ($options = $configuration->get('application.options') ?: []) {
    $argvInputReader->setOptionsFromConfiguration($options);
}

if ($target) {
    try{
        $targetOptions = $configurationManager->readTarget($target);
    }catch (Exception $e) {
        $io->error($e->getMessage());
        exit(1);
    }

    if ($targetOptions) {
        $argvInputReader->setOptionsFromTargetConfiguration($targetOptions);
        $argvInputReader->setOptionsAsArgv();
        $type = $targetOptions['type'];
        if ($type !== 'local') {
            $launcher = $launcherType[$type];
            $exitCode = $launcher->launch($targetOptions);
            exit($exitCode);
        } else {
            $root = $targetOptions['root'];
            $drupalFinder->locateRoot($root);
        }
    }
}

if ($debug || ($drupalFinder->isValidDrupal() && $command == 'list')) {
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

if ($drupalFinder->isValidDrupal()) {
    $launcher = $launcherType['local'];
    $exitCode = $launcher->launch($drupalFinder);
    if ($exitCode === FALSE) {
        $translator = new TranslatorManager();
        $translator->loadCoreLanguage(
            $configuration->get('application.language'),
            $pharRoot
        );

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

    exit($exitCode);
}

// Restore original argv values
$argvInputReader->restoreOriginalArgvValues();
// Boot Launcher as standalone.
$drupalConsole = new DrupalConsoleCore($pharRoot, null, $drupalFinder);
$container = $drupalConsole->boot();
$application = new Application($container);
$application->run();
