<?php

/**
 * @file
 * Contains \Drupal\Console\Core\Utils\Server.
 */

namespace Drupal\Console\Launcher\Utils;

use Drupal\Console\Launcher\Utils\Server;
use Symfony\Component\Process\Process;

/**
 * Class Server
 *
 * @package Drupal\Console\Core\Utils
 */
class RemoteProcess {

    private $Server;
    private $output;

    public function __construct(Server $Server) {
        $this->Server = $Server;
        $this->output = '';
    }

    public function run($command) {
        // cd path
        if ($this->Server->getRoot()) {
            $command = sprintf('cd %s && drupal %s && exit'. "\n", $this->Server->getRoot(), $command);
        }

        $process = new Process($this->Server->getSshConnectionString(), null, null, $command);
        $process->setTimeout(null);
        $process->start();
        $process->wait(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->output .= $buffer;
            } else {
                $this->output .= $buffer;
            }
        });
        return $process;
    }

    public function getOutput() {
        return $this->output;
    }
}
