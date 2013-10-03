<?php
/*
Plugin Name: Carolyn Submenu Select
*/


// Plugin Options 
require_once( dirname(__FILE__) . '/carolyn-submenu-options.php' );


// Custom WordPress Meta Box
// http://www.farinspace.com/how-to-create-custom-wordpress-meta-box/


add_action('admin_init','menu_meta_init');

function menu_meta_init() {
	// http://codex.wordpress.org/Function_Reference/add_meta_box

	add_meta_box('menu_all_meta', 'Menu', 'menu_meta_setup', 'page', 'side', 'default');
	add_meta_box('carolynmenu_template_options', 'Template Options', 'template_meta_setup', 'page', 'side', 'default');
	add_action('save_post','menu_meta_save');
}


function template_meta_setup() {
	global $post;
	$meta = get_post_meta($post->ID,'_carolynmenu',TRUE);	

	echo '<p><strong>Thumbnail Size</strong><br />Sizes set via <em>Settings &rarr; Media</em></p><p><select id="gsgallery_mode" name="_carolynmenu[thumbsize]">';

	echo '<option value="medium" ';
		if( $meta['thumbsize'] == 'medium' || !$meta['thumbsize'] || $meta['thumbsize'] == '' ) echo 'selected="selected"';
	echo ' />Medium (Default)</option>';

	echo '<option value="thumbnail" ';
		if( $meta['thumbsize'] == 'thumbnail' ) echo 'selected="selected"';
	echo ' />Thumbnail</option>';

	echo '</select></p>';

	echo '<input type="hidden" name="menu_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />'; 		// create a custom nonce for submit verification later

}

function menu_meta_setup() {

	global $post;
 
	$meta = get_post_meta($post->ID,'_carolynmenu',TRUE);	

	$menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) );

	$options = get_option( 'carolyn_submenu_options' ); 
	if ( $options['type'] != 'woo' ) :

		$output .= "<p><strong>Submenu:</strong></p>";
		$output .= "<select name='_carolynmenu[sub]'>";

		$output .= '<option value="none">(none)</option>';

		foreach ( $menus as $menu ):

			$output .= '<option value="' . $menu->name . '"';
			if( $meta['sub'] == $menu->name ) $output .= ' selected="selected"';
			$output .= '>' . $menu->name . '</option>';
		
		endforeach;

		$output .= "</select>";	
	
	endif;


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


function the_carolyn_menu($location='below') {
	global $post;
	$options = get_option( 'carolyn_submenu_options' ); 

	if ( $location == 'above' && $options['placement'] == 'above' ):
		$doit = true;
	elseif ( $location != 'above' && $options['placement'] != 'above' ):
		$doit = true;
	else:
		$doit = false;
	endif;

	if ( $doit ) :
		if ( $options['type'] == 'woo' ) :
			dynamic_sidebar( 'woosidebars' );
		else :
			carolyn_get_menu($post->ID,'subnav');
		endif; 
	endif;


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


function special_nav_class($classes, $item ) {

	global $post;

	$the_menu = get_post_meta( $post->ID,'_carolynmenu', TRUE );

	$thetitle = $the_menu['highlight'];

	if ( $item->title == $thetitle ) :
//		print_r($item);
		$classes[] = "current_page_ancestor";

		if ( $item->menu_item_parent != 0 ) :
			echo 'parent: ' . $item->menu_item_parent;
		endif;

	endif;

	return $classes;

}
// add_filter('nav_menu_css_class' , 'special_nav_class' , 10, 2 );


/**
 * Hide Editor
 * @author Bill Erickson
 * @link http://www.billerickson.net/code/hide-editor-on-specific-page-template/
 */
function be_hide_editor() {
	// Get the Post ID
	if( isset( $_GET['post'] ) ) $post_id = $_GET['post'];
	elseif( isset( $_POST['post_ID'] ) ) $post_id = $_POST['post_ID'];
	if( !isset( $post_id ) ) return;
 
	// Get the Page Template
	$template_file = get_post_meta( $post_id, '_wp_page_template', true );
 
	// Exclude on these templates
	$exclude_templates = array( 'page-category.php','page-category-nav.php' );
	
	// Exclude on these IDs
//	$exclude_ids = array( get_option( 'page_on_front' ) );
 
	if( in_array( $template_file, $exclude_templates ) ) { // || in_array( $post_id, $exclude_ids )
		// Do nothing
	} else {
	    $carolynmenucss = plugins_url( 'carolyn-submenu.css' , __FILE__ ); // Static
	    wp_register_style('carolynmenucss',$carolynmenucss);
	    wp_enqueue_style( 'carolynmenucss');

	}

}
add_action( 'admin_enqueue_scripts', 'be_hide_editor' );

function this_items_menu($item_id) {

	$navmenus = get_terms( 'nav_menu' );

	foreach ( $navmenus as $navmenu ) :

		$navitems = wp_get_nav_menu_items( $navmenu->term_id );

			foreach ( $navitems as $navitem ) :

				if ( $navitem->ID == $item_id ) :
					return $navmenu->term_id;
				endif;

		endforeach;
	
	endforeach;
	
	
}


function this_posts_genealogy($thishighlight, $item) {

	$theitems = array();
	
	$this_items_menu = this_items_menu($item->ID);
	$navitems = wp_get_nav_menu_items( $this_items_menu );
	
	foreach ( $navitems as $navitem ) :
	
	 		if ( $navitem->title == $thishighlight ) {	
			$theitems[] = get_parent_menu_name($navitem);
		}
	
	
	endforeach;

	wp_reset_postdata();

	
	// To be honest I'm fuzzy on why they're repeating in here. Anyhow, remove duplicates...
	$theitems = array_unique($theitems);
	
	if ( count($theitems) > 0 ) :
	
		return $theitems;
	
	endif;

} 


function get_parent_menu_name($theitem) {

//	$theitems = array();

	$parentid = $theitem->menu_item_parent;
	
	if ( $parentid != 0 ) :

		$my_query = new WP_Query(array('p'=>$parentid, 'post_type'=>'nav_menu_item'));

		while ( $my_query->have_posts() ) : $my_query->the_post();

			return get_the_title();

		endwhile;
		
//		wp_reset_postdata();
		
	endif;


	
}



function highlight_nav( $classes, $item ) {

	global $post;

	$the_meta = get_post_meta( $post->ID,'_carolynmenu', TRUE );
	
	$thishighlight = $the_meta['highlight'];
		
	$theitems = array();
	
	if ( $thishighlight ) $theitems[] = $thishighlight;
		
	$thegenealogy = this_posts_genealogy($thishighlight, $item);
	
	if (is_array($thegenealogy)) :
	
		$thecombineditems = array_merge($theitems, $thegenealogy);
	
	else :
		$thecombineditems = $theitems;
	endif;
	
	
	foreach ( $thecombineditems as $theitem ) :
		 
		if ( $item->title == $theitem ) $classes[] = "current-menu-ancestor";

	endforeach;
	
	return $classes;

}

add_filter('nav_menu_css_class' , 'highlight_nav' , 10, 2 );

?>
