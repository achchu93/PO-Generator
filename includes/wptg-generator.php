<?php

if( !defined( 'ABSPATH' ) ) die();

use Gettext\Translations;

function wptg_generate_file($file=null){

    if( !$file || !file_exists($file) || wp_check_filetype($file)['ext'] != 'po' ){
        return;
    }

    $translations = Translations::fromPoFile($file);
    $translate_data = $translations->getArrayCopy();

    if( !count($translate_data) ){
        return;
    }

    $translate_string = "";
    foreach ($translate_data as $data){
        $translate_string .= "[c= " . $data->getContext() . " |o=" . $data->getOriginal() . " |p=" . $data->getPlural() . "] ";
    }

    $translated_data = get_translated_data($translate_string);
    

}

function get_translated_data($string, $target, $source=''){
    $translated_data = wp_remote_post("https://api.deepl.com/v1/translate", array(
        "body" => array(
            "text" => $string,
            "target_lang" => $target,
            "auth_key" => "74936d46-81c9-bd1a-a0a6-020b97603fbd"
        ),
        "timeout" => 50
    ));

    $json = json_decode($translated_data['body']);
    $translations_new = $json->translations[0];
    $new_data = explode("]", $translations_new->text);
    $large_data = array();
    foreach ($new_data as $n_data){
        $n_data = ltrim(trim($n_data), "[");
        $n_array = explode("|", $n_data);
        array_push($large_data, $n_array);
    }

    return $large_data;
}