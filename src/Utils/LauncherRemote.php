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
class LauncherRemote
{
    public function launch($options)
    {
        $skipOptionKeys = [
            'target',
            'root'
        ];
        $args = (new ParseArguments())->parse($skipOptionKeys);

        $command = sprintf(
            '%s/vendor/bin/drupal --root=%s %s && exit',
            $options['root'],
            $options['root'],
            $args
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

    public function getSshConnectionString($options)
    {
        return sprintf(
            'ssh -A -tt %s%s%s',
            $options['user'] ? : '',
            $options['user'] ? '@' . $options['host'] : $options['host'],
            $options['port'] ? ' -p ' . $options['port'] : ''
        );
    }
}
