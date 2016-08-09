<?php

namespace Drupal\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Style\DrupalStyle;

/**
 * Class Application
 * @package Drupal\Console\Console
 */
class LauncherApplication extends BaseApplication {

    /**
     * @var string
     */
    const NAME = 'Drupal Console Launcher';

    /**
     * @var string
     */
    const VERSION = '1.0.0-rc1';

    protected $container;

    public function __construct($container)
    {
        parent::__construct($this::NAME, $this::VERSION);
        $this->container = $container;
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

    public function getConfiguration()
    {
        if ($this->container) {
            $configurationManager = $this->container->get('configuration_manager');
            return $configurationManager->getConfiguration();
        }

        return null;
    }

    public function getTranslator()
    {
        if ($this->container && $this->container->has('translator')) {
            return $this->container->get('translator');
        }

        return null;
    }

    /**
     * @param $key string
     *
     * @return string
     */
    public function trans($key)
    {
        if ($this->container && $this->container->has('translator')) {
            return $this->container->get('translator')->trans($key);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getContainer() {
        return $this->container;
    }
}