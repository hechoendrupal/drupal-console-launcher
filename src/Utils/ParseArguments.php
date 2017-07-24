<?php

/**
 * @file
 * Contains \Drupal\Console\Core\Utils\Server.
 */

namespace Drupal\Console\Launcher\Utils;

/**
 * Class Server
 *
 * @package Drupal\Console\Core\Utils
 */
class ParseArguments
{
    public function __construct()
    {
    }

    public function parse($skipOptionKeys)
    {
        $args      = \CommandLine::parseArgs($_SERVER['argv']);
        $parseArgs = '';
        foreach ($args as $key => $value) {
            if ($key !== 0 && in_array($key, $skipOptionKeys)) {
                continue;
            }

            if (is_numeric($key)) {
                $parseArgs .= ' ' . $value;
                continue;
            }
            if (is_bool($value)) {
                $parseArgs .= ' --' . $key;
                continue;
            }
            $argv = '--' . $key . '=\'' . $value . '\'';

            if ($argv === "--uri='http://default'") {
                continue;
            }

            $parseArgs .= ' ' . $argv;
        }

        return $parseArgs;
    }
}
