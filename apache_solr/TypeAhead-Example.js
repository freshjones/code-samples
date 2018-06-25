var typeahead = typeahead || {};

typeahead = (function($,typeahead) {

  var template;

  typeahead.search = function(query)
  {
    $.get('/services/api/search?q=' + query, function(data)
    {
      var output = TypeAheadSearch.templates.results(data);
      $('.type-ahead-results .items').html(output);
      typeahead.show();
    })
  }

  typeahead.selectNextOption = function()
  {
    var nextIndex = 0;
    var all = $('.type-ahead-results .items ul li').length;
    var active = $('.type-ahead-results .items ul li.active');
    if(active.length)
      nextIndex = $('.type-ahead-results .items ul li.active').first().index() + 1;

    $('.type-ahead-results .items ul li').removeClass('active');

    var inputField = $('.typeahead-search-form input.typeahead');

    if(nextIndex >= all)
    {
      var newValue = $('.typeahead-search-form input[name="orig"]').val();
    } else {
      var nextItem = $('.type-ahead-results .items ul li').eq(nextIndex);
      nextItem.addClass('active');
      var newValue = nextItem.data('product-number');
    }
    inputField.val( newValue );
  }

  typeahead.selectPrevOption = function()
  {
    var all = $('.type-ahead-results .items ul li').length;
    var prevIndex = all-1;
    var active = $('.type-ahead-results .items ul li.active');
    
    if(active.length)
      prevIndex = $('.type-ahead-results .items ul li.active').first().index() - 1;

    $('.type-ahead-results .items ul li').removeClass('active');

    var inputField = $('.typeahead-search-form input.typeahead');

    if(prevIndex < 0)
    {
      var newValue = $('.typeahead-search-form input[name="orig"]').val();
    } else {
      var prevItem = $('.type-ahead-results .items ul li').eq(prevIndex);
      prevItem.addClass('active');
      var newValue = prevItem.data('product-number');
    }
    inputField.val(newValue);
  }

  typeahead.isValid = function(str) {
    return str.match(/^[0-9a-z \&\,\.\-\/]{2,}$/i);
  };

  typeahead.submitSearch = function( keyword ) {
    
    if($('.typeahead-search-form input.typeahead').val().length <= 0)
      return;

    var link = false;
    var active = $('.type-ahead-results .items ul li.active');
    
    if(active.length)
      link = $('.type-ahead-results .items ul li.active').first().find('a').attr('href');
    if(link){
      window.location.href = link;
    } else {

      var location = '/search?keyword=' + encodeURIComponent(keyword);
      window.location.href = location;
      //$('.typeahead-search-form').submit();
    }

  }

  typeahead.activate = function()
  {
    $('.typeahead-search').addClass('type-ahead-active');
    if(!$('.type-ahead-results li').length)
      typeahead.setdefaults();
    typeahead.show();
  }

  typeahead.dectivate = function()
  {
    $('.typeahead-search').removeClass('type-ahead-active');
  }

  typeahead.show = function()
  {
    $('.type-ahead-results').addClass('type-ahead-results-show');
  }
  
  typeahead.setdefaults = function()
  {
    var defaults = TypeAheadSearch.templates.defaults();
    $('.type-ahead-results .items').html(defaults);
  }

  typeahead.hide = function()
  {
    typeahead.setdefaults();
    $('.type-ahead-results').removeClass('type-ahead-results-show');
  }

  return typeahead;

})(jQuery,typeahead);
