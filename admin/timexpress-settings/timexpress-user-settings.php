<?php
defined('ABSPATH')||die('No Script Kiddies Please');

$client=TIME_EXPRESS_SHIPPING::get_client_details();
//print_r($client);

?>
<style>
.client-info-table table{
    width:70%;
    min-width:400px;
    background:white;
    margin:10px;
}
.client-info-table table tr,.client-info-table table td,.client-info-table table th{
    border:1px solid black;
}
.client-info-table table td,.client-info-table table th{
    width:50%;
}
.actions{
    display:flex;
}

.actions>div{
    margin:0 10px;
}
.client-info-table{
    display: flex;
    flex-direction: column;
    align-items: center;
}
</style>
<div class="timexpress-client-detail">
<h1 style="text-align:center;"><img src="<?php echo plugin_dir_url(__FILE__).'logo.png'; ?>"></h1>
    <div class="client-info-table">
        <table>
            <tr>
                <th>Name</th>
                <td><?php echo  $client['name']; ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo  $client['address']; ?></td>
            </tr>
            <tr>
                <th>Account No</th>
                <td><?php echo  $client['account_no']; ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo  $client['phone']; ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo  $client['email']; ?></td>
            </tr>
        </table>
        <div class="actions">
        <div class="settings_link">
            <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping&section=time_express_shipping');?>">Go To Settings</a>
        </div>
		
        <div class="log_out" style="display:none">
            <form  action="" method="post">
            <input type="hidden" name="action" value="logout"/>
            <button type="submit">LogOut.</button>
            </form>
        </div>
        </div>
        
        </div>

    </div>
</div>