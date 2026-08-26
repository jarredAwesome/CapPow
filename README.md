This is a short plug-in I made for WooCommerce. It allows you to add a PoW captcha to Wordpress and WooCommerce.


Requirements:
  Credentials for a Cap server (see tryCap.dev)
  Wordpress 7.1
  WooCommerce
  php 8.6

This only protects Classic Checkout. Un-tested with blocks checkout

How to use:
1. Install plug-in via wordpress
2. Goto WooCommerce -> Settings -> CapPow
3. Insert your Cap credentials and click "save changes"
4. Select the Forms Tab, and select all the areas you'd like to add protection

For Devs:

to make a verify call to the Cap server:
cap_verify($cap_token);
$cap_token = the token added to the form, to show the browser did the work. Usally $_POST['cap-token'];
This will return the server response as an array


To add captcha to form use the following function:

add_form_validation($hook, $func, $name, $id, $form_selector, $submit_selector, $args = array());

$hook = is the action or filter where the form data is validated
$func = is the function that is called inorder to complete the validation

$name = human readable name of the form, seen in the settings menu

$id   = a unique id for this form.

$form_selector = a jquery selector to identify the form. Must be unique to that form

$submit_selector = a jquery selector to identify the form submit button.

$args = array of additional arguments such as 'num_args' for number of arguments in action hook function, and 'type' to state is the hook belongs to a action or filter.
