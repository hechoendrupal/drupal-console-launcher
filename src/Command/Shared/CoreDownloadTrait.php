<?php

/**
 * @file
 * Contains Drupal\Console\Command\Shared\CoreDownloadTrait.
 */

namespace Drupal\Console\Command\Shared;

use Drupal\Console\Style\DrupalStyle;
use Drupal\Console\Zippy\Adapter\TarGzGNUTarForWindowsAdapter;
use Drupal\Console\Zippy\FileStrategy\TarGzFileForWindowsStrategy;
use Alchemy\Zippy\Zippy;
use Alchemy\Zippy\Adapter\AdapterContainer;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CoreDownloadTrait
 * @package Drupal\Console\Command\Shared
 */
trait CoreDownloadTrait
{
    /**
     * @param \Drupal\Console\Style\DrupalStyle $io
     * @param $version
     *
     * @return string
     */
    public function downloadCore(DrupalStyle $io, $version)
    {
        $project = 'drupal';
        $commandKey = str_replace(':', '.', $this->getName());

        $io->comment(
            sprintf(
                $this->trans('commands.'.$commandKey.'.messages.downloading'),
                $project,
                $version
            )
        );

        try {
            $destination = $this->downloadCoreRelease(
                $version
            );

            $projectPath = tempnam(sys_get_temp_dir(), 'drupal_');
            unlink($projectPath);

            if (!file_exists($projectPath)) {
                if (!mkdir($projectPath, 0777, true)) {
                    $io->error(
                        sprintf(
                            $this->trans('commands.'.$commandKey.'.messages.error-creating-folder'),
                            $projectPath
                        )
                    );
                    return null;
                }
            }

            $zippy = Zippy::load();
            if (PHP_OS === "WIN32" || PHP_OS === "WINNT") {
                $container = AdapterContainer::load();
                $container['Drupal\\Console\\Zippy\\Adapter\\TarGzGNUTarForWindowsAdapter'] = function ($container) {
                    return TarGzGNUTarForWindowsAdapter::newInstance(
                        $container['executable-finder'],
                        $container['resource-manager'],
                        $container['gnu-tar.inflator'],
                        $container['gnu-tar.deflator']
                    );
                };
                $zippy->addStrategy(new TarGzFileForWindowsStrategy($container));
            }
            $archive = $zippy->open($destination);
            $archive->extract(getenv('MSYSTEM') ? null : $projectPath);

            unlink($destination);
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return null;
        }

        return $projectPath;
    }

    /**
     * @param \Drupal\Console\Style\DrupalStyle $io
     * @param bool                              $latest
     * @param bool                              $stable
     * @return string
     */
    public function releasesQuestion(DrupalStyle $io, $latest = false, $stable = false)
    {
        $commandKey = str_replace(':', '.', $this->getName());

        $io->comment(
            sprintf(
                $this->trans('commands.'.$commandKey.'.messages.getting-releases'),
                'drupal'
            )
        );

        $releases = $this->getCoreReleases($latest?1:15, $stable);

        if (!$releases) {
            $io->error(
                sprintf(
                    $this->trans('commands.'.$commandKey.'.messages.no-releases'),
                    'drupal'
                )
            );

            return null;
        }

        if ($latest) {
            return $releases[0];
        }

        $version = $io->choice(
            $this->trans('commands.'.$commandKey.'.messages.select-release'),
            $releases
        );

        return $version;
    }

    /**
     * @param $limit
     * @param $stable
     * @return array
     * @throws \Exception
     */
    public function getCoreReleases($limit = 10, $stable = false)
    {
        $projectPageResponse = $this->httpClient->getUrlAsString(
          'https://updates.drupal.org/release-history/drupal/8.x'
        );

        if ($projectPageResponse->getStatusCode() != 200) {
            throw new \Exception('Invalid path.');
        }

        $releases = [];
        $crawler = new Crawler($projectPageResponse->getBody()->getContents());
        $filter = './project/releases/release/version';
        if ($stable) {
            $filter = './project/releases/release[not(version_extra)]/version';
        }

        foreach ($crawler->filterXPath($filter) as $element) {
            $releases[] = $element->nodeValue;
        }

        if (count($releases)>$limit) {
            array_splice($releases, $limit);
        }

        return $releases;
    }

    /**
     * @param $release
     * @param null    $destination
     * @return null|string
     */
    public function downloadCoreRelease($release, $destination = null)
    {
        if (!$release) {
            $releases = $this->getCoreReleases(1);
            $release = current($releases);
        }

        if (!$destination) {
            $destination = sprintf(
              '%s/%s.tar.gz',
              sys_get_temp_dir(),
              'drupal'
            );
        }

        $releaseFilePath = sprintf(
          'https://ftp.drupal.org/files/projects/%s-%s.tar.gz',
          'drupal',
          $release
        );

        if ($this->downloadFile($releaseFilePath, $destination)) {
            return $destination;
        }

        return null;
    }

    public function downloadFile($url, $destination)
    {
        $this->httpClient->get($url, array('sink' => $destination));

        return file_exists($destination);
    }
}
