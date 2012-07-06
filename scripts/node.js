/**
 * @file
 * Node module javascript.
 */

(function($) {
  Drupal.behaviors.sexyBookmarksNode = {
    attach: function(context) {
      $('fieldset#edit-sexybookmarks', context).drupalSetSummary(function(context) {
        return Drupal.checkPlain($('#edit-node-sexybookmarks-profile', context).val());
      });
    }
  };
})(jQuery);
