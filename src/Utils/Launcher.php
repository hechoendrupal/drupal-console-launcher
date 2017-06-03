<?php

namespace Drupal\Console\Launcher\Utils;

use DrupalFinder\DrupalFinder;

/**
 * Class Launcher
 *
 * @package Drupal\Console\Launcher\Utils
 */
class Launcher
{
    /**
     * @param $drupalFinder
     *
     * @return bool
     */
    public function launch(DrupalFinder $drupalFinder)
    {
        chdir($drupalFinder->getComposerRoot());
        $drupal = $drupalFinder->getVendorDir() . '/drupal/console/bin/drupal';

        if (!file_exists($drupal)) {
            return false;
        }

        $drupal = realpath($drupal).'.php';

        include_once $drupal;

        return true;
    }
}
