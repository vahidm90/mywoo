<?php
/*This file is part of storekeeper child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/

function storekeeper_enqueue_child_styles() {
    $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
    $parent_style = 'storecommerce-style';

    $fonts_url = 'https://fonts.googleapis.com/css?family=Cabin:400,400italic,500,600,700';
    wp_enqueue_style('storekeeper-google-fonts', $fonts_url, array(), null);
    wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/bootstrap/css/bootstrap' . $min . '.css');
    wp_enqueue_style('owl-carousel', get_template_directory_uri() . '/assets/owl-carousel-v2/assets/owl.carousel' . $min . '.css');
    wp_enqueue_style('owl-theme-default', get_template_directory_uri() . '/assets/owl-carousel-v2/assets/owl.theme.default.css');
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style(
        'storekeeper-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'bootstrap', $parent_style ),
        wp_get_theme()->get('Version') );
        
        
        $loc_var = array( 'url' => get_template_directory_uri() . '/js' ); 
        
    wp_register_script( 'enamad-temp-js', get_template_directory_uri() . '/js/enamad-temp.js', array('jquery'), false, true ); 
    wp_localize_script( 'enamad-temp-js', 'myVar', $loc_var ); 
    wp_enqueue_script( 'enamad-temp-js'); 


}
add_action( 'wp_enqueue_scripts', 'storekeeper_enqueue_child_styles' );





include get_template_directory().'/feed.class.php';

add_action( 'after_switch_theme', 'check_theme_dependencies', 10, 2 );

function check_theme_dependencies( $oldtheme_name, $oldtheme ) {

  if (!class_exists('hwpfeed')) :

    switch_theme( $oldtheme->stylesheet );
	
      return false;

  endif;

}










add_filter('the_content','my_nofollow');
add_filter('the_excerpt','my_nofollow');
function my_nofollow($content){
return preg_replace_callback('/<a[^>]+/','my_nofollow_callback',$content);}
function my_nofollow_callback($matches){
$link = $matches[0]; $site_link = get_bloginfo('url');
if(strpos($link,'rel') === false){
$link = preg_replace("%(href=\S(?!$site_link))%i",'rel="nofollow" $1',$link);
} elseif (preg_match("%href=\S(?!$site_link)%i",$link)){
$link = preg_replace('/rel=\S(?!nofollow)\S*/i','rel="nofollow"',$link);}
return $link;}


/*
 * Fix: Removed theme copyright footer.
 */
// add_action('wordpress_theme_initialize', 'wp_generate_theme_initialize');
//function wp_generate_theme_initialize(  ) {
//    echo base64_decode('2YHYp9ix2LPbjCDYs9in2LLbjCDZvtmI2LPYqtmHINiq2YjYs9i3OiA8YSBocmVmPSJodHRwczovL2hhbXlhcndwLmNvbS8/dXRtX3NvdXJjZT11c2Vyd2Vic2l0ZXMmdXRtX21lZGl1bT1mb290ZXJsaW5rJnV0bV9jYW1wYWlnbj1mb290ZXIiIHRhcmdldD0iX2JsYW5rIj7Zh9mF24zYp9ixINmI2LHYr9m+2LHYszwvYT4=');
//}
// add_action('after_setup_theme', 'setup_theme_after_run', 999);
//function setup_theme_after_run() {
//    if( empty(has_action( 'wordpress_theme_initialize',  'wp_generate_theme_initialize')) ) {
//        add_action('wordpress_theme_initialize', 'wp_generate_theme_initialize');
//    }
//}
// add_action('wp_footer', 'setup_theme_after_run_footer', 1);
//function setup_theme_after_run_footer() {
//    if( empty(did_action( 'wordpress_theme_initialize' )) ) {
//        add_action('wp_footer', 'wp_generate_theme_initialize');
//    }
//}

/*
 * Fix: Don't display the price on top in presence of variation attributes.
 */
add_action( 'woocommerce_single_product_summary', 'vm_change_single_product_price_html', 9 );
function vm_change_single_product_price_html() {
	global $product;
	if ('WC_Product_Variable' !== get_class($product)) :
		return;
	endif;
	$var_attr = $product->get_variation_attributes();
	if ( empty($var_attr) || ! is_array( $var_attr ) ) :
		return;
	endif;
	foreach ( $var_attr as $attr_array ) :
		if ( ! empty($attr_array) && is_array( $attr_array ) && (1 < count( $attr_array ) ) ) :
			continue;
		endif;
		return;
	endforeach;

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
}













