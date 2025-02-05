/**
 * @file
 * nys_unav smartling javascript file
 */

(function ($, Drupal, once, window, document) {

  'use strict';

  Drupal.behaviors.unavsmartling = {
    attach: function (context, settings) {

      // hide menu if javascript is enabled
      $('#translate-wrap .smt-trigger ul').css('display', 'none');
      $(".footer-translate .inside-wrap .translation-menu ul.smt-menu ul").css("display", "none");

      // bind click event to trigger menu display
      $(once('smt-trigger-click-event', '.smt-trigger', context)).each(
        function () {
          $(this).click(
            function (e) {
              e.stopPropagation();
              $(this).find('ul').toggle();
              $('#translate-wrap a.smt-trigger-link').toggleClass("open");
            }
          );
        }
      );

      // build object full of key presses
      var keys = {};
      onkeydown = onkeyup = function (e) {
        keys[e.keyCode] = e.type == 'keydown';
      };

      // on focusout of last item in menu, check if key press is tab and close menu if so
      $('#translate-wrap .language-links li:last-child').focusout(
        function () {
          if (keys[9] && !keys[16]) {
            $('#translate-wrap .smt-trigger').find('ul').hide();
            $('#translate-wrap a.smt-trigger-link').removeClass("open");
          } 
        }
      );

      // on focusout of first item in menu, check if key press is shift+tab and close menu if so
      $('#translate-wrap .language-links li:first-child').focusout(
        function () {
          if (keys[9] && keys[16]) {
            $('#translate-wrap .smt-trigger').find('ul').hide();
            $('#translate-wrap a.smt-trigger-link').removeClass("open");
          } 
        }
      );

      // close menu on click outside
      $(once('body-click-event', 'body', context)).each(
        function () {
          $(this).click(
            function (e) {
              $('#translate-wrap .smt-trigger').find('ul').hide();
              $('#translate-wrap a.smt-trigger-link').removeClass("open");
            }
          );
        }
      );
        
      // bind enter key to trigger
      $('.smt-trigger', context).keyup(
        function (e) {
          if (e.keyCode === 13) {
            $(this).find('ul').toggle();
            $('#translate-wrap a.smt-trigger-link').toggleClass("open");
          }
        }
      );

      // Hide the vertical list of links in the footer translate menu in mobile (looks for window < 768px for NYGOV).
      var expandCollapse = function () {
        if ($(window).width() < 768) {
          $(
            function () {
              $(".footer-translate .inside-wrap .translation-menu ul.smt-menu ul").css("display", "none");
            }
          );
        } else {
          $(
            function () {
              $(".footer-translate .inside-wrap .translation-menu ul.smt-menu ul").css("display", "flex");
            }
          );
        }
      };
      $(window).resize(expandCollapse);

      // change footer translate menu links into <select> menu (to display on mobile devices)
      // append <select> to footer translate nav
      var buildSelectMenu = function () {
        var label = $('<label for="footertranslate">Translate</label>');
        var select = $('<select id="footertranslate" name="footertranslate"></select>');
        select.append(new Option('Translate', ''));
        $(".footer-translate .smt-menu ul.language-links li a").each(
          function () {
            select.append(new Option($(this).text(), $(this).attr('href')));
          }
        );

        $(once('footer-change', '.footer-translate .smt-menu')).each(
          function () {
            $(this).append(label).append(select);
          }
        );
      };

      buildSelectMenu();
      $("#footertranslate option:first-child").prop({"disabled": "disabled", "hidden": "hidden"});

      // Ensure links open in the same browser tab
      $(".smt-menu select").change(
        function () {
          window.location = $(this).find("option:selected").val();
        }
      );
    }
  }
})(jQuery, Drupal, once, this);