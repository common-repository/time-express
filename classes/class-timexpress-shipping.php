<?php
defined('ABSPATH')||die('No Script Kiddies Please');

class TIME_EXPRESS_SHIPPING_METHOD extends WC_Shipping_Method{

    /**
     * Constructor for Shipping Class
     * 
     * @access public
     * @return void
     */
    public function __construct($instance_id=0){
        $this->id                  ='time_express_shipping';
        $this->instance_id         = absint($instance_id);
        $this->has_settings        =true;
        $this->method_title        =__('Time Express Parcels');
        $this->method_description  =__('Opt Time Express as your delivery partner');
        $this->supports             = array(
            'shipping-zones',
            // 'instance-settings',
            // 'instance-settings-modal',
        );

        $this->init();

        $this->enabled  = isset($this->settings['enabled'])?$this->settings['enabled']:'no';
        $this->title    =__('Time Express Parcels');
        
    }

    /**
     * Init Time Express Settings
     */

     function init(){
         //Load the settings API
         $this->init_form_fields();
         $this->init_settings();

         // Save settings in admin if you have any defined
         add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
     }
     function has_settings(){
         return $this->has_settings;
     }

     /**
      * Define settings field for this shipping
      * @return void
      */
      function init_form_fields(){
          //Time Express Settings
          $this->form_fields = array(
 
            'enabled' => array(
                 'title' => __( 'Enable', 'timexpress' ),
                 'type' => 'checkbox',
                 'description' => __( 'Enable this shipping.', 'tutsplus' ),
                 'default' => 'no'
            ),
            'auto_processing' => array(
                 'title'=>__('Automatic Process','timexpress'),
                 'type'=> 'checkbox',
                 'description' => __('This will automatically process all orders opted for Time Express Shipping by customer to Time Express'),
                 'default'=>'no'
            )
        );

      }

      /**
       * Function to calculate the shipping cost
       * @param mixed $package
       * @return void
       */
      public function calculate_shipping($package=array()){
          //Add the cost, rate and logics in here
          if((new TIME_EXPRESS_SHIPPING_METHOD())->settings['enabled']=="no"){
            return;
          }
          
          $weight = 0;
          $cost = 10;
          $country = $package["destination"]["country"];
          $state= $package['destination']['state'];
          $city=$package['destination']['city'];
          //print_r($package['destination']);
          $length=0;
          $breadth=0;
          $height=0;
       
          foreach ( $package['contents'] as $item_id => $values ) 
          { 
              $_product = $values['data']; 
              $weight = $weight + ((float)$_product->get_weight() )* $values['quantity']; 
              $height+=((float)$_product->get_height())*$values['quantity'];
              $length=max($length,(float)$_product->get_length());
              $breadth=max($breadth,(float)$_product->get_width());
          }
       
          $weight = wc_get_weight( $weight, 'kg' );
          $height= wc_get_dimension($height,'cm');
          $length = wc_get_dimension($length,'cm');
          $breadth= wc_get_dimension($breadth,'cm');
          
          if($weight==0){
              $weight=3;
          }
          $store_raw_country = get_option( 'woocommerce_default_country' );

          // Split the country/state
          $split_country = explode( ":", $store_raw_country );   
          // Country and state separated:
          $store_country = $split_country[0];
         // $store_state   = $split_country[1];

          /**Data For API */
           list($length,$breadth,$height)=TIMEX_get_default_dimensions_for_API($length,$breadth,$height,$weight);
           
           $dimensional_weight=($length*$breadth*$height)/5000;
           $weight=max($weight,$dimensional_weight);

           list($store_country,$country)=TIMEX_get_origin_destination_for_API($country,$state,$city,$store_country);

         // print_r(array($length,$breadth,$height,$weight,$store_country,$country,$state));
          $cost=TIMEXPRESS_API::getRate($length,$breadth,$height,$weight,$store_country,$country);
          $cost=TIMEX_currency_conversion($cost, get_woocommerce_currency());
          if($cost){
            $rate = array(
                'id' => $this->id,
                'label' => $this->title,
                'cost' => $cost
            );
         
            $this->add_rate( $rate );
          }
          
      }

} 


?>