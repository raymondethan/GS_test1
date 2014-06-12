        function removeAid() {
            if (document.switches.aid.checked) {
                map.removeLayer(aid);
                aidLabel.id = 'aidOff';
            }
            else {
                map.addLayer(aid);
                aidOff.id = 'aidLabel';
            }
        }

        function removeEcon()
        {
            if (document.switches.economic.checked) {
                map.removeLayer(economic);
                econ.id = 'econOff';
            }
            else {
                map.addLayer(economic);
                econOff.id = 'econ';
            }
        }

        function removeEnviro() {
            if (document.switches.environment.checked) {
                map.removeLayer(environment);
                enviro.id = 'enviroOff';
            }
            else {
                map.addLayer(environment);
                enviroOff.id = 'enviro';
            }
        }

        function removeHR() {
            if (document.switches.hr.checked) {
                map.removeLayer(hr);
                human_rights.id = 'human_rightsOff';
            }
            else {
                map.addLayer(hr);
                human_rightsOff.id = 'human_rights';      
            }
        }

        function removePolit() {
            if (document.switches.political.checked) {
                map.removeLayer(political);
                polit.id = 'politOff';
            }
            else {
                map.addLayer(political);
                politOff.id = 'polit';
            }
        }

        function highlightFeature(e) {
            var layer = e.target;

            layer.setStyle({
                weight: 1,
                color: 'white',
                dashArray: '',
                fillColor: 'white',
                fillOpacity: 1,
            });
            if (!L.Browser.ie && !L.Browser.opera) {
                layer.bringToFront();
            }
            info.update(layer.feature.properties);
        }

        function resetHighlight(e) {
            geojson.resetStyle(e.target);
            info.update();
        }

        function zoomToFeature(e) {
            map.fitBounds(e.target.getBounds());
        }

        function onEachFeature(feature, layer) {
            layer.on({
                mouseover: highlightFeature,
                mouseout: resetHighlight,
                click: zoomToFeature
            });
        }

        function zoomToOriginal(e) {
            map.setView([-3.578, 3.515], 1);
        }

        function onOcean(feature, layer) {
            layer.on({
                click: zoomToOriginal
            });
        }

        var redVideo = L.icon({
            iconUrl: 'images/red_video.png'
        });

        function addHover(marker, type1, type2) {
            //var coords = marker.getLatLng();
            //var new_coords = [marker.getLatLng().lat + 4, marker.getLatLng().lng - 4];
            //, marker.setLatLng(new_coords)
            //, marker.setLatLng(coords)
            marker.on('mouseover', function(){marker.setIcon(type1), marker.openPopup();});
            marker.on('mouseout', function(){marker.setIcon(type2), marker.closePopup();});
        }

        function attachHover(markers, type1, type2) {
            for (var i = 0; i < markers.length; i++) {
                addHover(markers[i], type1, type2);
            }
        }

        function toggle(formname) {
            var checkboxes = new Array(); 
            checkboxes = document[formname].getElementsByTagName('input');
 
            for (var i=0; i<checkboxes.length; i++)  {
                if (checkboxes[i].type == 'checkbox' && checkboxes[i].checked)   {
                    checkboxes[i].click();
                }
            }
        }

        function addOverlap(markers) {
            for (var i = 0; i<markers.length; i++) {
                oms.addMarker(markers[i]);
            }
        }
function onLocationFound(e) {
    var radius = e.accuracy / 2;

    L.marker(e.latlng).addTo(map)
        .bindPopup("You are here").openPopup();

    L.circle(e.latlng, radius).addTo(map);
}



function onLocationError(e) {
    //alert(e.message);
}
