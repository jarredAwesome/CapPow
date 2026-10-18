<?php
/*
 * Plugin Name: CapPoW!
 * Description: A Wordpress & Woocommerce plugin for tryCap
 * Author: Duck
 * Version: 
 *
*/

defined( 'ABSPATH' ) || exit;

//set all definitions
require_once('includes/defines.php');
//set all functions
require_once('includes/functions.php');

//add JS & set up all forms to use with captcha
require_once('forms/apply.php');


if(is_admin()){
    require_once('admin/settings.php');
}