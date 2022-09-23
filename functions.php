<?php
/**
 * oms Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package oms
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_OMS_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'oms-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_OMS_VERSION, 'all' );

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

add_action('nsl_before_register', function ($provider) {
    /** @var $provider NextendSocialProvider */

    /**
     * You must return true if you want to add custom fields
     */
    add_filter('nsl_registration_require_extra_input', function ($askExtraData) {
        return true;
    });

    add_filter('nsl_registration_validate_extra_input', function ($userData, $errors) {
        /** @var $errors WP_Error */


        $isPost = isset($_POST['submit']);

        if ($isPost) {
            /**
             * You must add an error if your fields are not filled or does not fulfill your validation.
             * If no errors added, that means that the register form is fine.
             */

            if (!empty($_POST['billing_phone']) && is_string($_POST['billing_phone'])) {
                $userData['billing_phone'] = $_POST['billing_phone'];
            } else {
                $userData['billing_phone'] = '';
                $errors->add('billing_phone_missing', '<strong>' . __('ERROR') . '</strong>: billing phone can not be empty.', array('form-field' => 'billing_phone'));
            }
        } else {
            /**
             * Fill up user data with default values to prevent the notice in the form
             */
            $userData['billing_phone'] = '';
        }

        return $userData;
    }, 10, 2);

    /** You can use nsl_registration_form_start and nsl_registration_form_end action.  */
    add_action('nsl_registration_form_start', function ($userData) {
        ?>
        <p>
            <label for="billing_phone">請輸入預訂時所留下的聯絡電話，以作為回饋時的索引，勿輸入符號<br/>
                <input type="text" name="billing_phone" id="billing_phone" class="input"
                       value="<?php echo esc_attr(wp_unslash($userData['billing_phone'])); ?>" size="10"/></label>
        </p>
        <?php
    });

    /**
     * $user_id contains the created user's id
     * $userData contains the previously validated input
     */
    add_action('nsl_registration_store_extra_input', function ($user_id, $userData) {
        add_user_meta($user_id, 'billing_phone', $userData['billing_phone']);
    }, 10, 2);
});
/**
* test custom checkout
*
*/
// ADDING 2 NEW COLUMNS WITH THEIR TITLES (keeping "Total" and "Actions" columns at the end)
add_filter( 'manage_edit-shop_order_columns', 'custom_shop_order_column', 20 );
function custom_shop_order_column($columns)
{
    $reordered_columns = array();
	$column01 = '客戶代稱';
	$column02 = '備註';
	$column03 = '司機聯繫';
	$column04 = '出發時間';
	$column05 = '司料已給';
	$column06 = '平台';
	$column07 = '已付訂金';
	$column08 = '是否開發票';
	$column09 = '司機姓名';
	$column10 = '回款收入';
	$column11 = '匯款支出';
	$column12 = '訂金';
	$column13 = '尾款';
	$column14 = '電話';
	$column15 = '人數';
	$column16 = '車款';
	$column17 = '上車位置';
	$column18 = '下車位置';
	$column19 = '行程內容';

    // Inserting columns to a specific location
    foreach( $columns as $key => $column){
        $reordered_columns[$key] = $column;
        if( $key ==  'order_status' ){
            // Inserting after "Status" column
            $reordered_columns['my-column01'] = __( $column01,'theme_domain');
            $reordered_columns['my-column02'] = __( $column02,'theme_domain');
			$reordered_columns['my-column03'] = __( $column03,'theme_domain');
			$reordered_columns['my-column04'] = __( $column04,'theme_domain');
			$reordered_columns['my-column14'] = __( $column14,'theme_domain');
			$reordered_columns['my-column06'] = __( $column06,'theme_domain');
			$reordered_columns['my-column15'] = __( $column15,'theme_domain');
			$reordered_columns['my-column16'] = __( $column16,'theme_domain');
			$reordered_columns['my-column17'] = __( $column17,'theme_domain');
			$reordered_columns['my-column18'] = __( $column18,'theme_domain');
			$reordered_columns['my-column19'] = __( $column19,'theme_domain');
			$reordered_columns['my-column12'] = __( $column12,'theme_domain');
			$reordered_columns['my-column13'] = __( $column13,'theme_domain');
			$reordered_columns['my-column07'] = __( $column07,'theme_domain');
			$reordered_columns['my-column08'] = __( $column08,'theme_domain');
			$reordered_columns['my-column05'] = __( $column05,'theme_domain');
			$reordered_columns['my-column09'] = __( $column09,'theme_domain');
			$reordered_columns['my-column10'] = __( $column10,'theme_domain');
			$reordered_columns['my-column11'] = __( $column11,'theme_domain');
        }
    }
    return $reordered_columns;
}

// Adding custom fields meta data for each new column (example)
add_action( 'manage_shop_order_posts_custom_column' , 'custom_orders_list_column_content', 20, 2 );
function custom_orders_list_column_content( $column, $post_id )
{
    switch ( $column )
    {
        case 'my-column01' :
            // Get custom post meta data
            $my_var_one = get_post_meta( $post_id, 'remark_nickname', true );
            if(!empty($my_var_one))
                echo $my_var_one;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;

        case 'my-column02' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_content', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
			
		case 'my-column03' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_notice_driver', true );
            if(!empty($my_var_two))
				switch ($my_var_two) {
					case "no":
						echo "<mark class='order-status status-failed'><span>待通知</span></mark>";
						break;
					case "okay":
						echo "OKAY";
						break;
				}
            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
			
		 case 'my-column04' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_departure', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column05' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_driver_data', true );
            if(!empty($my_var_two))
                switch ($my_var_two) {
					case "no":
						echo "<mark class='order-status status-failed'><span>沒給</span></mark>";
						break;
					case "yes":
						echo "給了";
						break;
				}

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column06' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_platform', true );
            if(!empty($my_var_two))
                switch ($my_var_two) {
					case "taiwantourcar":
						echo "夢玩家";
						break;
					case "jobin":
						echo "九賓";
						break;
					case "ctplayer":
						echo "海山林";
						break;
					case "skytour":
						echo "天地玩家";
						break;
					case "skybus":
						echo "天地巴士";
						break;
				}

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column07' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_deposit_paid', true );
            if(!empty($my_var_two))
                switch ($my_var_two) {
					case "no":
						echo "<mark class='order-status status-failed'><span>否</span></mark>";
						break;
					case "yes":
						echo "是";
						break;
				}

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column08' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_invoice', true );
            if(!empty($my_var_two))
                switch ($my_var_two) {
					case "no":
						echo "否";
						break;
					case "yes_by_l":
						echo "<mark class='order-status status-cancelled'><span>小禎開</span></mark>";
						break;
					case "yes_by_driver":
						echo "<mark class='order-status status-on-hold'><span>車頭開</span></mark>";
						break;
				}

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column09' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_driver', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column10' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_driver_income', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column11' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'remark_driver_transfer', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column12' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'billing_deposit', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column13' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'billing_final_payment', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column14' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'billing_phone', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column15' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'billing_numberofpeople', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column16' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'billing_car_type', true );
            if(!empty($my_var_two))
                switch ($my_var_two) {
					case "5seat":
						echo "五座";
						break;
					case "7seat":
						echo "七座";
						break;
					case "9seat":
						echo "九座";
						break;
					case "vito":
						echo "VITO";
						break;
					case "volkswagen":
						echo "大T";
						break;
					case "alphard":
						echo "阿法";
						break;
					case "lm300h":
						echo "LM";
						break;
					case "gclass":
						echo "G系列";
						break;
					case "sclass":
						echo "S轎車";
						break;
					case "bus21":
						echo "小可愛";
						break;
					case "bus43":
						echo "大巴";
						break;
				}

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column17' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'billing_pickup', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column18' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'billing_getoff', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
		 case 'my-column19' :
            // Get custom post meta data
            $my_var_two = get_post_meta( $post_id, 'billing_content', true );
            if(!empty($my_var_two))
                echo $my_var_two;

            // Testing (to be removed) - Empty value case
            else
                echo '';

            break;
    }
}

/*取消購物車AJAX*/

function disable_checkout_script(){
    wp_dequeue_script( 'wc-checkout' );
}
add_action( 'wp_enqueue_scripts', 'disable_checkout_script' );

// redirects to checkout if the product added to the cart
add_filter( 'woocommerce_add_to_cart_redirect', 'redirect_to_checkout_if_product_is_subscription' );
function redirect_to_checkout_if_product_is_subscription( $url ) {
    global $woocommerce;
    $checkout_url = $woocommerce->cart->get_checkout_url();
    return $checkout_url;
    return $url;
}

// Change the 'Billing details' checkout label to '訂單資訊'
function wc_billing_field_strings( $translated_text, $text, $domain ) {
switch ( $translated_text ) {
case 'Billing Details' :
$translated_text = __( '訂單資訊', 'woocommerce' );
break;
}
return $translated_text;
}
add_filter( 'gettext', 'wc_billing_field_strings', 20, 3 );

// 刪除客戶取消按鈕
add_filter('woocommerce_my_account_my_orders_actions', 'remove_myaccount_orders_cancel_button', 10, 2);
function remove_myaccount_orders_cancel_button( $actions, $order ){
    unset($actions['cancel']);
	unset($actions['pay']);

    return $actions;
}

// 限制下單資格
if ( ! function_exists( 'wpf_is_current_user_role' ) ) {
    function wpf_is_current_user_role( $roles_to_check ) {
        $current_user       = wp_get_current_user();
        $current_user_roles = ( empty( $current_user->roles ) ? array( '' ) : $current_user->roles );
        $roles_intersect    = array_intersect( $current_user_roles, $roles_to_check );
        return ( ! empty( $roles_intersect ) );
    }
}

if ( ! function_exists( 'wpf_do_hide_product' ) ) {

    function wpf_do_hide_product( $product_id_to_check ) {

        $products_to_hide  = array( 22,); 
        $roles_to_hide_for = array( 'administrator','shop_manager' ); 

        return (
            in_array( $product_id_to_check, $products_to_hide ) && 
            !wpf_is_current_user_role( $roles_to_hide_for )         
        );
    }
}
add_filter( 'woocommerce_is_purchasable', 'wpf_product_purchasable_by_user_role', PHP_INT_MAX, 2 );
if ( ! function_exists( 'wpf_product_purchasable_by_user_role' ) ) {
    function wpf_product_purchasable_by_user_role( $purchasable, $product ) {
        return ( wpf_do_hide_product( $product->get_id() ) ? false : $purchasable );
    }
}
// 限制下單資格-end

// 預設shop_as_client建立新帳戶
add_filter( 'shop_as_client_default_create_user', function( $option ) {
	return 'yes';
});