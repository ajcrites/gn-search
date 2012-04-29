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
      //Nothing to put results in, so don't show the input either
      if (!$input.container || !$input.container.length) {
         return;
      }

      /**
       * @var int maximum number of results to display at once
       */
      $input.results = $input.attr('data-results');

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

      $input.show();

      $input.on('keyup', function () {
         //Cancel the last request to give the server a little break
         $input.jqxhr.abort();

         if ($input.val().length >= $input.attr('data-chars')) {
            $input.initSearch($input.val());
         }
      });

      $input.initSearch = function (term) {
         $input.container
            .hide()
            .empty()
            .after($input.throbber)
         ;

         $input.jqxhr = $.getJSON(GnSearch.ajaxurl, {term: term, action: 'gnsearch', limit: $input.attr('data-limit')})
            .done($input.success)
            .fail($input.failure)
            .always($input.always)
         ;
      };

      /**#@+
       * Handle Ajax results
       */
      $input.success = function (json) {
         if (!json.status) {
            $input.failure();
         }
         else if (json.status == 'success') {
            $input.displayResults(json.response);
         }
         else if (json.status == 'error') {
            $input.displayError(json.msg);
         }
         //Unknown response, so display it as a normal error
         else {
            $input.failure();
         }
      };

      $input.failure = function () {
         $input.container.show().html('<span class="gn-search-error">Error trying to get results</span>');
      };

      $input.always = function () {
         $input.throbber.detach();
      }
      /**#@-*/

      /**#@+
       * Update the container based on results
       */
      $input.displayResults = function (news) {
         //TODO if the results are too long for the container, it would be nice to cut them off with a ...
         $.each(news, function (index) {
            //IE really hates 'class' not used in its intended context
            var clss = 'gn-search-result';

            if ($input.results != 0 && index + 1 > $input.results) {
               clss = 'gn-search-result-extra';
            }
            $input.container.append(
               $("<a>", {href: this.url, text: this.title, 'class': clss})
            );
         });
         var $extralinks = $('.gn-search-result-extra', $input.container);
         if ($extralinks.length) {
            $("<div>", {text: 'Show More...', 'class': 'gn-show-extra'})
               .on('click', function () {
                  $(this).hide();
                  $extralinks.css('display', 'block');
               })
               .appendTo($input.container)
            ;
         }
         $input.container.show();
      };

      $input.displayError = function (err) {
         $input.container.show().html('<span class="gn-search-error">' + err + '</span>');
      }
      /**#@-*/
   };

   $(function () {
      //TODO do this separately so we can use a different ID or element
      $("#gn-search").gnsearch();
   });
})(jQuery);
