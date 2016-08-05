<?php

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Drupal\Console\Utils\ArgvInputReader;
use Drupal\Console\Utils\ConfigurationManager;
use Drupal\Console\Utils\DrupalConsoleLauncher;
use Drupal\Console\Utils\DrupalChecker;
use Drupal\Console\Utils\Translator;

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

$argvInputReader = new ArgvInputReader();
$configurationManager = new ConfigurationManager();
$configuration = $configurationManager->getConfiguration();
$translator = new Translator();
$translator->loadResource('en', $pharRoot);

if ($options = $configuration->get('application.options') ?: []) {
    $argvInputReader->setOptionsFromConfiguration($options);
}

if ($target = $argvInputReader->get('target')) {
    $targetConfig = $configurationManager->readTarget($target);
    $argvInputReader->setOptionsFromTargetConfiguration($targetConfig);
}

$argvInputReader->setOptionsAsArgv();

if ($argvInputReader->get('remote', false)) {
    /*
     *  Execute command via ssh
     *  Relocate remote execution to this project
     */
    exit(0);
}

$input = null;
$output = new ConsoleOutput();
$input = new ArrayInput([]);
$io = new SymfonyStyle($input, $output);

$drupalChecker = new DrupalChecker();
$isValidDrupal = $drupalChecker->isValidRoot($argvInputReader->get('root'), true);

if ($isValidDrupal) {
    $drupalConsoleLauncher = new DrupalConsoleLauncher();
    $launch = $drupalConsoleLauncher->launch($argvInputReader->get('root'));

    if (!$launch) {
        /* Read message from translation file. */
        $message = sprintf(
            $translator->trans('application.site.errors.not-installed'),
            $argvInputReader->get('root')
        );
        $io->error($message);

        $message = sprintf(
            '<info> %s</info>',
            $translator->trans('application.site.errors.execute-composer')
        );
        $io->writeln($message);

        $io->block(
            $configuration->get('application.composer.install-console'),
            null,
            'bg=yellow;fg=black',
            ' ',
            true
        );
        exit(1);
    }
}

$message = sprintf(
    $translator->trans('application.site.errors.directory'),
    $argvInputReader->get('root')
);

$io->warning($message);
exit(1);
