<?php if ( !defined( 'ABSPATH' ) ) exit;

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}


//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class Customer_List_Table extends WP_List_Table {

	var $datas;

	/**
	* Constructor, we override the parent to pass our own arguments
	* We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	*/
	function __construct(){
			parent::__construct( array(
			'singular'=> 'wp_list_customer', //Singular label
			'plural' => 'wp_list_customers', //plural label, also this well be one of the table css class
			'ajax' => false
		) );
	}

	/**
	*	Define the output for each column if specific column output has not been defined
	*
	*	@param object|array $item The item to output column content for
	*	@param string $column_name The column that we are trying to output
	*
	*	@return mixed The column output if found, or defaut is an complete output of the item
	*/
	function column_default($item, $column_name){
		switch($column_name){
			case 'customer_id':{
				return '<label for="wpshop_customer_order_cb_dialog_' . $item->ID . '" >' . WPSHOP_IDENTIFIER_CUSTOMER . $item->ID . '</label>';
			}break;
			case 'customer_lastname':{
				return $item->user_lastname;
			}break;
			case 'customer_firstname':{
				return $item->user_firstname;
			}break;
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
			break;
		}
	}

	/**
	*	Define specific outptu for a given column
	*
	*	@param object|array $item The item we want to get the specific output for the current column
	*
	*	@return string The output build specificly for the given column
	*/
	function column_cb($item){
		return sprintf(
				'<input type="radio" name="%1$s" value="%2$s" class="wpshop_customer_order_cb_dialog" id="wpshop_customer_order_cb_dialog_%2$s" name="customer_order" />',
				/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
				/*$2%s*/ $item->ID                //The value of the checkbox should be the record's id
		);
	}

	/**
	* Define the columns that are going to be used in the table
	* @return array $columns, the array of columns to use with the table
	*/
	function get_columns() {
		return $columns= array(
			'cb'=>'',
			'customer_id'=>'',
			'customer_lastname'=>__('Lastname', 'wpshop'),
			'customer_firstname'=>__('Firstname', 'wpshop')
		);
	}

	/**
	* Define the columns that are going to be used for sorting the table
	* @return array $columns, the array of sortable columns in the table
	*/
	function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	/**
	*	Build The table output for future display
	*
	*	@param array|object $data, The list of item to display in the table
	*	@param int $per_page, The number of items per page in the table
	*	@param int $current_page, The current page number allowing to know wich item to display
	*
	*	@return void
	*/
	function prepare_items($data, $per_page, $current_page){



		debug_print_backtrace();


		/**
		* REQUIRED. Now we need to define our column headers. This includes a complete
		* array of columns to be displayed (slugs & titles), a list of columns
		* to keep hidden, and a list of columns that are sortable. Each of these
		* can be defined in another method (as we've done here) before being
		* used to build the value for our _column_headers property.
		*/
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();

		/**
		 * REQUIRED. Finally, we build an array to be used by the class for column
		 * headers. The $this->_column_headers property takes an array which contains
		 * 3 other arrays. One for all columns, one for hidden columns, and one
		 * for sortable columns.
		 */
		$this->_column_headers = array($columns, $hidden, $sortable);

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		 */
		$this->items = $this->datas;

		/**
		 * REQUIRED. We also have to register our pagination options & calculations.
		 */
		$this->set_pagination_args(array());
	}

}