<?php

namespace Drupal\Console\Launcher\Utils;

/**
 * Class Launcher
 *
 * @package Drupal\Console\Launcher\Utils
 */
class Launcher
{
    /**
     * @param $root
     *
     * @return bool
     */
    public function launch($root, $launcherVersion)
    {
        chdir($root);

        /* drupal executable */
        $drupal = $root.'/vendor/drupal/console/bin/drupal';

        if (!file_exists($drupal)) {
            return false;
        }

        $drupal = realpath($drupal).'.php';

        include_once $drupal;

        return true;
    }
}
