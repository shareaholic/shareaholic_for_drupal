/**
 * @file
 * Node module javascript.
 */

(function($) {
  Drupal.behaviors.sexyBookmarksNode = {
    attach: function(context) {
      $('fieldset#edit-shareaholic', context).drupalSetSummary(function(context) {
        return Drupal.checkPlain($('#edit-node-shareaholic-profile', context).val());
      });
    }
  };
})(jQuery);
