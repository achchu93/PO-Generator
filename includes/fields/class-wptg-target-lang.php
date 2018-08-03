<?php

if ( ! class_exists( 'GFForms' ) ) {
    die();
}

class WPTG_Target_Language_Field extends GF_Field_Select{

    public $type;
    public $choices;
    public $noDuplicates;
    public $isRequired;

    /**
     * WPTG_Source_Language_Field constructor.
     */
    public function __construct()
    {
        $this->noDuplicates = true;
        $this->isRequired = true;
        $this->type = "target_lang_select";
        $this->choices = array(
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
    }


    public function get_choices($value)
    {
        return GFCommon::get_select_choices( $this, $value );
    }

    public function get_form_editor_field_title()
    {
		return esc_attr__( 'Target Language', 'wptg' );
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
GF_Fields::register( new WPTG_Target_Language_Field() );