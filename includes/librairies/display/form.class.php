<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Form management
 *
 * Define the different method to create a form dynamically from a database table field list
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wp-paybox
 * @subpackage librairies
 */

/**
 * Define the different method to create a form dynamically from a database table field list
 * @package wp-paybox
 * @subpackage librairies
 */
class wpshop_form {
	/**
	*	Create The complete form by defining the form open and close and call the different function that allows to create the different type of input
	*
	*	@param string $name The name of the form
	*	@param array $input_list The list build by the database class' function that get the type of a table
	*	@param string $method The default method for the form Default is set to post
	*	@param string $action The default action for the form Default is set to empty
	*
	*	@return mixed $the_form The complete html output of the form
	*/
	function form($name, $input_list, $method = 'post', $action = ''){
		$the_form_content_hidden = $the_form_content = '';
		foreach ($input_list as $input_key => $input_def) {
			$the_input = self::check_input_type($input_def);
			$input_name = $input_def['name'];
			$input_value = $input_def['value'];
			$input_type = $input_def['type'];

			if($input_type != 'hidden')
			{
				$label = 'for="' . $input_name . '"';
				if(($input_type == 'radio') || ($input_type == 'checkbox'))
				{
					$label = '';
				}
				$the_form_content .= '
<div>
	<label ' . $label . ' >' . __($input_name, 'wpshop') . '</label>&nbsp;:&nbsp;
	' . $the_input . '
</div>';
			}
			else
			{
				$the_form_content_hidden .= '
	' . $the_input;
			}
		}

		$the_form = '
<form name="' . $name . '" id="' . $name . '" method="' . $method . '" action="' . $action . '" >' . $the_form_content_hidden . $the_form_content . '
</form>';

		return $the_form;
	}

	/**
	*	Check the input type
	*
	*	@param array $input_def The input definition
	*
	*	@return string $the_input
	*/
	function check_input_type($input_def, $input_domain = '') {
		$input_option = '';
		if(!empty($input_def['option']) && $input_def['option'])
			$input_option = $input_def['option'];

		$valueToPut = '';
		if(!empty($input_def['valueToPut']) && $input_def['valueToPut'])
			$valueToPut = $input_def['valueToPut'];

		$input_id = $input_def['name'];
		if(!empty($input_def['id']))
			$input_id = $input_def['id'];

		$input_name = $input_def['name'];
		if($input_domain != '')
			$input_name = $input_domain . '[' . $input_def['name'] . ']';

		// Formatage des donn�es
		if(!empty($input_def['value']) && !is_array($input_def['value']) && preg_match("/^-?(?:\d+|\d*\.\d+)$/", $input_def['value']))
			$input_value = str_replace('.',',',$input_def['value']/1); // format francais avec virgule
		else $input_value = (!empty($input_def['value']) ? $input_def['value'] : '');

		$input_type = $input_def['type'];
		$the_input = '';

		if($input_type == 'text')
			$the_input .= self::form_input( $input_name, $input_id, $input_value, 'text', $input_option, (!empty($input_def['options']['label']) ? $input_def['options']['label'] : '') );
		elseif($input_type == 'password')
			$the_input .= self::form_input($input_name, $input_id, $input_value, 'password', $input_option);
		elseif($input_type == 'textarea')
			$the_input .= self::form_input_textarea($input_name, $input_id, $input_value, $input_option);
		elseif($input_type == 'hidden')
			$the_input .= self::form_input($input_name, $input_id, $input_value, 'hidden', $input_option);
		elseif($input_type == 'select')
			$the_input .= self::form_input_select($input_name, $input_id, ( !empty($input_def['possible_value']) ? $input_def['possible_value'] : array() ), $input_value, $input_option, $valueToPut);
		elseif($input_type == 'multiple-select')
			$the_input .= self::form_input_multiple_select($input_name, $input_id, ( !empty($input_def['possible_value']) ? $input_def['possible_value'] : array() ), $input_value, $input_option, $valueToPut);
		elseif(($input_type == 'radio') || ($input_type == 'checkbox'))
			$the_input .= self::form_input_check($input_name, $input_id, ( !empty($input_def['possible_value']) ? $input_def['possible_value'] : array() ), $input_value, $input_type, $input_option, $valueToPut, (!empty($input_def['options']['label']) ? $input_def['options']['label'] : ''));
		elseif($input_type == 'file')
			$the_input .= self::form_input($input_name, $input_id, $input_value, 'file', $input_option);
		elseif($input_type == 'gallery')
			$the_input .= self::form_input($input_name, $input_id, $input_value, 'text', 'readonly = "readonly"') . 'Gallery field to check';

		return $the_input;
	}

	/**
	*	Create an input type text or hidden or password
	*
	*	@param string $name The name of the field given by the database
	*	@param mixed $value The default value for the field Default is empty
	*	@param string $type The input type Could be: text or hidden or passowrd
	*	@param string $option Allows to define options for the input Could be readonly or disabled or style
	*
	*	@return mixed The output code to add to the form
	*/
	function form_input($name, $id, $value = '', $type = 'text', $option = '', $input_label = ''){

		$allowedType = array('text', 'hidden', 'password', 'file');
		if(in_array($type, $allowedType))
		{
			$output = '<input type="' . $type . '" name="' . $name . '" id="' . $id . '" value="' . $value . '" ' . $option . ' />' ;
		}
		else
		{
			return sprintf(__('Input type not allowed here in %s at line %s', 'wpshop'), __FILE__, __LINE__);
		}
		$output.=(is_array($input_label) && !empty($input_label['custom']) ? '<label for="' . $id . '">'.$input_label['custom'].'</label> ':'');
		return $output;
	}

	/**
	*	Create an textarea
	*
	*	@param string $name The name of the field given by the database
	*	@param mixed $value The default value for the field Default is empty
	*	@param string $option Allows to define options for the input Could be maxlength or style
	*
	*	@return mixed The output code to add to the form
	*/
	function form_input_textarea($name, $id, $value = '', $option = '')
	{
		return '<textarea name="' . $name.'" id="' . $id . '" ' . $option . ' rows="4" cols="10" >' . $value . '</textarea>';
	}

	/**
	*	Create a combo box input regarding to the type of content given in parameters could be an array or a wordpress database object
	*
	*	@param string $name The name of the field given by the database
	*	@param mixed $content The list of element to put inot the combo box Could be an array or a wordpress database object with id and nom as field
	*	@param mixed $value The selected value for the field Default is empty
	*	@param string $option Allows to define options for the input Could be onchange
	*
	*	@return mixed $output The output code to add to the form
	*/
	function form_input_select($name, $id, $content, $value = '', $option = '', $optionValue = ''){
		global $comboxOptionToHide;

		$output = '<select id="' . $id . '" name="' . $name . '" ' . $option . ' data-placeholder="' . __('Select an Option', 'wpshop') . '" >';

		if(is_array($content) && (count($content) > 0)){
			foreach($content as $index => $datas){
				if(is_object($datas) && (!is_array($comboxOptionToHide) || !in_array($datas->id, $comboxOptionToHide))){
					$selected = ($value == $datas->id) ? ' selected="selected" ' : '';

					$dataText = __('Nothing to output' ,'wpshop');
					if(isset($datas->name))
						$dataText = __($datas->name ,'wpshop');
					elseif(isset($datas->code))
						$dataText = __($datas->code ,'wpshop');

					$output .= '<option value="' . $datas->id . '" ' . $selected . ' >' . $dataText. '</option>';
				}
				elseif(!is_array($comboxOptionToHide) || !in_array($datas, $comboxOptionToHide)){
					$valueToPut = $datas;
					$selected = ($value == $datas) ? ' selected="selected" ' : '';
					if($optionValue == 'index'){
						$valueToPut = $index;
						$selected = ($value == $index) ? ' selected="selected" ' : '';
					}
					$output .= '<option value="' . $valueToPut . '" ' . $selected . ' >' . __($datas ,'wpshop') . '</option>';
				}
			}
		}
		else
			$output .= '<option value="" >'.__('Nothing found here...', 'wpshop').'</option>';

			$output .= '</select>';

		return $output;
	}

	/**
	*	Create a combo box input regarding to the type of content given in parameters could be an array or a wordpress database object
	*
	*	@param string $name The name of the field given by the database
	*	@param mixed $content The list of element to put inot the combo box Could be an array or a wordpress database object with id and nom as field
	*	@param mixed $value The selected value for the field Default is empty
	*	@param string $option Allows to define options for the input Could be onchange
	*
	*	@return mixed $output The output code to add to the form
	*/
	function form_input_multiple_select($name, $id, $content, $value = array(), $option = '', $optionValue = '') {
		global $comboxOptionToHide;

		$values = array();
		if (!empty($value) && (is_array($value))) {
			foreach($value as $v) {
				$values[] = $v->value;
			}
		}
		else {
			$values = (array)$value;
		}

		$output = '';
		if (is_array($content) && (count($content) > 0)) {
			$output = '<select id="' . $id . '" name="' . $name . '[]" ' . $option . ' multiple size="4" data-placeholder="' . __('Select values from list', 'wpshop') . '" >';

			foreach($content as $index => $datas) {
				if (is_object($datas) && (!is_array($comboxOptionToHide) || !in_array($datas->id, $comboxOptionToHide))) {
					//$selected = ($value == $datas->id) ? ' selected="selected" ' : '';
					$selected = in_array($datas->id, $values) ? ' selected="selected" ' : '';

					$dataText = __($datas->name ,'wpshop');
					if (isset($datas->code)) {
						$dataText = __($datas->code ,'wpshop');
					}
					$output .= '<option value="' . $datas->id . '" ' . $selected . ' >' . $dataText . '</option>';
				}
				elseif (!is_array($comboxOptionToHide) || !in_array($datas, $comboxOptionToHide)) {
					$valueToPut = $datas;
					//$selected = ($value == $datas) ? ' selected="selected" ' : '';
					$selected = in_array($datas, $values) ? ' selected="selected" ' : '';
					if($optionValue == 'index'){
						$valueToPut = $index;
						//$selected = ($value == $index) ? ' selected="selected" ' : '';
						$selected = in_array($index, $values) ? ' selected="selected" ' : '';
					}
					$output .= '<option value="' . $valueToPut . '" ' . $selected . ' >' . __($datas ,'wpshop') . '</option>';
				}
			}

			$output .= '</select>';
		}

		return $output;
	}

	/**
	*	Create a checkbox input
	*
	*	@param string $name The name of the field given by the database
	*	@param string $id The identifier of the field
	*	@param string $type The input type Could be checkbox or radio
	*	@param mixed $content The list of element to put inot the combo box Could be an array or a wordpress database object with id and nom as field
	*	@param mixed $value The selected value for the field Default is empty
	*	@param string $option Allows to define options for the input Could be onchange
	*
	*	@return mixed $output The output code to add to the form
	*/
	function form_input_check($name, $id, $content, $value = '', $type = 'checkbox', $option = '', $optionValue = '', $input_label=''){
		$output = '';
		$allowedType = array('checkbox', 'radio');
		$container_start = (isset($input_label['container']) && $input_label['container'] ? '<div class="wpshop_input_' . $type . ' wpshop_input_' . $type . '_' . $id . '" >' : '');
		$container_end = (isset($input_label['container']) && $input_label['container'] ? '</div>' : '');

		if(in_array($type, $allowedType)){
			if(is_array($content) && (count($content) > 0)){
				foreach($content as $index => $datas){
					if(is_object($datas)){
						$id = $name . '_' . sanitize_title($datas->nom);
						$checked = ($value == $datas->id) ? ' checked="checked" ' : '';
					}
					else{
						$valueToPut = $datas;
						$checked = ( ($value == $datas) || (is_array($value) && in_array($valueToPut, $value))) ? ' checked="checked" ' : '';
						if($optionValue == 'index'){
							$valueToPut = $index;
							$checked = ( ($value == $index) || (is_array($value) && in_array($valueToPut, $value))) ? ' checked="checked" ' : '';
						}
						$id = $id . '_' . sanitize_title($datas);
						$checked = ( ($value == $datas) || (is_array($value) && in_array($valueToPut, $value))) ? ' checked="checked" ' : '';
						$output .= $container_start . '<input type="' . $type . '" name="' . $name . '" id="' . $id . '" value="' . $valueToPut . '" ' . $checked . ' ' . $option . ' />'.(!empty($input_label['original'])?'<label for="' . $id . '">'.__($datas,'wpshop').'</label>&nbsp;':'')  . $container_end ;
					}
				}
			}
			else{
				$checked = (($value != '') && ($value == $content)) ? ' checked="checked" ' : '';
				$output .= $container_start . '<input type="' . $type . '" name="' . $name . '" id="' . $id . '" value="' . $content . '" ' . $checked . ' ' . $option . ' />' . $container_start ;
			}
			$output.=(is_array($input_label) && !empty($input_label['custom']) ? '<label for="' . $id . '">'.$input_label['custom'].'</label> ':'');

			if ( isset($input_label['container']) && $input_label['container'] ) $output .= '<div class="clear" ></div>';
			return $output;
		}
		else
			return sprintf(__('Input type not allowed here in %s at line %s', 'wpshop'), __FILE__, __LINE__);
	}

}