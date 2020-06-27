jQuery(function($) {

  // Assume for now that the search input is the sole form field with a
  // name="s" attribute
  var $ac = $('form [name="s"], form [name="sitka_search"]').autocomplete({
    source: '/wp-json/sitka/v2/completions',
    minLength: 3,
  });

});
