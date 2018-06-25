var FC = FC || {};
var chromaCart = chromaCart || {};

chromaCart = (function (chromaCart,$) {

  $(document).ready(function()
  {
    
    $('#checkout-button').on('click',function(event)
    {
      if(!FC.json.item_count)
        event.preventDefault();
    });

  });
  
  FC.onLoad = function () 
  {
    FC.client.on('ready.done', function () 
    {
      chromaCart.loadCart();
    });
  };

  chromaCart.loadCart = function()
  {
    var data = FC.json;

    $('#fc_cartcount').text(data.item_count); 

    var templateName = 'cart';
    
    if(data.item_count <= 0)
    {
      templateName = 'cart-empty';
      $('#total_order').html('&mdash;');
    }
    
    var template = getTemplate(templateName,data);

    template.done(function(output){
      $('#fc-cart-items').html(output);
      
      if(data.item_count > 0)
        $('#total_order').html(data.total_order.toFixed(2));
      
      $('#fc-loader').fadeOut(250, function(){
        
        if(data.item_count > 0)
        {
          $('#checkout-button').removeClass('disabled').prop('disabled',false);
        } else {
          $('#checkout-button').addClass('disabled').prop('disabled','disabled');
        }

        $('.fc-add-items-from-cart').prop('disabled',false);
        
      });

      if(templateName == 'cart-empty')
      {
        cartSearch.initSearch();
        quoteSearch.initQuoteSearch();
      }

      initCartFunctions();
    });
  }

  function getTemplate(name,data)
  {
    
    var rand = Math.floor((Math.random() * 100) + 1);
    var templatePath = Drupal.settings.foxycart.template_path;
    
    return $.get(templatePath + '/'+name+'.html?g=' + $.now() ).then(function(src) {
         return Handlebars.compile(src)(data);
      });
    
  }

  function FCLoader(value)
  {
    if(value==='show')
      $('#fc-loader').fadeIn(250);

    if(value==='hide')
      $('#fc-loader').fadeOut(250);
  }

  function initCartFunctions()
  {
    
    $('.fc-update-qty').on('change',function(){

      var id = $(this).closest('.fc-body-item-wrapper').data('id');
      var value = $(this).val();

      FCLoader('show');
      FCUpdateQuantity(id,value);

    });

    $('.fc-remove-item').on('click',function(){

      var id = $(this).closest('.fc-body-item-wrapper').data('id');
      var value = 0;

      FCLoader('show');
      FCUpdateQuantity(id,value);
      
    });
    
    initCartClueTips();

  }

  function FCUpdateQuantity(id,value)
  {

    FC.client
      .request('https://'+FC.settings.storedomain+'/cart?cart=update&quantity='+value+'&id='+id)
      .done(function(data) 
      { 
        chromaCart.loadCart(data);
      });
    
  }
  function initCartClueTips()
  {
    $('.showCartClueTips').cluetip({
        ajaxCache: false,
        showTitle: true,
        arrows: true,
        cluezIndex: 5000,
        cluetipClass:'jtip',
        activation: 'hover',
        mouseOutClose: false,
        closePosition: 'top',
        closeText: 'close',
        sticky: true
    });
  }

  return chromaCart;

})(chromaCart,jQuery);
