<?php

namespace Drupal\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Style\DrupalStyle;

/**
 * Class Application
 * @package Drupal\Console
 */
class Application extends ConsoleApplication
{
    /**
     * @var string
     */
    const NAME = 'Drupal Console Launcher';

    /**
     * @var string
     */
    const VERSION = '1.0.0-rc1';

    public function __construct($container)
    {
        parent::__construct($container, $this::NAME, $this::VERSION);
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        parent::doRun($input, $output);

        if ($this->getCommandName($input) == 'list') {
            $io = new DrupalStyle($input, $output);
            $io->warning($this->trans('application.site.errors.directory'));
        }
    }
}
