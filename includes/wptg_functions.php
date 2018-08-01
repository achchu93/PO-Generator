<?php

/*
* Admin menu
*/
add_action('admin_menu', 'wptg_menu_pages');

function wptg_menu_pages(){
    
    add_menu_page('WP Translation Generator', 'Translation Generator', 'manage_options', 'translation-generator', 'wptg_settings_output', 'dashicons-editor-customchar' );

}

function wptg_settings_output(){

	$selected = get_option('wptg_gravity_forms', array());
	$g_forms = GFAPI::get_forms();
	?>

	<div id="wpbody-content">

		<div class="wrap">
			
			<h1><?php _e('WP Translation Generator', 'wptg'); ?></h1>

			<form method="post" action="<?php echo admin_url('admin-post.php'); ?>">

				<p class="form-row">

					<label for="wptg_gravity_forms"><?php _e('Gravity Forms', 'wptg'); ?></label>
					<select name="wptg_gravity_forms[]" id="wptg_gravity_forms" multiple>
                        <?php
                            if( count($g_forms) ){

                                foreach ( $g_forms as $g_form ) :
                                    ?>

                                    <option value="<?php echo $g_form['id'] ?>" <?php echo  in_array($g_form['id'], $selected) ? "selected" : "" ?> ><?php echo $g_form['title'] ?></option>

                                <?php endforeach;

                            }
                            else{
                            ?>
                                <option value="0" disabled ><?php _e('No Gravity Forms Found', 'wptg')?></option>
                            <?php
                            }
                        ?>
                    </select>

				</>

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

function wptg_enqueue_scripts() {

    global $current_screen;

    if ( !isset($current_screen) || $current_screen->id != 'toplevel_page_translation-generator' ) return;

    wp_enqueue_style( 'wptg-select2-css', WPTG_URL . 'assets/css/select2.min.css' );
    wp_enqueue_style( 'wptg-admin-css', WPTG_URL . 'assets/css/admin.css' );
    wp_enqueue_script( 'wptg-select2-js', WPTG_URL . 'assets/js/select2.full.min.js' , array('jquery'), null, true );
    wp_enqueue_script( 'wptg-admin-js', WPTG_URL . 'assets/js/admin.js' , array('jquery'), null, true );
}
add_action( 'admin_enqueue_scripts', 'wptg_enqueue_scripts' );

