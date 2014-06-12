$(document).ready(function(){
	$("#add_point").bind('click',function(){
		set_marker(map.getCenter());
	});
});

function load_gmap() 
{
	$(".geolocation_hidden").remove();
    
	var gmap_options = {
		zoom: 7,
    	    streetViewControl: false,
    	    scaleControl: false,
    	    mapTypeId: google.maps.MapTypeId.ROADMAP,
    	    mapTypeControl: true,
    	    mapTypeControlOptions: {
    	    	style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
    	    }
    };
	map = new google.maps.Map($("#geolocation")[0],gmap_options)
	
	var latlngbounds = new google.maps.LatLngBounds();
  	var geocoder = new google.maps.Geocoder();
  	var i = 0;
  	
   	$.each(gmap_markers,function(k,v){
   		var latlngStr = String(v).split(',',2);
   		
   		if (latlngStr.length == 2) {
   			var lat = parseFloat(latlngStr[0].replace('(','').trim());
   			var lng = parseFloat(latlngStr[1].replace(')','').trim());
   		}

   		if (typeof lat == 'number' && typeof lng == 'number' && !isNaN(lat) && !isNaN(lng)) {
   			var geocode_params = {'latLng': new google.maps.LatLng(lat, lng)};
   		}
   		else {
   			var geocode_params = {'address': v};
   		}

		geocoder.geocode(geocode_params, function(results, status) { i++;
   			if (status == google.maps.GeocoderStatus.OK) {
   				var latlng = results[0].geometry.location;
   			}
   			else {
   				var latlng = geocode_params['latLng'];
   			}
   			
   			latlngbounds.extend(latlng);
   			set_marker(latlng, k);
   			
   			if (i == gmap_markers.length) {
   				map.setCenter(latlngbounds.getCenter());
   				if (i > 1) {
   					map.fitBounds(latlngbounds);
   				}
   			}
   		});
   	});
}

function set_marker(latlng, num)
{
	if (typeof num == 'undefined') {
		num = gmap_markers.length;
	}
	gmap_markers[num] = latlng;

	$("#geolocation").parent().append('<input id="geolocation_'+num+'" class="geolocation_hidden" name="geolocation['+num+']" value="'+latlng+'" type="hidden" />');
	
	var marker_params = {
		map: map,
		position: latlng,
		draggable: true
	}
	if (num == 0) {
		marker_params['icon'] = new google.maps.MarkerImage(
			'/images/markers/default.png',
			new google.maps.Size(32,32),
			new google.maps.Point(0,0),
			new google.maps.Point(16,32)
		);
		marker_params['shadow'] = new google.maps.MarkerImage(
			'/images/markers/default-shadow.png',
			new google.maps.Size(52,32),
			new google.maps.Point(0,0),
			new google.maps.Point(16,32)
		);
		marker_params['shape'] = {
			coord: [18,0,20,1,22,2,23,3,23,4,24,5,24,6,24,7,24,8,24,9,24,10,24,11,24,12,24,13,24,14,24,15,23,16,23,17,23,18,22,19,22,20,21,21,21,22,20,23,20,24,19,25,19,26,18,27,18,28,17,29,17,30,16,31,13,31,12,30,12,29,11,28,11,27,10,26,10,25,9,24,9,23,8,22,8,21,7,20,7,19,6,18,6,17,6,16,5,15,5,14,5,13,5,12,5,11,5,10,5,9,5,8,5,7,5,6,5,5,6,4,6,3,7,2,9,1,11,0,18,0],
			type: 'poly'
		};
		
		var marker = new google.maps.Marker(marker_params);
	}
	else {
		var marker = new google.maps.Marker(marker_params);
		
		google.maps.event.addListener(marker, 'click', function() {
			if ($("#points_delete_mode").is(':checked')) {
				marker.setMap(null);
				delete gmap_markers[num];
				$("#geolocation_"+num).remove();
			}
		});
	}

	google.maps.event.addListener(marker, 'dragend', function() {
		var latlng = marker.getPosition();
		gmap_markers[num] = latlng;
		$("#geolocation_"+num).val(latlng);
	});
}

function location_change()
{
	$("select[name=location]").bind('change',function(){
        $.ajax({
            url: generate_location_string_url,
            data: {id: $(this).val()},
            success: function(data){
            	gmap_markers[0] = data;
	            load_gmap();
            }
        });
	});

	if(!(gmap_markers.length > 0)){
		$("select[name=location]").trigger('change');
	}
	else {
		load_gmap();
	}
}

