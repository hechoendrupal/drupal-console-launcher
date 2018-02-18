<?php

namespace Drupal\Console\Launcher\Utils;

use Drupal\Console\Core\Utils\DrupalFinder;

/**
 * Class Launcher
 *
 * @package Drupal\Console\Launcher\Utils
 */
class LauncherLocal extends Launcher
{
    /**
     * @param $drupalFinder DrupalFinder
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

        $command = $drupal . $this->parseArguments();

        $process = proc_open(
            $command,
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes,
            $drupalFinder->getComposerRoot()
        );

        // If process was successful, we'll return it's exit code to propagate
        if ($process) {
          return proc_close($process);
        }

        return false;
    }
}
