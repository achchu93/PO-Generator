<?php

if( !defined( 'ABSPATH' ) ) die();

if ( ! class_exists( 'GFForms' ) ) {
    die();
}

class WPTG_PO_File_Input extends GF_Field_FileUpload{

    public $type = "po_fileupload";
    public $allowedExtensions = "po";
    public $isRequired = true;

    public function get_form_editor_field_title()
    {
		return esc_attr__( 'PO File Uploader', 'wptg' );
    }

    public function get_form_editor_field_settings()
    {
        return array(
            'conditional_logic_field_setting',
            'error_message_setting',
            'label_setting',
            'label_placement_setting',
            'admin_label_setting',
            'file_size_setting',
            'visibility_setting',
            'description_setting',
            'css_class_setting',
        );
    }

    public function validate($value, $form)
    {
        $file = $_FILES['input_' . $this->id];
        if(!isset($file) || $file["error"] != 0){
            $this->failed_validation = true;
            return false;
        }

        $file_type = wp_check_filetype($file['name']);
        if($file_type['ext'] != 'po'){
            $this->failed_validation = true;
            $this->validation_message = "invalid file";
            return false;
        }

        return true;
    }


}
GF_Fields::register( new WPTG_PO_File_Input() );