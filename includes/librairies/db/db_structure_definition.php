<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Plugin database definition file.
*
*	This file contains the different definitions for the database structure. It will permit to check if database is correctly build
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies-db
*/

$wpshop_update_way = array();
$wpshop_db_table = array();
$wpshop_db_table_list = array();
$wpshop_db_table_operation_list = array();
$wpshop_db_request = array();
$wpshop_db_version = 0;

/*	Define the different database table	*/
	/*	Entities	*/
	$t = WPSHOP_DBT_ENTITIES;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	id INT(10) unsigned NOT NULL AUTO_INCREMENT ,
	status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
	creation_date datetime ,
	last_update_date datetime ,
	code varchar(50) collate utf8_unicode_ci NOT NULL ,
	entity_table varchar(255) collate utf8_unicode_ci NOT NULL ,
	PRIMARY KEY (id),
	KEY status (status),
	UNIQUE code (code)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute set	*/
	$t = WPSHOP_DBT_ATTRIBUTE_SET;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
	status ENUM('valid','moderated','deleted') NULL DEFAULT 'valid' ,
	default_set ENUM('yes','no') NULL DEFAULT 'no' ,
	creation_date datetime ,
	last_update_date datetime ,
	position INT(10) NOT NULL DEFAULT '0' ,
	entity_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
	name VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_swedish_ci' NOT NULL DEFAULT '' ,
	PRIMARY KEY (id) ,
	KEY position (position) ,
	KEY status (status) ,
	KEY entity_id (entity_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute set	*/
	$t = WPSHOP_DBT_ATTRIBUTE_GROUP;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT ,
	status ENUM('valid','moderated','deleted') NULL DEFAULT 'valid' ,
	default_group ENUM('yes','no') NULL DEFAULT 'no' ,
	attribute_set_id INT UNSIGNED NOT NULL DEFAULT '0' ,
	position INT NOT NULL DEFAULT '0' ,
	creation_date datetime ,
	last_update_date datetime ,
	backend_display_type ENUM('fixed-tab','movable-tab') NULL DEFAULT 'fixed-tab' ,
	used_in_shop_type ENUM('presentation','sale') NULL DEFAULT '" . WPSHOP_DEFAULT_SHOP_TYPE . "' ,
	display_on_frontend ENUM('yes','no') NULL DEFAULT 'yes' ,
	code VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' ,
	name VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL DEFAULT '' ,
	PRIMARY KEY (id) ,
	UNIQUE attribute_set_id_name_unique (attribute_set_id, code) ,
	KEY attribute_set_id_position_key (attribute_set_id, position) ,
	KEY attribute_set_id_index (attribute_set_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute units	*/
	$t = WPSHOP_DBT_ATTRIBUTE_UNIT;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  creation_date datetime default NULL,
  last_update_date datetime default NULL,
  group_id int(10) default NULL,
  is_default_of_group enum('yes','no') collate utf8_unicode_ci default 'no',
  unit char(25) collate utf8_unicode_ci NOT NULL,
  name char(50) collate utf8_unicode_ci NOT NULL,
  change_rate decimal(12,5),
  PRIMARY KEY  (id),
  KEY status (status)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute units group	*/
	$t = WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  creation_date datetime default NULL,
  last_update_date datetime default NULL,
  name varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (id),
  KEY status (status)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute	*/
	$t = WPSHOP_DBT_ATTRIBUTE;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  status enum('valid','moderated','deleted','notused') collate utf8_unicode_ci NOT NULL default 'valid',
  creation_date datetime default NULL,
  last_update_date datetime default NULL,
  entity_id int(10) unsigned NOT NULL default '0',
  is_visible_in_front enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  is_visible_in_front_listing enum('yes','no') collate utf8_unicode_ci NOT NULL default 'yes',
  is_global enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_user_defined enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_required enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_visible_in_advanced_search enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_searchable enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_filterable enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_comparable enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_html_allowed_on_front enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_unique enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_filterable_in_search enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_used_for_sort_by enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_configurable enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_requiring_unit enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_recordable_in_cart_meta enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_used_in_admin_listing_column enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_used_in_quick_add_form enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_used_for_variation enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  is_used_in_variation enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  _display_informations_about_value enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  _need_verification enum('yes','no') collate utf8_unicode_ci NOT NULL default 'no',
  _unit_group_id int(10) default NULL,
  _default_unit int(10) default NULL,
  is_historisable enum('yes','no') collate utf8_unicode_ci default 'yes',
  is_intrinsic enum('yes','no') collate utf8_unicode_ci default 'no',
  data_type_to_use enum('custom','internal') collate utf8_unicode_ci NOT NULL default 'custom',
  use_ajax_for_filling_field enum('yes','no') collate utf8_unicode_ci default 'no',
  data_type enum('datetime','decimal','integer','text','varchar') collate utf8_unicode_ci NOT NULL default 'varchar',
  backend_table varchar(255) collate utf8_unicode_ci default NULL,
  backend_label varchar(255) collate utf8_unicode_ci default NULL,
  backend_input enum('text', 'textarea', 'select', 'multiple-select', 'password', 'hidden', 'radio', 'checkbox') collate utf8_unicode_ci NOT NULL default 'text',
  frontend_label varchar(255) collate utf8_unicode_ci default NULL,
  frontend_input enum('text', 'textarea', 'select', 'multiple-select', 'password', 'hidden','radio', 'checkbox') collate utf8_unicode_ci NOT NULL default 'text',
  frontend_verification enum('','username','email','postcode','country','state','phone') collate utf8_unicode_ci default NULL,
  code varchar(255) collate utf8_unicode_ci NOT NULL default '',
  note varchar(255) collate utf8_unicode_ci NOT NULL,
  default_value text collate utf8_unicode_ci,
  frontend_css_class varchar(255) collate utf8_unicode_ci default NULL,
  backend_css_class varchar(255) collate utf8_unicode_ci default NULL,
  frontend_help_message varchar(255) collate utf8_unicode_ci default NULL,
  PRIMARY KEY	(id),
  UNIQUE KEY code (code),
  KEY status (status),
  KEY is_global (is_global),
  KEY is_user_defined (is_user_defined),
  KEY is_required (is_required),
  KEY is_visible_in_advanced_search (is_visible_in_advanced_search),
  KEY is_searchable (is_searchable),
  KEY is_filterable (is_filterable),
  KEY is_comparable (is_comparable),
  KEY is_html_allowed_on_front (is_html_allowed_on_front),
  KEY is_unique (is_unique),
  KEY is_filterable_in_search (is_filterable_in_search),
  KEY is_used_for_sort_by (is_used_for_sort_by),
  KEY is_configurable (is_configurable),
  KEY is_requiring_unit (is_requiring_unit),
  KEY is_recordable_in_cart_meta (is_recordable_in_cart_meta),
  KEY use_ajax_for_filling_field (use_ajax_for_filling_field),
  KEY data_type (data_type)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute	*/
	$t = WPSHOP_DBT_ATTRIBUTE_DETAILS;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
	status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
	creation_date datetime ,
	last_update_date datetime ,
	entity_type_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
	attribute_set_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
	attribute_group_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
	attribute_id INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
	position INT(10) NOT NULL DEFAULT '0' ,
	PRIMARY KEY (id) ,
	KEY status (status),
	KEY attribute_set_id (attribute_set_id, position) ,
	KEY position (position) ,
	KEY attribute_id (attribute_id) ,
	KEY attribute_set_id_position (attribute_set_id) ,
	KEY attribute_group_id (attribute_group_id) ,
	KEY entity_type_id (entity_type_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute	values (VARCHAR) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  value_id int(10) NOT NULL AUTO_INCREMENT,
  entity_type_id int(10) unsigned NOT NULL default '0',
  attribute_id int(10) unsigned NOT NULL default '0',
  entity_id int(10) unsigned NOT NULL default '0',
  unit_id int(10) unsigned NOT NULL default '0',
	user_id bigint(20) unsigned NOT NULL default '1',
	creation_date_value datetime,
  language char(10) collate utf8_unicode_ci NOT NULL default '" . WPSHOP_CURRENT_LOCALE . "',
  value varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (value_id),
  KEY entity_id (entity_id),
  KEY attribute_id (attribute_id),
  KEY entity_type_id (entity_type_id),
  KEY unit_id (unit_id),
  KEY language (language)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	/*	Attribute	values (DATETIME) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  value_id int(10) NOT NULL AUTO_INCREMENT,
  entity_type_id int(10) unsigned NOT NULL default '0',
  attribute_id int(10) unsigned NOT NULL default '0',
  entity_id int(10) unsigned NOT NULL default '0',
  unit_id int(10) unsigned NOT NULL default '0',
	user_id bigint(20) unsigned NOT NULL default '1',
	creation_date_value datetime,
  language char(10) collate utf8_unicode_ci NOT NULL default '" . WPSHOP_CURRENT_LOCALE . "',
  value datetime default NULL,
  PRIMARY KEY  (value_id),
  KEY entity_id (entity_id),
  KEY attribute_id (attribute_id),
  KEY entity_type_id (entity_type_id),
  KEY unit_id (unit_id),
  KEY language (language)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	/*	Attribute	values (DECIMAL) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  value_id int(10) NOT NULL AUTO_INCREMENT,
  entity_type_id int(10) unsigned NOT NULL,
  attribute_id int(10) unsigned NOT NULL,
  entity_id int(10) unsigned NOT NULL,
  unit_id int(10) unsigned NOT NULL default '0',
	user_id bigint(20) unsigned NOT NULL default '1',
	creation_date_value datetime,
  language char(10) collate utf8_unicode_ci NOT NULL default '" . WPSHOP_CURRENT_LOCALE . "',
  value decimal(12,5) NOT NULL,
  PRIMARY KEY  (value_id),
  KEY entity_id (entity_id),
  KEY attribute_id (attribute_id),
  KEY entity_type_id (entity_type_id),
  KEY unit_id (unit_id),
  KEY language (language)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	/*	Attribute	values (INTEGER) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  value_id int(10) NOT NULL AUTO_INCREMENT,
  entity_type_id int(10) unsigned NOT NULL default '0',
  attribute_id int(10) unsigned NOT NULL default '0',
  entity_id int(10) unsigned NOT NULL default '0',
  unit_id int(10) unsigned NOT NULL default '0',
	user_id bigint(20) unsigned NOT NULL default '1',
	creation_date_value datetime,
  language char(10) collate utf8_unicode_ci NOT NULL default '" . WPSHOP_CURRENT_LOCALE . "',
  value int(10) NOT NULL,
  PRIMARY KEY  (value_id),
  KEY entity_id (entity_id),
  KEY attribute_id (attribute_id),
  KEY entity_type_id (entity_type_id),
  KEY unit_id (unit_id),
  KEY language (language)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
	/*	Attribute	values (TEXT) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  value_id int(10) NOT NULL AUTO_INCREMENT,
  entity_type_id int(10) unsigned NOT NULL default '0',
  attribute_id int(10) unsigned NOT NULL default '0',
  entity_id int(10) unsigned NOT NULL default '0',
  unit_id int(10) unsigned NOT NULL default '0',
	user_id bigint(20) unsigned NOT NULL default '1',
	creation_date_value datetime,
  language char(10) collate utf8_unicode_ci NOT NULL default '" . WPSHOP_CURRENT_LOCALE . "',
  value longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (value_id),
  KEY entity_id (entity_id),
  KEY attribute_id (attribute_id),
  KEY entity_type_id (entity_type_id),
  KEY unit_id (unit_id),
  KEY language (language)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Attribute	values (HISTO) */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
  value_id int(10) NOT NULL AUTO_INCREMENT,
  status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
  creation_date datetime default NULL,
  last_update_date datetime default NULL,
  original_value_id int(10) unsigned NOT NULL default '0',
  entity_type_id int(10) unsigned NOT NULL default '0',
  attribute_id int(10) unsigned NOT NULL default '0',
  entity_id int(10) unsigned NOT NULL default '0',
  unit_id int(10) unsigned NOT NULL default '0',
	user_id bigint(20) unsigned NOT NULL default '1',
	creation_date_value datetime,
  language char(10) collate utf8_unicode_ci NOT NULL default '" . WPSHOP_CURRENT_LOCALE . "',
  value longtext collate utf8_unicode_ci NOT NULL,
  value_type char(70) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (value_id),
  KEY entity_id (entity_id),
  KEY attribute_id (attribute_id),
  KEY entity_type_id (entity_type_id),
  KEY unit_id (unit_id),
  KEY language (language),
  KEY status (status)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

	/*	Message history of send message */
	$t = WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
	status enum('valid','moderated','deleted') collate utf8_unicode_ci NOT NULL default 'valid',
	creation_date datetime ,
	last_update_date datetime ,
	attribute_id INT(10) UNSIGNED NOT NULL,
	position INT(10) UNSIGNED NOT NULL DEFAULT '1',
	value VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
	label VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

	/*	Plugin documentation */
	$t = $wpdb->prefix . wpshop_doc::prefix . '__documentation';
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	doc_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	doc_active ENUM('active', 'deleted') default 'active',
	doc_page_name varchar(255) NOT NULL,
	doc_url varchar(255) NOT NULL,
	doc_html text NOT NULL,
	doc_creation_date datetime NOT NULL,
	PRIMARY KEY ( doc_id )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";


	/*	Users' cart */
	$t = WPSHOP_DBT_CART;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	id int(11) unsigned NOT NULL AUTO_INCREMENT,
	session_id varchar(255) DEFAULT NULL,
	user_id int(11) unsigned DEFAULT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

	/*	Users' cart content */
	$t = WPSHOP_DBT_CART_CONTENTS;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	id int(11) unsigned NOT NULL AUTO_INCREMENT,
	cart_id int(11) unsigned NOT NULL,
	product_id int(11) unsigned NOT NULL,
	product_qty int(11) unsigned NOT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

	/*	Messages send to user */
	$t = WPSHOP_DBT_MESSAGES;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	mess_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	mess_user_id bigint(20) unsigned NOT NULL,
	mess_user_email varchar(255) NOT NULL,
	mess_object_type varchar(55) NOT NULL,
	mess_object_id int(11) NOT NULL,
	mess_title varchar(255) NOT NULL,
	mess_message text CHARACTER SET utf8 NOT NULL,
	mess_statut enum('sent','resent') NOT NULL DEFAULT 'sent',
	mess_visibility enum('normal','archived') NOT NULL DEFAULT 'normal',
	mess_creation_date datetime NOT NULL,
	mess_last_dispatch_date datetime NOT NULL,
	PRIMARY KEY (mess_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

	/*	Message history of send message */
	$t = WPSHOP_DBT_HISTORIC;
	$wpshop_db_table[$t] =
"CREATE TABLE {$t} (
	hist_id int(11) unsigned NOT NULL AUTO_INCREMENT,
	hist_message_id int(11) unsigned NOT NULL,
	hist_datetime datetime NOT NULL,
	PRIMARY KEY (hist_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


/*	Start the different creation and update plan	*/
{/*	Version 0	*/
	$wpshop_db_version = 0;
	$wpshop_update_way[$wpshop_db_version] = 'creation';

	$wpshop_db_table_operation_list[$wpshop_db_version]['ADD_TABLE'] = array(WPSHOP_DBT_ENTITIES, WPSHOP_DBT_ATTRIBUTE_SET, WPSHOP_DBT_ATTRIBUTE_GROUP, WPSHOP_DBT_ATTRIBUTE_UNIT, WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_DETAILS, WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT);
	$wpshop_db_table_list[$wpshop_db_version] = array(/*WPSHOP_DBT_ENTITIES, */WPSHOP_DBT_ATTRIBUTE_SET, WPSHOP_DBT_ATTRIBUTE_GROUP, WPSHOP_DBT_ATTRIBUTE_UNIT, WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_DETAILS, WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT);
}

{/*	Version 1	*/
	$wpshop_db_version = 1;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_UNIT] = array('group_id', 'is_default_of_group');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('_unit_group_id', '_default_unit', 'is_historisable');
	$wpshop_db_table_operation_list[$wpshop_db_version]['ADD_TABLE'] = array($wpdb->prefix . wpshop_doc::prefix . '__documentation', WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP, WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO);

	$wpshop_db_table_list[$wpshop_db_version] = array($wpdb->prefix . wpshop_doc::prefix . '__documentation', WPSHOP_DBT_ATTRIBUTE_UNIT, WPSHOP_DBT_ATTRIBUTE_UNIT_GROUP, WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, WPSHOP_DBT_ATTRIBUTE);
}

{/*	Version 2	*/
	$wpshop_db_version = 2;
	$wpshop_update_way[$wpshop_db_version] = 'creation';

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_CART, WPSHOP_DBT_CART_CONTENTS);
}

{/*	Version 3	*/
	$wpshop_db_version = 3;
	$wpshop_update_way[$wpshop_db_version] = 'update';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR] = array('user_id', 'creation_date_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO] = array('user_id', 'creation_date_value');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME, WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL, WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER, WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT, WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR, WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO);
}

{/*	Version 4	*/
	$wpshop_db_version = 4;
	$wpshop_update_way[$wpshop_db_version] = 'update';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('is_intrinsic');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE);
}

{/*	Version 7	*/
	$wpshop_db_version = 7;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT] = array(array('field' => 'value', 'type' => 'longtext'));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO] = array(array('field' => 'value', 'type' => 'longtext'));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL] = array(array('field' => 'value', 'type' => 'decimal(12,5)'));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE] = array(array('field' => 'status', 'type' => "enum('valid','moderated','deleted','notused')"));
	// $wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE] = array(array('field' => 'backend_input', 'type' => "enum('text','textarea','select')"));
	$wpshop_db_table_operation_list[$wpshop_db_version]['ADD_TABLE'] = array(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS);

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT, WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL);
}

{/*	Version 8	*/
	$wpshop_db_version = 8;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}

{/*	Version 9	- 1.3.0.2	*/
	$wpshop_db_version = 9;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	/*	Add some explanation in order to check done update	*/
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_SET] = array('default_set');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_GROUP] = array('default_group');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE_SET, WPSHOP_DBT_ATTRIBUTE_GROUP);
}

{/*	Version 10	- 1.3.0.3	*/
	$wpshop_db_version = 10;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}

{/*	Version 11	- 1.3.0.4	*/
	$wpshop_db_version = 11;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}

{/*	Version 12	- 1.3.0.6	*/
	$wpshop_db_version = 12;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}

{/*	Version 13	- 1.3.0.7	*/
	$wpshop_db_version = 13;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}

{/*	Version 14	- 1.3.1.0	*/
	$wpshop_db_version = 14;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}

{/*	Version 15	- 1.3.1.1	*/
	$wpshop_db_version = 15;
	$wpshop_update_way[$wpshop_db_version] = 'datas';

	/*	Add some explanation in order to check done update	*/
// 	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_MESSAGES] = array('mess_object_type', 'mess_object_id');

// 	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_MESSAGES);
}

{/*	Version 16	- 1.3.1.2	*/
	$wpshop_db_version = 16;
	$wpshop_update_way[$wpshop_db_version] = 'datas';

	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE] = array(array('field' => 'backend_input', 'type' => "enum('text','textarea','select','multiple-select')"));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_GROUP] = array('backend_display_type');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_GROUP);
	$wpshop_db_request[$wpshop_db_version][] = "ALTER TABLE ".WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME." DROP INDEX entity_attribute_id";
	$wpshop_db_request[$wpshop_db_version][] = "ALTER TABLE ".WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL." DROP INDEX entity_attribute_id";
	$wpshop_db_request[$wpshop_db_version][] = "ALTER TABLE ".WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT." DROP INDEX entity_attribute_id";
	$wpshop_db_request[$wpshop_db_version][] = "ALTER TABLE ".WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER." DROP INDEX entity_attribute_id";
	$wpshop_db_request[$wpshop_db_version][] = "ALTER TABLE ".WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR." DROP INDEX entity_attribute_id";
}

{/*	Version 17  - 1.3.1.3	*/
	$wpshop_db_version = 17;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}

{/*	Version 18  - 1.3.1.5	*/
	$wpshop_db_version = 18;
	$wpshop_update_way[$wpshop_db_version] = 'datas';
}

{/*	Version 19  - 1.3.1.7	*/
	$wpshop_db_version = 19;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_GROUP] = array('used_in_shop_type');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('is_recordable_in_cart_meta');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_GROUP);
}

{/*	Version 20  - 1.3.1.8	*/
	$wpshop_db_version = 20;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('data_type_to_use', 'use_ajax_for_filling_field');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_GROUP] = array('display_on_frontend');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_GROUP);
}

{/*	Version 21  - 1.3.2.0	*/
	$wpshop_db_version = 21;
	$wpshop_update_way[$wpshop_db_version] = 'data';
}

{/*	Version 22  - 1.3.2.3	*/
	$wpshop_db_version = 22;
	$wpshop_update_way[$wpshop_db_version] = 'data';
}

{/*	Version 23  - 1.3.2.4	*/
	$wpshop_db_version = 23;
	$wpshop_update_way[$wpshop_db_version] = 'data';
}

{/*	Version 24  - 1.3.2.5	*/
	$wpshop_db_version = 24;
	$wpshop_update_way[$wpshop_db_version] = 'data';
}

{/*	Version 25  - 1.3.2.6	*/
	$wpshop_db_version = 25;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('is_used_in_admin_listing_column');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE);
}

{/*	Version 26  - 1.3.2.7	*/
	$wpshop_db_version = 26;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';
}

{/*	Version 27  - 1.3.2.8	*/
	$wpshop_db_version = 27;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('is_visible_in_front_listing');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE);
}

{/*	Version 28  - 1.3.2.9	*/
	$wpshop_db_version = 28;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('is_used_in_quick_add_form');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE);
}

{/*	Version 29  - 1.3.3.4	*/
	$wpshop_db_version = 29;
	$wpshop_update_way[$wpshop_db_version] = 'multiple';

	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_CHANGE'][WPSHOP_DBT_ATTRIBUTE] = array(array('field' => 'frontend_verification', 'type' => "enum('','email','postcode','country','state','phone')"));
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE] = array('is_used_for_variation', 'is_used_in_variation', '_display_informations_about_value');
	$wpshop_db_table_operation_list[$wpshop_db_version]['FIELD_ADD'][WPSHOP_DBT_ATTRIBUTE_UNIT] = array('change_rate');

	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE, WPSHOP_DBT_ATTRIBUTE_UNIT);
}

{/*	Version 30  - 1.3.3.5	*/
	$wpshop_db_version = 30;
}

{/*	Version dev	- Call for every plugin db version	*/
	$wpshop_db_version = 'dev';
	$wpshop_update_way[$wpshop_db_version] = 'multiple';
// 	$wpshop_db_table_list[$wpshop_db_version] = array(WPSHOP_DBT_ATTRIBUTE);
}