<?php

namespace Drupal\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Style\DrupalStyle;

/**
 * Class Application
 * @package Drupal\Console
 */
class LauncherApplication extends ConsoleApplication
{
    /**
     * @var string
     */
    const NAME = 'Drupal Console Launcher';

    /**
     * @var string
     */
    const VERSION = '1.0.0-rc8';

    public function __construct($container)
    {
        parent::__construct($container, $this::NAME, $this::VERSION);
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerGenerators();
        $this->registerCommands();
        $clear = $this->container->get('console.configuration_manager')
            ->getConfiguration()
            ->get('application.clear')?:false;
        if ($clear === true || $clear === 'true') {
            $output->write(sprintf("\033\143"));
        }
        parent::doRun($input, $output);
        if ($this->getCommandName($input) == 'list') {
            $io = new DrupalStyle($input, $output);
            $io->warning($this->trans('application.site.errors.directory'));
        }
    }

    private function registerCommands()
    {
        $consoleCommands = $this->container
            ->findTaggedServiceIds('drupal.command');

        foreach ($consoleCommands as $name => $tags) {
            if (!$this->container->has($name)) {
                continue;
            }

            $command = $this->container->get($name);
            if (!$command) {
                continue;
            }
            if (method_exists($command, 'setTranslator')) {
                $command->setTranslator(
                    $this->container->get('console.translator_manager')
                );
            }
            $this->add($command);
        }
    }

    private function registerGenerators()
    {
        $consoleGenerators = $this->container
            ->findTaggedServiceIds('drupal.generator');

        foreach ($consoleGenerators as $name => $tags) {
            if (!$this->container->has($name)) {
                continue;
            }

            $generator = $this->container->get($name);
            if (!$generator) {
                continue;
            }
            if (method_exists($generator, 'setRenderer')) {
                $generator->setRenderer(
                    $this->container->get('console.renderer')
                );
            }
            if (method_exists($generator, 'setFileQueue')) {
                $generator->setFileQueue(
                    $this->container->get('console.file_queue')
                );
            }
        }
    }
}
