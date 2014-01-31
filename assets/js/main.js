(function($) {
  window.Shareaholic = window.Shareaholic || {};

  $(document).ready(function() {

    $('#terms_of_service_modal').reveal({
      closeonbackgroundclick: false,
      closeonescape: false,
      topPosition: 90
    });

    $('#failed_to_create_api_key').reveal({
      closeonbackgroundclick: false,
      closeonescape: false,
      topPosition: 90
    });

  });
})(sQuery);
