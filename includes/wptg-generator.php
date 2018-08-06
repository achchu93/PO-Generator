<?php

if( !defined( 'ABSPATH' ) ) die();

use Gettext\Translations;

function wptg_generate_file($file, $target, $source=""){
    if( !file_exists($file) || wp_check_filetype($file)['ext'] != 'po' ){
        return set_wp_error("invalid", "Invalid file provided");
    }

    if(!$target){
        return set_wp_error("invalid", "Target Language is required");
    }

    $translations = Translations::fromPoFile($file);
    $translate_data = $translations->getArrayCopy();

    if( !count($translate_data) ){
        return set_wp_error("not_found", "No data found");
    }

    $translate_string = "";
    $translate_data = array_values($translate_data);
    foreach ( $translate_data as $key => $data){
        $plural = $data->hasPlural() ? $data->getPlural() : 0;
        $translate_string .= "text= " . html_entity_decode($data->getOriginal()) . " | $plural &";
    }
    rtrim($translate_string, "&");

    $translated_data = get_translated_data($translate_string, $target, $source);
    if($translated_data instanceof WP_Error){
        return $translated_data;
    }
    $new_translations = wptg_get_translations($translated_data->translations, $translate_data);
    generate_po_file($new_translations, $translations, $target);

}

function get_translated_data($string, $target, $source=''){
    $body = $string . "&target_lang=$target&auth_key=4cbe01fe-834a-6f4e-18e1-8612a823192a";
    $translated_data = wp_remote_post("https://api.deepl.com/v1/translate", array(
        "body" => $body,
        "timeout" => 500
    ));
    if(wp_remote_retrieve_response_code($translated_data) != 200 ){
        return set_wp_error(wp_remote_retrieve_response_code($translated_data), wp_remote_retrieve_response_code($translated_data));
    }

    $translations = json_decode($translated_data['body']);
    if(!$translations || !count($translations->translations)){
        return set_wp_error("invalid", "invalid file");
    }

    return $translations;
}

function set_wp_error($code="", $message=""){
    return new WP_Error($code, __($message, 'wptg'));
}

function wptg_get_translations($translations, $old){
    $_translations = new Translations();
    foreach ($translations as $key => $translation){
        $t_data = wptg_get_translation_data($key, $translation->text, $old);
        $_translation = new \Gettext\Translation($t_data["context"], $t_data["original"], $t_data["plural"]);
        $_translation->setTranslation($t_data["translation"]);
        $_translation->setPluralTranslations(array($t_data["plural_translation"]));

        $_translations->offsetSet(null, $_translation);
    }

    return $_translations;
}

function wptg_get_translation_data($index, $translation, $old){
    $array = explode("|", $translation);
    $data_array = array();
    $data_array["index"] = $index;
    $data_array["context"] = trim($old[$index]->getContext());
    $data_array["original"] = trim($old[$index]->getOriginal());
    $data_array["translation"] = isset($array[0]) ? trim($array[0]) : "";
    $data_array["plural"] = trim($old[$index]->getPlural());
    $data_array["plural_translation"] = isset($array[1]) ? trim($array[1]) : "";

    return $data_array;

}

function wptg_filter_upload_dir($path)
{
    $directory = '/'."wptg-uploads";

    $path['basedir'] = $path['basedir'] . $directory;
    $path['baseurl'] = $path['baseurl'] . $directory;
    $path['path'] = $path['basedir'] . $path['subdir'];
    $path['url'] = $path['baseurl'] . $path['subdir'];

    return $path;
}


function generate_po_file($translations, $old, $target){

    add_filter('upload_dir', 'wptg_filter_upload_dir');

    $headers = $old->getHeaders();
    foreach ($headers as $key => $header){
        if(strcasecmp($key, "Language") == 0){
            $translations->setHeader($key, $target);
            continue;
        }
        $translations->setHeader($key, $header);
    }

    $path = wp_upload_dir();
    $file = $path['path'] . '/' . $target.'.po';
    $translations->toPoFile($file);
}