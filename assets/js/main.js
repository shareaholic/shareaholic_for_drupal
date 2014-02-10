(function($) {
  window.Shareaholic = window.Shareaholic || {};

    Shareaholic.bind_button_clicks = function (click_object, off) {
        if (off) {
            $(click_object.selector).off('click.app_settings');
        }

        $(click_object.selector).off('click.app_settings').on('click.app_settings', function (e) {
            button = this;
            e.preventDefault();
            url = click_object.url(this);
            if (click_object.selector == '#general_settings') {
                window.open(url);
                return false;
            } else {
                $frame = $('<iframe>', { src:url }).appendTo('#iframe_container');
                if (click_object.callback) {
                    click_object.callback(this);
                }
                $('#editing_modal').reveal({
                    topPosition:90,
                    close:function () {
                        if (click_object.close) {
                            click_object.close(button);
                        }
                        $frame.remove();
                    }
                });
            }
        });
    }

    Shareaholic.click_objects = {
        'app_settings': {
            selector: '#app_settings button',
            url: function(button) {
                id = $(button).data('location_id');
                app = $(button).data('app')
                url = first_part_of_url + $(button).data('href') + '?embedded=true&'
                    + 'verification_key=' + verification_key;
                url = url.replace(/{{id}}/, id);
                return url;
            },
            callback: function(button) {
                id = $(button).data('location_id');
                app = $(button).data('app');
                text = 'You can also use this shortcode to place this {{app}} App anywhere.';
                html = "<div id='shortcode_container'> \
          <span id='shortcode_description'></span> \
          <textarea id='shortcode' name='widget_div' onclick='select();' readonly='readonly'></textarea> \
        </div>"
                $(html).appendTo('.reveal-modal');
                $('#shortcode_description').text(text.replace(/{{app}}/, Shareaholic.titlecase(app)));
                $('#shortcode').text('[shareaholic app="' + app + '" id="' + id + '"]');
            },
            close: function(button) {
                $('#shortcode_container').remove();
            }
        },

        'general_settings': {
            selector: '#general_settings',
            url: function(button) {
                return first_part_of_url + 'edit'
                    + '?verification_key=' + verification_key;
            }
        }
    }

    Shareaholic.titlecase = function(string) {
        return string.charAt(0).toUpperCase() + string.replace(/_[a-z]/g, function(match) {
            return match.toUpperCase().replace(/_/, ' ');
        }).slice(1);
    }

    Shareaholic.disable_buttons = function() {
        $('#app_settings button').each(function() {
            if (!$(this).data('location_id')) {
                $(this).attr('disabled', 'disabled');
            } else {
                $(this).removeAttr('disabled');
            }
        });
    }

   Shareaholic.Utils.PostMessage.receive('settings_saved', {
       success: function(data) {
           $('input[type="submit"]').click();
       },
       failure: function(data) {
           console.log(data);
       }
   });

    Shareaholic.create_new_location = function(_this) {
      $(_this).prop('disabled', true);
      var button = $(_this).siblings('button');
      var app = button.data('app');
      var location_id = button.data('location_id');
      if (location_id) {
        return;
      }

      var data = {};
      data['configuration_' + app + '_location'] = {
        name: /.*\[(.*)\]/.exec($(_this).attr('name'))[1]
      }

      button.text('Creating...');

      $.ajax({
        url: first_part_of_url + app + '/locations.json',
        type: 'POST',
        data: data,
        success: function(data, status, jqxhr) {
          data['action'] = 'shareaholic_add_location';
          button.data('location_id', data['location']['id']);
          button.text('Customize');
          Shareaholic.disable_buttons();
          $(_this).prop('disabled', false);
        },
        failure: function(things) {
          button.text('Creation Failed');
          $(_this).prop('disabled', false);
        },
        error: function() {
          button.text('Creation Failed');
          $(_this).prop('disabled', false);
        },
        xhrFields: {
          withCredentials: true
        }
      });
    }

  $(document).ready(function() {
    Shareaholic.disable_buttons();

    Shareaholic.bind_button_clicks(Shareaholic.click_objects['app_settings']);

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

    $('input[type=checkbox]').click(function() {
      if($(this).is(':checked') && !$(this).data('location_id')) {
        Shareaholic.create_new_location(this);
      }
    });

  });
})(sQuery);
