<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Woo Floating Minicart Backend
 *
 * Allows admin to set WooCommerce Floating Minicart of specific product.
 *
 * @class   Woo_floating_minicart_backend 
 */


class Woo_floating_minicart_backend {

	/**
	 * Init and hook in the integration.
	 *
	 * @return void
	 */


	public function __construct() {
		$this->id                 = 'Woo_floating_minicart_backend';
		$this->method_title       = __( 'WooCommerce Floating Minicart Backend', 'woo-floating-minicart' );
		$this->method_description = __( 'WooCommerce Floating Minicart Backend', 'woo-floating-minicart' );

	
		// Filters
		// Hooks floating minicart setting to the woocommerce general product product admin setting section.	 
		//add_filter( 'woocommerce_general_settings', array( $this, 'awfm_floating_minicart_setting') , 100, 1 );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'awfm_add_settings_tab'), 50 );
        add_action( 'woocommerce_settings_tabs_floating_minicart', array( $this, 'awfm_settings_tab') );
        add_action( 'woocommerce_update_options_floating_minicart', array( $this, 'awfm_update_settings') );
		
		//add custom type
        add_action( 'woocommerce_admin_field_awfm_section_title', array( $this,'output_awfm_section_title'), 100, 1 );

		
	}

	public static function output_awfm_section_title($value){
	        ?>
        	<tr valign="top">
						<th scope="row" class="titledesc awfm-section-title" colspan="2">					
							<h2><?php echo $value['title']; ?></h2>
							<!-- <p><?php //echo $value['desc']; ?></p> -->	
						</th>						
			</tr>
        <?php
    }

	 /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function awfm_add_settings_tab( $settings_tabs ) {
        $settings_tabs['floating_minicart'] = __( 'Floating Minicart', 'woo-floating-minicart' );
        return $settings_tabs;
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::awfm_get_settings()
     */
    public static function awfm_settings_tab() {
        woocommerce_admin_fields( self::awfm_floating_minicart_setting() );
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::awfm_get_settings()
     */
    public static function awfm_update_settings() {
        woocommerce_update_options( self::awfm_floating_minicart_setting() );
    }
   


	
	/**
	 * Loading  floating minicart setting to the woocommerce general product product admin setting section.
	 *
	 * @return array
	 */


	public static function awfm_floating_minicart_setting(){    
	   
		
		$settings[] = array( 
			'name' => __( 'Floating Minicart Setting', 'woo-floating-minicart' ), 
			'type' => 'title', 
			'desc' => __('The following options affect how floating minicart is displayed on the frontend.', 'woo-floating-minicart'), 
			'id' => 'Woo_floating_minicart_title' 
		);

		$settings[] = array(
                'name' => __( 'Position', 'woocommerce-settings-tab-demo' ),
                'type' => 'awfm_section_title',
                'desc' => __('Set position of the floating cart', ''),
                'id'   => 'wc_settings_tab_demo_awfm_section_title'
            );
					
		$settings[] = array(
			'title'    	=> __( 'Position', 'woo-floating-minicart' ),
			'css'      => 'min-width:350px;',
			'id'       	=> 'Woo_floating_minicart_position',
			'desc'  	=> __( 'Floating minicart position', 'woo-floating-minicart' ),
			'type' => 'select',  
                  'options' => array( 
                      '' => __( 'Select Minicart Position', 'woo-floating-minicart' ),  
                      'floating-left' => __( 'Float Minicart left', 'woo-floating-minicart' ),  
                      'floating-right' => __( 'Float Minicart right', 'woo-floating-minicart' ),  
 				),  
             'desc_tip' =>  true, 
			
		);

		$settings[] = array(
			'title'    	=> __( 'Offset from top (%)', 'woo-floating-minicart' ),
			'css'      => 'width: 95px;',
			'id'       	=> 'Woo_floating_minicart_offset',
			'desc'  	=> __( 'Set desired offset from top in %', 'woo-floating-minicart' ),
			'type'     	=> 'number',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '50', 'woo-floating-minicart' ),
		);


		$settings[] = array(
                'name' => __( 'Background color', 'woocommerce-settings-tab-demo' ),
                'type' => 'awfm_section_title',
                'desc' => __('Set color hex codes to the respective section', ''),
                'id'   => 'wc_settings_tab_demo_awfm_section_title'
            );

		$settings[] = array(
			'title'    	=> __( 'Primary Background', 'woo-floating-minicart' ),
			'css'      => 'width:70px;',
			'id'       	=> 'Woo_floating_minicart_primary_color',
			'desc'  	=> __( 'Select/paste minicart primary color', 'woo-floating-minicart' ),
			'type'     	=> 'color',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '#42a2ce', 'woo-floating-minicart' ),
		);

		$settings[] = array(
			'title'    	=> __( 'Secondary Background', 'woo-floating-minicart' ),
			'css'      => 'width:70px;',
			'id'       	=> 'Woo_floating_minicart_secondary_color',
			'desc'  	=> __( 'Select/paste floating minicart secondary color and also depandent to button hover color', 'woo-floating-minicart' ),
			'type'     	=> 'color',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '#3c3c3c', 'woo-floating-minicart' ),
		);


		$settings[] = array(
			'title'    	=> __( 'Button Background', 'woo-floating-minicart' ),
			'css'      => 'width:70px;',
			'id'       	=> 'Woo_floating_minicart_button_color',
			'desc'  	=> __( 'Select/paste floating minicart button color', 'woo-floating-minicart' ),
			'type'     	=> 'color',
			'default'	=> '',
			'desc_tip'	=> true,
			'placeholder' => __( '#71b02f', 'woo-floating-minicart' ),
		);

		$settings[] = array(
                'name' => __( 'Empty cart setting', 'woocommerce-settings-tab-demo' ),
                'type' => 'awfm_section_title',
                'desc' => __('Set color hex codes to the respective section', ''),
                'id'   => 'wc_settings_tab_demo_awfm_section_title'
            );
		
		//shop page url
		
		if(function_exists('wc_get_page_id')){
				//for new version (<3.0.0) WooCommerce
				$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) ); 
			} else {
				// for old version ( >3.0.0 )WooCommerce
				$shop_page_url = get_permalink( woocommerce_get_page_id( 'shop' ) ); 
			}		
		$settings[] = array(
			'title'    	=> __( 'Show Shop page link while cart is empty.', 'woo-floating-minicart' ),			
			'id'       	=> 'Woo_floating_minicart_show_shop_page_link',
			'desc'  	=> __( 'Check for "Yes". '.$shop_page_url, 'woo-floating-minicart' ),
			'type'     	=> 'checkbox',
			'default'	=> '',
			'desc_tip'	=> true,			
		);


		// best selling popular products
		$settings[] = array(
			'title'    	=> __( 'Show best selling products while cart is empty.', 'woo-floating-minicart' ),			
			'id'       	=> 'Woo_floating_minicart_show_best_selling_products',
			'desc'  	=> __( 'Check for "Yes". 5 best selling products link will be shown on empty cart.', 'woo-floating-minicart' ),
			'type'     	=> 'checkbox',
			'default'	=> '',
			'desc_tip'	=> true,			
		);

		$settings[] = array(
			'title'    	=> __( 'Or, simply hide the floating minicart while cart is empty.', 'woo-floating-minicart' ),			
			'id'       	=> 'Woo_floating_minicart_hide',
			'desc'  	=> __( 'Check for "Yes"', 'woo-floating-minicart' ),
			'type'     	=> 'checkbox',
			'default'	=> '',
			'desc_tip'	=> true,			
		);


		$settings[] = array( 'type' => 'sectionend', 'id' => 'Woo_floating_minicart_sectionend');

		return apply_filters( 'awfm_floating_minicart_setting_fields', $settings );
	   
	}
	
}

$awfm_backend = new Woo_floating_minicart_backend();