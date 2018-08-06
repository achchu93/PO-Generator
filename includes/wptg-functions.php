<?php

if( !defined( 'ABSPATH' ) ) die();

add_action('admin_menu', 'wptg_menu_page');
add_action('admin_post', 'wptg_setting_save');
add_action( 'admin_enqueue_scripts', 'wptg_enqueue_scripts' );
add_filter('upload_mimes', 'add_po_mime_type', 1, 1);
add_action( 'gform_after_submission', 'generate_translation', 10, 2 );
add_action( 'gform_loaded', 'load_wptg_field', 1 );
add_filter( 'gform_validation', 'wptg_gform_validation' );
add_filter( 'gform_validation_message', 'change_message', 10, 2 );


$errorMessage = "";

/**
 * add admin menu page
 */
function wptg_menu_page(){
    add_menu_page('WP Translation Generator', 'Translation Generator', 'manage_options', 'translation-generator', 'wptg_settings_output', 'dashicons-editor-customchar' );
}

/**
 * admin menu page output
 */
function wptg_settings_output(){
    $selected = get_option('wptg_gravity_forms', array());
    $g_forms = GFAPI::get_forms();
    wptg_generate_file(WPTG_PATH . "lang/wc_crm-en.po", "FR");
?>

    <div id="wpbody-content">
        <div class="wrap">
            <h1><?php _e('WP Translation Generator', 'wptg'); ?></h1>

            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <p class="form-row">
                    <label for="wptg_gravity_forms"><?php _e('Gravity Forms', 'wptg'); ?></label>
                    <select name="wptg_gravity_forms[]" id="wptg_gravity_forms" multiple>
                        <?php
                        if(count($g_forms)) :
                            foreach ($g_forms as $g_form) : ?>
                                <option value="<?php echo $g_form['id'] ?>" <?php echo  in_array($g_form['id'], $selected) ? "selected" : "" ?> ><?php echo $g_form['title'] ?></option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value="0" disabled ><?php _e('No Gravity Forms Found', 'wptg')?></option>
                        <?php endif; ?>
                    </select>
                </p>
            </form>
        </div>
    </div>
    <?php
}

/**
 * save admin settings
 */
function wptg_setting_save()
{
    if (!isset($_POST['wptg_settings_nonce']) || !wp_verify_nonce($_POST['wptg_settings_nonce'], 'wptg_setting_save')) {
        redirect_to_wptg_settings();
    }

    if (isset($_POST['wptg_gravity_forms'])){
        update_option('wptg_gravity_forms', $_POST['wptg_gravity_forms']);
    }else{
        delete_option('wptg_gravity_forms');
    }

    redirect_to_wptg_settings();
}

/**
 * redirect to plugin admin page
 */
function redirect_to_wptg_settings(){

	$url = !isset($_POST['_wp_http_referer']) ? admin_url('page=translation-generator') : $_POST['_wp_http_referer'];

	wp_safe_redirect($url);
}

/**
 * enqueueing admin styles and scripts
 */
function wptg_enqueue_scripts() {

    global $current_screen;

    if ( !isset($current_screen) || $current_screen->id != 'toplevel_page_translation-generator' ) return;

    wp_enqueue_style( 'wptg-select2-css', WPTG_URL . 'assets/css/select2.min.css' );
    wp_enqueue_style( 'wptg-admin-css', WPTG_URL . 'assets/css/admin.css' );
    wp_enqueue_script( 'wptg-select2-js', WPTG_URL . 'assets/js/select2.full.min.js' , array('jquery'), null, true );
    wp_enqueue_script( 'wptg-admin-js', WPTG_URL . 'assets/js/admin.js' , array('jquery'), null, true );
}

/**
 * enable .po file type uploading
 *
 * @param $mime_types
 * @return mixed
 */
function add_po_mime_type($mime_types){
    $mime_types['po'] = 'text/x-gettext-translation'; //Adding svg extension
    return $mime_types;
}

/**
 * do generating after form submission
 * @param $entry
 * @param $form
 */
function generate_translation($entry, $form ) {
    $po = "";
    $tl = "";
    $sl = "";

    foreach ($form['fields'] as $field){
        if($field instanceof WPTG_PO_File_Input){
            $po = rgar($entry, $field->id);
        }
        if($field instanceof  WPTG_Target_Language_Field){
            $tl = rgar($entry, $field->id);
        }
        if($field instanceof WPTG_Source_Language_Field){
            $sl = rgar($entry, $field->id);
        }
    }

    $upload_path = GFFormsModel::get_upload_path( $form['id'] );
    $upload_url = GFFormsModel::get_upload_url( $form['id'] );
    $filename = str_replace( $upload_url, $upload_path, $po );

    wptg_generate_file($filename, $tl, $sl);

}


function load_wptg_field(){

    require_once("fields/class-wptg-source-lang.php");
    require_once("fields/class-wptg-target-lang.php");
    require_once("fields/class-wptg-po-file.php");
}

function wptg_gform_validation($validation_result){
    global $errorMessage;
    $form  = $validation_result['form'];
    $entry = GFFormsModel::get_current_lead();
    $el = array();
    foreach ($form['fields'] as $field){
        $id = $field->id;
        $input = 'input_'.$id;

        if($field instanceof WPTG_PO_File_Input){
            array_push($el, 'WPTG_PO_File_Input');
            if( !isset($_FILES[$input]) || $_FILES[$input]['error'] != 0){
                $validation_result['is_valid'] = false;
                $field->failed_validation = true;
                $field->validation_message = 'PO file is required';
            }
        }

        if($field instanceof WPTG_Target_Language_Field){
            array_push($el, 'WPTG_Target_Language_Field');
            if( empty(rgar($entry, $id)) ){
                $validation_result['is_valid'] = false;
                $field->failed_validation = true;
                $field->validation_message = 'Target Language is required';
            }
        }
    }

    if(in_array('WPTG_PO_File_Input', $el) && !in_array('WPTG_Target_Language_Field', $el)){
        var_dump($el);
        $validation_result['is_valid'] = false;
        $validation_result['form'] = $form;
        $errorMessage = "PO File upload field requires Target language field";
    }

    return $validation_result;
}

function change_message( $message, $form ) {
    global $errorMessage;

    if(isset($errorMessage) && !empty($errorMessage)){
        return "<div class='validation_error'>". $errorMessage ."</div>";
    }
    
    return $message;
}
