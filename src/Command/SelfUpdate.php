<?php

namespace Drupal\Console\Launcher\Command;

use Humbug\SelfUpdate\Updater;
use Drupal\Console\Launcher\Utils\ManifestStrategy;

class SelfUpdate
{
    public function run()
    {
        if (!extension_loaded('Phar') || !(\Phar::running(false))) {
            echo 'This instance of the CLI was not installed as a Phar archive.' . PHP_EOL;
            echo 'Update using: composer global update drupal/console-launcher' . PHP_EOL;
            exit(1);
        }

        echo sprintf(
            'Checking for updates from version: "%s"',
            '@git_version@'
        ) . PHP_EOL;

        $updater = new Updater(null, false);
        $strategy = new ManifestStrategy(
            '@git_version@',
            true,
            'http://drupalconsole.com/manifest.json'
        );

        $updater->setStrategyObject($strategy);

        if (!$updater->hasUpdate()) {
            echo sprintf(
                'The latest version "%s", was already installed on your system.',
                '@git_version@'
            ) . PHP_EOL;
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
        ;
        exit(0);
    }
}
