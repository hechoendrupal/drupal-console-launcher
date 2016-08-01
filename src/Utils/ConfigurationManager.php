<?php

namespace Drupal\Console\Utils;

use Symfony\Component\Yaml\Yaml;
use Dflydev\DotAccessConfiguration\YamlFileConfigurationBuilder;
use Dflydev\DotAccessConfiguration\ConfigurationInterface;

/**
 * Class ConfigurationManager
 * @package Drupal\Console\Utils
 */
class ConfigurationManager
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration = null;

    /**
     * ConfigurationReader constructor.
     *
     * @param $root
     */
    public function __construct($root)
    {
        $files = [
            __DIR__.'/../../config.yml',
            $this->getHomeDirectory() . '/.console/config.yml',
            getcwd() . '/console/config.yml',
        ];

        if ($root) {
            $files[] = $root . '/console/config.yml';
        }

        $builder = new YamlFileConfigurationBuilder($files);

        $this->configuration = $builder->build();
    }

    /**
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param $target
     * @return array
     */
    public function readTarget($target)
    {
        if (!$target || !strpos($target, '.')) {
            return [];
        }

        $site = explode('.', $target)[0];
        $env = explode('.', $target)[1];

        $siteFile = sprintf(
            '%s%s%s.yml',
            $this->getSitesDirectory(),
            DIRECTORY_SEPARATOR,
            $site
        );

        if (!file_exists($siteFile)) {
            return [];
        }

        $targetInformation = Yaml::parse(file_get_contents($siteFile));

        if (!array_key_exists($env, $targetInformation)) {
            return [];
        }

        $targetInformation = $targetInformation[$env];

        if (array_key_exists('host', $targetInformation) && $targetInformation['host'] != 'local') {
            $targetInformation['remote'] = true;
        }

        return array_merge(
            $this->configuration->get('application.remote'),
            $targetInformation
        );
    }

    /**
     * Return the user home directory.
     *
     * @return string
     */
    private function getHomeDirectory()
    {
        if (function_exists('posix_getuid')) {
            return posix_getpwuid(posix_getuid())['dir'];
        }

        return rtrim(getenv('HOME') ?: getenv('USERPROFILE'), '/\\');
    }

    /**
     * Return the site config directory.
     *
     * @return string
     */
    private function getSitesDirectory()
    {
        return sprintf(
            '%s%s.console%ssites',
            $this->getHomeDirectory(),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
    }
}
