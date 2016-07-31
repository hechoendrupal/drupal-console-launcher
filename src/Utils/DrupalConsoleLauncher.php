<?php

namespace Drupal\Console\Utils;

/**
 * Class Launcher
 * @package Drupal\Console\Utils
 */
class DrupalConsoleLauncher
{
    /**
     * @param $root
     *
     * @return boolean
     */
    public function launch($root)
    {
        chdir($root);
        /* default drupal installation */
        $drupal = $root.'/vendor/bin/drupal';

        if (!file_exists($drupal)) {
            /* drupal installed via drupal-composer related project */
            $drupal = $root.'/../vendor/bin/drupal';
        }

        if (!file_exists($drupal)) {
            return false;
        }

        /* Add option to identify if pre-launched */
        // $_SERVER['argv'][] = '--pre-launch';

        $drupal = realpath($drupal) . '.php';

        include_once $drupal;

        return true;
    }
}
