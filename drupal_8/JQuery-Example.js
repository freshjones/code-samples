var megamap = megamap || {};

(function ($,drupalSettings,mm) {
  var map;
  var bounds;
  var markers = drupalSettings.megamenu_locator.markers;
  var infowindow = new google.maps.InfoWindow();

  mm.init = function(el) {
    map = new google.maps.Map(el, {
      center: {lat: 42.2215144, lng: -70.8435625},
      zoom: 9,                        // Set the zoom level manually
      maxZoom: 9,
      zoomControl: false,
      scaleControl: false,
      scrollwheel: false,
      disableDoubleClickZoom: true
    });
    bounds = new google.maps.LatLngBounds();
    markers.forEach(function(e,i){
      var position = new google.maps.LatLng(e.coordinates.lat, e.coordinates.lng);
      var marker = new google.maps.Marker({
        position: position,
        map: map,
        descrip: e.message  
      });
      mm.attachMessage(marker);
      bounds.extend(position);
    })
    map.fitBounds(bounds);
    
    $(window).resize(function() {
      google.maps.event.trigger(map, 'resize');
      map.fitBounds(bounds);
    });
  }
  mm.attachMessage = function(marker) {
    marker.addListener('click', function() {
      infowindow.setOptions({
          content: this.descrip,
          maxWidth:300
      });
      infowindow.open(map, marker);
    });
  }
  return mm;
})(jQuery,drupalSettings,megamap);

(function ($,mm) {

  'use strict';

  Drupal.behaviors.megamenu_location_finder = {
    attach: function (context, settings) {
      if( $('.megamenu-map').length )
      {
        $('.megamenu-map').each(function(){
          mm.init(this);
        });
      }
    }
  };

})(jQuery,megamap);
