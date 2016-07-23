<?php

/**
 * @file
 * Contains \Drupal\github_data\Controller\ContributorsController.
 */

namespace Drupal\github_data\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\github_data\GitHubDataService;

/**
 * Class ContributorsController.
 *
 * @package Drupal\github_data\Controller
 */
class ContributorsController extends ControllerBase {

  /**
   * Drupal\github_data\GitHubDataService definition.
   *
   * @var Drupal\github_data\GitHubDataService
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
   * @return array
   *   Contributors render array.
   */
  public function index() {

    return [
        '#theme' => 'contributors',
        '#contributors_data' => $this->github_data_api->getAllContributors()
    ];
  }

}
