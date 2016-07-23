<?php

/**
 * @file
 * Contains \Drupal\github_data\GitHubDataService.
 */

namespace Drupal\github_data;

use Github\Client;
use Github\HttpClient\CachedHttpClient;
use Github\ResultPager;

/**
 * Class GitHubDataService.
 *
 * @package Drupal\github_data
 */
class GitHubDataService
{


    /**
     * @var \Github\Client
     */
    private $githubClient;

    /**
     *
     */
    const ORGANIZATION = 'hechoendrupal';

    /**
     *
     */
    const REPOSITORY = 'drupalconsole';


    private function getGithubClient()
    {
        if (!$this->githubClient) {

            $cacheDir = '/tmp/github-api-cache';
            if (defined('PANTHEON_BINDING')) {
                $cacheDir = sprintf(
                  '/srv/bindings/%s/%s',
                  PANTHEON_BINDING,
                  $cacheDir
                );
            }

            $this->githubClient = new Client(
              new CachedHttpClient(['cache_dir' => $cacheDir])
            );
        }

        return $this->githubClient;
    }

    /**
     * @return array|mixed
     */
    public function getAllContributors()
    {
        $repoApi = $this->getGithubClient()->api('repo');

        $paginator = new ResultPager($this->githubClient);
        $parameters = [self::ORGANIZATION, self::REPOSITORY];
        $contributors = $paginator->fetchAll(
          $repoApi,
          'contributors',
          $parameters
        );

        return $contributors;
    }

    /**
     * @return array
     */
    public function getStatistics(){
        $statistics = $this->getGithubClient()->api('repo')->show(self::ORGANIZATION, self::REPOSITORY);

        return [
          'forks' =>  $statistics['forks'],
          'stars' => $statistics['stargazers_count'],
          'contributors' => count($this->getAllContributors()),
          'latest_release' => $this->getLatestRelease(),
          'total_downloads' => $this->getTotalDownloads()
        ];
    }

    /**
     * Implement call since $paginator did not work for releases.
     *
     * @return array|mixed
     */
    public function getAllReleases()
    {
        $http_client = \Drupal::httpClient();
        $releasesURL = sprintf(
          'https://api.github.com/repos/%s/%s/releases?per_page=250',
          self::ORGANIZATION,
          self::REPOSITORY
        );

        $response = $http_client->get($releasesURL);

        $body    = $response->getBody();
        $content = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return $body;
        }

        return $content;
    }

    public function getLatestRelease(){
        return $this->getGithubClient()->api('repo')->releases()->latest(self::ORGANIZATION, self::REPOSITORY)['tag_name'];
    }

    public function getTotalDownloads(){
        $releases = $this->getAllReleases();

        $ghTotalDownloads = 0;
        foreach($releases as $release){
            $assets = $release['assets'];
            foreach($assets as $asset){
                if($asset['name'] == 'drupal.phar' || $asset['name'] == 'console.phar') {
                    $ghTotalDownloads += $asset['download_count'];
                }
            }
        }

        $http_client = \Drupal::httpClient();

        $response = $http_client->get('https://packagist.org/packages/drupal/console.json');

        $packagist = json_decode($response->getBody(), TRUE);

        $packagistTotalDownloads = $packagist['package']['downloads']['total'];

        return number_format($ghTotalDownloads + $packagistTotalDownloads);
    }

}
