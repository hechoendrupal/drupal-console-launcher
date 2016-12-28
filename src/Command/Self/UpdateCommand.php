<?php

/**
 * @file
 * Contains \Drupal\Console\Command\Self\UpdateCommand.
 */

namespace Drupal\Console\Launcher\Command\Self;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Humbug\SelfUpdate\Updater;
use Drupal\Console\Core\Command\Shared\CommandTrait;
use Drupal\Console\Core\Style\DrupalStyle;

class UpdateCommand extends Command
{
    use CommandTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('self-update')
            ->setDescription($this->trans('commands.self-update.description'))
            ->setHelp($this->trans('commands.self-update.help'))
            ->addOption(
                'major',
                null,
                InputOption::VALUE_NONE,
                $this->trans('commands.self-update.options.major')
            )
            ->addOption(
                'manifest',
                null,
                InputOption::VALUE_REQUIRED,
                $this->trans('commands.self-update.options.manifest')
            )
            ->addOption(
                'current-version',
                null,
                InputOption::VALUE_REQUIRED,
                $this->trans('commands.self-update.options.current-version')
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var DrupalStyle
         */
        $io = new DrupalStyle($input, $output);
        $application = $this->getApplication();

        $manifest = $input->getOption('manifest') ?: 'http://drupalconsole.com/manifest.json';
        $currentVersion = $input->getOption('current-version') ?: $application->getVersion();
        $major = true; // temporary fix to force mayor version.
        if (!extension_loaded('Phar') || !(\Phar::running(false))) {
            $io->error($this->trans('commands.self-update.messages.not-phar'));
            $io->block($this->trans('commands.self-update.messages.instructions'));

            return 1;
        }
        $io->info(
            sprintf(
                $this->trans('commands.self-update.messages.check'),
                $currentVersion
            )
        );
        $updater = new Updater(null, false);
        $strategy = new ManifestStrategy(
            $currentVersion,
            $major,
            $manifest
        );

        $updater->setStrategyObject($strategy);

        if (!$updater->hasUpdate()) {
            $io->info(
                sprintf(
                    $this->trans('commands.self-update.messages.current-version'),
                    $currentVersion
                )
            );

            return 0;
        }

        $oldVersion = $updater->getOldVersion();
        $newVersion = $updater->getNewVersion();

        if (!$io->confirm(
            sprintf(
                $this->trans('commands.self-update.questions.update'),
                $oldVersion,
                $newVersion
            ),
            true
        )) {
            return 1;
        }

        $io->comment(
            sprintf(
                $this->trans('commands.self-update.messages.update'),
                $newVersion
            )
        );
        $updater->update();
        $io->success(
            sprintf(
                $this->trans('commands.self-update.messages.success'),
                $oldVersion,
                $newVersion
            )
        );

        // Errors appear if new classes are instantiated after this stage
        // (namely, Symfony's ConsoleTerminateEvent). This suggests PHP
        // can't read files properly from the overwritten Phar, or perhaps it's
        // because the autoloader's name has changed. We avoid the problem by
        // terminating now.
        exit;
    }
}
