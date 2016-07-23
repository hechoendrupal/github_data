<?php

/**
 * @file
 * Contains \Drupal\github_data\Controller\InstallerController.
 */

namespace Drupal\github_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\github_data\GitHubDataService;
use GuzzleHttp\Client;
use TheIconic\Tracking\GoogleAnalytics\Analytics;

/**
 * Class InstallerController.
 *
 * @package Drupal\github_data\Controller
 */
class InstallerController extends ControllerBase {

  /**
   * Drupal\github_data\GitHubDataService definition.
   *
   * @var Drupal\github_data\GitHubDataService
   */
  protected $github_data_api;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var GuzzleHttp\Client
   */
  protected $http_client;
  /**
   * {@inheritdoc}
   */
  public function __construct(GitHubDataService $github_data_api, Client $http_client) {
    $this->github_data_api = $github_data_api;
    $this->http_client = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('github_data.api'),
      $container->get('http_client')
    );
  }

  /**
   * Index.
   *
   * @return response
   *   Return Drupal Console Installer.
   */
  public function index() {
    $uuid = \Drupal::service('uuid');
    $Ip = \Drupal::request()->getClientIp();
    $analytics = new Analytics(true);
    $analytics
        ->setProtocolVersion('1')
        ->setTrackingId('UA-57046308-1')
        ->setDocumentPath('/installer')
        ->setClientId($uuid->generate())
        ->setDocumentTitle('Drupal Console Installer')
        ->setIpOverride($Ip);;
    $analytics->sendPageview();

    $ghDataApi = $this->github_data_api;
    $filename = sprintf(
      'https://github.com/%s/%s/releases/download/%s/drupal.phar',
      $ghDataApi::ORGANIZATION,
      $ghDataApi::REPOSITORY,
      $this->github_data_api->getLatestRelease()
    );

    $response = $this->http_client->get($filename);

    return $response;
  }

}
