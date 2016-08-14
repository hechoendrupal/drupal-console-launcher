<?php

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Drupal\Console\Style\DrupalStyle;
use Drupal\Console\Application;
use Drupal\Console\Utils\ArgvInputReader;

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

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container, new FileLocator($pharRoot));
$loader->load('vendor/drupal/console-core/services.yml');
$loader->load('services.yml');

$argvInputReader = new ArgvInputReader();
$configuration = $container->get('console.configuration_manager')
    ->loadConfiguration($pharRoot)
    ->getConfiguration();

$translator = $container->get('console.translator_manager')
    ->loadCoreLanguage('en', $pharRoot);

if ($options = $configuration->get('application.options') ?: []) {
    $argvInputReader->setOptionsFromConfiguration($options);
}

if ($target = $argvInputReader->get('target')) {
    $targetConfig = $container->get('console.configuration_manager')
        ->readTarget($target);
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

$output = new ConsoleOutput();
$input = new ArrayInput([]);
$io = new DrupalStyle($input, $output);

$drupalChecker = $container->get('console.drupal_checker');
$isValidDrupal = $drupalChecker->isValidRoot($argvInputReader->get('root'), true);

if ($isValidDrupal) {
    $drupalConsoleLauncher = $container->get('console.launcher');
    $launch = $drupalConsoleLauncher->launch($argvInputReader->get('root'));

    if (!$launch) {
        /* Read message from translation file. */
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

$tags = $container->findTaggedServiceIds('console.command');
foreach ($tags as $name => $tags) {
    $command = $container->get($name);
    if (method_exists($command, 'setTranslator')) {
        $command->setTranslator($translator);
    }
    $application->add($command);
}

$application->setDefaultCommand('about');
$application->run();
