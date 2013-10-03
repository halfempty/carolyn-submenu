<?php 
// Options Screen

add_action( 'admin_init', 'carolyn_submenu_options_init' );
add_action( 'admin_menu', 'carolyn_submenu_menu' );

/**
 * Init plugin options to white list our options
 */
function carolyn_submenu_options_init(){
	register_setting( 'carolyn_submenu_options', 'carolyn_submenu_options', 'carolyn_submenu_validate' );
}

/**
 * Load up the menu page
 */

function carolyn_submenu_menu() {
	add_options_page( 'Submenu Options', 'Submenu', 'manage_options', 'carolyn-submenu-options', 'carolyn_submenu_options' );
}


function carolyn_submenu_options() {

	if ( ! isset( $_REQUEST['settings-updated'] ) )
		$_REQUEST['settings-updated'] = false;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	} ?>
	
<div class="wrap">	

<?php screen_icon();?>

<h2>Submenu Options</h2>


		<form method="post" action="options.php">
			<?php settings_fields( 'carolyn_submenu_options' ); ?>
			<?php $options = get_option( 'carolyn_submenu_options' ); ?>

<h3>Submenu Placement</h3>

<p>
<input id="carolyn_submenu_placement_below" name="carolyn_submenu_options[placement]" type="radio" value="below" <?php
if( $options['placement'] != 'above' ) echo 'checked="checked"';	
?> />
<label class="description" for="carolyn_submenu_placement_below"> <strong>Below</strong> slideshow/content</label>
<br />

<input id="carolyn_submenu_placement_above" name="carolyn_submenu_options[placement]" type="radio" value="above" <?php
if( $options['placement'] == 'above' ) echo 'checked="checked"';	
?> />
<label class="description" for="carolyn_submenu_placement_above"> <strong>Above</strong> slideshow/content</label>
</p>

<h3>Submenu Type</h3>

<p>
<input id="carolyn_submenu_type_marty" name="carolyn_submenu_options[type]" type="radio" value="marty" <?php
if( $options['type'] != 'woo' ) echo 'checked="checked"';	
?> />
<label class="description" for="carolyn_submenu_type_marty"> Marty&rsquo;s Plugin</label>
<br />

<input id="carolyn_submenu_type_woo" name="carolyn_submenu_options[type]" type="radio" value="woo" <?php
if( $options['type'] == 'woo' ) echo 'checked="checked"';	
?> />
<label class="description" for="carolyn_submenu_type_woo"> WooSidebars Plugin</label>
</p>

<p class="submit">
	<input type="submit" class="button-primary" value="Save Options" />
</p>



	</form>
	</div>

<?php }


/**
 * Sanitize and validate input. Accepts an array, return a sanitized array.
 */
function carolyn_submenu_validate( $input ) {

	$types = get_post_types('','names'); 

	$alwaysexclude = array('attachment','revision','nav_menu_item');

	foreach ( $types as $key => $value) :
		if ( !in_array( $key, $alwaysexclude ) ) : 
			
			if ( ! isset( $input[$key] ) ) $input[$key] = "exclude";

		endif;
	endforeach;

	return $input;

}

?>