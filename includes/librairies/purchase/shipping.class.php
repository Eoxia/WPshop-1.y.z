<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Products management method file
* 
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
*	This file contains the different methods for products management
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/
class wpshop_shipping {

	/**
	 * Convert fees (string format) to fees in array format
	 * @param $fees_string : fees string type by the user
	 * @return $data : fees in array format
	 */
	function shipping_fees_string_2_array($fees_string) {
		$data = array();
		if(!empty($fees_string)) {
			if(preg_match_all('#{[^{]+}#', $fees_string, $cont)){
				foreach($cont[0] as $c) {
					preg_match_all('#([a-z]+) ?: ?"(.+)"#', $c, $atts);
					$temp_data = array();
					$country_code = '';
					foreach($atts[1] as $key => $value) {
						$temp_data[$value] =  $atts[2][$key];
						if($value=='destination') {
							$country_code = $atts[2][$key];
						}
						elseif($value=='fees') {
							$fees_data = array();
							$fees = explode(',', $atts[2][$key]);
							foreach($fees as $fee){
								$fee_element = explode(':', $fee);
								$fees_data[trim($fee_element[0])] =  trim($fee_element[1]);
							}
							$number = count($fees_data);

							$fees_data_1 = array();
							preg_match_all('#([0-9]+\.?[0-9]?+) ?: ?([0-9]+\.?[0-9]?+)#', $atts[2][$key], $fees);
							foreach($fees[1] as $_key => $_value) {
								$fees_data_1[$_value] =  $fees[2][$_key];
							}
							$number_1 = count($fees_data_1);
							if ($number == $number_1) {
								$temp_data[$value] =  $fees_data;
							}
							else {
								$temp_data[$value] =  $fees_data_1;
							}
						}
					}
					if(!empty($country_code)) {
						$data[] = $temp_data;
					}
				}
			}
			return $data;
		}
		return array();
	}
	
	/**
	 * Convert fees (array format) to fees in string format
	 * @param $fees_array : fees in array format
	 * @return $string : fees in string format
	 */
	function shipping_fees_array_2_string($fees_array) {
		$string = '';
		
		if(!empty($fees_array)) {
			foreach($fees_array as $d) {
				$string .= '{'."\n";
				foreach($d as $att => $value) {
					$val = '';
					if($att=='fees') {
						foreach($value as $_k=>$_value) $val .= $_k.':'.$_value.', ';
						$val = substr($val,0,-2);
					} else $val = $value;
					$string .= $att.': "'.$val.'",'."\n";
				}
				$string = substr($string,0,-2)."\n";
				$string .= '},'."\n";
			}
			$string = substr($string,0,-2);
			return $string;
		}
		else return false;
	}
	
	/*function calculate_shipping_cost($dest='FR', $rule_code='weight', $att_value, $fees) {
		$fees_table = array();
		$key = '';
		
		if(!empty($fees)) {
		
			// Get the fees table regarding parameter
			foreach($fees as $k => $v) {
				if(isset($fees[$k]['destination']) && $fees[$k]['destination']==$dest) {
					if(isset($fees[$k]['rule']) && $fees[$k]['rule']==$rule_code) {
						$fees_table = $fees[$k]['fees'];
						$key = $k;
					}
				}
			}
			
			// If dont get fees table, test if OTHERS rule exist
			if(empty($fees_table) && isset($fees['OTHERS'])) {
				if(isset($fees['OTHERS']['fees'])) {
					if(isset($fees['OTHERS']['rule']) && $fees['OTHERS']['rule']==$rule_code) {
						$fees_table = $fees['OTHERS']['fees'];
						$key = 'OTHERS';
					}
				} else return false;
			}
			
			// Calculate appopriate price within fees table
			if(!empty($fees_table)) {
				foreach($fees_table as $k => $v) {
					if($att_value<=$k)
						return $v;
				}
				// If $att_value overflow given fees table, recall the function
				if(!empty($key)) {
					unset($fees[$key]);
					return self::calculate_shipping_cost($dest='FR', $rule_code='weight', $att_value, $fees);
				}
			} else return false;
		}
		
		return false;
	}*/
	
	function calculate_shipping_cost($dest='FR', $data, $fees) {
		
		$fees_table = array();
		$key = '';
		
		if(!empty($fees)) {
		
			$_dest = $dest;
			for($i=0; $i<2; $i++) {
			
				// Get the fees table regarding parameter
				foreach($fees as $k => $v) {
					if(isset($fees[$k]['destination']) && $fees[$k]['destination']==$_dest) {
						// Test if the rule exist in the data given
						if(isset($fees[$k]['rule']) && isset($data[$fees[$k]['rule']])) {
							$fees_table = $fees[$k];
							$key = $k;
							break 2; // break the loop
						}
					}
				}
				
				if($_dest=='OTHERS') return false;
				else $_dest = 'OTHERS';
			}
			
			// If dont get fees table, test if OTHERS rule exist
			/*if(empty($fees_table) && isset($fees['OTHERS'])) {
				if(isset($fees['OTHERS']['fees'])) {
					// Test if the rule exist in the data given
					if(isset($fees['OTHERS']['rule']) && isset($data[$fees['OTHERS']['rule']])) {
						$fees_table = $fees['OTHERS'];
						$key = 'OTHERS';
					}
				} else return false;
			}*/
			
			// Calculate appopriate price within fees table
			if(!empty($fees_table)) {
				foreach($fees_table['fees'] as $k => $v) {
					if($data[$fees_table['rule']] <= $k) return $v;
				}
				// If $att_value overflow given fees table, recall the function
				if($key !== '') {
					unset($fees[$key]);
					return self::calculate_shipping_cost($dest, $data, $fees);
				}
			} else return false;
		}
		
		return false;
	}
}