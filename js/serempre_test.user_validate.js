(function ($, Drupal) {

  Drupal.behaviors.serempre_test = {
    attach: function (context, settings) {

      var form = $("form.serempre-test-user-register", context);
      var submitButton = $('input[type="submit"]', form);

      form.once('submitForm').off('submit').on('submit', function(event) {

        // Prevent normal form submission
        event.preventDefault();
        var form = $(this);

        $(this).validate({
          rules: {
            user_name: {
              required: true
            },
            messages: {
              user_name: "Validar por jquery here"
            }
          }
        });

        // The trigger value should match what you have in your $form['submit'] array's ajax array
        //if the form is valid, trigger the ajax event
        if(form.valid()) {
          submitButton.trigger('submit_form');
        }

      });


    }
  };

  Drupal.AjaxCommands.prototype.insertModal = function insertModal(ajax, response) {
    if ($(response.selector).length) {
      $(response.selector).replaceWith(response.data);
    }
    else {
      $('body').append(response.data);
    }
    // Show modal on ajax response.
    $(response.selector).modal('show');
    // Clear textfield.
    $('input[name="user_name"]').val('');
  }

})(jQuery, Drupal);
