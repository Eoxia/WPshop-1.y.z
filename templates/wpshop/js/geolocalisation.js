	var map;
	var geocoder;
	var coord;
	var marker;
	var the_new_coord;

	function show_map()
	{
	  if (GBrowserIsCompatible())
		{
			map = new GMap2(document.getElementById("wp_Gmap"));
			map.setCenter(new GLatLng(43.6086723,3.8801916), 9);
			map.addControl(new GLargeMapControl);
			map.enableScrollWheelZoom();
			geocoder = new GClientGeocoder();
		}
	}

	function getCoordonnees( address )
	{
		//var address = document.getElementById(adress).value + " " + document.getElementById(town).value + " " + document.getElementById(postal_code).value;

		geocoder.getLatLng(address,function (coord)
									{
										if(coord)
										{
											document.getElementById(latitude_input).value = coord.y;
											document.getElementById(longitude_input).value = coord.x;

											geocoder.getLocations(coord,FillExtraFields);
											map.clearOverlays();
											generateMarker(coord, map, geocoder);
										}
										
									}
		);
	}

	function getDraggedCoordonees(response)
	{
		if (!response || response.Status.code != 200)
		{
      alert("Status Code:" + response.Status.code);
    } 
		else 
		{
      place = response.Placemark[0];
			document.getElementById(latitude_input).value = place.Point.coordinates[1];
			document.getElementById(longitude_input).value = place.Point.coordinates[0];
    }
	}

	function FillExtraFields(response)
	{
    if (!response || response.Status.code != 200) 
		{
      alert("Sorry, we were unable to geocode that address");
    } 
		else 
		{
      place = response.Placemark[0];

			if(place != undefined){
				if(place.AddressDetails != undefined){
					if(place.AddressDetails.Country != undefined){
						if(place.AddressDetails.Country.CountryName != undefined){
							document.getElementById(input_country).value = place.AddressDetails.Country.CountryName;
						}
						if(place.AddressDetails.Country.AdministrativeArea != undefined){
							if(place.AddressDetails.Country.AdministrativeArea.AdministrativeAreaName != undefined){
								document.getElementById(input_region).value = place.AddressDetails.Country.AdministrativeArea.AdministrativeAreaName;
							}
							if(place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea != undefined){
								if(place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.SubAdministrativeAreaName != undefined){
									document.getElementById(input_dept).value = place.AddressDetails.Country.AdministrativeArea.SubAdministrativeArea.SubAdministrativeAreaName;
								}
							}
						}
					}
				}
			}
    }
  }

	function generateMarker (coordinates, themap, geocoder_object)
	{
		var pro_icon = new GIcon();
		pro_icon.image = image_icon;
		pro_icon.iconSize = new GSize(32,32);
		pro_icon.iconAnchor = new GPoint(32,32);

		marker = new GMarker(coordinates,{draggable: true, icon:pro_icon});

		themap.addOverlay(marker);
		GEvent.addListener(marker, "dragend", function getAddress() 
			{
				geocoder.getLocations(marker.getLatLng(), getDraggedCoordonees);
			});
			
		themap.setCenter(coordinates, 17);
	}
