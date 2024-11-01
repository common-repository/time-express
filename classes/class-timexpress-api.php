<?php
defined('ABSPATH')||die('No Script Kiddies Please!');

class TIMEXPRESS_API{
    public static $auto_processing='no';
    const ENQ_RATE_URL="http://timeexpress.dnsalias.com:880/Mobile/TimeServices.svc/EnqRateAgainstAgent";
    const CREATE_AWB_URL="http://timeexpress.dnsalias.com:880/Mobile/TimeServices.svc/AWBCreation";
    const TRANSMISSION_DAYS_API="http://timeexpress.dnsalias.com:880/Mobile/TimeServices.svc/Dashboardavgdlvytime_Daterange";
    const USER_LOGIN_URL="http://timeexpress.dnsalias.com:880/Special/TimeServices_Special.svc/Userauthentication";


     const CUSTOM_API = "https://timexpress.ae/wooapi.php";
    

    public static function base64CleanEncrypt($data){
        return str_replace(['+','/','='], ['-','_',''], base64_encode(convert_uuencode($data)));
    }

  /**Return Account No If Logged In 
  sample credentials: TEST,TEST
  */

    public static function login($username,$password){
        $login_data=array(
            'UserName'=>$username,
            'PassWord'=>$password
        );

       
        $login_data = self::base64CleanEncrypt(json_encode($login_data));
        $requestURL = self::CUSTOM_API."?mode=USER_LOGIN_URL&req=$login_data" ;

		$response=TIMEX_makeApiRequest($requestURL,$login_data);
        if($response['error']){
			return $response['msg'];
		}
		

        
        $newresponse=$response['response'];
       
        
        if(gettype($response['response'])== "string"){
           
             $response_json = json_decode($newresponse,true);
        }
      
          
          if($response_json['code']==1){
             $client= $response_json['ClientList'][0];
               
                $tes_user=array(
                'user'=>$client['CPerson'],
                'name'=>$client['CustName'],
                'address'=>$client['CustAddress'],
                'account_no'=>$client['CustCode'],
                'email'=>$client['CustEmail'],
                'phone'=>$client['CustPhone'],
            );
            
           
          
           update_option('tes_user',$tes_user);
            update_option('timexpress_account_no',$tes_user['account_no']);
            return false;
        }
        else{
           
            return "Credentials doesn't match";
        }
       
      
 
        
    }
    public static function logout(){
        update_option('tes_user','');
        update_option('timexpress_account_no','');
    }


    /**Return Account No If Logged In */
    public static function getAccountNo(){
        $account_no=get_option('timexpress_account_no');
        if($account_no){
            return $account_no;
        }
        return false;
    }
    
    public static function getRate($length,$breadth,$height,$weight,$origin,$destination){
        if(!SELF::getAccountNo())
            return false;

        /**TimeExpress Rate Enquiry */
        $shipment_data=array(
            "Breadth"=>$breadth,
            "Length"=>$length,
            "accounNo"=>SELF::getAccountNo(),
            "agent"=>"TEC",
            "destination"=>$destination,
            "height"=>$height,
            "origin"=>$origin,
            "pcs"=>"1",
            "productType"=>"XPS",
            "serviceType"=>"NOR",
            "weight"=>$weight
        );
      // print_r($shipment_data);
      
      //  $response=TIMEX_makeApiRequest(SELF::ENQ_RATE_URL,$shipment_data);
		
		 $shipment_data = self::base64CleanEncrypt(json_encode($shipment_data));
        $requestURL = self::CUSTOM_API."?mode=ENQ_RATE_URL&req=$shipment_data" ;

		$response=TIMEX_makeApiRequest($requestURL,$shipment_data);
	
        if($response['error']){
            echo $response['msg'];
            return false;
        }
        //print_r($response_json);
        $rates_json=json_decode($response['response']);
        if($rates_json->code==1){
            return $rates_json->Rate;
        }
        return false;
        
    }
    /**Process Order Info To Time Express */
    public static function processOrderToTimeExpress($order_id){
        $awbTrackingNo=get_post_meta($order_id,'awb_tracking_no',true);
        if($awbTrackingNo){
            $status=TIMEX_send_mail_on_completion($order_id,$awbTrackingNo);
            return array('tracking_no'=>$awbTrackingNo,'mail_status'=>$status);
        }
        $shipment_data=TIMEX_getDataArrayToProcess($order_id);
        $shipment_data['accounNo']=SELF::getAccountNo();
        //return $shipment_data;
        //print_r($shipment_data);
        
        //laiji 
        $shipment_data = self::base64CleanEncrypt(json_encode($shipment_data));
    $requestURL = self::CUSTOM_API."?mode=CREATE_AWB_URL&req=$shipment_data" ;

		$response=TIMEX_makeApiRequest($requestURL,$shipment_data);
        
        
        //$response=TIMEX_makeApiRequest(SELF::CREATE_AWB_URL,$shipment_data);
        if($response['error']){
            echo $response['msg'];
            return false;
        }
        $awbResponse=json_decode($response['response']);
        if($awbResponse->code==1){
            $awbTrackingNo=$awbResponse->awbNo;
            update_post_meta($order_id,'awb_tracking_no',$awbTrackingNo);
           $status=TIMEX_send_mail_on_completion($order_id,$awbTrackingNo);
            return array('tracking_no'=>$awbTrackingNo,'mail_status'=>$status);
        }
        return false;
            
    }

}

?>