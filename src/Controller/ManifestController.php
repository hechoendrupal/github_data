<?php

/**
 * @file
 * Contains \Drupal\github_data\Controller\ManifestController.
 */

namespace Drupal\github_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\github_data\GitHubDataService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Class ManifestController.
 *
 * @package Drupal\github_data\Controller
 */
class ManifestController extends ControllerBase {

  /**
   * Drupal\github_data\GitHubDataService definition.
   *
   * @var \Drupal\github_data\GitHubDataService
   */
  protected $github_data_api;

  /**
   * {@inheritdoc}
   */
  public function __construct(GitHubDataService $github_data_api) {
    $this->github_data_api = $github_data_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('github_data.api')
    );
  }


  /**
   * Index.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {

    $uuid = \Drupal::service('uuid');
    $Ip = \Drupal::request()->getClientIp();
    $analytics = new Analytics(true);
    $analytics
        ->setProtocolVersion('1')
        ->setTrackingId('UA-57046308-1')
        ->setDocumentPath('/manifest.json')
        ->setClientId($uuid->generate())
        ->setDocumentTitle('Drupal Console Manifest')
        ->setIpOverride($Ip);
    $analytics->sendPageview();

    $ghDataApi = $this->github_data_api;

    $version = $this->github_data_api->getLatestRelease();

    $url = sprintf(
        'https://github.com/%s/%s/releases/download/%s/drupal.phar',
        $ghDataApi::ORGANIZATION,
        $ghDataApi::REPOSITORY,
        $version
    );

    $urlVersion = sprintf(
      'https://github.com/%s/%s/releases/download/%s/drupal.phar.version',
      $ghDataApi::ORGANIZATION,
      $ghDataApi::REPOSITORY,
      $version
    );

    $request = \Drupal::httpClient()->get($urlVersion);

    $sha1 = (String) $request->getBody();

    $jsonData[] = [
      'name' => 'drupal.phar',
      'sha1' => str_replace("\n", "", $sha1),
      'url' => $url,
      'version' => $version
    ];

    return new JsonResponse($jsonData);
  }

}
