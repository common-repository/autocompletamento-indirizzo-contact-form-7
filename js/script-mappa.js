// JavaScript Document// This sample uses the Autocomplete widget to help the user select a
// place, then it retrieves the address components associated with that
// place, and then it populates the form fields with those details.
// This sample requires the Places library. Include the libraries=places
// parameter when you first load the API. For example:
// <script
// src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places">

var placeSearch, autocomplete;

var componentForm = {
  street_number: 'short_name',
  route: 'long_name',
  locality: 'long_name',
  administrative_area_level_1: 'short_name',
  country: 'long_name',
  postal_code: 'short_name'
};

function initAutocomplete() {
	jQuery('#partenza_geo').attr('onFocus',"geolocate('a')");
	jQuery('#arrivo_geo').attr('onFocus',"geolocate('b')");
  // Create the autocomplete object, restricting the search predictions to
  // geographical location types.
  autocomplete = new google.maps.places.Autocomplete(
      document.getElementById('partenza_geo'), {types: ['geocode']});

  // Avoid paying for data that you don't need by restricting the set of
  // place fields that are returned to just the address components.
  //autocomplete.setFields('address_components');

  // When the user selects an address from the drop-down, populate the
  // address fields in the form.
  autocomplete.addListener('place_changed', fillInAddress);
  
   autocompleteB = new google.maps.places.Autocomplete(
      document.getElementById('arrivo_geo'), {types: ['geocode']});

  autocompleteB.addListener('place_changed', fillInAddressB);/**/
}
function fillInAddress() {
  // Get the place details from the autocomplete object.
  var place = autocomplete.getPlace();
  var placeB = autocompleteB.getPlace();
//console.log('quo '+place.geometry.location);

		  
		   jQuery('#partenza_latlng').val(place.geometry.location);
		   
			   calcolaDistanza(place,placeB);
			   //jQuery('#distanza').val(place.geometry.location+' to '+placeB.geometry.location);
			 
  /*for (var component in componentForm) {
    document.getElementById(component).value = '';
    document.getElementById(component).disabled = false;
  }

  // Get each component of the address from the place details,
  // and then fill-in the corresponding field on the form.
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm[addressType]) {
      var val = place.address_components[i][componentForm[addressType]];
      document.getElementById(addressType).value = val;
    }
  }*/
}
function calcolaDistanza(f,e){
	
	var urld	=	url_sitoweb;
	var ak		=	apikey;
				jQuery.post(urld,{a:f.place_id,b:e.place_id, k:apikey},function(data){var distanzaJ = JSON.parse(data); 
				 //console.log(distanzaJ.rows[0].elements[0].distance.text);
				//console.info(distanzaJ);
				jQuery('#distanza').val(distanzaJ.rows[0].elements[0].distance.text);
		})
		
		
	
	}
function fillInAddressB() {
  // Get the place details from the autocomplete object.
  var place = autocomplete.getPlace();
  var placeB = autocompleteB.getPlace();
//console.log('qui '+place.geometry.location);

		 jQuery('#arrivo_latlng').val(placeB.geometry.location);
		   calcolaDistanza(place,placeB);
  /*for (var component in componentForm) {
    document.getElementById(component).value = '';
    document.getElementById(component).disabled = false;
  }

  // Get each component of the address from the place details,
  // and then fill-in the corresponding field on the form.
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if (componentForm[addressType]) {
      var val = place.address_components[i][componentForm[addressType]];
      document.getElementById(addressType).value = val;
    }
  }*/
}

// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate(f) {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };
	  
	  //console.log(lat+'----'+lng);
      var circle = new google.maps.Circle(
          {center: geolocation, radius: position.coords.accuracy});
      autocomplete.setBounds(circle.getBounds());
    });
  }
}