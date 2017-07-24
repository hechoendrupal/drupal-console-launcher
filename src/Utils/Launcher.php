<?php

namespace Drupal\Console\Launcher\Utils;

use Drupal\Console\Core\Utils\DrupalFinder;

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

        $skipOptionKeys = [
            'target',
            'root'
        ];
        $args = (new ParseArguments())->parse($skipOptionKeys);
        $command = $drupal . $args;

        $process = proc_open(
            $command,
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes,
            $drupalFinder->getComposerRoot()
        );

        proc_close($process);

        return true;
    }
}
