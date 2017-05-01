<?php

namespace Drupal\Console\Launcher\Command;

use Humbug\SelfUpdate\Updater;
use Drupal\Console\Launcher\Utils\ManifestStrategy;

class SelfUpdate
{
    public function run($version, $isValidDrupal, $composerRoot)
    {
        if (!extension_loaded('Phar') || !(\Phar::running(false))) {
            echo 'This instance of the CLI was not installed as a Phar archive.' . PHP_EOL;
            echo 'Update using: composer global update drupal/console-launcher' . PHP_EOL;
            exit(1);
        }

        echo sprintf(
            'Checking for updates from version: "%s"',
            $version
        ) . PHP_EOL;

        $updater = new Updater(null, false);
        $strategy = new ManifestStrategy(
            $version,
            true,
            'http://drupalconsole.com/manifest.json'
        );

        $updater->setStrategyObject($strategy);

        if (!$updater->hasUpdate()) {
            echo sprintf(
                'The latest version "%s" of DrupalConsole Launcher,' . PHP_EOL .
                'was already installed on your system.',
                $version
            ) . PHP_EOL;
            $this->isValidMessage($isValidDrupal, $composerRoot);
            exit(0);
        }

        $oldVersion = $updater->getOldVersion();
        $newVersion = $updater->getNewVersion();
        $updater->update();

        echo sprintf(
            'Updated from version "%s" to version "%s".',
            $oldVersion,
            $newVersion
        ) . PHP_EOL;
        $this->isValidMessage($isValidDrupal, $composerRoot);
        exit(0);
    }

    private function isValidMessage($isValidDrupal, $composerRoot)
    {
        if ($isValidDrupal) {
            echo PHP_EOL;
            echo 'If you want to update the DrupalConsole dependency' . PHP_EOL ;
            echo 'in your current site: ' . $composerRoot . PHP_EOL;
            echo 'Execute: composer update drupal/console --with-dependencies' . PHP_EOL;
        }
    }
}
