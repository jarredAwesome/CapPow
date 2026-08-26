<?php
//  If accessed directly, abort
defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_get_settings_pages', function ( $settings ) {
	$settings[] = new class extends WC_Settings_Page {
		function __construct() {
			$this->id    = 'cappow';
			$this->label = esc_html__( 'CapPoW' );
			parent::__construct();
		}
	};
	return $settings;
});

// Add Fields for Cap PoW
add_filter( 'woocommerce_get_sections_cappow', function($sections){
    $sections[''] = esc_html__('Credentials');
    $sections['forms'] = esc_html__('Forms');

	return $sections;

    
}, 10, 1 );


add_filter( 'woocommerce_get_settings_cappow', function($settings, $section_id){

    if(''=== $section_id ){

        $settings = array(
            array(
                'title' => 'Cap Server Settings',
                'name'  => 'Cap Server Settings',
                'desc'  => 'Settings for Cap Server',
                'type'  => 'title',
            ),
            array(
                'title' => 'Enable',
                'name'  => 'Enable',
                'desc'  => 'Enable Cap PoW captcha',
                'id'    => 'cappow[enable]',
                'type'  => 'checkbox',
            ),
            array(
                'title' => 'Server Url',
                'name'  => 'Server Url',
                'id'    => 'cappow[server_url]',
                'desc'  => 'Server Url for Cap Server',
                'type'  => 'text',
            ),
            array(
                'title' => 'Asset Server Url',
                'name'  => 'Asset Server Url',
                'id'    => 'cappow[asset_url]',
                'desc'  => 'Asset Server Url for Cap Server',
                'type'  => 'text',
            ),
            array(
                'title' => 'Cap Site Key',
                'name'  => 'Cap Site Key',
                'desc'  => 'Site Key credential from Cap Server',
                'id'    => 'cappow[server_site_key]',
                'type'  => 'text',
            ),
            array(
                'title' => 'Cap Secret Key',
                'name'  => 'Cap Secret Key',
                'desc'  => 'Secret Key credential from Cap Server',
                'id'    => 'cappow[server_secret_key]',
                'type'  => 'text',
            ),
            array(
                'type'  => 'sectionend',
            ),
        );
        return $settings;
    }

    if('forms'=== $section_id ){
        $forms = apply_filters('cappow_formsToCheckOptoins', array());
        $settings = array();
        $settings[] = array(
            'title' => 'Cap Form Settings',
            'name'  => 'Cap Form Settings',
            'desc'  => 'Select Form to protect with Cap Server',
            'type'  => 'title'
        );
        foreach($forms as $form_id => $form_name){
            $settings[] = array(
                'title' => "Protect $form_name",
                'name'  => $form_name,
                'desc'  => "Use CapPoW with $form_name",
                'id'    => "cappow[forms_$form_id]",
                'type'  => 'checkbox',
            );
        }
        return $settings;
    }

    
}, 10, 2);



//encrypt/decrypt values

$cappow_to_encrypt = array('cappow[asset_url], cappow[server_url]', 'cappow[server_site_key]', 'cappow[server_secret_key]');
foreach($cappow_to_encrypt as $form_item){
    add_filter( 'woocommerce_admin_settings_sanitize_option_'.$form_item, function($value){
        return cappow_encryption($value);
    });

    add_filter( 'option_'.$form_item, function($value){
        return cappow_decryption($value);
    });
}
