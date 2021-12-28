<?php

namespace Drupal\drupal_ajax_form_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class AjaxForm extends FormBase {

  /**
   * @var array
   */
  protected $venues;

  protected $artists;

  /**
   * AjaxForm constructor.
   *
   * @param $concerts
   * @param $artists
   */
  public function __construct($venues, $artists) {
    $this->venues = $venues;
    $this->artists = $artists;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ajax_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Add our CSS and tiny JS to submit form on change.
    $form['#attached']['library'][] = 'drupal_ajax_form_example/drupal_ajax_form_example.library';

    // Sets forms elements to not be auto filled by browser (i.e. Firefox)
    $form['#attributes']['autocomplete'] = 'off';

    // Set global variables for form.
    $venues = $this->venues;
    $artists= $this->artists;

    /*
     * Default values for select dropdown lists on first page load.
     * Venues: Bottleneck
     * Artist: Less Than Jake
     */
    $venue_selected = 'bneck';
    $artist_selected = 'ltj';

    // Radio Buttons
    $form['ages_radio'] = [
      '#type' => 'radios',
      '#title' => '<span class="sr-only">Age:</span>',
      '#default_value' => 'underage',
      '#options' => ['underage' => 'Under 21', 'adult' => 'Adult'],
      '#attributes' => [
        'class' => ['ages-radios'],
      ],
    ];

    // Create array for Convert venue options list dropdown.
    $concert_venues = [];

    foreach ($venues as $venue) {
      $concert_options[$venue['abrv']] = $venue['venue'];
    }

    // Drop down list for concert venues.
    $form['venues_selected'] = [
      '#type' => 'select',
      '#title' => 'Venues',
      '#name' => 'concert_venues',
      '#default_value' => $venue_selected,
      '#options' => $concert_options,
      '#validated' => TRUE,
      '#attributes' => [
        'class' => ['venues-select'],
      ],
    ];

    // Container for ajax call back in prefix id ajax-container.
    $form['concerts_container'] = [
      '#type' => 'container',
      '#prefix' => '<div id="ajax-container">',
      '#suffix' => '</div>',
    ];

    $concerts_fee_list = $concerts[$venue_selected]['field_aud_costs'];

    // Drop down list for artists playing at venues.
    $form['concerts_container']['artists_selected'] = [
      '#type' => 'select',
      '#title' => 'in',
      '#default_value' => $artist_selected,
      '#options' => $concerts_fee_list,
      '#validated' => TRUE,
      '#attributes' => [
        'class' => ['artists-select'],
      ],
    ];

    // Ticket cost for selected concert venue and artist.
    $concert_ticket_cost = $this->concertTicketCosts($venue_selected, $artist_selected);

     // Number of tickets a venue can sell (capacity limit)
    $ticket_list = getNumberOfTickets($concert_selected, $artist_selected);

    // Concerts selected inside ajax container.
    $form['concerts_container']['number_of_tickets_selected'] = [
      '#type' => 'select',
      '#name' => 'concerts',
      '#title' => '<span class="sr-only">Tickets</span>',
      '#field_prefix' => '<span>costs</span>',
      '#field_suffix' => '<span>$' . number_format($concert_ticket_cost, 2) . '/ticket.</span>',
      '#description' => '<span class="sr-only"> credits @ $' . number_format($concert_ticket_cost, 2) . '/credit.</span>',
      '#default_value' => $number_of_tickets,
      '#options' => $tickets_list,
      '#validated' => TRUE,
      '#attributes' => [
        'class' => ['tickets-select'],
      ],
    ];

    // Trigger value gets name attribute on change event from form inputs.
    $form['concerts_container']['trigger_value'] = [
      '#type' => 'hidden',
      '#default_value' => 'none',
      '#validated' => TRUE,
      '#attributes'   => [
        'class' => ['trigger_input'],
      ],
    ];

    $total_ticket_cost = totalTicketCost($number_of_tickets, $concert_ticket_cost);

    $form['concerts_container']['ticket_total']['ticket_cost'] = [
      '#markup' => '<span class="ticket-cost">$' . number_format($total_ticket_cost, 0) . '</span>',
    ];

    // Submit button to call back function on event click.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Form submitted'),
      '#ajax' => [
        'callback' => [$this, 'ajax_states_callback'], // Call back function.
        'disable-refocus' => TRUE,
        'wrapper' => 'ajax-container', // html container for form submit update.
        'event' => 'click',
        'progress' => ['type' => 'none'], // Added custom throbber in jquery.
      ],
      '#attributes' => [
        'class' => ['sr-only'],
      ],
    ];

    // Finish form build.
    return $form;

  }

  /**
  * Return ticket cost based on selected underage or adult ticket cost.
  */
  public function concertTicketCosts($concerts, $artist_selected) {

    $concert_ticket_cost = $concerts[$artist_selected]['field_cost_per_ticket'];

    if ($artist_selected == 'underage') {
      $concert_ticket_cost = $concerts[$artist_selected]['field_cost_per_ticket_underage'];
    }

    return $concert_ticket_cost;

  }

  /**
  * Return total ticket cost.
  */
  public function totalTicketCost($number_of_tickets, $concert_ticket_cost) {

    return  $number_of_tickets * $concert_ticket_cost;

  }

  /**
  * Creates a number list for tickets drop down sold.
  */
  public function getNumberOfTickets($venue_selected, $artist_selected) {

    $tickets_list = [];

    for ($tickets= 0; $tickets<= 100; $tickets++) {
      $tickets_list[$tickets] = $tickets;
    }

    return $tickets_list;

  }


  /**
   * Returns ajax after form submit that triggers with onchange jQuery.
   */
  public function ajax_states_callback(array &$form, FormStateInterface $form_state) {

    // Validate values.
    $venue_selected = $form_state->getValue('venues_selected');
    $artist_selected = $form_state->getValue('artists_selected');
    $ages_radio = $form_state->getValue('ages_radio');
    $number_of_tickets_selected = $form_state->getValue('number_of_tickets_selected');

    // Name of the input element changed on form.
    $trigger_value = $form_state->getValue('trigger_value');

    // Global form vars.
    $concerts = $this->concerts;
    $artists= $this->artists;

    // Artists list for venue selected on change.
    $artists_list = $concerts[$venue_selected]['field_aud_artists'];

    // Set new values and options for course dropdown.
    $form['concerts_container']['artists_selected']['#value'] = $artist_selected;
    $form['concerts_container']['artists_selected']['#options'] = $artists_list;

    // Total sum cost of tickets.
    $total_ticket_cost = totalTicketCost($number_of_tickets, $concert_ticket_cost);

    // Number of tickets a venue can sell (capacity limit)
   $ticket_list = getNumberOfTickets($concert_selected, $artist_selected);

    // Return new Annual course hours.
    $form['concerts_container']['number_of_tickets_selected']['#value'] = $number_of_tickets_selected;
    $form['concerts_container']['number_of_tickets_selected']['#options'] = $ticket_list;
    $form['concerts_container']['ticket_total']['ticket_cost']['#field_suffix'] = '<span> credits @ $'
      . number_format($total_ticket_cost, 2) . '/ticket.</span>';

    // Ticket cost for selected concert venue and artist.
    $concert_ticket_cost = $this->concertTicketCosts($venue_selected, $artist_selected);

    // Create accessibility message.
    $accessibility_msg = 'Total cost for concert venue at $' . number_format($total_ticket_cost, 2) . ' each.';

    // Add accessibility message.
    $form['concerts_container']['accessibility']['#markup'] = '<span class="sr-only" aria-live="polite">' . $accessibility_msg . '</span>';

    // Ajax return form container on submit change.
    return $form['concerts_container'];

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Validate values.
    $venue_selected = $form_state->getValue('venues_selected');
    $artist_selected = $form_state->getValue('artists_selected');
    $ages_radio = $form_state->getValue('ages_radio');
    $number_of_tickets_selected = $form_state->getValue('number_of_tickets_selected');

    // Name of the input element changed on form.
    $trigger_value = $form_state->getValue('trigger_value');

    $concerts = $this->concerts;
    $artists= $this->artists;

    // Before form submits validate venue selected or artist selected reference each other.
    if ($trigger_value === 'venues_selected' || $trigger_value === 'artists_selected') {

      // Make sure venue selected references the artists selected list in drop down.


    }
    elseif ($trigger_value === 'number_of_tickets_selected') {

      // Make sure ticket prices are associated with ticket fees.

    }

    // Set modified values to calculate in ajax submit.
    $form_state->setValue('venues_selected', $venue_selected);
    $form_state->setValue('artist_selected', $artist_selected);
    $form_state->setValue('ages_radio', $ages_radio);
    $form_state->setValue('number_of_tickets_selected', $number_of_tickets_selected);
    $form_state->setValue('trigger_value', $trigger_value);

    parent::validateForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Set values for form.
    foreach ($form_state->getValues() as $key => $value) {
      $form_state->setValue($key, $value);
      // \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }

  }

}
