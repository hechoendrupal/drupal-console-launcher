<?php

/**
 * @file
 * Contains \Drupal\Console\Utils\Remote.
 */

namespace Drupal\Console\Utils;

use phpseclib\Crypt\RSA;
use phpseclib\System\SSH\Agent;
use phpseclib\Net\SFTP;
use Drupal\Console\Style\DrupalStyle;

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
     * @param DrupalStyle $io
     * @param string $commandName
     * @param string $target
     * @param array  $targetConfig
     * @param array  $inputCommand
     * @param array  $userHomeDir
     *
     * @return boolean
     */
    public function executeCommand(
        $io,
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
                    $io->error(
                        $this->translator->trans('application.remote.errors.passphrase-missing')
                    );
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
                    $io->error(
                        $this->translator->trans('application.remote.errors.passphrase-empty')
                    );
                    return false;
                }
                $passPhrase = trim(file_get_contents($passPhrase))?:false;

                if (!array_key_exists('private', $targetConfig['keys'])) {
                    $io->error(
                        $this->translator->trans('application.remote.errors.private-missing')
                    );
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
                    $io->error(
                        $this->translator->trans('application.remote.errors.private-empty')
                    );
                    return false;
                }
                $private = trim(file_get_contents($private));

                $key = new RSA();
                $key->setPassword($passPhrase);
                if (!$key->loadKey($private)) {
                    $io->error(
                        $this->translator->trans('application.remote.errors.private-invalid')
                    );
                    return false;
                }
            }
        }

        if (!$key) {
            $key = new Agent();
            $key->startSSHForwarding(null);
        }

        $sftp = new SFTP($targetConfig['host'], $targetConfig['port'], 600);
        if (!$sftp->login($targetConfig['user'], $key)) {
           $io->error(sprintf(
                '%s - %s',
                $sftp->getExitStatus(),
                $sftp->getErrors()
            ));
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
            $io->error($this->translator
                ->trans('application.remote.errors.invalid-root')
            );
            return false;
        }

        if (!$sftp->chdir($targetConfig['root'])) {
            $io->error($this->translator
                ->trans('application.remote.errors.invalid-root')
            );
            return false;
        }

        if (!$sftp->file_exists($targetConfig['root'].'/vendor/drupal/console/bin/drupal')) {
            $io->error(
                $this->translator
                    ->trans('application.remote.errors.console-not-found')
            );
        }

        $root = $targetConfig['root'];
        $remoteCommand = "cd $root && vendor/drupal/console/bin/drupal $remoteCommand";
        $executionResult = rtrim($sftp->exec($remoteCommand)) . PHP_EOL;

        if (preg_match('(ERROR|WARNING)', $executionResult) === 1) {
            $io->block($executionResult, null, 'fg=white;bg=red', ' ', false);

            return false;
        }

        $io->write($executionResult);

        return true;
    }
}
