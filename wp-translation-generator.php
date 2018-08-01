<?php
/*
Plugin Name: WP Translation Generator
Plugin URI: https://wordpress.com/
Description: Generate Translation File
Version: 1.0
Author: Admin
Author URI: https://wordpress.com/
License: GPL
Text Domain: wptg
Domain Path: /lang
*/


if( !defined( 'ABSPATH' ) ) exit;

if( !in_array('gravityforms/gravityforms.php', get_option('active_plugins')) ){

	function dependency_error(){

		?>
		
		<div class="error notice">

			<p><?php _e( 'WP Translation Generator requires Gravity Forms Plugin', 'my_plugin_textdomain' ); ?></p>
		
		</div>

		<?php

	}

	add_action('admin_notices', 'dependency_error');

	return;
}


/*
*  Plugin Constants
*/

if( !defined('WPTG_URL')) define('WPTG_URL', plugin_dir_url( __FILE__ ) );

if( !defined('WPTG_PATH')) define('WPTG_PATH', plugin_dir_path( __FILE__ ) );


/*
* Libraries
*/

require_once "includes/gettext/src/autoloader.php";

require_once "includes/cldr-to-gettext-plural-rules/src/autoloader.php";

require_once "includes/wptg_functions.php";

