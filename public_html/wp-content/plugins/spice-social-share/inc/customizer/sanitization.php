<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// Callback function for spice social share color
function spice_social_share_color_callback($control) {
    if (false == $control->manager->get_setting('enable_spice_social_share_clr')->value()) {
        return false;
    } else {
        return true;
    }
}

// Callback function for spice social share typo
function spice_social_share_typo_callback($control) {
    if (false == $control->manager->get_setting('spice_social_share_typo')->value()) {
        return false;
    } else {
        return true;
    }
}

/**
 * Checkbox sanitization callback
*/
function spice_social_share_sanitize_checkbox($checked) {

    // Boolean check.
    return ( ( isset($checked) && true == $checked ) ? true : false );

}

/**
 * Sorting sanitization callback
*/
function spice_social_share_sanitize_array( $value ){
    if ( is_array( $value ) ) {
        foreach ( $value as $key => $subvalue ) {
            $value[ $key ] = esc_attr( $subvalue );
        }
        return $value;
    }
    return esc_attr( $value );
}


/**
 * Select choices sanitization callback
*/
function spice_social_share_sanitize_select($input, $setting) {
    //input must be a slug: lowercase alphanumeric characters, dashes and underscores are allowed only
    $input = sanitize_key($input);
    //get the list of possible radio box options 
    $choices = $setting->manager->get_control($setting->id)->choices;
    //return input if valid or return default option
    return ( array_key_exists($input, $choices) ? $input : $setting->default );
}

function spice_social_share_sanitize_text($input) {

    return wp_kses_post(force_balance_tags($input));

}