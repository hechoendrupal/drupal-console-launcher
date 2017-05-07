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
        $composerRoot = $drupalFinder->getComposerRoot();
        chdir($composerRoot);
        $vendorDir = str_replace(
            $composerRoot .'/',
            '',
            $drupalFinder->getVendorDir()
        );

        $drupal = $composerRoot.'/'.$vendorDir.'/drupal/console/bin/drupal';
        if (!file_exists($drupal)) {
            return false;
        }

        pcntl_exec($vendorDir.'/bin/drupal', $this->readArgv());

        return true;
    }

    private function readArgv()
    {
        $argv = $_SERVER['argv'];
        unset($argv[0]);

        foreach ($argv as $key => $value) {
            if (substr($value, 0, 7) == "--root=") {
                unset($argv[$key]);
            }
            if (substr($value, 0, 9) == "--target=") {
                unset($argv[$key]);
            }
        }

        return $argv;
    }
}
