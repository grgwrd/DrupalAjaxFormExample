<?php

namespace Drupal\drupal_ajax_form_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\drupal_ajax_form_example\Form\TabulatorForm;

/**
 * Provides a 'TabulatorBlock' block.
 *
 * @Block(
 *  id = "concert_ticket_sales_block",
 *  admin_label = @Translation("Concert Ticket Sales block"),
 * )
 */
class ConcertTicketSalesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\drupal_ajax_form_example\TabulatorData
   */
  protected $entityQuery;

  /**
   * TabulatorBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param $entity_query
   *   query from tabulator data.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $entity_query) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityQuery = $entity_query;
  }

  /**
   * Service to inject tabulator data.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('drupal_ajax_form_example.concert_data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    /*
     * Start building variables for template from taxonomies.
     *
     */
    $venues = $this->entityQuery->venuesAll();
    $artists = $this->entityQuery->artistsAll();

    // Check to make sure service returns array.
    if (is_array($audiences) && is_array($courses)) {

      $concert_block = new AjaxForm($venues, $artists);

      $concertForm = \Drupal::formBuilder()->getForm($concert_block);

      $build['cost_form'] = $concertForm;

    }
    else {
      return $build['cost_form'] = 'There was an error for the services.';
    }
    return $build;
  }

}
