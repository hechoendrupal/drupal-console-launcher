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
        $args = \CommandLine::parseArgs($_SERVER['argv']);

        chdir($drupalFinder->getComposerRoot());
        $drupal = $drupalFinder->getVendorDir() . '/drupal/console/bin/drupal';

        if (!file_exists($drupal)) {
            return false;
        }

        $command = $drupal;
        $skipOptionKeys = [
            'uri',
            'target',
            'root'
        ];
        foreach ($args as $key => $value) {

            if ($key !== 0 && in_array($key, $skipOptionKeys)) {
                continue;
            }

            if (is_numeric($key)) {
                $command .= ' ' . $value;
                continue;
            }
            if (is_bool($value)) {
                $command .=  ' --'.$key;
                continue;
            }
            $command .=  ' --'.$key.'=\''.$value . '\'';
        }

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
