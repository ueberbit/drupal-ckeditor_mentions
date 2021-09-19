/**
 * @file Contain scripts for the ckeditor mentions module.
 */

(($, Drupal, CKEDITOR) => {
  /**
   * Wraps mentions pattern into RegExp.
   *
   * We cannot pass valid js regex from backend to frontend without ugly hacks,
   * so this script is executed before ckeditor will initialize editor's instance.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for ckeditorMentionsPattern.
   */
  Drupal.behaviors.ckeditorMentionsPattern = {
    attach: function attach() {
      if (!$(window).once('ckeditor_mentions_pattern').length) {
        return;
      }

      // React on instance loaded, event occurs when all configuration are fully loaded,
      // but before interaction is ready.
      // @see https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR.html#event-instanceLoaded
      CKEDITOR.on('instanceLoaded', function (event) {
        var editor = event.editor;
        CKEDITOR.tools.array.forEach(editor.config.mentions || [], function (config) {
          // If pattern exists wrap it with RegExp.
          config.pattern && (config.pattern = new RegExp(config.pattern));
        });
      });
    },
  }
})(jQuery, Drupal, CKEDITOR)
