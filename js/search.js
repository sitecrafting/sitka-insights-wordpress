jQuery(function($) {

  // Assume for now that the search input is the sole form field with a
  // name="s" attribute
  var $ac = $('form [name="s"]').autocomplete({
    source: '/wp-json/gearlab/v2/completions',
    minLength: 3,
  });

});
