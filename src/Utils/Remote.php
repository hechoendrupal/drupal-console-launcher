<?php

/**
 * @file
 * Contains \Drupal\Console\Utils\Remote.
 */

namespace Drupal\Console\Launcher\Utils;

use phpseclib\Crypt\RSA;
use phpseclib\System\SSH\Agent;
use phpseclib\Net\SFTP;

/**
 * Class Remote
 *
 * @package Drupal\Console\Launcher\Utils
 */
class Remote
{

    /**
     * @param string $commandName
     * @param string $target
     * @param array  $targetConfig
     * @param array  $inputCommand
     * @param array  $userHomeDir
     *
     * @return boolean
     */
    public function executeCommand(
        $commandName,
        $target,
        $targetConfig,
        $inputCommand,
        $userHomeDir
    ) {
        $key = null;
        if (array_key_exists('password', $targetConfig)) {
            $key = $targetConfig['password'];
        }

        if (!$key) {
            if (array_key_exists('keys', $targetConfig)) {
                if (!array_key_exists('passphrase', $targetConfig['keys'])) {
                    echo 'Passphrase file is missing' . PHP_EOL;
                    return false;
                }
                $passPhrase = realpath(
                    preg_replace(
                        '/~/',
                        $userHomeDir,
                        $targetConfig['keys']['passphrase'],
                        1
                    )
                );
                if (!file_exists($passPhrase)) {
                    echo  'Passphrase file is empty' . PHP_EOL;

                    return false;
                }
                $passPhrase = trim(file_get_contents($passPhrase))?:false;

                if (!array_key_exists('private', $targetConfig['keys'])) {
                    echo 'Private file is missing' . PHP_EOL;

                    return false;
                }
                $private = realpath(
                    preg_replace(
                        '/~/',
                        $userHomeDir,
                        $targetConfig['keys']['private'],
                        1
                    )
                );
                if (!file_exists($private)) {
                    echo 'Private file is empty' . PHP_EOL;

                    return false;
                }
                $private = trim(file_get_contents($private));

                $key = new RSA();
                $key->setPassword($passPhrase);
                if (!$key->loadKey($private)) {
                    echo 'Private file is invalid' . PHP_EOL;

                    return false;
                }
            }
        }

        if (!$key) {
            $key = new Agent();
            $key->startSSHForwarding(null);
        }

        $sftp = new SFTP($targetConfig['host'], $targetConfig['port'], 600);
        try {
            $logged = $sftp->login($targetConfig['user'], $key);
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            $this->showErrorsAsString($sftp);

            return false;
        }

        if (!$logged) {
            echo 'Invalid login credentials.' . PHP_EOL;
            $this->showErrorsAsString($sftp);

            return false;
        }

        $remoteCommand = str_replace(
            [
                sprintf('\'%s\'', $commandName),
                sprintf('--target=\'%s\'', $target),
                sprintf('--root=\'%s\'', $targetConfig['root']),
                '--remote=1'
            ],
            [
                $commandName,
                '',
                '',
                ''
            ],
            $inputCommand
        );

        if (!$sftp->is_dir($targetConfig['root'])) {
            echo 'Invalid root directory' . PHP_EOL;

            return false;
        }

        if (!$sftp->chdir($targetConfig['root'])) {
            echo 'Invalid root directory' . PHP_EOL;

            return false;
        }

        if (!$sftp->file_exists($targetConfig['root'].'/vendor/drupal/console/bin/drupal')) {
            echo 'Drupal Console not found on this site' . PHP_EOL;

            return false;
        }
        $root = $targetConfig['root'];
        $remoteCommand = "cd $root && vendor/drupal/console/bin/drupal $remoteCommand --no-interaction";
        $executionResult = rtrim($sftp->exec($remoteCommand)) . PHP_EOL;

        if (preg_match('(ERROR|WARNING)', $executionResult) === 1) {
            echo $executionResult . PHP_EOL;

            return false;
        }

        if ($sftp->getExitStatus() == 1) {
            $this->showErrorsAsString($sftp);
            echo $executionResult . PHP_EOL;

            return false;
        }

        echo $executionResult . PHP_EOL;

        return true;
    }

    /**
     * @param $sftp
     */
    private function showErrorsAsString($sftp)
    {
        echo sprintf(
            '%s - %s',
            $sftp->getExitStatus(),
            implode(', ', $sftp->getErrors())
        ) . PHP_EOL;
    }
}
