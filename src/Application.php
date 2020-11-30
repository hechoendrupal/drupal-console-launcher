<?php

namespace Drupal\Console\Launcher;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Console\Core\Application as CoreApplication;

/**
 * Class Application
 *
 * @package Drupal\Console
 */
class Application extends CoreApplication
{
    /**
     * @var string
     */
    const NAME = 'Drupal Console Launcher';

    /**
     * @var string
     */
    const VERSION = '1.9.7';

    /**
     * Application constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        parent::__construct($container, $this::NAME, $this::VERSION);
    }
}
