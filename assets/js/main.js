(function($) {
  window.Shareaholic = window.Shareaholic || {};

  $(document).ready(function() {

    $('#terms_of_service_modal').reveal({
      closeonbackgroundclick: false,
      closeonescape: false,
      topPosition: 50
    });

    $('#get_started').on('click', function(e) {
      //e.preventDefault();
      //data = {action: 'shareaholic_accept_terms_of_service'};
      // $('#terms_of_service_modal').trigger('reveal:close');
      //Shareaholic.submit_to_admin(data, function(){
      //  location.reload();
      //});
    })

  });
})(sQuery);
