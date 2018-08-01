<?php

/*
* Admin menu
*/
add_action('admin_menu', 'wptg_menu_pages');

function wptg_menu_pages(){
    
    add_menu_page('WP Translation Generator', 'Translation Generator', 'manage_options', 'translation-generator', 'wptg_settings_output', 'dashicons-editor-customchar' );

}

function wptg_settings_output(){

	$selected = get_option('wptg_gravity_forms');

	?>

	<div id="wpbody-content">

		<div class="wrap">
			
			<h1><?php _e('WP Translation Generator', 'wptg'); ?></h1>
			
			<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
				
				<p class="form-row">
					
					<label for="wptg_gravity_forms"><?php _e('Gravity Forms', 'wptg'); ?></label>
					
					<select name="wptg_gravity_forms" id="wptg_gravity_forms">
						
						<option value="1" <?php selected($selected, 1, true) ?> >One</option>
						
						<option value="2" <?php selected($selected, 2, true) ?>>Two</option>
					
					</select>
				
				</p>
				
				<?php 

					wp_nonce_field('wptg_setting_save', 'wptg_settings_nonce');
					
					submit_button();

				?>
			
			</form>
		
		</div>		
	
	</div>


	<?php

}


/*
* Saving setting page
*/

add_action('admin_post', 'wptg_setting_save');

function wptg_setting_save(){

	if( !isset($_POST['wptg_settings_nonce']) || !wp_verify_nonce($_POST['wptg_settings_nonce'], 'wptg_setting_save') ){

		redirect_to_wptg_settings();

	}


	if( isset($_POST['wptg_gravity_forms']) && !empty($_POST['wptg_gravity_forms']) )

		update_option('wptg_gravity_forms', $_POST['wptg_gravity_forms']);

	redirect_to_wptg_settings();

}

function redirect_to_wptg_settings(){

	$url = !isset($_POST['_wp_http_referer']) ? admin_url('page=translation-generator') : $_POST['_wp_http_referer'];

	wp_safe_redirect($url);
}
