<?php
defined( 'ABSPATH' ) || exit;

//attach many hooks to one function
if(!function_exists('add_actions')){
    function add_actions(){
        //get all arguments from action args
        $args = func_get_args();

        //converts first argument to array if it isn't
        if(!is_array($args[0])){
            $args[0] = array($args[0]);
        }

        //calls add_action for each hook
        foreach($args[0] as $hook){
            $action_args = $args;
            $action_args[0] = $hook;
            call_user_func_array("add_action", $action_args);
        }
    }
}
//check Post Values
if(!function_exists('postValue')){
    function postValue($v, $rFalse=false){
        if(isset($_POST[$v])){
            return $_POST[$v];
        }
        
        if($rFalse == false){
            return '';
        }
        
        return false;
    };
}

//check get Values
if(!function_exists('getValue')){
    function getValue($v, $return_empty_on_false=false){
        if(isset($_GET[$v])){
            return $_GET[$v];
        }
        
        if($return_empty_on_false == false){
            return false;
        }
        
        return '';
    };
}

//encrpytion
function cappow_encryption($value){
    if (!extension_loaded( 'openssl')){
        return $value;
    }

    $key = AUTH_KEY;

    $seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()'); // and any other characters
    $salt = '';
    for($i=0; $i < 125; $i++){
        $salt = $salt.$seed[array_rand($seed, 1)];
    }

    $method = 'aes-256-ctr';
    $ivlen  = openssl_cipher_iv_length($method);
    $iv     = openssl_random_pseudo_bytes($ivlen);

    $raw_value = openssl_encrypt($value . $salt, $method, $key, 0, $iv);
    if ( ! $raw_value ){
        return false;
    }

    return base64_encode( $iv . $raw_value );
}
//decryptions
function cappow_decryption($raw_value, $return_raw=false){

    $original_value = $raw_value;

    if($raw_value == null){
        return $raw_value;
    }

    if (!extension_loaded('openssl')){
        return $raw_value;
    }

    $key = AUTH_KEY;
    
    $raw_value = base64_decode($raw_value, true);
    
    $method = 'aes-256-ctr';
    $ivlen  = openssl_cipher_iv_length($method);
    $iv     = substr($raw_value, 0, $ivlen);

    
    if(strlen($iv) != 16){
        if($return_raw == true){
            return $original_value;
        }
        return false;
    }
            
    $raw_value = substr($raw_value, $ivlen);
    $value = openssl_decrypt($raw_value, $method, $key, 0, $iv);

    if ($value !== false){
        return substr($value, 0, - 125);
    }

    if($return_raw ==true){
        return $original_value;
    }

    return false;    
}

//verify User Solution
function cap_verify($token){
    return json_decode(@file_get_contents(get_option('cappow')['server_url']."/siteverify",
	false, stream_context_create([
    	"http" => [
      		"method" => "POST",
      		"header" => "Content-Type: application/json",
      		"content" => json_encode(["secret"=>get_option('cappow')['server_secret_key'],"response"=>$token])
    	]
  	])
	), true);
}

//add new cap challenge
function add_form_validation($hook, $func, $name, $id, $form_selector, $submit_selector, $args = array()){
    $num_args       = (isset($args['num_args']))                           ? $args['num_args']             : 0;
    $pri            = (isset($args['pri']))                                ? $args['pri']                  : 9999;
    $type           = (!isset($args['type']) || $args['type'] != 'action') ? 'applied_captcha_filters'     : 'applied_captcha_actions';

    //get all cappow settings
    $values         = get_option("cappow");

    //check $values to get setting for cappow
    $cappowFilter   = function($value)use($values){
        return (isset($values[$value])) ? $values[$value] : 'no';
    };

    if('yes' == $cappowFilter("forms_$id")){
        add_filter($type, function($captchas)use($hook, $func, $num_args, $pri){
            $captchas[$hook] = array($func, $pri, $num_args);
            return $captchas;
        });
        
        add_filter('formsToCheck', function($form_elements)use($form_selector, $submit_selector){
            $form_elements[$form_selector] = $submit_selector;
            return $form_elements;
        });
    }

    add_filter('cappow_formsToCheckOptoins', function($ids)use($id, $name){
        $ids[$id] = $name;
        return $ids;
    });
}
