<?php

/**
 * @file
 * Contains \Drupal\Console\Utils\Remote.
 */

namespace Drupal\Console\Utils;

use phpseclib\Crypt\RSA;
use phpseclib\System\SSH\Agent;
use phpseclib\Net\SFTP;

/**
 * Class RemoteHelper
 * @package \Drupal\Console\Utils
 */
class Remote
{
    /**
     * @var TranslatorManager
     */
    protected $translator;

    /**
     * Remote constructor.
     *
     * @param $translator
     */
    public function __construct(
        TranslatorManager $translator
    ) {
        $this->translator = $translator;
    }

    /**
     * @param string $commandName
     * @param string $target
     * @param array  $targetConfig
     * @param array  $inputCommand
     * @param array  $userHomeDir
     * @return string
     */
    public function executeCommand(
        $commandName,
        $target,
        $targetConfig,
        $inputCommand,
        $userHomeDir
    ) {
        $remoteCommand = str_replace(
            [sprintf('\'%s\'', $commandName), sprintf('target=\'%s\'', $target), '--remote=1'],
            [$commandName, sprintf('root=%s', $targetConfig['root']), ''],
            $inputCommand
        );

        $remoteCommand = sprintf(
            '%s %s',
            $targetConfig['console'],
            $remoteCommand
        );

        $key = null;
        if (array_key_exists('password', $targetConfig)) {
            $key = $targetConfig['password'];
        }

        if (!$key) {
            if (array_key_exists('keys', $targetConfig)) {
                if (!array_key_exists('passphrase', $targetConfig['keys'])) {
                    return $this->translator->trans('commands.site.debug.messages.passphrase-file');
                }
                $passphrase = realpath(
                    preg_replace(
                        '/~/',
                        $userHomeDir,
                        $targetConfig['keys']['passphrase'],
                        1
                    )
                );
                if (!file_exists($passphrase)) {
                    return $this->translator->trans('commands.site.debug.messages.passphrase-file');
                }
                $passphrase = trim(file_get_contents($passphrase))?:false;

                if (!array_key_exists('private', $targetConfig['keys'])) {
                    return $this->translator->trans('commands.site.debug.messages.private-file');
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
                    return $this->translator->trans('commands.site.debug.messages.private-file');
                }
                $private = trim(file_get_contents($private));

                $key = new RSA();
                $key->setPassword($passphrase);
                if (!$key->loadKey($private)) {
                    return $this->translator->trans('commands.site.debug.messages.private-key');
                }
            } else {
                $key = new Agent();
                $key->startSSHForwarding(null);
            }
        }

        $sftp = new SFTP($targetConfig['host'], $targetConfig['port'], 30);
        if (!$sftp->login($targetConfig['user'], $key)) {
            return sprintf(
                '%s - %s',
                $sftp->getExitStatus(),
                $sftp->getErrors()
            );
        } else {
            return $sftp->exec($remoteCommand);
        }
    }
}
