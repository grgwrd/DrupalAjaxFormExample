(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.drupal_ajax_form_example = {
    attach: function attach(context) {

      'use strict';

      // Trigger the event to submit form.
      $('.venues-select, .artists-select , .tickets-select, input[name="ages_radio"]:radio').change(function() {


          console.log("Hello inside javascript!!!");

          // Add name of input to trigger input value to help backend.
          let input_name = $(this).attr('name');
          $('.trigger_input').val(input_name).change();

          //Submit form.
          $("#edit-submit").trigger('click');

        });

      // Assign Drupal's core AJAX functions to variables so we can override the
      // function and still call the core function.
      var beforeSend = Drupal.Ajax.prototype.beforeSend;
      var success = Drupal.Ajax.prototype.success;
      var error = Drupal.Ajax.prototype.error;

      // Add a trigger when beforeSend fires.
      Drupal.Ajax.prototype.beforeSend = function(xmlhttprequest, options) {

        beforeSend.call(this, xmlhttprequest, options);

        $(document).trigger('beforeSend');
        // Add throbber to form before Ajax call.
        $('.cost_of_tickets')
          .closest('td')
          .once()
          .append('<div aria-live="polite" role="alert" class="tickets-sold-progress ajax-progress ajax-progress-throbber"><div class="throbber">&nbsp;&nbsp;</div>Calculating</div>');

      };

      // Add a trigger when success fires.
      Drupal.Ajax.prototype.success = function(xmlhttprequest, options) {

        success.call(this, xmlhttprequest, options);
        $(document).trigger('success');

        // Remove throbber after Ajax call success.
        $('.ticket-cost').remove('.tickets-sold-progress');

      };

      // Add a trigger when error fires.
      Drupal.Ajax.prototype.error = function(xmlhttprequest, options) {

        console.log("Error: ");
        error.call(this, xmlhttprequest, options);

      };

    }
  };


})(jQuery, Drupal, drupalSettings);
