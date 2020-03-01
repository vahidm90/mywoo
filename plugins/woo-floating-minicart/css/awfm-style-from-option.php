<?php
/*** set the content type header ***/
/*** Without this header, it wont work ***/
$minicart_position 	= get_option('Woo_floating_minicart_position');
/*$minicart_offset 	= get_option('Woo_floating_minicart_offset');
$primary_color 		= get_option('Woo_floating_minicart_primary_color');
$secondary_color 	= get_option('Woo_floating_minicart_secondary_color');
$button_color 		= get_option('Woo_floating_minicart_button_color');	*/
$absolute_path = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
 $wp_load = $absolute_path[0] . 'wp-load.php';
 require_once($wp_load);

  /**
  Do stuff like connect to WP database and grab user set values
  */

  header('Content-type: text/css');
  header('Cache-control: must-revalidate');
?>

body{
	background: red!important;
}