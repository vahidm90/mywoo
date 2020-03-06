<?php
	
	defined( 'ABSPATH' ) or die( 'Keep Quit' );
	
	//-------------------------------------------------------------------------------
	// Detecting IE 11 Browser
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_is_ie11' ) ):
		function wvg_is_ie11() {
			global $is_IE;
			$ua   = $_SERVER[ 'HTTP_USER_AGENT' ];
			$is11 = preg_match( "/Trident\/7.0;(.*)rv:11.0/", $ua, $match ) !== false;
			
			return $is_IE && $is11;
			//return TRUE;
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Generate Inline Style
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_generate_inline_style' ) ):
		
		function wvg_generate_inline_style( $styles = array() ) {
			
			$generated = array();
			
			foreach ( $styles as $property => $value ) {
				$generated[] = "{$property}: $value";
			}
			
			return implode( '; ', array_unique( apply_filters( 'wvg_generate_inline_style', $generated ) ) );
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Enable Theme Support
	//-------------------------------------------------------------------------------
	
	if ( ! function_exists( 'wvg_enable_theme_support' ) ):
		function wvg_enable_theme_support() {
			// WooCommerce.
			add_theme_support( 'wc-product-gallery-zoom' );
			add_theme_support( 'wc-product-gallery-lightbox' );
			// add_theme_support( 'wc-product-gallery-slider' );
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Remove Default Template
	//-------------------------------------------------------------------------------
	
	if ( ! function_exists( 'wvg_remove_default_template' ) ) {
		function wvg_remove_default_template() {
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 10 );
			remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
			
			// remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
			// remove_action( 'woocommerce_before_single_product_summary_product_images', 'woocommerce_show_product_thumbnails', 20 );
		}
	}
	
	//-------------------------------------------------------------------------------
	// Add Gallery Template MAIN WooCommerce Override
	//-------------------------------------------------------------------------------
	
	/*	function __woocommerce_show_product_images() {
			
			if ( apply_filters( 'wvg_product_images_template_include_once', false ) ) {
				include_once woo_variation_gallery()->template_path( 'product-images.php' );
			} else {
				wc_get_template( 'product-images.php', array(), '', woo_variation_gallery()->template_path() );
			}
		}
		
		// Override
		function __woocommerce_show_product_thumbnails() {
		
		}*/
	
	if ( ! function_exists( 'wvg_gallery_template_override' ) ) {
		function wvg_gallery_template_override( $located, $template_name ) {
			
			// Disable for Bundle Product
			if ( function_exists( 'wc_pb_is_product_bundle' ) && wc_pb_is_product_bundle() ) {
				return $located;
			}
			
			if ( $template_name == 'single-product/product-image.php' ) {
				$located = woo_variation_gallery()->template_path( 'product-images.php' );
			}
			
			if ( $template_name == 'single-product/product-thumbnails.php' ) {
				$located = woo_variation_gallery()->template_path( 'product-thumbnails.php' );
			}
			
			return apply_filters( 'wvg_gallery_template_override_location', $located, $template_name );
		}
	}
	
	if ( ! function_exists( 'wvg_gallery_template_part_override' ) ) {
		function wvg_gallery_template_part_override( $template, $slug ) {
			
			// Disable for Bundle Product
			if ( function_exists( 'wc_pb_is_product_bundle' ) && wc_pb_is_product_bundle() ) {
				return $template;
			}
			
			if ( $slug == 'single-product/product-image' ) {
				$template = woo_variation_gallery()->template_path( 'product-images.php' );
			}
			
			if ( $slug == 'single-product/product-thumbnails' ) {
				$template = woo_variation_gallery()->template_path( 'product-thumbnails.php' );
			}
			
			return apply_filters( 'wvg_gallery_template_part_override_location', $template, $slug );
		}
	}
	
	
	// For Elementor Page Builder Override
	add_filter( 'wc_get_template', 'wvg_gallery_template_override', 30, 2 );
	
	// Flatsome Theme Custom Layout Support
	add_filter( 'wc_get_template_part', 'wvg_gallery_template_part_override', 30, 2 );
	
	
	//-------------------------------------------------------------------------------
	// Gallery Template
	//-------------------------------------------------------------------------------
	
	function wvg_get_gallery_image_props( $attachment_id, $product_id = false ) {
		$props      = array(
			'image_id'                => '',
			'title'                   => '',
			'caption'                 => '',
			'url'                     => '',
			'alt'                     => '',
			'full_src'                => '',
			'full_src_w'              => '',
			'full_src_h'              => '',
			'full_class'              => '',
			//'full_srcset'              => '',
			//'full_sizes'               => '',
			'gallery_thumbnail_src'   => '',
			'gallery_thumbnail_src_w' => '',
			'gallery_thumbnail_src_h' => '',
			'gallery_thumbnail_class' => '',
			//'gallery_thumbnail_srcset' => '',
			//'gallery_thumbnail_sizes'  => '',
			'archive_src'             => '',
			'archive_src_w'           => '',
			'archive_src_h'           => '',
			'archive_class'           => '',
			//'archive_srcset'           => '',
			//'archive_sizes'            => '',
			'src'                     => '',
			'class'                   => '',
			'src_w'                   => '',
			'src_h'                   => '',
			'srcset'                  => '',
			'sizes'                   => '',
		);
		$attachment = get_post( $attachment_id );
		
		if ( $attachment ) {
			
			$props[ 'image_id' ] = $attachment_id;
			$props[ 'title' ]    = wp_strip_all_tags( $attachment->post_title );
			$props[ 'caption' ]  = wp_strip_all_tags( $attachment->post_excerpt );
			$props[ 'url' ]      = wp_get_attachment_url( $attachment_id );
			
			// Alt text.
			$alt_text = array( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ), $props[ 'caption' ], wp_strip_all_tags( $attachment->post_title ) );
			
			if ( $product_id ) {
				$product    = wc_get_product( $product_id );
				$alt_text[] = wp_strip_all_tags( get_the_title( $product->get_id() ) );
			}
			
			$alt_text       = array_filter( $alt_text );
			$props[ 'alt' ] = isset( $alt_text[ 0 ] ) ? $alt_text[ 0 ] : '';
			
			// Large version.
			$full_size             = apply_filters( 'woocommerce_gallery_full_size', apply_filters( 'woocommerce_product_thumbnails_large_size', 'full' ) );
			$full_size_src         = wp_get_attachment_image_src( $attachment_id, $full_size );
			$props[ 'full_src' ]   = $full_size_src[ 0 ];
			$props[ 'full_src_w' ] = $full_size_src[ 1 ];
			$props[ 'full_src_h' ] = $full_size_src[ 2 ];
			
			$full_size_class = $full_size;
			if ( is_array( $full_size_class ) ) {
				$full_size_class = implode( 'x', $full_size_class );
			}
			
			$props[ 'full_class' ] = "attachment-$full_size_class size-$full_size_class";
			//$props[ 'full_srcset' ] = wp_get_attachment_image_srcset( $attachment_id, $full_size );
			//$props[ 'full_sizes' ]  = wp_get_attachment_image_sizes( $attachment_id, $full_size );
			
			
			// Gallery thumbnail.
			$gallery_thumbnail                  = wc_get_image_size( 'gallery_thumbnail' );
			$gallery_thumbnail_size             = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail[ 'width' ], $gallery_thumbnail[ 'height' ] ) );
			$gallery_thumbnail_src              = wp_get_attachment_image_src( $attachment_id, $gallery_thumbnail_size );
			$props[ 'gallery_thumbnail_src' ]   = $gallery_thumbnail_src[ 0 ];
			$props[ 'gallery_thumbnail_src_w' ] = $gallery_thumbnail_src[ 1 ];
			$props[ 'gallery_thumbnail_src_h' ] = $gallery_thumbnail_src[ 2 ];
			
			$gallery_thumbnail_class = $gallery_thumbnail_size;
			if ( is_array( $gallery_thumbnail_class ) ) {
				$gallery_thumbnail_class = implode( 'x', $gallery_thumbnail_class );
			}
			
			$props[ 'gallery_thumbnail_class' ] = "attachment-$gallery_thumbnail_class size-$gallery_thumbnail_class";
			//$props[ 'gallery_thumbnail_srcset' ] = wp_get_attachment_image_srcset( $attachment_id, $gallery_thumbnail );
			//$props[ 'gallery_thumbnail_sizes' ]  = wp_get_attachment_image_sizes( $attachment_id, $gallery_thumbnail );
			
			
			// Archive/Shop Page version.
			$thumbnail_size           = apply_filters( 'woocommerce_thumbnail_size', 'woocommerce_thumbnail' );
			$thumbnail_size_src       = wp_get_attachment_image_src( $attachment_id, $thumbnail_size );
			$props[ 'archive_src' ]   = $thumbnail_size_src[ 0 ];
			$props[ 'archive_src_w' ] = $thumbnail_size_src[ 1 ];
			$props[ 'archive_src_h' ] = $thumbnail_size_src[ 2 ];
			
			$archive_thumbnail_class = $thumbnail_size;
			if ( is_array( $archive_thumbnail_class ) ) {
				$archive_thumbnail_class = implode( 'x', $archive_thumbnail_class );
			}
			
			$props[ 'archive_class' ] = "attachment-$archive_thumbnail_class size-$archive_thumbnail_class";
			//$props[ 'archive_srcset' ] = wp_get_attachment_image_srcset( $attachment_id, $thumbnail_size );
			//$props[ 'archive_sizes' ]  = wp_get_attachment_image_sizes( $attachment_id, $thumbnail_size );
			
			
			// Image source.
			$image_size       = apply_filters( 'woocommerce_gallery_image_size', 'woocommerce_single' );
			$src              = wp_get_attachment_image_src( $attachment_id, $image_size );
			$props[ 'src' ]   = $src[ 0 ];
			$props[ 'src_w' ] = $src[ 1 ];
			$props[ 'src_h' ] = $src[ 2 ];
			
			$image_size_class = $image_size;
			if ( is_array( $image_size_class ) ) {
				$image_size_class = implode( 'x', $image_size_class );
			}
			$props[ 'class' ]  = "wp-post-image wvg-post-image attachment-$image_size_class size-$image_size_class ";
			$props[ 'srcset' ] = wp_get_attachment_image_srcset( $attachment_id, $image_size );
			$props[ 'sizes' ]  = wp_get_attachment_image_sizes( $attachment_id, $image_size );
		}
		
		return apply_filters( 'woo_variation_gallery_get_image_props', $props, $attachment_id, $product_id );
	}
	
	
	if ( ! function_exists( 'wvg_get_gallery_image_html' ) ):
		function wvg_get_gallery_image_html( $attachment_id, $options = array() ) {
			
			$defaults = array( 'is_main_thumbnail' => false, 'has_only_thumbnail' => false );
			$options  = wp_parse_args( $options, $defaults );
			
			$image = wvg_get_gallery_image_props( $attachment_id );
			
			$classes = apply_filters( 'wvg_gallery_image_html_class', array(
				'wvg-gallery-image',
			), $attachment_id, $image );
			
			
			$template = '<div class="wvg-single-gallery-image-container"><img width="%d" height="%d" src="%s" class="%s" alt="%s" title="%s" data-caption="%s" data-src="%s" data-large_image="%s" data-large_image_width="%d" data-large_image_height="%d" srcset="%s" sizes="%s" /></div>';
			
			$inner_html = sprintf( $template, $image[ 'src_w' ], $image[ 'src_h' ], $image[ 'src' ], $image[ 'class' ], $image[ 'alt' ], $image[ 'title' ], $image[ 'caption' ], $image[ 'full_src' ], $image[ 'full_src' ], $image[ 'full_src_w' ], $image[ 'full_src_h' ], $image[ 'srcset' ], $image[ 'sizes' ] );
			
			$inner_html = apply_filters( 'woo_variation_gallery_image_inner_html', $inner_html, $image, $template, $attachment_id, $options );
			
			// If require thumbnail
			if ( ! $options[ 'is_main_thumbnail' ] ) {
				$classes = apply_filters( 'woo_variation_gallery_thumbnail_image_html_class', array(
					'wvg-gallery-thumbnail-image',
				), $attachment_id, $image );
				
				/*if ( $loop_index < 1 ) {
				//	$classes[] = 'current-thumbnail';
				}*/
				
				$template   = '<img width="%d" height="%d" src="%s" class="%s" alt="%s" title="%s" />';
				$inner_html = sprintf( $template, $image[ 'gallery_thumbnail_src_w' ], $image[ 'gallery_thumbnail_src_h' ], $image[ 'gallery_thumbnail_src' ], $image[ 'gallery_thumbnail_class' ], $image[ 'alt' ], $image[ 'title' ] );
				$inner_html = apply_filters( 'woo_variation_gallery_thumbnail_image_inner_html', $inner_html, $image, $template, $attachment_id, $options );
			}
			
			return '<div class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"><div>' . $inner_html . '</div></div>';
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Get Embed URL
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_get_simple_oembed_url' ) ):
		function wvg_get_simple_embed_url( $main_link ) {
			
			// Youtube
			$re    = '@https?://(www.)?youtube.com/watch\?v=([^&]+)@';
			$subst = 'https://www.youtube.com/embed/$2?feature=oembed';
			
			$link = preg_replace( $re, $subst, $main_link, 1 );
			
			// Vimeo
			$re    = '@https?://(www.)?vimeo.com/([^/]+)@';
			$subst = 'https://player.vimeo.com/video/$2';
			
			$link = preg_replace( $re, $subst, $link, 1 );
			
			
			return apply_filters( 'wvg_get_simple_oembed_url', $link, $main_link );
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Gallery Admin
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_gallery_admin_html' ) ):
		function wvg_gallery_admin_html( $loop, $variation_data, $variation ) {
			$variation_id   = absint( $variation->ID );
			$gallery_images = get_post_meta( $variation_id, 'woo_variation_gallery_images', true );
			?>
            <div class="form-row form-row-full woo-variation-gallery-wrapper">
                <h4><?php esc_html_e( 'Variation Image Gallery', 'woo-variation-gallery' ) ?></h4>
                <div class="woo-variation-gallery-image-container">
                    <ul class="woo-variation-gallery-images">
						<?php
							if ( is_array( $gallery_images ) && ! empty( $gallery_images ) ) {
								include 'admin-template.php';
							}
						?>
                    </ul>
                </div>
                <p class="add-woo-variation-gallery-image-wrapper hide-if-no-js">
                    <a href="#" data-product_variation_id="<?php echo absint( $variation->ID ) ?>" class="button add-woo-variation-gallery-image"><?php esc_html_e( 'Add Gallery Images', 'woo-variation-gallery' ) ?></a>
					<?php if ( ! woo_variation_gallery()->is_pro_active() ): ?>
                        <a target="_blank" href="<?php echo esc_url( woo_variation_gallery()->get_pro_link() ) ?>" style="display: none" class="button woo-variation-gallery-pro-button button-danger"><?php esc_html_e( 'Upgrade to pro to add more images and videos', 'woo-variation-gallery' ) ?></a>
					<?php endif; ?>
                </p>
            </div>
			<?php
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Save Gallery
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_save_variation_gallery' ) ):
		function wvg_save_variation_gallery( $variation_id, $i ) {
			if ( isset( $_POST[ 'woo_variation_gallery' ] ) ) {
				if ( isset( $_POST[ 'woo_variation_gallery' ][ $variation_id ] ) ) {
					$gallery_image_ids = (array) array_map( 'absint', $_POST[ 'woo_variation_gallery' ][ $variation_id ] );
					update_post_meta( $variation_id, 'woo_variation_gallery_images', $gallery_image_ids );
				} else {
					delete_post_meta( $variation_id, 'woo_variation_gallery_images' );
				}
			} else {
				delete_post_meta( $variation_id, 'woo_variation_gallery_images' );
			}
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Available Gallery
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_available_variation_gallery' ) ):
		function wvg_available_variation_gallery( $available_variation, $variationProductObject, $variation ) {
			
			$product_id         = absint( $variation->get_parent_id() );
			$variation_id       = absint( $variation->get_id() );
			$variation_image_id = absint( $variation->get_image_id() );
			
			$has_variation_gallery_images = (bool) get_post_meta( $variation_id, 'woo_variation_gallery_images', true );
			//  $product                      = wc_get_product( $product_id );
			
			if ( $has_variation_gallery_images ) {
				$gallery_images = (array) get_post_meta( $variation_id, 'woo_variation_gallery_images', true );
			} else {
				// $gallery_images = $product->get_gallery_image_ids();
				$gallery_images = $variationProductObject->get_gallery_image_ids();
			}
			
			
			if ( $variation_image_id ) {
				// Add Variation Default Image
				array_unshift( $gallery_images, $variation_image_id );
			} else {
				// Add Product Default Image
				
				/*if ( has_post_thumbnail( $product_id ) ) {
					array_unshift( $gallery_images, get_post_thumbnail_id( $product_id ) );
				}*/
				$parent_product          = wc_get_product( $product_id );
				$parent_product_image_id = $parent_product->get_image_id();
				
				if ( ! empty( $parent_product_image_id ) ) {
					array_unshift( $gallery_images, $parent_product_image_id );
				}
			}
			
			$available_variation[ 'variation_gallery_images' ] = array();
			
			foreach ( $gallery_images as $i => $variation_gallery_image_id ) {
				$available_variation[ 'variation_gallery_images' ][ $i ] = wvg_get_gallery_image_props( $variation_gallery_image_id );
			}
			
			return apply_filters( 'wvg_available_variation_gallery', $available_variation, $variation, $product_id );
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Get Single Variation info
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_get_product_variation' ) ):
		
		function wvg_get_product_variation( $product_id, $variation_id ) {
			$variable_product = new WC_Product_Variable( absint( $product_id ) );
			$variation        = $variable_product->get_available_variation( absint( $variation_id ) );
			
			return $variation;
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Get Variations info
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_get_product_variations' ) ):
		function wvg_get_product_variations( $product ) {
			
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( absint( $product ) );
			}
			
			return $product->get_available_variations();
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Get Product Default Attributes
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_get_product_default_attributes' ) ):
		function wvg_get_product_default_attributes( $product_id ) {
			
			$product = wc_get_product( $product_id );
			
			if ( ! $product->is_type( 'variable' ) ) {
				return array();
			}
			
			$variable_product = new WC_Product_Variable( absint( $product_id ) );
			
			return $variable_product->get_default_attributes();
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Get Selected variation id
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_get_product_default_variation_id' ) ):
		function wvg_get_product_default_variation_id( $product, $attributes ) {
			
			if ( is_numeric( $product ) ) {
				$product = wc_get_product( $product );
			}
			
			if ( ! $product->is_type( 'variable' ) ) {
				return 0;
			}
			
			foreach ( $attributes as $key => $value ) {
				if ( strpos( $key, 'attribute_' ) === 0 ) {
					continue;
				}
				
				unset( $attributes[ $key ] );
				$attributes[ sprintf( 'attribute_%s', $key ) ] = $value;
			}
			
			$data_store = WC_Data_Store::load( 'product' );
			
			return $data_store->find_matching_product_variation( $product, $attributes );
		}
	endif;
	
	
	//-------------------------------------------------------------------------------
	// Product Loop Class
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_product_loop_post_class' ) ):
		function wvg_product_loop_post_class( $classes, $class, $product_id ) {
			
			if ( 'product' === get_post_type( $product_id ) ) {
				$product = wc_get_product( $product_id );
				//if ( $product->is_type( 'variable' ) ) {
				$classes[] = 'woo-variation-gallery-product';
				//}
			}
			
			return $classes;
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Ajax request of non ajax variation
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_get_default_gallery' ) ):
		function wvg_get_default_gallery() {
			$product_id = absint( $_POST[ 'product_id' ] );
			
			$images = wvg_get_default_gallery_images( $product_id );
			
			wp_send_json_success( apply_filters( 'wvg_get_default_gallery', $images, $product_id ) );
		}
	endif;
	
	
	//-------------------------------------------------------------------------------
	// Ajax request of non ajax variation
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_get_available_variation_images' ) ):
		function wvg_get_available_variation_images( $product_id = false ) {
			$product_id           = $product_id ? $product_id : absint( $_POST[ 'product_id' ] );
			$images               = array();
			$available_variations = wvg_get_product_variations( $product_id );
			
			foreach ( $available_variations as $i => $variation ) {
				array_push( $variation[ 'variation_gallery_images' ], $variation[ 'image' ] );
			}
			
			foreach ( $available_variations as $i => $variation ) {
				foreach ( $variation[ 'variation_gallery_images' ] as $image ) {
					array_push( $images, $image );
				}
			}
			
			wp_send_json_success( apply_filters( 'wvg_get_available_variation_images', $images, $product_id ) );
		}
	endif;
	
	//-------------------------------------------------------------------------------
	// Get Default Gallery Images
	//-------------------------------------------------------------------------------
	if ( ! function_exists( 'wvg_get_default_gallery_images' ) ):
		function wvg_get_default_gallery_images( $product_id ) {
			
			$product           = wc_get_product( $product_id );
			$product_id        = $product->get_id();
			$attachment_ids    = $product->get_gallery_image_ids();
			$post_thumbnail_id = $product->get_image_id();
			
			$images = array();
			
			/*if ( has_post_thumbnail( $product_id ) ) {
				array_unshift( $gallery_images, get_post_thumbnail_id( $product_id ) );
			}*/
			
			$post_thumbnail_id = (int) apply_filters( 'woo_variation_gallery_post_thumbnail_id', $post_thumbnail_id, $attachment_ids, $product );
			$attachment_ids    = (array) apply_filters( 'woo_variation_gallery_attachment_ids', $attachment_ids, $post_thumbnail_id, $product );
			
			
			if ( ! empty( $post_thumbnail_id ) ) {
				array_unshift( $attachment_ids, $post_thumbnail_id );
			}
			
			if ( is_array( $attachment_ids ) && ! empty( $attachment_ids ) ) {
				
				foreach ( $attachment_ids as $i => $image_id ) {
					$images[ $i ] = wvg_get_gallery_image_props( $image_id );
				}
			}
			
			return apply_filters( 'wvg_get_default_gallery_images', $images, $product );
		}
	endif;
	
	//----------------------------------------------------------------------
	// Hook Info
	//----------------------------------------------------------------------
	
	if ( ! function_exists( 'wvg_hook_info' ) ):
		function wvg_hook_info( $hook_name ) {
			global $wp_filter;
			$docs     = array();
			$template = "\t - %s Priority - %s.\n\tin file %s #%s\n\n";
			echo '<pre>';
			echo "\t# Hook Name \"" . $hook_name . "\"";
			echo "\n\n";
			if ( isset( $wp_filter[ $hook_name ] ) ) {
				foreach ( $wp_filter[ $hook_name ] as $pri => $fn ) {
					foreach ( $fn as $fnname => $fnargs ) {
						if ( is_array( $fnargs[ 'function' ] ) ) {
							$reflClass = new ReflectionClass( $fnargs[ 'function' ][ 0 ] );
							$reflFunc  = $reflClass->getMethod( $fnargs[ 'function' ][ 1 ] );
							$class     = $reflClass->getName();
							$function  = $reflFunc->name;
						} else {
							$reflFunc  = new ReflectionFunction( $fnargs[ 'function' ] );
							$class     = false;
							$function  = $reflFunc->name;
							$isClosure = (bool) $reflFunc->isClosure();
						}
						if ( $class ) {
							$functionName = sprintf( 'Class "%s::%s"', $class, $function );
						} else {
							$functionName = ( $isClosure ) ? "Anonymous Function $function" : "Function \"$function\"";
						}
						printf( $template, $functionName, $pri, str_ireplace( ABSPATH, '', $reflFunc->getFileName() ), $reflFunc->getStartLine() );
						$docs[] = array( $functionName, $pri );
					}
				}
				echo "\tAction Hook Commenting\n\t----------------------\n\n";
				echo "\t/**\n\t* " . $hook_name . " hook\n\t*\n";
				foreach ( $docs as $doc ) {
					echo "\t* @hooked " . $doc[ 0 ] . " - " . $doc[ 1 ] . "\n";
				}
				echo "\t*/";
				echo "\n\n";
				echo "\tdo_action( '" . $hook_name . "' );";
			}
			echo '</pre>';
		}
	endif;
	