<?php
/**
 * @package timexpress-delivery
 * @version 1.0.9
 */
/*
Plugin Name: Time Express Parcels For WooCommerce
Description: Plugin Enables Option To Add Time Express as shipping provider
Author: Time Express
Version: 1.0.9
* Requires at least: 5.4
* Requires PHP: 7.0

*/

class TIME_EXPRESS_SHIPPING{
    
    public function __construct(){
        require_once 'utils/functions.php';
        require_once 'classes/class-timexpress-api.php';
        register_activation_hook(__FILE__,array($this, 'activate_time_express_shipping_plugin'));
        $this->init_time_express();
        add_action('admin_footer',array($this,'timexpress_admin_style_script_enqueue'));
        add_action('admin_menu',array($this,'timexpress_menu_item'));
        add_action('wp_ajax_process_order_action',array($this,'process_order_action'));
        add_action('woocommerce_thankyou',array($this,'init_awb_tracking_on_create_order'),10,1);
        
    }

    function init_awb_tracking_on_create_order($order_id){
        $order=wc_get_order($order_id);
        $shipping_method=$order->get_shipping_method();
        if($shipping_method=="Time Express Parcels"){
            update_post_meta($order->get_id(),'awb_tracking_no',false);
            if((new TIME_EXPRESS_SHIPPING_METHOD())->settings['auto_processing']=="yes"){
                TIMEXPRESS_API::processOrderToTimeExpress($order_id);
            }
            
            
        }
    }

    public static function get_client_details(){
        if(!TIMEXPRESS_API::getAccountNo())
            return array();
        $client=get_option('tes_user');
        return $client;
        
    }

    function process_order_action(){
        $order_id=intval($_POST['order_id']);
        //$awb_tracking_no=TIMEX_getDataArrayToProcess($order_id);
        $response=TIMEXPRESS_API::processOrderToTimeExpress($order_id);
        wp_send_json($response);
    }

    function timexpress_admin_style_script_enqueue(){
        wp_enqueue_style('TES_admin_style',plugin_dir_url(__FILE__).'/admin/assets/tes-admin.css');
        wp_register_script('TES_admin_script',plugin_dir_url(__FILE__).'/admin/assets/tes-admin.js');
        $ajax_var=array(
            'ajaxUrl'=>admin_url('admin-ajax.php'),
            'process_order_action'=>'process_order_action'
        );
        wp_localize_script('TES_admin_script','tes',$ajax_var);
        wp_enqueue_script('TES_admin_script');
    }

    function init_time_express(){
        if(!TIMEXPRESS_API::getAccountNo())
            return; 
        add_filter('woocommerce_shipping_methods',array($this, 'add_time_express_shipping_method'),10,1);
        add_action('woocommerce_shipping_init',array($this,'time_express_shipping_method'));

    }

    function add_time_express_shipping_method($methods){
        $methods['time_express_shipping']="TIME_EXPRESS_SHIPPING_METHOD";
        return $methods;
    }

    function time_express_shipping_method(){
        require_once 'classes/class-timexpress-shipping.php';
    }
    
    function activate_time_express_shipping_plugin(){
        if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
            die('Plugin NOT activated: ' . "It needs WooCommerce to be activated");
        }
    }

    function make_login_logout(){
        include_once 'admin/timexpress-login/login_logout.php';
        return $error;
    }

    function timexpress_settings(){
        //TimeExpress Menu Page
        $error=$this->make_login_logout();

        if(!TIMEXPRESS_API::getAccountNo()){
            $this->init_login_form($error);
        }
        else{
            $this->timexpress_user_settings();
        }
    
    }
    function init_login_form($error){

        include_once('admin/timexpress-login/login-form.php');

    }

    function timexpress_user_settings(){
            include_once('admin/timexpress-settings/timexpress-user-settings.php');
    }

    function timexpress_new_orders(){
        //New Orders For TimeExpress
        include_once 'admin/timexpress-orders.php';

    }

    function timexpress_menu_item(){
        add_menu_page(
            'Time Express Parcels',
            'Time Express Parcels',
            'manage_options',
            'timexpress-delivery',
            array($this,'timexpress_settings'),
            plugin_dir_url(__FILE__).'/admin/assets/imgs/icon.png',
            5
        );

        if(TIMEXPRESS_API::getAccountNo()){
            add_submenu_page(
                'timexpress-delivery',
                'New Orders - Time Express',
                'New Orders',
                'manage_options',
                'new-tc-orders',
                array($this,'timexpress_new_orders'),
                0
    
            );
        }
        
    }

}

global $timexpress;
$timexpress=new TIME_EXPRESS_SHIPPING;
