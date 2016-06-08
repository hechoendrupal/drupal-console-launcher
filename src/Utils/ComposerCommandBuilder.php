<?php

namespace Drupal\Console\Utils;

use Symfony\Component\Console\Input\ArrayInput;

class ComposerCommandBuilder
{
    private $stopExecution = true;

    public function build($argvInputReader, $configuration)
    {
        $input = null;
        if ($argvInputReader->get('command') === 'site:new') {
            $package = $configuration
                ->get('application.composer.create-project.default');
            $input = new ArrayInput(
                [
                'command' => 'create-project',
                'package' => $package,
                'version' => '8.x-dev',
                'directory' => $argvInputReader->get('root'),
                '--no-interaction' => true,
                '--prefer-dist' => true,
                '--no-dev' => true
                ]
            );
        }

        return $input;
    }

    /**
     * @return boolean
     */
    public function isStopExecution()
    {
        return $this->stopExecution;
    }
}
