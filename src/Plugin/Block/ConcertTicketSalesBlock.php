<?php

namespace Drupal\drupal_ajax_form_example\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\drupal_ajax_form_example\Form\AjaxForm;

/**
 * Provides a 'Concert Ticket Sales' block.
 *
 * @Block(
 *  id = "concert_ticket_sales_block",
 *  admin_label = @Translation("Concert Ticket Sales block"),
 * )
 */
class ConcertTicketSalesBlock extends BlockBase {

  /**
   * Block to show tickets sold for each venue and artist.
   */
  public function build() {

    $build = [];

    $venues = [
                  ['venue' => 'Granada', 'abrv' => 'granada', 'artist' => ['tbr', 'ltj'], 'ticket_limit' => 100, 'underage' => 30, 'adult' => 20],
                  ['venue' => 'Emo\'s Tavern', 'abrv' => 'emos', 'artist' => 'mxpx', 'ticket_limit' => 200, 'underage' => 30, 'adult' => 20 ],
                  ['venue' => 'Bottleneck', 'abrv' => 'bneck', 'artist' => 'ltj', 'ticket_limit' => 300, 'underage' => 30, 'adult' => 20 ],
                ];

    $artists = [
                  ['artist' => 'tbr', 'abrv' => 'tbr', 'lineup' => ['Teenage Bottle Rocket', 'Less Than Jake'], 'tickets_sold' => 10 ],
                  ['artist' => 'ltj', 'abrv' => 'ltj', 'lineup' => 'Less Than Jake', 'tickets_sold' => 20 ],
                  ['artist' => 'mxpx', 'abrv' => 'mxpx', 'lineup' => 'MXPX', 'tickets_sold' => 30 ],
                ];

    // $concerts = [];
    // $artists = [];

    $concert_block = new AjaxForm($venues, $artists);

    $concertForm = \Drupal::formBuilder()->getForm($concert_block);

    $build['concert_form'] = $concertForm;

    return $build;
  }

}
