<?php
defined('ABSPATH')||die('No Script Kiddies Please');

$args=array(
    'limit'=>-1,
    'meta_query'=>array(
        array(
            'key'=>'awb_tracking_no',
            'compare'=>'EXISTS'
        )
    )
);
$orders=wc_get_orders($args);
$csv_hdr = "Sl No, Order Id, Name, AWB";
 $csv_output="";

?>
<h3>New Orders</h3>
<table class="widefat fixed" cellspacing="0" style="margin-top:10px;">
    <thead>
    <tr>

            <th id="order-count" style="text-align:left;" class="manage-column column-columnname num" scope="col">Sl.no</th>
            <th id="order-title" style="text-align:left;"style="text-align:left;" class="manage-column column-columnname" scope="col">Order</th>
            <th id="order-date" style="text-align:left;" class="manage-column column-columnname num" scope="col">Date</th>
            <th id="order-amount" style="text-align:left;" class="manage-column column-columnname num" scope="col">Amount</th>
            <th id="order-action" style="text-align:left;" class="manage-column column-columnname num" scope="col">Action</th>

    </tr>
    </thead>

    <tfoot>
    <tr>

            <th id="order-count-f" style="text-align:left;" class="manage-column column-columnname num" scope="col">Sl.no</th>
            <th id="order-title-f" style="text-align:left;" class="manage-column column-columnname" scope="col">Order</th>
            <th id="order-date-f" style="text-align:left;" class="manage-column column-columnname num" scope="col">Date</th>
            <th id="order-amount-f" style="text-align:left;" class="manage-column column-columnname num" scope="col">Amount</th>
            <th id="order-action-f" style="text-align:left;" class="manage-column column-columnname num" scope="col">Action</th>

    </tr>
    </tfoot>

    <tbody>
    <?php
        $count=0;
        
        if(!empty($orders)){
        foreach($orders as $order){
            //print_r(TIMEX_getDataArrayToProcess($order->get_id()));
            if($order->get_shipping_method()=="Time Express Parcels"){
                    $awb_tracking_no=get_post_meta($order->get_id(),'awb_tracking_no',true);
                    $count++;
                    
                    
                ?>
                <tr class="alternate">
                    <td class="column-columname"><?php echo $count;?></td>
                    <td class="column-columnname">#<?php echo $order->get_id()." ".$order->get_formatted_shipping_full_name();?></td>
                    <td class="column-columnname"><?php echo human_time_diff($order->get_date_modified()->getTimestamp(),time()).' ago';?></td>
                    <td class="column-columnname"><?php echo $order->get_formatted_order_total();?></td>
                    <?php if(!$awb_tracking_no):?>
                    <td class="column-columnname"><button class="process-tes-order" data-id="<?php echo $order->get_id();?>" >Process To TimeExpress</button></td>
                    <?php else: ?>
                    <td class="column-columname">T.N: <?php echo $awb_tracking_no; ?>(<a target='_blank' href="https://www.timexpress.ae/track/<?php echo $awb_tracking_no;?>">Track Here</a>)</td>
                    <?php endif; ?>
                </tr>
                <?php
                
                
                
                $csv_output .= $count . ", ";
				$csv_output .= "#".$order->get_id() . ", ";
				$csv_output .= $order->get_formatted_shipping_full_name() . ", ";
				$csv_output .= $awb_tracking_no . "\n ";
            };
        }
}

if($count==0){
?>
    <tr class="alternate"><td colspan="5" style="text-align:center">No orders with Time Express.</td></tr>
<?php }
        ?>
        
       
    </tbody>
</table>

<?php 
 if(!empty($orders)){
     if($count>0){
?>
  <!--For export button -->  
										  	<form name="export" action="https://www.timexpress.ae/export.php" method="post" style="margin-top:2%">
    <input type="submit" value="Export table to CSV" style="background:#7f3f98;float:right; margin-bottom: 4%;color:#fff;">
    <input type="hidden" value="<? echo $csv_hdr; ?>" name="csv_hdr">
    <input type="hidden" value="<? echo $csv_output; ?>" name="csv_output">
</form>

<?php } } ?>