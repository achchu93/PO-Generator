<?php

if( !defined( 'ABSPATH' ) ) die();

if ( ! class_exists( 'GFForms' ) ) {
    die();
}

class WPTG_Source_Language_Field extends GF_Field_Select{

    public $type = "source_lang_select";
    public $noDuplicates = true;
    public $isRequired = true;
    public $placeholder = "Select a source language";
    public $choices = array(
        array(
            'text'       => 'EN',
            'value'      => 'EN',
            'isSelected' => false
        ),
        array(
            'text'       => 'DE',
            'value'      => 'DE',
            'isSelected' => false
        ),
        array(
            'text'       => 'FR',
            'value'      => 'FR',
            'isSelected' => false
        ),
        array(
            'text'       => 'ES',
            'value'      => 'ES',
            'isSelected' => false
        ),
        array(
            'text'       => 'IT',
            'value'      => 'IT',
            'isSelected' => false
        ),
        array(
            'text'       => 'NL',
            'value'      => 'NL',
            'isSelected' => false
        ),
        array(
            'text'       => 'PL',
            'value'      => 'PL',
            'isSelected' => false
        )
    );


    public function get_choices($value)
    {
        return GFCommon::get_select_choices( $this, $value );
    }

    public function get_form_editor_field_title()
    {
		return esc_attr__( 'Source Language', 'wptg' );
    }

    public function get_form_editor_button()
    {

        return array(
            'group' => 'standard_fields',
            'text'  => "Source Language"
        );
    }

    public function get_form_editor_field_settings()
    {
        return array(
            'conditional_logic_field_setting',
            'prepopulate_field_setting',
            'error_message_setting',
            'enable_enhanced_ui_setting',
            'label_setting',
            'label_placement_setting',
            'admin_label_setting',
            'size_setting',
            'placeholder_setting',
            'default_value_setting',
            'visibility_setting',
            'description_setting',
            'css_class_setting',
        );
    }

    public function get_field_input($form, $value = '', $entry = null)
    {
        return parent::get_field_input($form, $value, $entry);
    }

    public function validate($value, $form)
    {
        if( !isset($value) || empty($value) ){
            $this->failed_validation = true;
            $this->validation_message = $this->errorMessage;
            return false;
        }

        return true;
    }

}
GF_Fields::register( new WPTG_Source_Language_Field() );