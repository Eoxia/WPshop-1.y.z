<?php
/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Define the different method to manage address
 *
 *	Define the different method and variable used to manage address
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wpshop
 * @subpackage librairies
 */

class wpshop_address{

	/**
	 * Generate an array with all fields for the address form construction. Classified by address type.
	 * @param $typeof
	 * @return array
	 */
	function get_addresss_form_fields_by_type ( $typeof, $id ='' ) {
		$current_item_edited = isset($id) ? (int)wpshop_tools::varSanitizer($id) : null;
		$address = array();
		/*	Get the attribute set details in order to build the product interface	*/
		$atribute_set_details = wpshop_attributes_set::getAttributeSetDetails($typeof, "'valid'");
		if (count($atribute_set_details) > 0) {

			foreach ($atribute_set_details as $productAttributeSetDetail) {
				$address = array();
				$group_name = $productAttributeSetDetail['name'];
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute) {
						if(!empty($attribute->id)) {
							if ( !empty($_POST['submitbillingAndShippingInfo']) ) {
								$value = $_POST['attribute'][$typeof][$attribute->data_type][$attribute->code];
							}
							else {
								$value = wpshop_attributes::getAttributeValueForEntityInSet($attribute->data_type, $attribute->id, wpshop_entities::get_entity_identifier_from_code(WPSHOP_NEWTYPE_IDENTIFIER_ADDRESS), $current_item_edited, array('intrinsic' => $attribute->is_intrinsic, 'backend_input' => $attribute->backend_input));
							}
							$attribute_output_def = wpshop_attributes::get_attribute_field_definition( $attribute, $value, array() );
							$attribute_output_def['id'] = 'address_' . $typeof . '_' .$attribute_output_def['id'];
							$address[str_replace( '-', '_', sanitize_title($group_name) ).'_'.$attribute->code] = $attribute_output_def;
						}
					}
				}
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['name'] = $group_name;
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['content'] = $address;
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['id'] = str_replace('-', '_', sanitize_title($group_name));
				$all_addresses[$productAttributeSetDetail['attribute_set_id']][$productAttributeSetDetail['id']]['attribute_set_id'] = $productAttributeSetDetail['attribute_set_id'];
			}

		}

		return $all_addresses;
	}
	/**
	 * Generate a google map with the addresses which are passed in parameters
	 * @param string $addresses
	 * @return string
	 */
	function generate_map ( $addresses = '' ) {

		$formated_addresses = self::convert_addresses( $addresses );
		$result = '<div id="map"></div>';
		$result .= ' <script type="text/javascript">
		<!--
	    var locations = ' .$formated_addresses. '

	    var map = new google.maps.Map(document.getElementById(\'map\'), {
	      zoom: 6,
	      center: new google.maps.LatLng(47.4,1.6),
	          mapTypeControl: true,
	        mapTypeControlOptions: {
	      style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
	    },
	    navigationControl: true,
	     navigationControlOptions: {
	        style: google.maps.NavigationControlStyle.SMALL,
	        position: google.maps.ControlPosition.TOP_RIGHT
	    },
	        scaleControl: true,
	    streetViewControl: false,
	      mapTypeId: google.maps.MapTypeId.ROADMAP
	    });

	    var infowindow = new google.maps.InfoWindow();

	    var marker, i;

	   for (i = 0; i < locations.length; i++) {
	      marker = new google.maps.Marker({
	        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
	        map: map
	      });

	      google.maps.event.addListener(marker, \'click\', (function(marker, i) {
	        return function() {
	          infowindow.setContent(locations[i][0]);
	          infowindow.open(map, marker);
	        }
	      })(marker, i));
	    }
	-->
	  </script>';
		return $result;
	}

	/**
	 * Formate the array of address to be used in javascript generation map
	 * @param array $addresses
	 */
	function convert_addresses ( $addresses ) {
		$address_array = "[";
		foreach ($addresses as $address ) {
			$address_array .= "['".$address['infos']."', ".$address['longitude'].", ".$address['latitude']."],";
		}
		$address_array .= "]";
		return $address_array;
	}
	/**
	 * Generate the GPS coord. from an address
	 * @param unknown_type $adresse
	 * @return array $adresse:
	 */
	function get_coord_from_address($address)
	{
		$google_map_key = get_option('wpshop_google_map_api_key');
		if ( !empty( $google_map_key ) ) {
			$address = urlencode($address);
			$url = 'http://maps.google.com/maps/geo?q=' . $address . '&output=xml&oe=utf8&gl=fr&sensor=false&key='.$google_map_key ;
			$page = file_get_contents($url);

			$xml_result = new SimpleXMLElement($page);

			if ($xml_result->Response->Status->code != 200) return array();

			$adresses = array();
			foreach ($xml_result->Response->Placemark as $place) {
				list($longitude, $latitude, $altitude) = explode(',', $place->Point->coordinates);

				$adresses = array('latitude' => $latitude,'longitude' => $longitude);
			}

		}
		else {
			$adresses = array('latitude' => '','longitude' => '');
		}
		return $adresses;
	}

}