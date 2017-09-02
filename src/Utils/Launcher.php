<?php

/**
 * @file
 * Contains \Drupal\Console\Core\Utils\LauncherBase.
 */

namespace Drupal\Console\Launcher\Utils;

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
        return (new ParseArguments())->parse($skipOptionKeys);
    }
}
