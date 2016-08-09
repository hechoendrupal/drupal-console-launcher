<?php

namespace Drupal\Console\Utils;

/**
 * Class Launcher.
 */
class DrupalConsoleLauncher
{
    /**
     * @param $root
     *
     * @return bool
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

        $drupal = realpath($drupal).'.php';

        include_once $drupal;

        return true;
    }
}
