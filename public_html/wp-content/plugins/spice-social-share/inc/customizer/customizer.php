<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Adding customizer settings
function spice_social_share_customizer_controls($wp_customize)
{

    $spice_social_share_font_size = array();
    for($i=10; $i<=100; $i++) {
      $spice_social_share_font_size[$i] = $i;
    }

    $spice_social_share_line_height = array();
    for($i=0; $i<=100; $i++) {
        $spice_social_share_line_height[$i] = $i;
    }
    $spice_social_share_font_style = array('normal'=>'Normal','italic'=>'Italic');
    $spice_social_share_text_transform = array('default'=>'Default','capitalize'=>'Capitalize','lowercase'=>'Lowercase','Uppercase'=>'Uppercase');
    $spice_social_share_font_weight = array('100'=>'100','200'=>'200','300'=>'300','400'=>'400','500'=>'500','600'=>'600','700'=>'700','800'=>'800','900'=>'900');
    $spice_social_share_font_family = spice_social_share_typo_fonts();

 
    /***** Social Share Panel *****/
    $wp_customize->add_panel('spice_social_share_panel',
        array(
            'priority'   => 150,
            'capability' => 'edit_theme_options',
            'title'      => esc_html__('Spice Social Share','spice-social-share')
        ));


    /* ===================================================================================================================
    * Social Share General Setting *
    ====================================================================================================================== */
    $wp_customize->add_section( 'spice_social_share_customizer_section' , array(
        'title'    => esc_html__('General Settings', 'spice-social-share'),
        'priority' => 1,
        'panel'    => 'spice_social_share_panel',
    ) );


    $default = array( 'spice_facebook_share', 'spice_twitter_share', 'spice_linkedin_share','spice_mail_share','spice_pinterest_share','spice_whatsapp_share','spice_print_share');

    $choices = array(
        'spice_facebook_share' => esc_html__( 'Facebook', 'spice-social-share' ),
        'spice_twitter_share'  => esc_html__( 'Twitter', 'spice-social-share' ),
        'spice_linkedin_share' => esc_html__( 'LinkedIn', 'spice-social-share' ),
        'spice_mail_share'     => esc_html__( 'Mail', 'spice-social-share' ),
        'spice_pinterest_share'=> esc_html__( 'Pinterest', 'spice-social-share' ),
		'spice_whatsapp_share'=> esc_html__( 'WhatsApp', 'spice-social-share' ),
		'spice_print_share'=> esc_html__( 'Print', 'spice-social-share' )
    );

    $wp_customize->add_setting( 'spice_social_share_sort',
    array(
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'spice_social_share_sanitize_array',
        'default'           => $default
    ) );

    $wp_customize->add_control( new Spice_Social_Share_Control_Sortable( $wp_customize, 'spice_social_share_sort',
    array(
        'label'   => esc_html__( 'Drag And Drop to Rearrange', 'spice-social-share' ),
        'section' => 'spice_social_share_customizer_section',
        'priority'=>  1,
        'settings'=> 'spice_social_share_sort',
        'type'    => 'sortable',
        'choices' => $choices
    ) ) );


     // Position
    $wp_customize->add_setting('spice_social_share_position',
        array(
            'default'    =>  'after',
            'capability' =>  'edit_theme_options',
            'sanitize_callback' => 'spice_social_share_sanitize_select'
        )
    );
    $wp_customize->add_control('spice_social_share_position', 
        array(
            'label'   =>  esc_html__('Position','spice-social-share' ),
            'section' =>  'spice_social_share_customizer_section',
            'setting' =>  'spice_social_share_position',
            'type'    =>  'select',
            'priority'=>  2,
            'choices' =>  array(
                'after'=>  esc_html__('After Content', 'spice-social-share' ),
                'before'=>  esc_html__('Before Content', 'spice-social-share' )
            )
        )
    );

    // Heading
    $wp_customize->add_setting('spice_social_share_heading',
        array(
            'default'   =>  esc_html__('Share this content:', 'spice-social-share' ),
            'capability'=>  'edit_theme_options',
            'sanitize_callback' => 'spice_social_share_sanitize_text'
        )
    );
    $wp_customize->add_control('spice_social_share_heading', 
        array(
            'label'    =>  esc_html__('Heading','spice-social-share' ),
            'section'  =>  'spice_social_share_customizer_section',
            'setting'  =>  'spice_social_share_heading',
            'type'     =>  'text',
            'priority' =>  3,
        )
    );

     // Twitter Username
    $wp_customize->add_setting('spice_social_share_tw_user',
        array(
            'default'    =>  '',
            'capability' =>  'edit_theme_options',
            'sanitize_callback' => 'spice_social_share_sanitize_text'
        )
    );
    $wp_customize->add_control('spice_social_share_tw_user', 
        array(
            'label'    =>  esc_html__('Twitter User Name','spice-social-share' ),
            'section'  =>  'spice_social_share_customizer_section',
            'setting'  =>  'spice_social_share_tw_user',
            'type'     =>  'text',
            'priority' =>  4,
        )
    );

    /* ===================================================================================================================
    * Social Share Typography Setting *
    ====================================================================================================================== */
    $wp_customize->add_section( 'spice_social_share_typo_section' , array(
        'title'   => esc_html__('Typography Settings', 'spice-social-share'),
        'priority'=> 2,
        'panel'   => 'spice_social_share_panel',
    ) );

    $wp_customize->add_setting('spice_social_share_typo',
        array(
            'default'           => false,
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' => 'spice_social_share_sanitize_checkbox'
        )
    );
    $wp_customize->add_control(new Spice_Social_Share_Toggle_Control( $wp_customize, 'spice_social_share_typo',
        array(
            'label'     =>  esc_html__( 'Enable to apply the settings', 'spice-social-share'  ),
            'section'   =>  'spice_social_share_typo_section',
            'setting'   =>  'spice_social_share_typo',
            'type'      =>  'toggle'
        )
    ));

    $wp_customize->add_setting(
    'spice_social_share_fontfamily',
    array(
        'default'           =>  'Poppins',
        'capability'        =>  'edit_theme_options',
        'sanitize_callback' => 'spice_social_share_sanitize_text'
        )   
    );
    $wp_customize->add_control('spice_social_share_fontfamily', array(
            'label'           => esc_html__('Font family','spice-social-share'),
            'section'         => 'spice_social_share_typo_section',
            'setting'         => 'spice_social_share_fontfamily',
            'type'            =>  'select',
            'choices'         => $spice_social_share_font_family,
            'active_callback' =>  'spice_social_share_typo_callback',
    ));

    $wp_customize->add_setting(
        'spice_social_share_fontstyle',
        array(
            'default'           =>  'normal',
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' => 'spice_social_share_sanitize_text'
        )   
    );
    $wp_customize->add_control('spice_social_share_fontstyle', array(
            'label'   => esc_html__('Font style','spice-social-share'),
            'section' => 'spice_social_share_typo_section',
            'setting' => 'spice_social_share_fontstyle',
            'type'    => 'select',
            'choices' => $spice_social_share_font_style,
            'active_callback' =>  'spice_social_share_typo_callback',
    ));

    $wp_customize->add_setting(
    'spice_social_share_fontsize',
    array(
        'default'           =>  18,
        'capability'        =>  'edit_theme_options',
        'sanitize_callback' =>  'absint'
        )   
    );

    $wp_customize->add_control('spice_social_share_fontsize', array(
            'label'   => esc_html__('Font size (px)','spice-social-share'),
            'section' => 'spice_social_share_typo_section',
            'setting' => 'spice_social_share_fontsize',
            'type'    =>  'select',
            'choices' => $spice_social_share_font_size,
            'active_callback' => 'spice_social_share_typo_callback',
        ));

    $wp_customize->add_setting(
        'spice_social_share_fontweight',
        array(
            'default'           =>  400,
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' =>  'absint'
        )   
    );
    $wp_customize->add_control('spice_social_share_fontweight', array(
            'label'   => esc_html__('Font Weight','spice-social-share'),
            'section' => 'spice_social_share_typo_section',
            'setting' => 'spice_social_share_fontweight',
            'type'    =>  'select',
            'choices' =>$spice_social_share_font_weight,
           'active_callback' =>  'spice_social_share_typo_callback',
    ));

    $wp_customize->add_setting(
        'spice_social_share_lheight',
        array(
            'default'           =>  22,
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' =>  'absint'
            )   
        );
    $wp_customize->add_control('spice_social_share_lheight', array(
            'label'   =>  esc_html__('Line Height (px)','spice-social-share'),
            'section' => 'spice_social_share_typo_section',
            'setting' => 'spice_social_share_lheight',
            'type'    => 'select',
            'choices' => $spice_social_share_line_height,
            'active_callback' => 'spice_social_share_typo_callback',
        ));

    $wp_customize->add_setting(
        'spice_social_share_transform',
        array(
            'default'           =>  'default',
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' => 'spice_social_share_sanitize_text'
        )   
    );
    $wp_customize->add_control('spice_social_share_transform', array(
            'label'   => esc_html__('Text Transform','spice-social-share'),
            'section' => 'spice_social_share_typo_section',
            'setting' => 'spice_social_share_transform',
            'type'    => 'select',
            'choices'=>  $spice_social_share_text_transform,
            'active_callback' =>  'spice_social_share_typo_callback',
    ));



    /* ===================================================================================================================
    * Social Share Color Setting *
    ====================================================================================================================== */
    $wp_customize->add_section('spice_social_share_clr_section', 
        array(
            'title'    => esc_html__('Color Settings', 'spice-social-share' ),
            'panel'    => 'spice_social_share_panel',
            'priority' => 3
        )
    );
    
    $wp_customize->add_setting('enable_spice_social_share_clr',
        array(
            'default'           => false,
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' => 'spice_social_share_sanitize_checkbox'
        )
    );
    $wp_customize->add_control(new Spice_Social_Share_Toggle_Control( $wp_customize, 'enable_spice_social_share_clr',
        array(
            'label'     =>  esc_html__( 'Enable to apply the settings', 'spice-social-share'  ),
            'section'   =>  'spice_social_share_clr_section',
            'setting'   =>  'enable_spice_social_share_clr',
            'type'      =>  'toggle'
        )
    ));

    $wp_customize->add_setting('spice_social_share_heading_color', 
        array(
            'default'           => '#858585',
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'spice_social_share_heading_color', 
        array(
            'label'             =>  esc_html__('Heading Color', 'spice-social-share' ),
            'active_callback'   =>  'spice_social_share_color_callback',
            'section'           =>  'spice_social_share_clr_section',
            'setting'           =>  'spice_social_share_heading_color'
        )
    ));

    $wp_customize->add_setting('spice_social_share_bg_color', 
        array(
            'default'           => '#efefef',
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'spice_social_share_bg_color', 
        array(
            'label'             =>  esc_html__('Icon Background Color', 'spice-social-share' ),
            'active_callback'   =>  'spice_social_share_color_callback',
            'section'           =>  'spice_social_share_clr_section',
            'setting'           =>  'spice_social_share_bg_color'
        )
    ));

    $wp_customize->add_setting('spice_social_share_icon_color', 
        array(
            'default'           => '#242020',
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'spice_social_share_icon_color', 
        array(
            'label'             =>  esc_html__('Icon Color', 'spice-social-share' ),
            'active_callback'   =>  'spice_social_share_color_callback',
            'section'           =>  'spice_social_share_clr_section',
            'setting'           =>  'spice_social_share_icon_color'
        )
    ));

    $wp_customize->add_setting('spice_social_share_bg_hover_color', 
        array(
            'default'           => '#061018',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'spice_social_share_bg_hover_color', 
        array(
            'label'             =>  esc_html__('Icon Background Hover Color', 'spice-social-share' ),
            'active_callback'   =>  'spice_social_share_color_callback',
            'section'           =>  'spice_social_share_clr_section',
            'setting'           =>  'spice_social_share_bg_hover_color'
        )
    ));

        $wp_customize->add_setting('spice_social_share_icon_hover_color', 
        array(
            'default'           => '#ffffff',
            'capability'        =>  'edit_theme_options',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'spice_social_share_icon_hover_color', 
        array(
            'label'             =>  esc_html__('Icon Hover Color', 'spice-social-share' ),
            'active_callback'   =>  'spice_social_share_color_callback',
            'section'           =>  'spice_social_share_clr_section',
            'setting'           =>  'spice_social_share_icon_hover_color'
        )
    ));

}

add_action( 'customize_register', 'spice_social_share_customizer_controls' );