<?php
/*
Plugin Name: Carolyn Submenu Select
*/


// Custom WordPress Meta Box
// http://www.farinspace.com/how-to-create-custom-wordpress-meta-box/


add_action('admin_init','menu_meta_init');

function menu_meta_init() {
	// http://codex.wordpress.org/Function_Reference/add_meta_box

	add_meta_box('menu_all_meta', 'Menu', 'menu_meta_setup', 'page', 'side', 'default');
	add_action('save_post','menu_meta_save');
}

function menu_meta_setup()
{
	global $post;
 
		$meta = get_post_meta($post->ID,'_carolynmenu',TRUE);	

		$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );

		$output .= "<p><strong>Submenu:</strong></p>";
		$output .= "<select name='_carolynmenu[sub]'>";

  		$output .= '<option value="none">(none)</option>';

		foreach ( $menus as $menu ):

	  		$output .= '<option value="' . $menu->name . '"';
			if( $meta['sub'] == $menu->name ) $output .= ' selected="selected"';
		 	$output .= '>' . $menu->name . '</option>';
			
		endforeach;

		$output .= "</select>";	

		$output .= "<p><strong>Navigation Highlight:</strong></p>";

		$output .= "<select name='_carolynmenu[highlight]'>";

  		$output .= '<option value="none">(none)</option>';


//	wp_nav_menu( array('theme_location' => 'navigation' )); 
// http://wikiduh.com/1541/custom-nav-menu-walker-function-to-add-classes

$highlight_locations = get_registered_nav_menus();
$highlight_menus = wp_get_nav_menus();
$highlight_menu_locations = get_nav_menu_locations();
$location_id = 'navigation';

if (isset($highlight_menu_locations[$location_id])) {

	foreach ($highlight_menus as $highlight_menu) {
		// If the ID of this menu is the ID associated with the location we're searching for
		if ($highlight_menu->term_id == $highlight_menu_locations[$location_id]) {
			// This is the correct menu

			// Get the items for this menu
			$navitems = wp_get_nav_menu_items($highlight_menu);

			// Now do something with them here.

			foreach ( $navitems as $navitem ):

		  		$output .= '<option value="' . $navitem->title . '"';
				if( $meta['highlight'] == $navitem->title ) $output .= ' selected="selected"';
			 	$output .= '>' . $navitem->title . '</option>';

			endforeach;


			break;
		}
	}
} else {
	// The location that you're trying to search doesn't exist
}

		$output .= "</select>";

		echo $output;

		echo '<input type="hidden" name="menu_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />'; 		// create a custom nonce for submit verification later
}
 
function menu_meta_save($post_id) {
	if (!wp_verify_nonce($_POST['menu_meta_noncename'],__FILE__)) return $post_id;
	if ($_POST['post_type'] == 'page') {
		if (!current_user_can('edit_page', $post_id)) return $post_id;
	} else {
		if (!current_user_can('edit_post', $post_id)) return $post_id;
	}

	$current_data = get_post_meta($post_id, '_carolynmenu', TRUE);	
 
	$new_data = $_POST['_carolynmenu'];

	menu_meta_clean($new_data);
	
	if ($current_data) {
		if (is_null($new_data)) delete_post_meta($post_id,'_carolynmenu');
		else update_post_meta($post_id,'_carolynmenu',$new_data);
	} elseif (!is_null($new_data)) {
		add_post_meta($post_id,'_carolynmenu',$new_data,TRUE);
	}

	return $post_id;
}

function menu_meta_clean(&$arr) {
	if (is_array($arr)) {
		foreach ($arr as $i => $v) {
			if (is_array($arr[$i])) {
				menu_meta_clean($arr[$i]);

				if (!count($arr[$i])) {
					unset($arr[$i]);
				}
			} else {
				if (trim($arr[$i]) == '') {
					unset($arr[$i]);
				}
			}
		}

		if (!count($arr)) {
			$arr = NULL;
		}
	}
}




function carolyn_get_menu($post_id, $class='') {
	
	$the_menu = get_post_meta($post_id,'_carolynmenu',TRUE);
	
	if ( $the_menu['sub'] ) {

		$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );

		foreach ( $menus as $menu )	:
			if( $the_menu['sub'] == $menu->name ) {
				if ($class !== '') echo '<div class="' . $class .'">'; 
				wp_nav_menu( array('menu' => $the_menu['sub'] ));	
				if ($class !== '') echo '</div>';
			}
			
		endforeach;

	}
	
}


add_filter('nav_menu_css_class' , 'special_nav_class' , 10, 2 );

function special_nav_class($classes, $item ) {

	global $post;

	$the_menu = get_post_meta( $post->ID,'_carolynmenu', TRUE );

	$thetitle = $the_menu['highlight'];

	if ( $item->title == $thetitle ) $classes[] = "current_page_ancestor";

	return $classes;

}


?>
