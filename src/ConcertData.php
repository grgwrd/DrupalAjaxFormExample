<?php

namespace Drupal\drupal_ajax_form_example;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Data queries for tabulator data cost calculator audience and course fees.
 */
class ConcertData {


  protected $entityTypeManager;

  /**
   * @var $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Creates array of cost calculator audience data.
   */
  public function venuesAll() {

    try {
      $storage = $this->entityTypeManager->getStorage('node');

      $aud_ids = $storage->getQuery()
        ->condition('type', 'cost_calculator_venue')
        ->condition('status', 1)
        ->execute();

      $nodes = $storage->loadMultiple($aud_ids);

      $audiences = [];
      foreach ($nodes as $key => $node) {

        if (isset($node->get('field_aud_abbreviation')->value)) {
          $audience = [
            'nid' => $node->get('nid')->value,
            'title' => $node->get('title')->value,
            'field_aud_add_info' => $node->get('field_aud_add_info')->value,
          ];

          if (!isset($audiences[$audience['field_aud_abbreviation']])) {
            $audiences[$audience['field_aud_abbreviation']] = $audience;
          }

        }
      }

      return $audiences;

    }
    catch (InvalidPluginDefinitionException $e) {
      return 'Error occurred' . $e;
    }
    catch (PluginNotFoundException $e) {
      return 'Error occurred' . $e;
    }

  }

  /**
   * Creates array of cost calculator course fees data.
   */
  public function artistsAll() {

    try {
      $storage = $this->entityTypeManager->getStorage('node');

      $nids = $storage->getQuery()
        ->condition('type', 'cost_calculator_artists')
        ->condition('status', 1)
        ->execute();

      $nodes = $storage->loadMultiple($nids);

      $course_fees = [];
      foreach ($nodes as $key => $node) {

        if (isset($node->get('field_pch_abbreviation')->value)) {
          $course_fee = [
            'nid' => $node->get('nid')->value,
            'title' => $node->get('title')->value,
            'field_pch_abbreviation' => $node->get('field_pch_abbreviation')->value,
            'field_pch_default' => $node->get('field_pch_default')->value,
          ];

          $additional_course_fees = [];

          foreach ($node->field_pch_additional as $item) {
            if ($item->entity) {
              $additional_course_fees[] = $item->entity->get('field_pch_abbreviation')->value;
            }
          }

          $course_fee['field_pch_additional'] = $additional_course_fees;

          if (!isset($course_fees[$course_fee['field_pch_abbreviation']])) {
            $course_fees[$course_fee['field_pch_abbreviation']] = $course_fee;
          }

        }

      }

      return $course_fees;

    }
    catch (InvalidPluginDefinitionException $e) {
      return 'Error occurred' . $e;
    }
    catch (PluginNotFoundException $e) {
      return 'Error occurred' . $e;
    }

  }

}
