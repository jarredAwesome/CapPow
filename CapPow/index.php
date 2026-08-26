<?php
/*
 * Plugin Name: CapPoW! 
 * Description: Woocomerce Plugin to work with tryCap.com
 * Author: Duck
 * Version: 1.0.1
 */

defined( 'ABSPATH' ) || exit;

//set all definitions
require_once('includes/defines.php');
//set all functions
require_once('includes/functions.php');

//add JS
require_once('forms/apply.php');

//set up all forms to use with captcha
require_once('forms/apply.php');

if(is_admin()){
    require_once('admin/settings.php');
}
