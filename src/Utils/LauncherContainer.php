<?php

namespace Drupal\Console\Launcher\Utils;

/**
 * Class Launcher
 *
 * @package Drupal\Console\Launcher\Utils
 */
class LauncherContainer extends Launcher
{
    /**
     * @param $options
     *
     * @return bool
     */
    public function launch($options)
    {
        $command = sprintf(
            '%s/vendor/drupal/console/bin/drupal --root=%s %s',
            $options['root'],
            $options['root'],
            $this->parseArguments()
        );

        $command = $options['extra-options'] . ' ' . $command;

        $process = proc_open(
            $command,
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes
        );

        proc_close($process);

        return true;
    }
}
