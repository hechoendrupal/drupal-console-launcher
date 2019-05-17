<?php

/**
 * @file
 * Contains \Drupal\Console\Core\Utils\LauncherRemote.
 */

namespace Drupal\Console\Launcher\Utils;

use Symfony\Component\Process\Process;

/**
 * Class LauncherRemote
 *
 * @package Drupal\Console\Core\Utils
 */
/**
 * Class LauncherSsh
 * @package Drupal\Console\Launcher\Utils
 */
class LauncherSsh extends Launcher
{
    /**
     * @param $options
     * @return bool
     */
    public function launch($options)
    {
        $drupalConsoleBinary = isset($options['options'])?isset($options['options']['drupal-console-binary'])?$options['options']['drupal-console-binary']:'drupal':'drupal';

        $command = sprintf(
            '%s --root=%s %s',
            $drupalConsoleBinary,
            $options['root'],
            $this->parseArguments()
        );

        $command = $this->getSshConnectionString($options) . ' ' . $command;

        try {
            $process = new Process($command);
            $process->enableOutput();
            $process->setTimeout(null);
            $process->mustRun();
            echo $process->getOutput();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return 0;
        }

        return 1;
    }

    /**
     * @param $options
     * @return string
     */
    private function getSshConnectionString($options)
    {
        $extraOptions = null;
        if (array_key_exists('extra-options', $options)) {
            $extraOptions = ' ' . $options['extra-options'] . ' ';
        }

        $ssh = sprintf(
            'ssh -A %s%s%s%s',
            $options['user'] ? : '',
            $options['user'] ? '@' . $options['host'] : $options['host'],
            $options['port'] ? ' -p ' . $options['port'] : '22',
            $extraOptions?:''
        );

        return $ssh;
    }
}
