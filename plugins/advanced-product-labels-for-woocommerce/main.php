<?php
define( "BeRocket_products_label_domain", 'BeRocket_products_label_domain');
define( "products_label_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
if ( ! defined('BeRocket_APL_better_position_lines') ) { define( "BeRocket_APL_better_position_lines", '9' ); }
load_plugin_textdomain('BeRocket_products_label_domain', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
require_once(plugin_dir_path( __FILE__ ).'berocket/framework.php');
foreach (glob(__DIR__ . "/includes/*.php") as $filename)
{
    include_once($filename);
}
foreach (glob(plugin_dir_path( __FILE__ ) . "includes/compatibility/*.php") as $filename)
{
    include_once($filename);
}
/**
 * Class BeRocket_products_label
 * REPLACE
 * products_label - plugin name
 * Products Labels - normal plugin name
 * WooCommerce Advanced Product Labels - full plugin name
 * 18 - id on BeRocket
 * woocommerce-advanced-product-labels - slug on BeRocket
 * 24 - price on BeRocket
 */
class BeRocket_products_label extends BeRocket_Framework {
    public static $settings_name = 'br-products_label-options';
    protected static $instance;
    public $info, $defaults, $values;
    public $custom_post;
    public $labels_ids = false;
    protected $check_init_array = array(
        array(
            'check' => 'woocommerce_version',
            'data' => array(
                'version' => '3.0',
                'operator' => '>=',
                'notice'   => 'Plugin WooCommerce AJAX Products Filter required WooCommerce version 3.0 or higher'
            )
        ),
        array(
            'check' => 'framework_version',
            'data' => array(
                'version' => '2.1',
                'operator' => '>=',
                'notice'   => 'Please update all BeRocket plugins to the most recent version. WooCommerce Terms and Conditions Popup is not working correctly with older versions.'
            )
        ),
    );
    function __construct () {
        $this->info = array(
            'id'          => 18,
            'lic_id'      => 35,
            'version'     => BeRocket_products_label_version,
            'plugin'      => '',
            'slug'        => '',
            'key'         => '',
            'name'        => '',
            'plugin_name' => 'products_label',
            'full_name'   => 'WooCommerce Advanced Product Labels',
            'norm_name'   => 'Products Labels',
            'price'       => '24',
            'domain'      => 'BeRocket_products_label_domain',
            'templates'   => products_label_TEMPLATE_PATH,
            'plugin_file' => BeRocket_products_label_file,
            'plugin_dir'  => __DIR__,
        );

        $this->defaults = array(
            'disable_labels'    => '0',
            'disable_plabels'   => '0',
            'disable_ppage'     => '0',
            'remove_sale'       => '0',
            'custom_css'        => '.product .images {position: relative;}',
            'script'            => '',
            'shop_hook'         => 'woocommerce_before_shop_loop_item_title+15',
            'product_hook_image'=> 'woocommerce_product_thumbnails+15',
            'product_hook_label'=> 'woocommerce_product_thumbnails+15',
            'fontawesome_frontend_disable'    => '',
            'fontawesome_frontend_version'    => '',
        );

        $this->values = array(
            'settings_name' => 'br-products_label-options',
            'option_page'   => 'br_products_label',
            'premium_slug'  => 'woocommerce-advanced-product-labels',
            'free_slug'     => 'advanced-product-labels-for-woocommerce'
        );
        // List of the features missed in free version of the plugin
        $this->feature_list = array(
            'Conditions by product attribute, sale price, stock quantity, page ID',
            'Discount Amount type of Label',
            'Custom Discount type of Label',
            'Image type of Label',
            'Time left for discount type of Label',
            'Product attribute type of Label',
            'Template for labels',
            'More options for stylization'
        );

        global $berocket_label_css_styles;
        $berocket_label_css_styles = array();
        if( method_exists($this, 'include_once_files') ) {
            $this->include_once_files();
        }
        if( $this->init_validation() ) {
            new BeRocket_advanced_labels_custom_post();
        }
        $this->framework_data['fontawesome_frontend'] = true;
        parent::__construct( $this );

        if ( $this->init_validation() ) {
            $this->custom_post = BeRocket_advanced_labels_custom_post::getInstance();
            add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'product_edit_advanced_label' ) );
            if ( version_compare( br_get_woocommerce_version(), '2.7', '>=' ) ) {
                add_action( 'woocommerce_product_data_panels', array( $this, 'product_edit_tab' ) );
            } else {
                add_action( 'woocommerce_product_write_panels', array( $this, 'product_edit_tab' ) );
            }
            add_action( 'wp_ajax_br_label_ajax_demo', array( $this, 'ajax_get_label' ) );
            add_action( 'wp_footer', array( $this, 'page_load_script' ) );
            add_filter( 'BeRocket_updater_menu_order_custom_post', array( $this, 'menu_order_custom_post' ) );

            add_action( 'berocket_apl_set_label', array($this, 'set_label'), 10, 2 );

            add_filter( 'berocket_apl_label_show_text', array($this, 'get_correct_text'), 1, 3);
            add_filter( 'berocket_apl_label_show_label_style', array($this, 'get_correct_label_style'), 1, 3);
            add_filter( 'berocket_apl_label_show_div_style', array($this, 'get_correct_div_style'), 1, 3);
            add_filter( 'berocket_apl_label_show_custom_css', array($this, 'get_correct_custom_css'), 1, 4);
            add_filter( 'berocket_apl_label_sanitize_data', array($this, 'sanitize_label_data'), 1, 2);
            add_filter( 'berocket_apl_label_show_text_each', array($this, 'get_correct_text_each'), 1, 3);
        }
    }
    public function page_load_script() {
        global $berocket_display_any_advanced_labels, $berocket_label_css_styles;
        if( ! empty($berocket_display_any_advanced_labels) ) {
            $options = $this->get_option();
            if( ! empty($options['script']['js_page_load']) ) {
                echo '<script>jQuery(document).ready(function(){', $options['script']['js_page_load'], '});</script>';
            }
        }
    }
    public function remove_woocommerce_sale_flash($html) {
        return '';
    }
    public function init () {
        $theme = wp_get_theme();
        $theme = ($theme->offsetGet('Parent Theme') ? $theme->offsetGet('Parent Theme') : $theme->Name);
        if( strpos($theme, 'LEGENDA') !== FALSE ) {
            $this->defaults["shop_hook"] = 'woocommerce_before_shop_loop_item+5';
        }
        parent::init();
        $options = $this->get_option();
        $shop_hook = $options['shop_hook'];
        $shop_hook = explode('+', $shop_hook);
        add_action ( $shop_hook[0], array( $this, 'set_all_label'), $shop_hook[1] );
        if( ! empty($options['remove_sale']) ) {
            remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
            remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
            add_filter('woocommerce_sale_flash', array($this, 'remove_woocommerce_sale_flash'));
        }
        add_action ( 'product_of_day_before_thumbnail_widget', array( $this, 'set_image_label'), 20 );
        add_action ( 'product_of_day_before_title_widget', array( $this, 'set_label_label_fix'), 20 );
        add_action ( 'lgv_advanced_after_img', array( $this, 'set_all_label'), 20 );
        if( empty($options['disable_ppage']) ) {
            $product_hook_image = $options['product_hook_image'];
            $product_hook_image = explode('+', $product_hook_image);
            add_action( $product_hook_image[0], array( $this, 'set_image_label'), $product_hook_image[1] );
            if( $product_hook_image[0] == 'woocommerce_product_thumbnails' ) {
                add_action( 'woocommerce_product_thumbnails', array( $this, 'move_labels_from_zoom'), 20 );
            }
            $product_hook_label = $options['product_hook_label'];
            $product_hook_label = explode('+', $product_hook_label);
            add_action( $product_hook_label[0], array( $this, 'set_label_label'), $product_hook_label[1] );
        }
        wp_register_style( 'berocket_products_label_style', 
            plugins_url( 'css/frontend.css', __FILE__ ), 
            "", 
            BeRocket_products_label_version );
        wp_enqueue_style( 'berocket_products_label_style' );
        wp_register_style( 'berocket_tippy', 
            plugins_url( 'css/tippy.css', __FILE__ ), 
            "", 
            BeRocket_products_label_version );
        wp_register_script( 'berocket_tippy', plugins_url( 'js/tippy.min.js',  __FILE__ ), array('jquery'), $this->info['version'] );

        if ( is_admin() ) {
            wp_register_style('berocket_products_label_admin_style',plugins_url( 'css/admin.css', __FILE__ ),"",
                $this->info[ 'version' ]
            );
            wp_enqueue_style( 'berocket_products_label_admin_style' );
        }
    }
    public function move_labels_from_zoom() {
        add_action('wp_footer', array( $this, 'set_label_js_script'));
    }
    public function set_label_js_script() {
        ?>
        <script>
            jQuery(".woocommerce-product-gallery .br_alabel:not(.br_alabel_better_compatibility), .woocommerce-product-gallery .berocket_better_labels").each(function(i, o) {
                jQuery(o).hide().parents(".woocommerce-product-gallery").append(jQuery(o));
            });
            galleryReadyCheck = setInterval(function() {
                if( jQuery(".woocommerce-product-gallery .woocommerce-product-gallery__trigger").length > 0 ) {
                    clearTimeout(galleryReadyCheck);
                    jQuery(".woocommerce-product-gallery .br_alabel:not(.br_alabel_better_compatibility), .woocommerce-product-gallery .berocket_better_labels").each(function(i, o) {
                        jQuery(o).show().parents(".woocommerce-product-gallery").append(jQuery(o));
                    });
                }
                else if(jQuery('.woocommerce-product-gallery__wrapper').length > 0) {
                    clearTimeout(galleryReadyCheck);
                    jQuery(".woocommerce-product-gallery .br_alabel:not(.br_alabel_better_compatibility), .woocommerce-product-gallery .berocket_better_labels").each(function(i, o) {
                        jQuery(o).show().parents(".woocommerce-product-gallery").append(jQuery(o));
                    });
                }
            }, 250);
        </script>
        <?php
    }
    public function set_all_label() {
        do_action('berocket_apl_set_label', true);
    }
    public function set_image_label() {
        do_action('berocket_apl_set_label', 'image');
    }
    public function set_label_label() {
        do_action('berocket_apl_set_label', 'label');
    }
    public function set_label_label_fix() {
        echo '<div>';
        do_action('berocket_apl_set_label', 'label');
        echo '<div style="clear:both;"></div></div>';
    }
    public function set_label($type = TRUE) {
        global $product;
        do_action('berocket_apl_set_label_start', $product);
        if( apply_filters('berocket_apl_set_label_prevent', false, $type, $product) ) {
            return true;
        }
        $product_post = br_wc_get_product_post($product);
        $options = $this->get_option();
        if( ! $options['disable_plabels'] ) {
            $label_type = $this->custom_post->get_option($product_post->ID);
            if( ! empty($label_type['label_from_post']) && is_array($label_type['label_from_post']) ) {
                foreach($label_type['label_from_post'] as $label_from_post) {
                    $br_label = $this->custom_post->get_option($label_from_post);
                    if( ! empty($br_label) ) {
                        $this->show_label_on_product($br_label, $product, $label_from_post);
                    }
                }
            }
            if( $type === TRUE || $type == $label_type['type'] ) {
                if( ( ! empty($label_type['text']) && $label_type['text'] != 'Label' ) 
                 || ( ! empty($label_type['content_type']) && $label_type['content_type'] != 'text' ) ) {
                    $this->show_label_on_product($label_type, $product, 'product');
                }
            }
        }
        if( ! $options['disable_labels'] ) {
            $posts_array = $this->get_labels_ids();
            foreach($posts_array as $label) {
                $br_label = $this->custom_post->get_option($label);
                if( $type === TRUE || $type == $br_label['type'] ) {
                    if( ! isset($br_label['data']) || $this->check_label_on_post($label, $br_label['data'], $product) ) {
                        $this->show_label_on_product($br_label, $product, $label);
                    }
                }
            }
        }
        do_action('berocket_apl_set_label_end', $product);
    }
    public function get_labels_ids() {
        if( $this->labels_ids === false ) {
            $suppress_filters = true;
            if( defined( 'WCML_VERSION' ) && defined('ICL_LANGUAGE_CODE') ) {
                $suppress_filters = false;
            }
            $args = apply_filters('berocket_labels_get_args', array(
                'suppress_filters' => $suppress_filters
            ));
            $this->labels_ids = $this->custom_post->get_custom_posts_frontend( $args );
        }
        return $this->labels_ids;
    }
    public function ajax_get_label() {
        if ( current_user_can( 'manage_options' ) ) {
            do_action('berocket_apl_set_label_start', 'demo');
            if( ! empty($_POST['br_labels']['tooltip_content']) ) {
                $_POST['br_labels']['tooltip_content'] = stripslashes($_POST['br_labels']['tooltip_content']);
            }
            $this->show_label_on_product($_POST['br_labels'], 'demo', 'demo');
            do_action('berocket_apl_set_label_end', 'demo');
        }
        wp_die();
    }
    public function product_edit_advanced_label () {
        echo '<li class="product_labels"><a href="#br_alabel"><span>' . __( 'Advanced label', 'BeRocket_tab_manager_domain' ) . '</span></a></li>';
    }
    public function load_admin_edit_scripts() {
        //GET CUSTOM POST DATA
        $custom_post_default_settings = $this->custom_post->get_default_template_settings();
        $custom_post_set_default = $this->custom_post->get_default_template_settings(false);
        $custom_post_default_settings_names = $this->custom_post->default_settings;
        $custom_post_default_settings_names = array_keys($custom_post_default_settings_names);
        //REGISTER ADMIN SCRIPTS
        wp_register_script( 'berocket_products_label_admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_products_label_version );

        wp_localize_script( 'berocket_products_label_admin', 'berocket_products_label_admin', array(
            'custom_post_default' => $custom_post_default_settings,
            'custom_post_default_set' => $custom_post_set_default,
            'custom_post_setting_names' => $custom_post_default_settings_names,
        ) );
        wp_enqueue_script( 'berocket_products_label_admin' );
        wp_enqueue_script( 'berocket_framework_admin' );
        wp_enqueue_style( 'berocket_framework_admin_style' );
        wp_enqueue_script( 'berocket_widget-colorpicker' );
        wp_enqueue_style( 'berocket_widget-colorpicker-style' );
        wp_enqueue_style( 'berocket_font_awesome' );
    }
    public function product_edit_tab () {
        $this->load_admin_edit_scripts();
        //LOAD SETTINGS TEMPLATE
        $one_product = true;
        set_query_var( 'one_product', true );
        include products_label_TEMPLATE_PATH . "label.php";
    }
    public function show_label_on_product($br_label, $product, $label_id = 'demo') {
        global $berocket_display_any_advanced_labels;
        $berocket_display_any_advanced_labels = true;
        $label_id = esc_html($label_id);
        if( empty($br_label) || ! is_array($br_label) ) {
            return false;
        }
        //set correct label data
        $br_label = apply_filters('berocket_apl_label_sanitize_data', $br_label, $product);
        if( ! empty($br_label['color']) && ! empty($br_label['color_use']) ) {
            $background_color = $br_label['color'];
        }
        //get text
        $br_label['text'] = apply_filters('berocket_apl_label_show_text', berocket_isset( $br_label['text'] ), $br_label, $product);
        if( $br_label['text'] === FALSE ) {
            return false;
        }
        if( ! is_array($br_label['text']) ) {
            $br_label['text'] = array($br_label['text']);
        }
        //get label unique class
        $style_id = $label_id;
        if( $label_id == 'product' ) {
            $style_id = ($product === 'demo' ? 'demo' : $product->get_id());
        }
        $style_id = 'berocket_alabel_id_' . $style_id;
        $div_class = 'br_alabel br_alabel_'.$br_label['type'].' br_alabel_type_'. (empty($br_label['content_type']) ? '' : $br_label['content_type']) . ' br_alabel_'.$br_label['position'];
        $div_class .= ' ' . $style_id . ' ' . berocket_isset($br_label['div_custom_class']);
        //apply filters to get all data
        $label_style = apply_filters('berocket_apl_label_show_label_style', berocket_isset( $label_style ), $br_label, $product);
        $div_style = apply_filters('berocket_apl_label_show_div_style', berocket_isset( $div_style ), $br_label, $product);
        $div_class = apply_filters('berocket_apl_label_show_div_class', $div_class, $br_label, $product);
        $div_class = esc_html($div_class);
        $custom_css = apply_filters('berocket_apl_label_show_custom_css', '', $br_label, $product, $style_id);
        //get tooltip data
        $tooltip = $this->generate_tooltip_data($br_label);
        $tooltip_data = $tooltip['tooltip_data'];
        $tooltip_content = $tooltip['tooltip_content'];
        //generate label HTML
        foreach($br_label['text'] as $text ) {
            $label_style_each = $label_style;
            if( ! empty($text) && $text[0] == '#' ) {
                $label_style_each = $label_style_each . ' background-color:' .$text . ';';
                $background_color = $text;
                $text = '';
            }
            $text = apply_filters('berocket_apl_label_show_text_each', $text, $br_label, $product);
            $i1_style = $i2_style = $i3_style = $i4_style = ( empty( $background_color ) ? '' : 'background-color:' . $background_color . '; border-color:' . $background_color . ';' );
            $html = array();
            $html['open_div']               = '<div class="' . $div_class . '" style="' . $div_style . '">';
            $html['open_span']              = '<span'.$tooltip_data.' style="' . $label_style_each . '"' . (empty($br_label['div_custom_class']) ? '' : ' class="' . esc_html($br_label['div_custom_class']) . '"') . '>';
            if( ! empty($br_label['i1_custom_class']) || ! empty($br_label['i1_custom_css']) ) {
                $html['template-span-before']   = '    <i' . ( empty( $i1_style ) ? '' : ' style="' . htmlentities($i1_style) . '"' ) . ' class="template-span-before ' . esc_html(berocket_isset($br_label['i1_custom_class'])) . '"></i>';
            }
            if( ! empty($br_label['i2_custom_class']) || ! empty($br_label['i2_custom_css']) ) {
                $html['template-i']             = '    <i' . ( empty( $i2_style ) ? '' : ' style="' . htmlentities($i2_style) . '"' ) . ' class="template-i ' . esc_html(berocket_isset($br_label['i2_custom_class'])) . '"></i>';
            }
            if( ! empty($br_label['i3_custom_class']) || ! empty($br_label['i3_custom_css']) ) {
                $html['template-i-before']      = '    <i' . ( empty( $i3_style ) ? '' : ' style="' . htmlentities($i3_style) . '"' ) . ' class="template-i-before ' . esc_html(berocket_isset($br_label['i3_custom_class'])) . '"></i>';
            }
            if( ! empty($br_label['i4_custom_class']) || ! empty($br_label['i4_custom_css']) ) {
                $html['template-i-after']       = '    <i' . ( empty( $i4_style ) ? '' : ' style="' . htmlentities($i4_style) . '"' ) . ' class="template-i-after ' . esc_html(berocket_isset($br_label['i4_custom_class'])) . '"></i>';
            }
            $html['template-b']             = '    <b' . ( empty( $br_label['b_custom_css'] ) ? '' : ' style="' . htmlentities($br_label['b_custom_css']) . '"' ) . ( empty( $br_label['b_custom_class'] ) ? '' : ' class="' . esc_html($br_label['b_custom_class']) . '"' ) . '>'.$text.'</b>';
            $html['tooltip']                = $tooltip_content;
            $html['close_span']             = '</span>';
            $html['custom_css']             = $custom_css;
            $html['close_div']              = '</div>';
            $html = apply_filters('berocket_apl_show_label_on_product_html', $html, $br_label, $product);
            echo implode($html);
        }
    }
    public function sanitize_label_data($br_label) {
        if( empty($br_label['content_type']) ) {
            $br_label['content_type'] = 'text';
        }
        if ( $br_label['color'][0] != '#' ) {
            $br_label['color'] = '#'.$br_label['color'];
        }
        if ( isset($br_label['font_color']) && $br_label['font_color'][0] != '#' ) {
            $br_label['font_color'] = '#'.$br_label['font_color'];
        }
        return $br_label;
    }
    public function get_correct_text($text = '', $br_label, $product) {
        if( $product === 'demo' ) {
            $br_label['text'] = stripslashes($br_label['text']);
        }
        if( @ $br_label['content_type'] == 'sale_p' ) {
            $br_label['text'] = '';
            if( $product == 'demo' || $product->is_on_sale() ) {
                $price_ratio = false;
                if( $product == 'demo' ) {
                    $product_sale = '250.5';
                    $product_regular = '430.25';
                    $price_ratio = $product_sale / $product_regular;
                } else {
                    $product_sale = br_wc_get_product_attr($product, 'sale_price');
                    $product_regular = br_wc_get_product_attr($product, 'regular_price');
                    if( ! empty($product_sale) && $product_sale != $product_regular ) {
                        $price_ratio = $product_sale / $product_regular;
                    }
                    if( $product->has_child() ) {
                        foreach($product->get_children() as $child_id) {
                            $child = br_wc_get_product_attr($product, 'child', $child_id);
                            $child_sale = br_wc_get_product_attr($child, 'sale_price');
                            $child_regular = br_wc_get_product_attr($child, 'regular_price');
                            if( ! empty($child_sale) && $child_sale != $child_regular ) {
                                $price_ratio2 = $child_sale / $child_regular;
                                if( $price_ratio === false || $price_ratio2 < $price_ratio ) {
                                    $price_ratio = $price_ratio2;
                                }
                            }
                        }
                    }
                }
                if( $price_ratio !== false ) {
                    $price_ratio = ($price_ratio * 100);
                    $price_ratio = number_format($price_ratio, 0, '', '');
                    $price_ratio = $price_ratio * 1;
                    $br_label['text'] = (100 - $price_ratio)."%";
                    if( ! empty($br_label['discount_minus']) ) {
                        $br_label['text'] = '-'.$br_label['text'];
                    }
                }
            }
            if( empty($br_label['text']) ) {
                $br_label['text'] = FALSE;
            }
        } elseif( @ $br_label['content_type'] == 'price' ) {
            $br_label['text'] = '';
            if( $product == 'demo' ) {
                if( @ $br_label['content_type'] != 'regular_price' ) {
                    $price = '250.5';
                } else {
                    $price = '430.25';
                }
                $br_label['text'] = wc_price($price);
            } else {
                if( $product->is_type('variable') || $product->is_type('grouped') ) {
                    $br_label['text'] = $product->get_price_html();
                } else {
                    $price = br_wc_get_product_attr($product, 'price');
                    $br_label['text'] = wc_price($price);
                }
            }
        } elseif( @ $br_label['content_type'] == 'stock_status' ) {
            $br_label['text'] = '';
            if( $product == 'demo' ) {
                $br_label['text'] = sprintf( __( '%s in stock', 'woocommerce' ), 24 );
            } else {
                $br_label['text'] = $this->product_get_availability_text($product);
            }
        }
        if( $br_label['content_type'] == 'text' && empty($br_label['text']) ) {
            $br_label['text'] = FALSE;
        }
        return $br_label['text'];
    }
    public function get_correct_text_each($text = '', $br_label, $product) {
        if( in_array($br_label['content_type'], apply_filters('berocket_apl_content_type_with_before_after', array('sale_p', 'price', 'stock_status'))) ) {
            $text = (empty($br_label['text_before']) ? '' : $br_label['text_before'].(empty($br_label['text_before_nl']) ? '' : '<br>'))
            .$text
            .(empty($br_label['text_after']) ? '' : (empty($br_label['text_after_nl']) ? '' : '<br>').$br_label['text_after']);
        }
        return $text;
    }
    public function get_correct_label_style($label_style = '', $br_label, $product) {
        if( ! empty($br_label['image_height']) ) {
            $label_style .= 'height: ' . floatval($br_label['image_height']) . 'px;';
        }
        if( ! empty($br_label['image_width']) ) {
            $label_style .= 'width: ' . floatval($br_label['image_width']) . 'px;';
        }
        if( empty($br_label['image_height']) && empty($br_label['image_width']) ) {
            $label_style .= 'padding: 0.2em 0.5em;';
        }
        if( ! empty($br_label['color']) && ! empty($br_label['color_use']) ) {
            $label_style .= 'background-color:' . $br_label['color'].';';
        }
        if( ! empty($br_label['font_color']) ) {
            $label_style .= 'color:'.@ $br_label['font_color'].';';
        }
        if( isset($br_label['border_radius']) ) {
            if( strpos($br_label['border_radius'], 'px') === FALSE
            && strpos($br_label['border_radius'], 'em') === FALSE
            && strpos($br_label['border_radius'], '%') === FALSE) {
                $br_label['border_radius'] = floatval($br_label['border_radius']) . 'px';
            }
            $label_style .= 'border-radius:' . esc_html($br_label['border_radius']) . ';';
        }
        if( isset($br_label['line_height']) ) {
            $label_style .= 'line-height:' . floatval($br_label['line_height']) . 'px;';
        }
        if( isset($br_label['font_size']) ) {
            $label_style .= 'font-size:' . $br_label['font_size'] . 'px;';
        }
        return $label_style;
    }
    public function get_correct_div_style($div_style = '', $br_label, $product) {
        if( isset($br_label['padding_top']) ) {
            $div_style .= 'top:' . floatval($br_label['padding_top']) . 'px;';
        }
        if( isset($br_label['padding_horizontal']) && $br_label['position'] != 'center' ) {
            $div_style .= ($br_label['position'] == 'left' ? 'left:' : 'right:' ) . floatval($br_label['padding_horizontal']) . 'px;';
        }
        if( ! empty($br_label['zindex']) ) {
            $div_style .= 'z-index:' . $br_label['zindex'] . ';';
        }
        return $div_style;
    }
    public function get_correct_custom_css($css_styles = '', $br_label, $product, $style_id) {
        global $berocket_label_css_styles;
        if( empty($berocket_label_css_styles[$style_id]) ) {
            $styles_to_class = array(
                'div_custom_css'    => '.br_alabel.'.$style_id,
                'span_custom_css'   => '.br_alabel.'.$style_id.' > span',
                'b_custom_css'      => '.br_alabel.'.$style_id.' > span b',
                'i1_custom_css'     => '.br_alabel.'.$style_id.' > span i.template-i-before',
                'i2_custom_css'     => '.br_alabel.'.$style_id.' > span i.template-i',
                'i3_custom_css'     => '.br_alabel.'.$style_id.' > span i.template-i-after',
                'i4_custom_css'     => '.br_alabel.'.$style_id.' > span i.template-span-before',
            );
            foreach($styles_to_class as $option_name_css => $class_name_css) {
                if( ! empty($br_label[$option_name_css]) ) {
                    $css_styles .= '
                    ' . $class_name_css . ' {
                    ' . $br_label[$option_name_css] . '
                    }';
                }
            }
            $css_styles = (empty($css_styles) ? '' : '<style>' . $css_styles . '</style>');
            $berocket_label_css_styles[$style_id] = true;
        }
        return $css_styles;
    }
    public function generate_tooltip_data($br_label) {
        $tooltip_data = $tooltip_content = '';
        if( ! empty($br_label['tooltip_content']) ) {
            $br_label['tooltip_open_delay'] = (empty($br_label['tooltip_open_delay']) ? '0' : $br_label['tooltip_open_delay']);
            $br_label['tooltip_close_delay'] = (empty($br_label['tooltip_close_delay']) ? '0' : $br_label['tooltip_close_delay']);
            $tooltip_data .= ' data-tippy-delay="['.$br_label['tooltip_open_delay'].', '.$br_label['tooltip_close_delay'].']"';
            if( ! empty($br_label['tooltip_position']) ) {
                $tooltip_data .= ' data-tippy-placement="'.$br_label['tooltip_position'].'"';
            }
            if( ! empty($br_label['tooltip_max_width']) ) {
                $tooltip_data .= ' data-tippy-maxWidth="'.$br_label['tooltip_max_width'].'px"';
            }
            if( ! empty($br_label['tooltip_open_on']) ) {
                $tooltip_data .= ' data-tippy-trigger="'.$br_label['tooltip_open_on'].'"';
            }
            if( ! empty($br_label['tooltip_theme']) ) {
                $tooltip_data .= ' data-tippy-theme="'.$br_label['tooltip_theme'].'"';
            }
            $tooltip_data .= ' data-tippy-hideOnClick="'.(empty($br_label['tooltip_close_on_click']) ? 'false' : 'true').'"';
            $tooltip_data .= ' data-tippy-arrow="'.(empty($br_label['tooltip_use_arrow']) ? 'false' : 'true').'"';
            $tooltip_content = '<div style="display:none;" class="br_tooltip">'.$br_label['tooltip_content'].'</div>';
            wp_enqueue_style( 'berocket_tippy' );
            wp_enqueue_script( 'berocket_tippy');
        }
        return array('tooltip_data' => $tooltip_data, 'tooltip_content' => $tooltip_content);
    }
    public function product_get_availability_text($product) {
        if ( ! $product->is_in_stock() ) {
            $availability = __( 'Out of stock', 'woocommerce' );
        } elseif ( $product->managing_stock() && $product->is_on_backorder( 1 ) ) {
            $availability = $product->backorders_require_notification() ? __( 'Available on backorder', 'woocommerce' ) : '';
        } elseif ( $product->managing_stock() ) {
            $availability = wc_format_stock_for_display( $product );
        } else {
            $availability = __( 'In stock', 'woocommerce' );
        }
        return apply_filters( 'woocommerce_get_availability_text', $availability, $product );
    }
    public function check_label_on_post($label_id, $label_data, $product) {
        $product_id = br_wc_get_product_id($product);
        $show_label = wp_cache_get( 'WC_Product_'.$product_id, 'brapl_'.$label_id );
        if( $show_label === false ) {
            $show_label = BeRocket_conditions_advanced_labels::check($label_data, 'berocket_advanced_label_editor', array(
                'product' => $product,
                'product_id' => $product_id,
                'product_post' => br_wc_get_product_post($product),
                'post_id' => $product_id
            ));
            wp_cache_set( 'WC_Product_'.$product_id, ($show_label ? 1 : -1), 'brapl_'.$label_id, 60*60*24 );
        } else {
            $show_label = ( $show_label == 1 ? true : false );
        }
        return $show_label;
    }
    public function admin_settings( $tabs_info = array(), $data = array() ) {
        $options = $this->get_option();
        $shop_hook_array = array(
            array('value' => 'woocommerce_before_shop_loop_item_title+15',  'text' => __('Before Title 1', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_shop_loop_item_title+5',          'text' => __('Before Title 2', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_after_shop_loop_item_title+5',    'text' => __('After Title', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_before_shop_loop_item+5',         'text' => __('Before All', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_after_shop_loop_item+500',        'text' => __('After All', 'BeRocket_products_label_domain')),
            array('value' => 'berocket_disabled_label_hook_shop+10',        'text' => __('=DISABLED=', 'BeRocket_products_label_domain')),
        );
        $shop_hook_array = $this->add_additional_hooks($shop_hook_array, $options['shop_hook'], 'content', 'product');
        $shop_hook_array = apply_filters('berocket_apl_settings_shop_hook_array', $shop_hook_array);
        
        $single_hook_array_image = array(
            array('value' => 'woocommerce_product_thumbnails+15',               'text' => __('Under thumbnails', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_before_single_product_summary+50',    'text' => __('After Images', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_single_product_summary+2',            'text' => __('Before Summary Data', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_single_product_summary+7',            'text' => __('After Title', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_single_product_summary+100',          'text' => __('After Summary Data', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_before_single_product_summary+5',     'text' => __('Before All', 'BeRocket_products_label_domain')),
            array('value' => 'berocket_disabled_label_hook_image+10',           'text' => __('=DISABLED=', 'BeRocket_products_label_domain')),
        );
        $single_hook_array_image = $this->add_additional_hooks($single_hook_array_image, $options['product_hook_image'], 'content', 'single-product');
        $single_hook_array_image = apply_filters('berocket_apl_settings_single_hook_array_image', $single_hook_array_image);
        
        $single_hook_array_label = array(
            array('value' => 'woocommerce_product_thumbnails+10',               'text' => __('Under thumbnails', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_before_single_product_summary+50',    'text' => __('After Images', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_single_product_summary+2',            'text' => __('Before Summary Data', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_single_product_summary+7',            'text' => __('After Title', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_single_product_summary+100',          'text' => __('After Summary Data', 'BeRocket_products_label_domain')),
            array('value' => 'woocommerce_before_single_product_summary+5',     'text' => __('Before All', 'BeRocket_products_label_domain')),
            array('value' => 'berocket_disabled_label_hook_labels+10',          'text' => __('=DISABLED=', 'BeRocket_products_label_domain')),
        );
        $single_hook_array_label = $this->add_additional_hooks($single_hook_array_label, $options['product_hook_label'], 'content', 'single-product');
        $single_hook_array_label = apply_filters('berocket_apl_settings_single_hook_array_label', $single_hook_array_label);
        
        parent::admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                ),
                'CSS'     => array(
                    'icon' => 'css3',
                ),
                'JavaScript'     => array(
                    'icon' => 'code',
                ),
                'Advanced'     => array(
                    'icon' => 'cogs',
                ),
                'Labels' => array(
                    'icon' => 'plus-square',
                    'link' => admin_url( 'edit.php?post_type=br_labels' ),
                ),
                'License' => array(
                    'icon' => 'unlock-alt',
                    'link' => admin_url( 'admin.php?page=berocket_account' )
                ),
            ),
            array(
            'General' => array(
                'disable_labels' => array(
                    "type"     => "checkbox",
                    "label"    => __('Disable global labels', 'BeRocket_products_label_domain'),
                    "name"     => "disable_labels",
                    "value"    => "1",
                    "selected" => false,
                ),
                'disable_plabels' => array(
                    "type"     => "checkbox",
                    "label"    => __('Disable product labels', 'BeRocket_products_label_domain'),
                    "name"     => "disable_plabels",
                    "value"    => "1",
                    "selected" => false,
                ),
                'disable_ppage' => array(
                    "type"     => "checkbox",
                    "label"    => __('Disable labels on product page', 'BeRocket_products_label_domain'),
                    "name"     => "disable_ppage",
                    "value"    => "1",
                    "selected" => false,
                ),
                'remove_sale' => array(
                    "type"     => "checkbox",
                    "label"    => __('Remove WooCommerce sale label', 'BeRocket_products_label_domain'),
                    "name"     => "remove_sale",
                    "value"    => "1",
                    "selected" => false,
                ),
            ),
            'CSS'     => array(
                'global_font_awesome_disable' => array(
                    "label"     => __( 'Disable Font Awesome', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "fontawesome_frontend_disable",
                    "value"     => '1',
                    'label_for' => __('Don\'t loading css file for Font Awesome on site front end. Use it only if you doesn\'t uses Font Awesome icons in widgets or you have Font Awesome in your theme.', 'BeRocket_AJAX_domain'),
                ),
                'global_fontawesome_version' => array(
                    "label"    => __( 'Font Awesome Version', "BeRocket_AJAX_domain" ),
                    "name"     => "fontawesome_frontend_version",
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '', 'text' => __('Font Awesome 4', 'BeRocket_AJAX_domain')),
                        array('value' => 'fontawesome5', 'text' => __('Font Awesome 5', 'BeRocket_AJAX_domain')),
                    ),
                    "value"    => '',
                    "label_for" => __('Version of Font Awesome that will be used on front end. Please select version that you have in your theme', 'BeRocket_AJAX_domain'),
                ),
                array(
                    "type"  => "textarea",
                    "label" => __('Custom CSS', 'BeRocket_products_label_domain'),
                    "name"  => "custom_css",
                    "class" => "berocket_custom_css",
                ),
            ),
            'Advanced' => array(
                'shop_hook' => array(
                    "type"     => "selectbox",
                    "options"  => $shop_hook_array,
                    "label"    => __('Shop Hook', 'BeRocket_products_label_domain'),
                    "label_for"=> __('Where labels will be displayed on shop page. In different theme it can be different place(This means that it is supposed to be in this place)', 'BeRocket_products_label_domain'),
                    "name"     => "shop_hook",
                    "value"     => $this->defaults["shop_hook"],
                ),
                'product_hook_image' => array(
                    "type"     => "selectbox",
                    "options"  => $single_hook_array_image,
                    "label"    => __('Product Hook Image', 'BeRocket_products_label_domain'),
                    "label_for"=> __('Where on image labels will be displayed on product page. In different theme it can be different place(This means that it is supposed to be in this place)', 'BeRocket_products_label_domain'),
                    "name"     => "product_hook_image",
                    "value"     => $this->defaults["product_hook_image"],
                ),
                'product_hook_label' => array(
                    "type"     => "selectbox",
                    "options"  => $single_hook_array_label,
                    "label"    => __('Product Hook Label', 'BeRocket_products_label_domain'),
                    "label_for"=> __('Where default labels will be displayed on product page. In different theme it can be different place(This means that it is supposed to be in this place)', 'BeRocket_products_label_domain'),
                    "name"     => "product_hook_label",
                    "value"     => $this->defaults["product_hook_label"],
                ),
                'additional_hooks_load' => array(
                    'value' => '',
                    'section' => 'additional_hooks_load',
                )
            ),
            'JavaScript'     => array(
                array(
                    "type"      => "textarea",
                    "label"     => __('On Page Load', 'BeRocket_products_label_domain'),
                    "name"      => array("script", "js_page_load"),
                    "value"     => "",
                    "class" => "berocket_custom_javascript",
                ),
            ),
        ) );
    }
    public function menu_order_custom_post($compatibility) {
        $compatibility['br_labels'] = 'br_products_label';
        return $compatibility;
    }
    public function section_additional_hooks_load($item, $options) {
        $html = '<td><a class="button" href="'.admin_url( 'admin.php?page=' . $this->values[ 'option_page' ] ).'&tab=advanced&additional_hooks_load=1">ADDITIONAL HOOKS</a></td>
        <td><strong>Selecting additional hooks can break shop/product pages. Use it only if default do not works</strong></td>';
        return $html;
    }
    public function add_additional_hooks($array, &$option, $slug, $name = '') {
        if( ! empty($_GET['additional_hooks_load']) && $_GET['additional_hooks_load'] == 1 ) {
            $additional_shop_hook = $this->get_additional_hooks($slug, $name);
            foreach($additional_shop_hook as $add_shop_hook) {
                $array[] = array('value' => $add_shop_hook.'+1', 'text' => '=ADDITIONAL= '.$add_shop_hook.'+1');
                $array[] = array('value' => $add_shop_hook.'+100', 'text' => '=ADDITIONAL= '.$add_shop_hook.'+100');
            }
        }
        if( ! empty($option) ) {
            $shop_hook_exist = false;
            foreach($array as $shop_hook) {
                if( $shop_hook['value'] == $option ) {
                    $shop_hook_exist = true;
                    break;
                }
            }
            if( ! $shop_hook_exist ) {
                $array[] = array('value' => $option, 'text' => '=ADDITIONAL= '.$option);
            }
        }
        return $array;
    } 
    public function get_additional_hooks($slug, $name = '') {
        $template = $this->wc_get_template_part($slug, $name);
        $additional_hooks = array();
        if( $template ) {
            $content_product = file_get_contents($template);
            preg_match_all("/do_action\s*\(\s*(('((\-|\_|\w|\d)+)')|(\"((\-|\_|\w|\d)+)\"))(.*?)\)\s*;/", $content_product, $matches);
            $additional_hooks = $matches[3];
        }
        return $additional_hooks;
    }
    public function wc_get_template_part( $slug, $name = '' ) {
        $template = '';

        // Look in yourtheme/slug-name.php and yourtheme/woocommerce/slug-name.php.
        if ( $name && ! WC_TEMPLATE_DEBUG_MODE ) {
            $template = locate_template( array( "{$slug}-{$name}.php", WC()->template_path() . "{$slug}-{$name}.php" ) );
        }

        // Get default slug-name.php.
        if ( ! $template && $name && file_exists( WC()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
            $template = WC()->plugin_path() . "/templates/{$slug}-{$name}.php";
        }

        // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php.
        if ( ! $template && ! WC_TEMPLATE_DEBUG_MODE ) {
            $template = locate_template( array( "{$slug}.php", WC()->template_path() . "{$slug}.php" ) );
        }

        // Allow 3rd party plugins to filter template file from their plugin.
        $template = apply_filters( 'wc_get_template_part', $template, $slug, $name );

        return $template;
    }
    public function update_version($previous, $current) {
        if ( version_compare( $previous, '1.1.15', '<' ) ) {
            $unset_values = array('better_position', 'rotate', 'zindex');
            $posts_array = $this->custom_post->get_custom_posts();
            if ( is_array( $posts_array ) ) {
                foreach ( $posts_array as $label ) {
                    $br_label = $this->custom_post->get_option( $label );
                    foreach($unset_values as $unset_value)
                    if( isset($br_label[$unset_value]) ) {
                        unset($br_label[$unset_value]);
                    }
                    $br_label['better_position'] = '';
                    update_post_meta( $label, $this->custom_post->post_name, $br_label );
                }
            }
        }

        if ( version_compare( $previous, '2.0', '>' ) && version_compare( $previous, '3.1.7', '<' ) ) {
            $posts_array = $this->custom_post->get_custom_posts();
            if ( is_array( $posts_array ) ) {
                foreach ( $posts_array as $label ) {
                    $br_label = $this->custom_post->get_option( $label );
                    if ( $br_label['content_type'] == 'image' ) {
                        $br_label['content_type'] = 'custom';
                        if ( $br_label['text'] == '' ) {
                            $br_label['text'] = ' ';
                        }
                        update_post_meta( $label, $this->custom_post->post_name, $br_label );
                    }
                }
            }
        }
        if ( (version_compare( $previous, '2.0', '>' ) && version_compare( $previous, '3.1.7.1', '<' ))
        || (version_compare( $previous, '1.1.16', '<' )) ) {
            $posts_array = $this->custom_post->get_custom_posts();
            foreach ( $posts_array as $label ) {
                add_post_meta( $label, 'berocket_post_order', '0', true );
            }
        }
    }
    public function set_styles() {
        parent::set_styles();
        if ( class_exists( 'Storefront' ) ) {
            include_once('includes/theme_compatibility/storefront.php');
        }
    }
}

new BeRocket_products_label;
