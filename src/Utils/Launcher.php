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
        $root = $drupalFinder->getComposerRoot();

        chdir($root);

        $vendorDir = 'vendor/';
        $json = json_decode(
            file_get_contents(getcwd() . '/composer.json'),
            true
        );
        if (is_array($json) && isset($json['config']['vendor-dir'])) {
            $vendorDir = $json['config']['vendor-dir'];
        }

        /* drupal executable */
        $drupal = $root.'/'.$vendorDir.'/drupal/console/bin/drupal';

        if (!file_exists($drupal)) {
            return false;
        }

        $argv = $this->readArgv();

        pcntl_exec($vendorDir.'/bin/drupal', $argv);

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
