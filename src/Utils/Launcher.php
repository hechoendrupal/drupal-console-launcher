<?php

/**
 * @file
 * Contains \Drupal\Console\Core\Utils\LauncherBase.
 */

namespace Drupal\Console\Launcher\Utils;

use Symfony\Component\Console\Input\ArgvInput;

/**
 * Class LauncherRemote
 *
 * @package Drupal\Console\Core\Utils
 */
abstract class Launcher
{
    protected $skipOptionKeys = [
        'target',
        'root'
    ];

    protected function parseArguments($skipOptionKeys = [], $merge = true)
    {
        if ($skipOptionKeys) {
            if ($merge) {
                $skipOptionKeys = array_merge(
                    $this->skipOptionKeys,
                    $skipOptionKeys
                );
            }
        } else {
            $skipOptionKeys = $this->skipOptionKeys;
        }

        $argvInput = new ArgvInput();
        $returnAsString = $argvInput->__toString();

        foreach ($skipOptionKeys as $option) {
            $returnAsString = preg_replace(
                '/--'.$option.'=(\')(.*?)(\')/',
                '',
                $returnAsString
            );
        }

        return ' ' . $returnAsString;
    }
}
