<?php
defined('ABSPATH')||die('No Script Kiddies Please');
$full_path = WP_PLUGIN_URL . '/'. str_replace( basename( __FILE__ ), "", plugin_basename(__FILE__) );
?>
<div class="timexpress_login_container">
    <h1><img src="<?php echo plugin_dir_url(__FILE__).'logo.png'; ?>"></h1>
    <h1>Login</h1>
    <form action="" method="post">
        <div class="form-group">
            <label for="tec_username">Username</label>
            <input type="text" name="tes_username" placeholder="Username" class="large-text" />
        </div>
        <div class="form-group">
            <label for="tec_password">Password</label>
            <input type="password" name="tes_password" placeholder="password" class="large-text"/>
        </div>
        <div class="form-group">
        <?php
                if(!empty($error)){
                    echo "<p style='color:red;'>{$error}</p>";
                }
            ?>
        </div>
        <div class="form-group">
            <input type="submit" name="tes_login" value="Log In">
        </div>
    </form>
</div>
