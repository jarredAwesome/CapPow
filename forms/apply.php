<?php

//todo log in

defined( 'ABSPATH' ) || exit;

//enqueue scripts when needed
add_actions(['wp_enqueue_scripts', 'login_enqueue_scripts'], function(){
    //creates a list of forms to add captacha
    $formsToprotect  = apply_filters('formsToCheck', array());

    //see if js is needed, even if form isn't listed above
    $forceWidget     = apply_filters('forceWidget', false);
    if($formsToprotect != array() || $forceWidget == true){
        wp_register_script('cap-widget', plugin_dir_url(__FILE__)."../includes/js/loadWidget.js", array('jquery'));
        wp_localize_script( 'cap-widget', 'cap_widget_params', array(
            'api'           => get_option('cappow')['server_url']."/".get_option('cappow')['server_site_key'],
            'widget_src'    => get_option('cappow')['asset_url']."/widget.js",
            'formsToCheck'  => $formsToprotect,
            'forceWidget'   => $forceWidget
        ));
        wp_enqueue_script('cap-widget');
    }
}, 9999);

//remove PoW when using wordfence passkey to login from wordpress login page
add_filter('cappow_process_check_woocommerceUserLogin', function($allow){
    if($allow != true){
        return $allow;
    }
    return !(wp_doing_ajax() && postValue('action') == 'wordfence_ls_finish_passkey_login');
});

//register forms

//wordpress login
add_form_validation('authenticate', function($user){
    //return new WP_Error('captcha_error', captchaError);
    //don't skip if its wordpress login
    if(!wp_doing_ajax() || !is_login()){
        return $user;
    }

    $data = cap_verify();
    if(!isset($data['success']) || $data['success'] != 'true'){
        return new WP_Error('captcha_error', captchaError);
    }
    return $user;
},"Wordpress Login", 'wordpressLogin', '#loginform', 'input#wp-submit', array('num_args'=>1, 'pri'=>9999, 'type'=>'filter'));


//wordpress forgot password
add_form_validation('lostpassword_post', function($errors, $user){

    //only check if its wordpress password reset
    if(!is_login()){
        return $errors;
    }

    $data = cap_verify();
    if(!isset($data['success']) || $data['success'] != 'true'){
        $errors->add('captcha_error', captchaError);
    }

    },"Wordpress Password Reset", 'wordpressPasswordReset', '#lostpasswordform', 'input#wp-submit', array('num_args'=>2, 'type'=>'action'));

//woocommerce new user reg
add_form_validation('woocommerce_process_registration_errors', function($errors, $username, $email){
    $data = cap_verify();
    if($data['success'] != 'true'){
        $errors->add('captcha_error', captchaError);
    }
    return $errors;
}, 'Woocommerce New User Register', 'woocommerceNewUserReg', '.woocommerce-form-register', '.woocommerce-form-register__submit', array('num_args'=>3, 'type'=>'filter'));

//woocomerce user login
add_form_validation('authenticate', function($user){

    //skip if its wordpress login
    if(wp_doing_ajax() || is_login()){
        return $user;
    }

    $data = cap_verify();
    if(!isset($data['success']) || $data['success'] != 'true'){
        return new WP_Error('captcha_error', captchaError);
    }
    return $user;

}, 'Woocommerce User Login', 'woocommerceUserLogin', '.woocommerce-form-login', '.woocommerce-form-login__submit', array('num_args'=>1, 'type'=>'filter', 'pri'=>9999));

//woocomerce password reset
add_form_validation('lostpassword_post', function($errors, $user){
    //only check if its woocommerce password reset
    
    if(is_login()){
        return $errors;
    }

    $data = cap_verify();

    if(!isset($data['success']) || $data['success'] != 'true'){
        $errors->add('captcha_error', captchaError);
    }

    return $errors;

},"Woocommerce Password Reset", 'wooocomercePasswordReset', '.woocommerce-ResetPassword', '.woocommerce-Button', array('num_args'=>2));

//woocomerce checkout
add_form_validation('woocommerce_after_checkout_validation', function($data, $errors){
    $data = cap_verify();

    if(isset($data['success']) && $data['success'] == 'true'){
        return;
    }

    $errors->add( 'validation', captchaError);
    
}, 'Woocommerce Checkout', 'woocommerceCheckout', '.woocommerce-checkout', 'form.woocommerce-checkout #place_order', array('num_args'=>2, 'pri'=>10, 'type'=>'action'));


//woocomerce pay for order
add_form_validation('woocommerce_before_pay_action', function(){
    $data = cap_verify();
    if(!isset($data['success']) || $data['success'] != 'true'){
        wc_add_notice(captchaError , 'error');
    }
}, 'Woocommerce Pay For Order', 'woocommercePayForOrder', '#order_review', '#order_review #place_order', array('num_args'=>0, 'type'=>'action'));

//woocomerce add payment option
add_form_validation('woocommerce_add_payment_method_form_is_valid', function($valid){
    $data = cap_verify();
    if(!isset($data['success']) || $data['success'] != 'true'){
        $valid = false;
        wc_add_notice(var_export($_POST, true), 'error');
        wc_add_notice(captchaError , 'error');
    }
    return $valid;
}, 'Woocommerce Add Payment Option', 'woocommerceAddPaymentOption', 'form#add_payment_method', 'form#add_payment_method #place_order', array('num_args'=>1));

//wordpress add comments
add_form_validation('pre_comment_approved', function($approve, $commentdata){

    //only run check once
    if(did_filter('pre_comment_approved') != 1){
        return $approve;
    }

    $data = cap_verify();
    if(isset($data['success']) && $data['success'] == 'true'){
        return $approve;
    }
    return new WP_Error('captcha_error', captchaError);

}, 'Wordpress Leave Comment', 'wordpressLeaveComment', '#commentform', '#commentform #submit', array('num_args'=>2));

//set cap PoW checks where needed
add_action('init', function(){
    $apply_captchas = apply_filters('applied_captcha_filters', array(), 9999, 1);
    foreach($apply_captchas as $apply_captcha => $args){
        extract($args);
        $apply = apply_filters('cappow_process_check_'.$id, true, 9999, 1);
        if($apply != true){
            continue;
        }
        add_filter($apply_captcha, $func, $pri, $num_args);
    }

    $apply_captchas = apply_filters('applied_captcha_actions', array(), 9999, 1);
    foreach($apply_captchas as $apply_captcha => $args){
        extract($args);
        $apply = apply_filters('cappow_process_check_'.$id, true, 9999, 1);
        if($apply != true){
            continue;
        }
        add_action($apply_captcha, $func, $pri, $num_args);
    }

});