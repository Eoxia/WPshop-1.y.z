<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * BREADCRUMB
 */
ob_start();
?>
<div id="wps-breadcrumb">{WPSHOP_BREADCRUMB_CONTENT}</div>
<?php
$tpl_element['wpshop']['default']['wpshop_breadcrumb'] = ob_get_contents();
ob_end_clean();


/**
 * BREADCRUMB FIRST ELEMENT
 */
ob_start();
?>
<li><strong>{WPSHOP_CATEGORY_NAME}</strong></li>
<?php
$tpl_element['wpshop']['default']['wpshop_breadcrumb_first_element'] = ob_get_contents();
ob_end_clean();


/**
 * BREADCRUMB ELEMENT
 */


ob_start();
?>
<li itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="{WPSHOP_CATEGORY_LINK}" title="<?php _e('Go to','wpshop'); ?> {WPSHOP_CATEGORY_NAME}" ><span itemprop="title">{WPSHOP_CATEGORY_NAME}</span></a> {WPSHOP_OTHERS_CATEGORIES_LIST}<i>›</i></li>
<?php
$tpl_element['wpshop']['default']['wpshop_breadcrumb_element'] = ob_get_contents();
ob_end_clean();


/**
 * BREADCRUMB OTHERS CATEGORIES ELEMENT
 */


ob_start();
?>
<li itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" href="{WPSHOP_ELEMENT_LIST_CATEGORY_LINK}" title="<?php _e('Go to','wpshop'); ?> {WPSHOP_ELEMENT_LIST_CATEGORY_NAME}" ><span itemprop="title">{WPSHOP_ELEMENT_LIST_CATEGORY_NAME}</span></a></li>
<?php
$tpl_element['wpshop']['default']['wpshop_breadcrumb_others_categories_list_element'] = ob_get_contents();
ob_end_clean();


/**
 * BREADCRUMB OTHERS CATEGORIES LIST
 */


ob_start();
?>
<!-- <div class="wpshop_breadcrumb_other_categories_list">[+]</div>
<ul>
{WPSHOP_ELEMENTS_LIST}
</ul> -->
<ul>
{WPSHOP_ELEMENTS_LIST}
</ul>
<?php
$tpl_element['wpshop']['default']['wpshop_breadcrumb_others_categories_list'] = ob_get_contents();
ob_end_clean();