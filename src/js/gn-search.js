//Free up the $ for jQuery, just in case
(function ($) {
   $.fn.gnsearch = function () {
      /**
       * @var Object reference the search box more easily within methods
       */
      var $input = this;

      /**
       * @var Object container for the results (not the input itself)
       */
      $input.container = $("#" + $input.attr('data-container'));

      /**
       * @var Object throbber image; this not only makes it easy to reference, but also preloads it
       * TODO do not use a specific path
       */
      $input.throbber = $("<img>",
         {
            src: '/wp-content/plugins/gn-search/media/throbber.gif',
            alt: 'Please Wait...',
            'class': 'gn-throbber'
         }
      );

      /**
       * @var current ajax request
       * Initialize this with the abort method so keyup does not freak out
       */
      $input.jqxhr = {abort: function () { $.noop(); }};

      //Nothing to put results in, so don't show the input either
      if (!$input.container || !$input.container.length) {
         return;
      }

      $input.show();

      $input.on('keyup', function () {
         //Cancel the last request so as not to give the server a little break
         $input.jqxhr.abort();

         if ($input.val().length >= $input.attr('data-chars')) {
            $input.initSearch($input.val());
         }
      });

      $input.initSearch = function (term) {
         $input.container
            .hide()
            .after($input.throbber)
         ;

         $input.jqxhr = $.getJSON(GnSearch.ajaxurl, {term: term, action: 'gnsearch'})
            .done($input.success)
            .fail($input.failure)
            .always($input.always)
         ;
      };

      $input.success = function (json) {
         alert(json);
      };

      $input.failure = function () {
         $input.container.show().html('<span class="gn-search-error">Error trying to get results</span>');
      };

      $input.always = function () {
         $input.throbber.detach();
      }
   };

   $(function () {
      //TODO do this separately so we can use a different ID or element
      $("#gn-search").gnsearch();
   });
})(jQuery);
