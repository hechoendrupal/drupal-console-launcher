<?php

namespace Drupal\Console\Utils;

/**
 * Class Launcher.
 */
class Launcher
{
    /**
     * @param $root
     *
     * @return bool
     */
    public function launch($root)
    {
        chdir($root);

        /* drupal executable */
        $drupal = $root.'/vendor/drupal/console/bin/drupal';

        if (!file_exists($drupal)) {
            return false;
        }

        $drupal = realpath($drupal).'.php';

        include_once $drupal;

        return true;
    }
}
