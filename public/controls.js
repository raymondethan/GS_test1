		var countryCodes = {'Canada': 'CA', 'Sao Tome and Principe': 'ST', 'Guernsey': 'GG', 'Iran': 'IR', 'Lithuania': 'LT', 'Saint Pierre and Miquelon': 'PM', 'Saint Helena, Ascension and Tristan da Cunha': 'SH', 'Ethiopia': 'ET', 'Aruba': 'AW', 'Swaziland': 'SZ', 'Belize': 'BZ', 'Argentina': 'AR', 'Cameroon': 'CM', 'Burkina Faso': 'BF', 'Turkmenistan': 'TM', 'Ghana': 'GH', 'Saudi Arabia': 'SA', 'Togo': 'TG', 'Cape Verde': 'CV', 'United States Minor Outlying Islands': 'UM', 'Cocos (Keeling) Islands': 'CC', 'Faroe Islands': 'FO', 'Guatemala': 'GT', 'Bosnia and Herzegovina': 'BA', 'Kuwait': 'KW', 'Russia': 'RU', 'Germany': 'DE', 'Bonaire, Sint Eustatius and Saba': 'BQ', 'Virgin Islands, British': 'VG', 'Spain': 'ES', 'Liberia': 'LR', 'Maldives': 'MV', 'Armenia': 'AM', 'Jamaica': 'JM', 'Oman': 'OM', 'Costa Rica': 'CR', 'Christmas Island': 'CX', 'Gabon': 'GA', 'Niue': 'NU', 'Monaco': 'MC', 'Wallis and Futuna': 'WF', 'New Zealand': 'NZ', 'Yemen': 'YE', 'Jersey': 'JE', 'Pakistan': 'PK', 'Greenland': 'GL', 'Samoa': 'WS', 'Norfolk Island': 'NF', 'United Arab Emirates': 'AE', 'Guam': 'GU', 'Vietnam': 'VN', 'Svalbard and Jan Mayen': 'SJ', 'Lesotho': 'LS', 'Saint Vincent and the Grenadines': 'VC', 'Kenya': 'KE', 'Macao': 'MO', 'Turkey': 'TR', 'Afghanistan': 'AF', 'Northern Mariana Islands': 'MP', 'Eritrea': 'ER', 'Solomon Islands': 'SB', 'India': 'IN', 'Saint Lucia': 'LC', 'Hungary': 'HU', 'San Marino': 'SM', 'Kyrgyzstan': 'KG', 'French Polynesia': 'PF', 'France': 'FR', 'Macedonia': 'MK', 'Syria': 'SY', 'Bermuda': 'BM', 'Slovakia': 'SK', 'Somalia': 'SO', 'Peru': 'PE', 'Vanuatu': 'VU', 'Nauru': 'NR', 'Norway': 'NO', 'Malawi': 'MW', 'Cook Islands': 'CK', 'Benin': 'BJ', 'Democratic Republic of the Congo': 'CD', 'Western Sahara': 'EH', 'Cuba': 'CU', 'Montenegro': 'ME', 'Saint Kitts and Nevis': 'KN', 'British Indian Ocean Territory': 'IO', 'Heard Island and McDonald Islands': 'HM', 'China': 'CN', 'Micronesia, Federated States of': 'FM', 'Antigua and Barbuda': 'AG', 'Dominican Republic': 'DO', 'Azerbaijan': 'AZ', 'Ukraine': 'UA', 'Bahrain': 'BH', 'Tonga': 'TO', 'Indonesia': 'ID', 'Qatar': 'QA', 'Libya': 'LY', 'Finland': 'FI', 'Central African Republic': 'CF', 'Mauritius': 'MU', 'Tajikistan': 'TJ', 'Sweden': 'SE', 'Australia': 'AU', 'Mali': 'ML', 'Cambodia': 'KH', 'American Samoa': 'AS', 'Bulgaria': 'BG', 'United States': 'US', 'Romania': 'RO', 'Angola': 'AO', 'French Southern Territories': 'TF', 'Portugal': 'PT', 'South Africa': 'ZA', 'Tokelau': 'TK', 'Fiji': 'FJ', 'South Georgia and the South Sandwich Islands': 'GS', 'Liechtenstein': 'LI', 'Venezuela': 'VE', 'Malaysia': 'MY', 'Senegal': 'SN', 'Mozambique': 'MZ', 'Uganda': 'UG', 'Japan': 'JP', 'Niger': 'NE', 'Isle of Man': 'IM', 'Brazil': 'BR', 'Pitcairn': 'PN', 'Guinea': 'GN', 'Panama': 'PA', 'South': 'KR', 'Saint Martin (French part)': 'MF', 'Luxembourg': 'LU', 'Virgin Islands, U.S.': 'VI', 'Bahamas': 'BS', 'Gibraltar': 'GI', 'Ivory Coast': 'CI', 'Italy': 'IT', 'Nigeria': 'NG', 'Ecuador': 'EC', 'Czech Republic': 'CZ', 'Belarus': 'BY', "North Korea": 'KP', 'Algeria': 'DZ', 'Slovenia': 'SI', 'El Salvador': 'SV', 'Tuvalu': 'TV', 'Marshall Islands': 'MH', 'Chile': 'CL', 'Puerto Rico': 'PR', 'Belgium': 'BE', 'Kiribati': 'KI', 'Haiti': 'HT', 'Iraq': 'IQ', 'Hong Kong': 'HK', 'Sierra Leone': 'SL', 'Georgia': 'GE', "Laos": 'LA', 'Gambia': 'GM', 'Poland': 'PL', 'Namibia': 'NA', 'Morocco': 'MA', 'Albania': 'AL', 'Croatia': 'HR', 'Mongolia': 'MN', 'Guinea-Bissau': 'GW', 'Thailand': 'TH', 'Switzerland': 'CH', 'Grenada': 'GD', 'Bangladesh': 'BD', 'Taiwan': 'TW', 'Honduras': 'HN', 'Seychelles': 'SC', 'United Republic of Tanzania': 'TZ', 'Chad': 'TD', 'Estonia': 'EE', 'Uruguay': 'UY', 'Equatorial Guinea': 'GQ', 'Lebanon': 'LB', 'Uzbekistan': 'UZ', 'Tunisia': 'TN', 'Falkland Islands (Malvinas)': 'FK', 'Holy See (Vatican City State)': 'VA', 'Timor-Leste': 'TL', 'Dominica': 'DM', 'Colombia': 'CO', 'Burundi': 'BI', 'Cyprus': 'CY', 'North Cyprus': 'NCY', 'Barbados': 'BB', 'Madagascar': 'MG', 'Palau': 'PW', 'Denmark': 'DK', 'Bhutan': 'BT', 'Sudan': 'SD', 'Bolivia': 'BO', 'Nepal': 'NP', 'Malta': 'MT', 'Brunei Darussalam': 'BN', 'Comoros': 'KM', 'Netherlands': 'NL', 'Suriname': 'SR', 'Cayman Islands': 'KY', 'Anguilla': 'AI', 'Turks and Caicos Islands': 'TC', 'Israel': 'IL', 'Bouvet Island': 'BV', 'Iceland': 'IS', 'Zambia': 'ZM', 'Austria': 'AT', 'Papua New Guinea': 'PG', 'Trinidad and Tobago': 'TT', 'Zimbabwe': 'ZW', 'Jordan': 'JO', 'Martinique': 'MQ', 'Kazakhstan': 'KZ', 'Philippines': 'PH', 'Moldova': 'MD', 'Djibouti': 'DJ', 'Mauritania': 'MR', 'Ireland': 'IE', 'Mayotte': 'YT', 'Montserrat': 'MS', 'New Caledonia': 'NC', 'Andorra': 'AD', 'Sri Lanka': 'LK', 'Latvia': 'LV', 'South Sudan': 'SS', 'Guyana': 'GY', 'Guadeloupe': 'GP', 'Rwanda': 'RW', 'Myanmar': 'MM', 'Mexico': 'MX', 'Egypt': 'EG', 'Nicaragua': 'NI', 'Singapore': 'SG', 'Republic of Serbia': 'RS', 'Botswana': 'BW', 'United Kingdom': 'GB', 'Antarctica': 'AQ', 'Congo': 'CG', 'Sint Maarten (Dutch part)': 'SX', 'Greece': 'GR', 'Paraguay': 'PY', 'French Guiana': 'GF', 'Palestine': 'PS', 'Kosovo': 'XK'}

    var info = L.control();

		info.onAdd = function (map) {
		    this._div = L.DomUtil.create('div', 'info'); // create a div with a class "info"
		    this.update();
		    return this._div;
		};

		info.update = function (props) {
    		this._div.innerHTML = (props ?
        	'<h4>' + props.name + '</h4><br><img src=flags/' + countryCodes[props.name] + ".png>"
        	: '');
		};

       var zoomOut = L.control();

       zoomOut.onAdd = function (map) {
            this._div = L.DomUtil.create('div', 'zoomOut');
            return this._div;
        }


    L.Control.EasyButtons = L.Control.extend({
    options: {
        position: 'topright',
        title: '',
        intentedIcon: ' fa-circle-o'
    },

    onAdd: function () {
        var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');

        this.link = L.DomUtil.create('a', 'leaflet-bar-part', container);
        L.DomUtil.create('i', 'fa fa-2x ' + this.options.intentedIcon , this.link);
        this.link.href = '#';

        L.DomEvent.on(this.link, 'click', this._click, this);
        this.link.title = this.options.title

        return container;
    },
    
    intendedFunction: function(){ alert('no function selected')},
  
    _click: function (e) {
        L.DomEvent.stopPropagation(e);
        L.DomEvent.preventDefault(e);
        this.intendedFunction();
    },
});

L.easyButton = {}

L.easyButton = function( btnIcon , btnFunction , btnTitle , btnMap ) {
  var newControl = new L.Control.EasyButtons
  if (btnIcon) newControl.options.intentedIcon = btnIcon
  
  if ( typeof btnFunction === 'function'){
    newControl.intendedFunction = btnFunction
  } 
  
  if (btnTitle) newControl.options.title = btnTitle
  
  if ( btnMap ){
    newControl.addTo(btnMap)
  } else {
    newControl.addTo(map)
  }
}


L.Control.MiniMap = L.Control.extend({
  options: {
    position: 'bottomright',
    toggleDisplay: false,
    zoomLevelOffset: -5,
    zoomLevelFixed: false,
    zoomAnimation: false,
    autoToggleDisplay: false,
    width: 150,
    height: 150,
    aimingRectOptions: {color: "#ff7800", weight: 1, clickable: false},
    shadowRectOptions: {color: "#000000", weight: 1, clickable: false, opacity:0, fillOpacity:0}
  },
  
  hideText: 'Hide MiniMap',
  
  showText: 'Show MiniMap',
  
  //layer is the map layer to be shown in the minimap
  initialize: function (layer, options) {
    L.Util.setOptions(this, options);
    //Make sure the aiming rects are non-clickable even if the user tries to set them clickable (most likely by forgetting to specify them false)
    this.options.aimingRectOptions.clickable = false;
    this.options.shadowRectOptions.clickable = false;
    this._layer = layer;
  },
  
  onAdd: function (map) {

    this._mainMap = map;

    //Creating the container and stopping events from spilling through to the main map.
    this._container = L.DomUtil.create('div', 'leaflet-control-minimap');
    this._container.style.width = this.options.width + 'px';
    this._container.style.height = this.options.height + 'px';
    L.DomEvent.disableClickPropagation(this._container);
    L.DomEvent.on(this._container, 'mousewheel', L.DomEvent.stopPropagation);


    this._miniMap = new L.Map(this._container,
    {
      attributionControl: false,
      zoomControl: false,
      zoomAnimation: false,
      autoToggleDisplay: this.options.autoToggleDisplay,
      touchZoom: false,
      scrollWheelZoom: false,
      doubleClickZoom: false,
      boxZoom: false,
      crs: map.options.crs,
      dragging: false
    });

    this._miniMap.addLayer(this._layer);

    //These bools are used to prevent infinite loops of the two maps notifying each other that they've moved.
    this._mainMapMoving = false;
    this._miniMapMoving = false;

    //Keep a record of this to prevent auto toggling when the user explicitly doesn't want it.
    this._userToggledDisplay = false;
    this._minimized = false;

    if (this.options.toggleDisplay) {
      this._addToggleButton();
    }

    this._miniMap.whenReady(L.Util.bind(function () {
      this._aimingRect = L.rectangle(this._mainMap.getBounds(), this.options.aimingRectOptions).addTo(this._miniMap);
      this._shadowRect = L.rectangle(this._mainMap.getBounds(), this.options.shadowRectOptions).addTo(this._miniMap);
      this._mainMap.on('moveend', this._onMainMapMoved, this);
      this._mainMap.on('move', this._onMainMapMoving, this);
      this._miniMap.on('movestart', this._onMiniMapMoveStarted, this);
      this._miniMap.on('move', this._onMiniMapMoving, this);
      this._miniMap.on('moveend', this._onMiniMapMoved, this);
    }, this));

    return this._container;
  },

  addTo: function (map) {
    L.Control.prototype.addTo.call(this, map);
    this._miniMap.setView(this._mainMap.getCenter(), this._decideZoom(true));
    this._setDisplay(this._decideMinimized());
    return this;
  },

  onRemove: function (map) {
    this._mainMap.off('moveend', this._onMainMapMoved, this);
    this._mainMap.off('move', this._onMainMapMoving, this);
    this._miniMap.off('moveend', this._onMiniMapMoved, this);

    this._miniMap.removeLayer(this._layer);
  },

  _addToggleButton: function () {
    this._toggleDisplayButton = this.options.toggleDisplay ? this._createButton(
        '', this.hideText, 'leaflet-control-minimap-toggle-display', this._container, this._toggleDisplayButtonClicked, this) : undefined;
  },

  _createButton: function (html, title, className, container, fn, context) {
    var link = L.DomUtil.create('a', className, container);
    link.innerHTML = html;
    link.href = '#';
    link.title = title;

    var stop = L.DomEvent.stopPropagation;

    L.DomEvent
      .on(link, 'click', stop)
      .on(link, 'mousedown', stop)
      .on(link, 'dblclick', stop)
      .on(link, 'click', L.DomEvent.preventDefault)
      .on(link, 'click', fn, context);

    return link;
  },

  _toggleDisplayButtonClicked: function () {
    this._userToggledDisplay = true;
    if (!this._minimized) {
      this._minimize();
      this._toggleDisplayButton.title = this.showText;
    }
    else {
      this._restore();
      this._toggleDisplayButton.title = this.hideText;
    }
  },

  _setDisplay: function (minimize) {
    if (minimize != this._minimized) {
      if (!this._minimized) {
        this._minimize();
      }
      else {
        this._restore();
      }
    }
  },

  _minimize: function () {
    // hide the minimap
    if (this.options.toggleDisplay) {
      this._container.style.width = '19px';
      this._container.style.height = '19px';
      this._toggleDisplayButton.className += ' minimized';
    }
    else {
      this._container.style.display = 'none';
    }
    this._minimized = true;
  },

  _restore: function () {
    if (this.options.toggleDisplay) {
      this._container.style.width = this.options.width + 'px';
      this._container.style.height = this.options.height + 'px';
      this._toggleDisplayButton.className = this._toggleDisplayButton.className
          .replace(/(?:^|\s)minimized(?!\S)/g, '');
    }
    else {
      this._container.style.display = 'block';
    }
    this._minimized = false;
  },

//  _onMainMapMoved: function (e) {
//    if (!this._miniMapMoving) {
//      this._mainMapMoving = true;
//      this._miniMap.setView(this._mainMap.getCenter(), this._decideZoom(true));
//      this._setDisplay(this._decideMinimized());
//    } else {
//      this._miniMapMoving = false;
//    }
//    this._aimingRect.setBounds(this._mainMap.getBounds());
//  },

  _onMainMapMoving: function (e) {
    this._aimingRect.setBounds(this._mainMap.getBounds());
  },

  _onMiniMapMoveStarted:function (e) {
    var lastAimingRect = this._aimingRect.getBounds();
    var sw = this._miniMap.latLngToContainerPoint(lastAimingRect.getSouthWest());
    var ne = this._miniMap.latLngToContainerPoint(lastAimingRect.getNorthEast());
    this._lastAimingRectPosition = {sw:sw,ne:ne};
  },

  _onMiniMapMoving: function (e) {
    if (!this._mainMapMoving && this._lastAimingRectPosition) {
      this._shadowRect.setBounds(new L.LatLngBounds(this._miniMap.containerPointToLatLng(this._lastAimingRectPosition.sw),this._miniMap.containerPointToLatLng(this._lastAimingRectPosition.ne)));
      this._shadowRect.setStyle({opacity:1,fillOpacity:0.3});
    }
  },

  _onMiniMapMoved: function (e) {
    if (!this._mainMapMoving) {
      this._miniMapMoving = true;
      this._mainMap.setView(this._miniMap.getCenter(), this._decideZoom(false));
      this._shadowRect.setStyle({opacity:0,fillOpacity:0});
    } else {
      this._mainMapMoving = false;
    }
  },

  _decideZoom: function (fromMaintoMini) {
    if (!this.options.zoomLevelFixed) {
      if (fromMaintoMini)
        return this._mainMap.getZoom() + this.options.zoomLevelOffset;
      else {
        var currentDiff = this._miniMap.getZoom() - this._mainMap.getZoom();
        var proposedZoom = this._miniMap.getZoom() - this.options.zoomLevelOffset;
        var toRet;
        
        if (currentDiff > this.options.zoomLevelOffset && this._mainMap.getZoom() < this._miniMap.getMinZoom() - this.options.zoomLevelOffset) {
          //This means the miniMap is zoomed out to the minimum zoom level and can't zoom any more.
          if (this._miniMap.getZoom() > this._lastMiniMapZoom) {
            //This means the user is trying to zoom in by using the minimap, zoom the main map.
            toRet = this._mainMap.getZoom() + 1;
            //Also we cheat and zoom the minimap out again to keep it visually consistent.
            this._miniMap.setZoom(this._miniMap.getZoom() -1);
          } else {
            //Either the user is trying to zoom out past the mini map's min zoom or has just panned using it, we can't tell the difference.
            //Therefore, we ignore it!
            toRet = this._mainMap.getZoom();
          }
        } else {
          //This is what happens in the majority of cases, and always if you configure the min levels + offset in a sane fashion.
          toRet = proposedZoom;
        }
        this._lastMiniMapZoom = this._miniMap.getZoom();
        return toRet;
      }
    } else {
      if (fromMaintoMini)
        return this.options.zoomLevelFixed;
      else
        return this._mainMap.getZoom();
    }
  },

  _decideMinimized: function () {
    if (this._userToggledDisplay) {
      return this._minimized;
    }

    if (this.options.autoToggleDisplay) {
      if (this._mainMap.getBounds().contains(this._miniMap.getBounds())) {
        return true;
      }
      return false;
    }

    return this._minimized;
  }
});

L.Map.mergeOptions({
  miniMapControl: false
});

L.Map.addInitHook(function () {
  if (this.options.miniMapControl) {
    this.miniMapControl = (new L.Control.MiniMap()).addTo(this);
  }
});

L.control.minimap = function (options) {
  return new L.Control.MiniMap(options);
};


//possible fix to the zoomOut method
//_onDoubleClick: function (e) {
 // 		var map = this._map,
 //This next line was a previous implementation which was taken out.
 //-		    zoom = map.getZoom() + 1;
 //this next line was added as a revision
 //+		    zoom = map.getZoom() + (e.originalEvent.shiftKey ? -1 : 1);
  
  	//	if (map.options.doubleClickZoom === 'center') {
  		//	map.setZoom(zoom);
//Of course, this must be changed to run with our map implementation, but I think this is a good starting point for a fix.
