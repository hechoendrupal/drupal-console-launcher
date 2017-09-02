<?php

/**
 * @file
 * Contains \Drupal\Console\Core\Utils\LauncherRemote.
 */

namespace Drupal\Console\Launcher\Utils;

/**
 * Class LauncherRemote
 *
 * @package Drupal\Console\Core\Utils
 */
class LauncherSsh extends Launcher
{
    public function launch($options)
    {
        $command = sprintf(
            '%s/vendor/drupal/console/bin/drupal --root=%s %s',
            $options['root'],
            $options['root'],
            $this->parseArguments()
        );

        $command = $this->getSshConnectionString($options) . ' ' . $command;

        $process = proc_open(
            $command,
            [0 => STDIN, 1 => STDOUT, 2 => STDERR],
            $pipes
        );

        proc_close($process);

        return true;
    }

    private function getSshConnectionString($options)
    {
        $extraOptions = null;
        if (array_key_exists('extra-options', $options)) {
            $extraOptions = ' ' . $options['extra-options'] . ' ';
        }

        $ssh = sprintf(
            'ssh -A -tt %s%s%s%s',
            $options['user'] ? : '',
            $options['user'] ? '@' . $options['host'] : $options['host'],
            $options['port'] ? ' -p ' . $options['port'] : '22',
            $extraOptions?:''
        );

        return $ssh;
    }
}
