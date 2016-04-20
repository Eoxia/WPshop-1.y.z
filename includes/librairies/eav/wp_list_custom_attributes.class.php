<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}



/**
 * Define the different method to manage attributes listing with wordpress methods
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_attributes_custom_List_table extends WP_List_Table{
	var $datas;

	/** ************************************************************************
	 * REQUIRED. Set up a constructor that references the parent constructor. We
	 * use the parent reference to set some default configs.
	 ***************************************************************************/
	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => __('attribute\'s', 'wpshop'),    //singular name of the listed records
			'plural'    => __('attributes\'', 'wpshop'),    //plural name of the listed records
			'ajax'     => true       //does this table support ajax?
		) );
	}

	/*	Allows to set different listings for attributes	*/
	function get_views(){
		$wpshop_attribute_links = array();

		$active_nb = wpshop_attributes::getElement('', "'valid'");
		$unactive_nb = wpshop_attributes::getElement('', "'moderated', 'notused'");
		$deleted_nb = wpshop_attributes::getElement('', "'deleted'");

		$wpshop_attribute_links['wpshop_attributes_links wpshop_attributes_links_valid'.(empty($_REQUEST['attribute_status'])?' current':'')] = '<a href="'.admin_url('admin.php?page=' . WPSHOP_URL_SLUG_ATTRIBUTE_LISTING).'" >'.__('Used attributes', 'wpshop').' ('.count($active_nb).')</a>';
		$wpshop_attribute_links['wpshop_attributes_links wpshop_attributes_links_moderated'.(!empty($_REQUEST['attribute_status']) && ($_REQUEST['attribute_status']=='unactive')?' current':'')] = '<a href="'.admin_url('admin.php?page=' . WPSHOP_URL_SLUG_ATTRIBUTE_LISTING.'&attribute_status=unactive').'" >'.__('Unactive attributes', 'wpshop').' ('.count($unactive_nb).')</a>';
		$wpshop_attribute_links['wpshop_attributes_links wpshop_attributes_links_deleted'.(!empty($_REQUEST['attribute_status']) && ($_REQUEST['attribute_status']=='deleted')?' current':'')] = '<a href="'.admin_url('admin.php?page=' . WPSHOP_URL_SLUG_ATTRIBUTE_LISTING.'&attribute_status=deleted').'" >'.__('Trashed attributes', 'wpshop').' ('.count($deleted_nb).')</a>';
		$wpshop_attribute_links['unit_management_link'] = '<a href="#" id="wpshop_attribute_unit_manager_opener">'.__('Unit management', 'wpshop').'</a>';
		$wpshop_attribute_links['unit_management_dialog'] = '<div id="wpshop_attribute_unit_manager"title="' . __('Unit management', 'wpshop') . '" class="wpshopHide" ><div class="loading_picture_container" id="product_chooser_picture" ><img src="' . WPSHOP_LOADING_ICON . '" alt="loading..." /></div></div>';
		return $wpshop_attribute_links;
	}

	/** ************************************************************************
	 * REQUIRED! This method dictates the table's columns and titles. This should
	 * return an array where the key is the column slug (and class) and the value
	 * is the column's title text. If you need a checkbox for bulk actions, refer
	 * to the $columns array below.
	 *
	 * The 'cb' column is treated differently than the rest. If including a checkbox
	 * column in your table you must create a column_cb() method. If you don't need
	 * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_columns(){
		$columns = array(
			'cb'       	=> '',//'<input type="checkbox" />', //Render a checkbox instead of text
			'id'       	=> __('Id.', 'wpshop'),
			'name'    	=> __('Name', 'wpshop'),
			'status'    => __('Status', 'wpshop'),
			'entity'    => __('Affected entity', 'wpshop'),
			'code'    	=> __('Attribute code', 'wpshop')
		);
		return $columns;
	}

	/** ************************************************************************
	 * Recommended. This method is called when the parent class can't find a method
	 * specifically build for a given column. Generally, it's recommended to include
	 * one method for each column you want to render, keeping your package class
	 * neat and organized. For example, if the class needs to process a column
	 * named 'title', it would first see if a method named $this->column_title()
	 * exists - if it does, that method will be used. If it doesn't, this one will
	 * be used. Generally, you should try to use custom column methods as much as
	 * possible.
	 *
	 * Since we have defined a column_title() method later on, this method doesn't
	 * need to concern itself with any column with a name of 'title'. Instead, it
	 * needs to handle everything else.
	 *
	 * For more detailed insight into how columns are handled, take a look at
	 * WP_List_Table::single_row_columns()
	 *
	 * @param array $item A singular item (one full row's worth of data)
	 * @param array $column_name The name/slug of the column to be processed
	 * @return string Text or HTML to be placed inside the column <td>
	 **************************************************************************/
	function column_default($item, $column_name){
		switch($column_name){
			default:
				return print_r($item,true); //Show the whole array for troubleshooting purposes
		}
	}

	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_id($item){
		return $item['id'];
	}
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_status($item){
		return __($item['status'], 'wpshop');
	}
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_code($item){
		return $item['code'];
	}
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_entity($item){
		return __($item['entity'],'wpshop');
	}
	/** ************************************************************************
	 * Recommended. This is a custom column method and is responsible for what
	 * is rendered in any column with a name/slug of 'title'. Every time the class
	 * needs to render a column, it first looks for a method named
	 * column_{$column_title} - if it exists, that method is run. If it doesn't
	 * exist, column_default() is called instead.
	 *
	 * This example also illustrates how to implement rollover actions. Actions
	 * should be an associative array formatted as 'slug'=>'link html' - and you
	 * will need to generate the URLs yourself. You could even ensure the links
	 *
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_name($item){
		$attribute_undeletable = unserialize(WPSHOP_ATTRIBUTE_UNDELETABLE);

		$link_format = admin_url('admin.php').'?page=%2$s&amp;action=%3$s&amp;id=%4$s';
		$default_action='edit';
		$default_action_text=__('Edit', 'wpshop');
		if(!empty($_REQUEST['attribute_status']) && ($_REQUEST['attribute_status']=='deleted')){
			$default_action='activate';
			$default_action_text=__('Restore', 'wpshop');
		}
		//Build row actions
		$actions = array(
			'edit'     => sprintf('<a href="'.$link_format.'">'.$default_action_text.'</a>',WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_URL_SLUG_ATTRIBUTE_LISTING,$default_action,$item['id'])
		);
		if(empty($_REQUEST['attribute_status']) && (!in_array($item['code'], $attribute_undeletable))){
			$actions['delete'] = sprintf('<a href="'.$link_format.'">'.__('Delete', 'wpshop').'</a>',WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_URL_SLUG_ATTRIBUTE_LISTING,'delete',$item['id']);
		}

		//Return the title contents
		return sprintf('%1$s%2$s',
			/*$1%s*/ sprintf('<a href="'.$link_format.'">'.__($item['name'], 'wpshop').'</a>',WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES, WPSHOP_URL_SLUG_ATTRIBUTE_LISTING,$default_action,$item['id']),
			/*$3%s*/ $this->row_actions($actions)
		);
	}
	/** ************************************************************************
	 * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
	 * is given special treatment when columns are processed. It ALWAYS needs to
	 * have it's own method.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 * @param array $item A singular item (one full row's worth of data)
	 * @return string Text to be placed inside the column <td> (movie title only)
	 **************************************************************************/
	function column_cb($item){
		return '';
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
			/*$2%s*/ $item['id']             //The value of the checkbox should be the record's id
		);
	}

	/** ************************************************************************
	 * Optional. If you want one or more columns to be sortable (ASC/DESC toggle),
	 * you will need to register it here. This should return an array where the
	 * key is the column that needs to be sortable, and the value is db column to
	 * sort by. Often, the key and value will be the same, but this is not always
	 * the case (as the value is a column name from the database, not the list table).
	 *
	 * This method merely defines which columns should be sortable and makes them
	 * clickable - it does not handle the actual sorting. You still need to detect
	 * the ORDERBY and ORDER querystring variables within prepare_items() and sort
	 * your data accordingly (usually by modifying your query).
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
	 **************************************************************************/
	function get_sortable_columns() {
		$sortable_columns = array(
			'name'    => array('frontend_label',true),    //true means its already sorted
			'status'  => array('status',true)
		);
		return $sortable_columns;
	}

	/** ************************************************************************
	 * Optional. If you need to include bulk actions in your list table, this is
	 * the place to define them. Bulk actions are an associative array in the format
	 * 'slug'=>'Visible Title'
	 *
	 * If this method returns an empty value, no bulk action will be rendered. If
	 * you specify any bulk actions, the bulk actions box will be rendered with
	 * the table automatically on display().
	 *
	 * Also note that list tables are not automatically wrapped in <form> elements,
	 * so you will need to create those manually in order for bulk actions to function.
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
	 **************************************************************************/
	function get_bulk_actions() {
		$actions = array(
			// 'delete'    => __('Delete','wpshop')
		);
		return $actions;
	}

	/** ************************************************************************
	 * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
	 * For this example package, we will handle it in the class to keep things
	 * clean and organized.
	 *
	 * @see $this->prepare_items()
	 **************************************************************************/
	function process_bulk_action() {
		//Detect when a bulk action is being triggered...
		if( 'delete'===$this->current_action() ) {
			wp_die('Items deleted (or they would be if we had items to delete)!');
		}
	}

	/** ************************************************************************
	 * REQUIRED! This is where you prepare your data for display. This method will
	 * usually be used to query the database, sort and filter the data, and generally
	 * get it ready to be displayed. At a minimum, we should set $this->items and
	 * $this->set_pagination_args(), although the following properties and methods
	 * are frequently interacted with here...
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/
	function prepare_items() {
		/*	First, lets decide how many records per page to show	*/
		$per_page = 10;//$this->get_items_per_page('attributes_per_page', 10);

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
		 * Optional. You can handle your bulk actions however you see fit. In this
		 * case, we'll handle them within our package just to keep things clean.
		*/
		$this->process_bulk_action();

		/**
		 * Instead of querying a database, we're going to fetch the example data
		 * property we created for use in this plugin. This makes this example
		 * package slightly different than one you might build on your own. In
		 * this example, we'll be using array manipulation to sort and paginate
		 * our data. In a real-world implementation, you will probably want to
		 * use sort and pagination data to build a custom query instead, as you'll
		 * be able to use your precisely-queried data immediately.
		*/
		$data = $this->datas;

		/**
		 * REQUIRED for pagination. Let's figure out what page the user is currently
		 * looking at. We'll need this later, so you should always include it in
		 * your own package classes.
		*/
		$current_page = $this->get_pagenum();

		/**
		 * REQUIRED for pagination. Let's check how many items are in our data array.
		 * In real-world use, this would be the total number of items in your database,
		 * without filtering. We'll need this later, so you should always include it
		 * in your own package classes.
		*/
		$total_items = count($data);

		/**
		 * The WP_List_Table class does not handle pagination for us, so we need
		 * to ensure that the data is trimmed to only the current page. We can use
		 * array_slice() to
		*/
		$data = array_slice($data,(($current_page-1)*$per_page),$per_page);

		/**
		 * REQUIRED. Now we can add our *sorted* data to the items property, where
		 * it can be used by the rest of the class.
		*/
		$this->items = $data;

		/*	REQUIRED. We also have to register our pagination options & calculations.	*/
		$this->set_pagination_args( array(
			'total_items' => $total_items,               		//WE have to calculate the total number of items
			'per_page'    => $per_page,                 		//WE have to determine how many items to show on a page
			'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
		) );
	}
}
