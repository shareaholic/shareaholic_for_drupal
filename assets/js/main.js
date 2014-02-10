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
                    topPosition:50,
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

  $(document).ready(function() {
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

  });
})(sQuery);
