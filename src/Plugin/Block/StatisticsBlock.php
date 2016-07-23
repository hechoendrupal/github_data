<?php

/**
 * @file
 * Contains \Drupal\github_data\Plugin\Block\StatisticsBlock.
 */

namespace Drupal\github_data\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\github_data\GitHubDataService;

/**
 * Provides a 'StatisticsBlock' block.
 *
 * @Block(
 *  id = "statistics_block",
 *  admin_label = @Translation("Statistics block"),
 * )
 */
class StatisticsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\github_data\GitHubDataService definition.
   *
   * @var Drupal\github_data\GitHubDataService
   */
  protected $github_data_api;
  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        GitHubDataService $github_data_api
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->github_data_api = $github_data_api;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('github_data.api')
    );
  }



  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['statistics_block'] = [
      '#theme' => 'statistics',
      '#statistics_data' => $this->github_data_api->getStatistics()
    ];

    return $build;
  }

}
