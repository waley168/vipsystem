<?php
/**
 * Plugin Name: Shop as Client for WooCommerce
 * Plugin URI: https://www.webdados.pt/wordpress/plugins/shop-as-client-for-woocommerce/
 * Version: 1.9.2
 * Description: Allows a WooCommerce Store Administrator or Shop Manager to use the frontend and assign a new order to a registered or new customer. Useful for phone or email orders.
 * Author: PT Woo Plugins (by Webdados)
 * Author URI: https://ptwooplugins.com/
 * Text Domain: shop-as-client
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * WC requires at least: 4.0
 * WC tested up to: 6.8
**/

/* WooCommerce CRUD ready */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 **/
add_action( 'plugins_loaded', function() {
	if ( class_exists( 'WooCommerce' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '4.0', '>=' ) ) {
	
		//Languages
		add_action( 'plugins_loaded', 'shop_as_client_init', 11 );
		function shop_as_client_init() {
			load_plugin_textdomain( 'shop-as-client' );
			add_action( 'wp_enqueue_scripts', 'shop_as_client_enqueue_scripts' );
		}
	
		//Can checkout with shop as client?
		function shop_as_client_can_checkout() {
			//The shop_as_client_allow_checkout filter can be used to allow other user roles to use this functionality - Use carefully and wisely
			return current_user_can( 'manage_options' ) || current_user_can( 'manage_woocommerce' ) || apply_filters( 'shop_as_client_allow_checkout', false );
		}
	
		//Our field
		add_filter( 'woocommerce_billing_fields' , 'shop_as_client_init_woocommerce_billing_fields', PHP_INT_MAX );
		function shop_as_client_init_woocommerce_billing_fields( $fields ) {
			if ( shop_as_client_can_checkout() && is_checkout() ) {
				$priority = apply_filters( 'shop_as_client_field_priority', 990 );
				//Shop as client?
				$fields['billing_shop_as_client'] = array(
					'label'		=> __( 'Shop as client', 'shop-as-client' ),
					'required'	=> true,
					'class'		=> array( 'form-row-wide' ),
					'clear'		=> true,
					'priority'	=> $priority,
					'type'		=> 'select',
					'options'	=> array(
						'yes'	=> __( 'Yes', 'shop-as-client' ),
						'no'	=> __( 'No', 'shop-as-client' ),
					),
					'default'	=> apply_filters( 'shop_as_client_default_shop_as_client', 'yes' ),
				);
				$priority++;
				//Create user if it doesn't exist?
				$fields['billing_shop_as_client_create_user'] = array(
					'label'		=> __( 'Create user (if not found by email)?', 'shop-as-client' ),
					'required'	=> true,
					'class'		=> array( 'form-row-wide' ),
					'clear'		=> true,
					'priority'	=> $priority,
					'type'		=> 'select',
					'options'	=> array(
						'yes'	=> __( 'Yes', 'shop-as-client' ),
						'no'	=> __( 'No (leave as guest)', 'shop-as-client' ),
					),
					'default'	=> apply_filters( 'shop_as_client_default_create_user', 'no' ),
				);
			}
			return $fields;
		}
	
		//Enqueue scripts
		function shop_as_client_enqueue_scripts() {
			if ( is_checkout() ) {
				wp_enqueue_script( 'shop-as-client', plugins_url( 'js/functions.js', __FILE__ ), array( 'jquery' ), '1.3.0', true );
				wp_localize_script( 'shop-as-client', 'shop_as_client', array(
					'txt_pro' => 
					sprintf(
						'<p><a href="https://ptwooplugins.com/product/shop-as-client-for-woocommerce-pro-add-on/" target="_blank">%s</a></p>',
						__( 'Do you want to load the customer details automatically?<br/>Get the PRO add-on!', 'shop-as-client' )
					)
				) );
			}
		}
	
		//Force our field defaults
		add_filter( 'default_checkout_billing_shop_as_client', 'shop_as_client_default_checkout_billing_shop_as_client', 10, 2 );
		function shop_as_client_default_checkout_billing_shop_as_client( $value, $input ) {
			return apply_filters( 'shop_as_client_default_shop_as_client', 'yes' );
		}
		add_filter( 'default_checkout_billing_shop_as_client_create_user', 'shop_as_client_default_checkout_billing_shop_as_client_create_user', 10, 2 );
		function shop_as_client_default_checkout_billing_shop_as_client_create_user( $value, $input ) {
			return apply_filters( 'shop_as_client_default_create_user', 'no' );
		}
	
		//Get order "shop as client"
		function shop_as_client_get_order_status( $order ) {
			return 'yes' == $order->get_meta( '_billing_shop_as_client' );
		}
	
		//Return yes to woocommerce_registration_generate_password
		function shop_as_client_woocommerce_registration_generate_password( $value ) {
			return 'yes';
		}
	
		//Adjust user - Inspiration: https://gist.github.com/twoelevenjay/80294a635969a54e4693
		add_filter( 'woocommerce_checkout_customer_id', 'shop_as_client_woocommerce_checkout_customer_id' );
		function shop_as_client_woocommerce_checkout_customer_id( $user_id ) {
			if ( shop_as_client_can_checkout() ) {
				if ( isset( $_POST['billing_shop_as_client'] ) && 'yes' == $_POST['billing_shop_as_client'] ) {
					$user_id = 0;
					 // Check if an exisiting user already uses this email address.
					$user_email = $_POST['billing_email'];
					$user_phone = $_POST['billing_phone'];
					if ( empty( $user_email ) ) $user_email = apply_filters( 'shop_as_client_user_email_if_empty', $user_email, $_POST );
					//Get user by profile email
					if ( $user = get_user_by( 'email', $user_email ) ) {
						//User found
						$user_id = $user->ID;
						//Should we update the user details? - This is by WooCommerce on WC_Checkout process_customer
					} else {
						//Get user by WooCommerce billing email
						if ( ( empty( $user_email ) ) && ( $users = get_users( array(
							'meta_key'     => 'billing_phone',
							'meta_value'   => $user_phone,
							'meta_compare' => '='
						) ) ) ) {
							//User found - We should check for more than one...
							$user_id = $users[0]->ID;
						} else {
							//Create user or guest?
							if ( isset( $_POST['billing_shop_as_client_create_user'] ) && 'yes' == $_POST['billing_shop_as_client_create_user'] ) {
								$temp_user_id = shop_as_client_create_customer( $user_email, sanitize_text_field( $_POST['billing_first_name'] ), sanitize_text_field( $_POST['billing_phone'] ) );
								if ( ! is_wp_error( $temp_user_id ) ) {
									$user_id = $temp_user_id;
								} else {
									$message = sprintf(
										__( 'Shop as Client failed to create user: %s' , 'shop-as-client' ),
										$temp_user_id->get_error_message()
									);
									throw new Exception( $message );
								}
							}
						}
					}
				}
			}
			return $user_id;
		}
	
		//Create the user/customer
		function shop_as_client_create_customer( $user_email, $user_first_name, $user_last_name ) {
			$username = $user_last_name;
			//Force password generation by WooCommerce (and sending via email), even if the option is not set
			if ( apply_filters( 'shop_as_client_email_password', true ) ) {
				add_filter( 'option_woocommerce_registration_generate_password', 'shop_as_client_woocommerce_registration_generate_password' );
				$password = '';
			} else {
				$password = wp_generate_password();
			}
			$user_id = wp_create_user( $username, $password );
			if ( apply_filters( 'shop_as_client_email_password', true ) ) remove_filter( 'option_woocommerce_registration_generate_password', 'shop_as_client_woocommerce_registration_generate_password' );
			if ( ! is_wp_error( $user_id ) ) {
				wp_update_user(
					array( 'ID' => $user_id,
						'first_name' => $user_first_name,
						'display_name' => trim( $user_first_name ),
						'role' => 'customer',
					)
				);
				update_user_meta( $user_id, 'billing_phone', $user_last_name );
			} else {
				$message = 'Shop as Client failed to create user: '.$user_id->get_error_message();
				//We should notify the admin user somehow - WooCommerce already does that
				//$errors  = new WP_Error();
				//$errors->add( 'shop_as_client_failed_create_user', $message );
				//foreach ( $errors->errors as $code => $messages ) {
				//	$data1 = $errors->get_error_data( $code );
				//	foreach ( $messages as $message ) {
				//		wc_add_notice( $message, 'error', $data1 );
				//	}
				//}
				//error_log( $message );
			}
			return $user_id;
		}
	
		//Prevent logged in user to be updated
		add_action( 'woocommerce_checkout_process', 'shop_as_client_woocommerce_checkout_process' );
		function shop_as_client_woocommerce_checkout_process() {
			if ( shop_as_client_can_checkout() ) {
				if ( isset( $_POST['billing_shop_as_client'] ) && 'yes' == $_POST['billing_shop_as_client'] ) {
					if ( ! apply_filters( 'shop_as_client_update_customer_data', false ) ) {
						add_filter( 'woocommerce_checkout_update_customer_data' , '__return_false' );
					}
				}
			}
		}
	
		//Save logged in user id
		add_action( 'woocommerce_checkout_update_order_meta', 'shop_as_client_woocommerce_checkout_update_order_meta', 10, 2 );
		function shop_as_client_woocommerce_checkout_update_order_meta( $order_id, $data ) {
			if ( shop_as_client_can_checkout() ) {
				if ( isset( $_POST['billing_shop_as_client'] ) && 'yes' == $_POST['billing_shop_as_client'] ) {
					$order = wc_get_order( $order_id );
					$order->update_meta_data( '_billing_shop_as_client_handler_user_id', get_current_user_id() );
					$order->save();
				}
			}
		}
	
		//Information on the order edit screen
		add_action( 'woocommerce_admin_order_data_after_order_details', 'shop_as_client_woocommerce_admin_order_data_after_order_details' );
		function shop_as_client_woocommerce_admin_order_data_after_order_details( $order ) {
			if ( shop_as_client_get_order_status( $order ) ) {
				$user_id = $order->get_meta( '_billing_shop_as_client_handler_user_id' );
				$user    = get_user_by( 'ID', $user_id );
				?>
				<p class="form-field form-field-wide">
					<label><?php _e( 'Shop as client', 'shop-as-client' ) ?>:</label>
					<?php _e( 'Yes', 'shop-as-client' ) ?>
	
				</p>
				<?php if ( $user ) { ?>
					<p class="form-field form-field-wide">
						<label><?php _e( 'Order handled by', 'shop-as-client' ) ?>:</label>
						<?php printf(
							'<a href="%s" target="_blank">%s</a>',
							esc_url( add_query_arg( 'user_id', $user_id, admin_url( 'user-edit.php' ) ) ),
							sprintf(
								'%s (%s)',
								$user->display_name,
								$user->nickname
							)
						); ?>
					</p>
				<?php }
			}
		}
	
		/* InvoiceXpress nag */
		add_action( 'admin_init', function() {
			if (
				( ! defined( 'WEBDADOS_INVOICEXPRESS_NAG' ) )
				&&
				( ! class_exists( '\Webdados\InvoiceXpressWooCommerce\Plugin' ) )
				&&
				empty( get_transient( 'webdados_invoicexpress_nag' ) )
				&&
				WC()->countries->get_base_country() == 'PT'
				&&
				apply_filters( 'shop_as_client_webdados_invoicexpress_nag', true )
			) {
				define( 'WEBDADOS_INVOICEXPRESS_NAG', true );
				require_once( 'webdados_invoicexpress_nag/webdados_invoicexpress_nag.php' );
			}
		} );
	
		/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
		
	}
}, 10 );

