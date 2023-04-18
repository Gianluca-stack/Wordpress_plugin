<?php

require_once 'NetumoAPI.php'; 


/**
* Plugin Name: Netumo
* Plugin URI: https://www.netumo.com/wp-plugin
* Description: Uptime monitoring for your Domains, Websites, SSL Certificates, APIs and more...
* Version: 1.0
* Author: AIRO Ltd.
* Author URI: https://www.netumo.com
*/



add_action('admin_menu', 'plugin_setup');
add_action('admin_post_add_login', 'netumo_login');
add_action('admin_post_create_monitor', 'netumo_monitor');
add_action('admin_post_delete_monitor', 'netumo_delete');
add_action('admin_post_add_logout', 'netumo_logout');

function plugin_setup()
{
    add_menu_page('Netumo Login Page', 'Netumo', 'manage_options', 'netumo-login', 'loginHTML', plugins_url('Netumo_Plugin/Images/netumo-icon20.svg'));
}

function netumo_login(){

  global $wpdb;

    if ((isset($_POST['username']) && $_POST['username']!="") && (isset($_POST['pwd']) &&
    $_POST['pwd']!="")){

          $username = $_POST['username'];
          $password = $_POST['pwd'];
      

      $login = new NetumoAPI();
      $access_token = $login->TokenLogin($username, $password);

      if($access_token != false){
          
          $result = $wpdb->query("CREATE TABLE IF NOT EXISTS ".$wpdb->prefix."netumo(
            Token varchar(240) PRIMARY KEY,
            Monitor_ID int(11)
        );");
        
      
      
        $wpdb->query("INSERT INTO ".$wpdb->prefix."netumo(Token, Monitor_ID)
        VALUES('$access_token',null)"); 


        wp_redirect(admin_url("admin.php?page=netumo-login"), 301);
        exit();
        
      }else{
      
        
          wp_redirect(admin_url("admin.php?page=netumo-login&errormessage=incorrect"), 301);
          exit();
      }

   }

}

function netumo_monitor(){
  global $wpdb;
  $token_exists = $wpdb->get_var("SELECT Token FROM ".$wpdb->prefix."netumo;");
  $monitor_name = $wpdb->get_var("SELECT option_value FROM ".$wpdb->prefix."options WHERE option_id=3;");
 
  $monitorcreation = new NetumoAPI();
  $createMonitorStatus = $monitorcreation->CreateMonitor($token_exists);

  if($createMonitorStatus == true){
          $mon_status = new NetumoAPI();
          $monitor_identification = $mon_status->MonitorStatusInfo($monitor_name, $token_exists);
        
        for($i = 0; $i<count($monitor_identification); $i++){
            if($monitor_name == $monitor_identification[$i]->Name){
              // return (array)$result;
              //inserting in the database
              $mon_id = $monitor_identification[$i]->Id;
              $wpdb->query("UPDATE ".$wpdb->prefix."netumo
              SET Monitor_ID = $mon_id
              WHERE Token = '$token_exists';");
              break;
             }
          }
              
      
        wp_redirect(admin_url("admin.php?page=netumo-login"), 301);
        exit();
  }else{
      wp_redirect(admin_url("admin.php?page=netumo-login&errormessage=notcreated"), 301);
      exit();
  }

  
}
function netumo_delete(){
      global $wpdb;
      $token_exists = $wpdb->get_var("SELECT Token FROM ".$wpdb->prefix."netumo;");
      $monitor_id = $wpdb->get_var("SELECT Monitor_ID FROM ".$wpdb->prefix."netumo;");

      $monitorcreation = new NetumoAPI();
      $monitorcreation->DeleteMonitor($monitor_id,$token_exists);

      $wpdb->query("UPDATE ".$wpdb->prefix."netumo
                    SET Monitor_ID = null
                    WHERE Token = '$token_exists';");

    
    wp_redirect(admin_url("admin.php?page=netumo-login"), 301);
    exit();
}
function netumo_logout(){
     global $wpdb;
     $token = $wpdb->get_var("SELECT Token FROM ".$wpdb->prefix."netumo");
     $wpdb->query("DELETE FROM ".$wpdb->prefix."netumo WHERE Token='$token'");

     
     wp_redirect(admin_url("admin.php?page=netumo-login"), 301);
     exit();
}

global $wpdb;
$token = $wpdb->get_var("SELECT Token FROM ".$wpdb->prefix."netumo");
$id = $wpdb->get_var("SELECT Monitor_ID FROM ".$wpdb->prefix."netumo");



if($token){
   $tokeninfo = new NetumoAPI();
   $token = $tokeninfo->TestAccessToken($token);
}
if($token){
  function loginHTML(){
    global $wpdb;
   
    
    $monitor_name = $wpdb->get_var("SELECT option_value FROM ".$wpdb->prefix."options WHERE option_id=3;");
    $url = $wpdb->get_var("SELECT option_value FROM ".$wpdb->prefix."options WHERE option_id=1;");
    // $status = $wpdb->get_var("SELECT Monitor_status FROM nt_netumo");
    $monitor_id = $wpdb->get_var("SELECT Monitor_ID FROM ".$wpdb->prefix."netumo;");
    $tok = $wpdb->get_var("SELECT Token FROM ".$wpdb->prefix."netumo;");
    
  

    if($monitor_id){
        $getmonitorid_status = new NetumoAPI();
        $identifcation = $getmonitorid_status->GetMonitor($monitor_id, $tok);
     
        if($identifcation == true){
          $wpdb->query("UPDATE ".$wpdb->prefix."netumo
                        SET Monitor_ID = null
                        WHERE Token = '$tok';");
                   
        }
    }

     if($monitor_id){

        //   Monitor Status
    
        $monitor_information = new NetumoAPI();
        $info_result = $monitor_information->MonitorStatusInfo($monitor_name, $tok);
        

              for($i = 0; $i<count($info_result); $i++){
                 if($monitor_name == $info_result[$i]->Name){
                    ?>
                    <link rel="stylesheet" href="<?php echo plugins_url('Netumo_Plugin/CSS/monitor_cssv28.css') ?>">
                    <script src="<?php echo plugins_url('Netumo_Plugin/JS/refreshbtn.js') ?>"></script>
                    <script src="https://kit.fontawesome.com/c158fa20c0.js" crossorigin="anonymous"></script>
                      <div class="monitor-info-flex">
                      <div class="logoutmon-bt">
                          <form action="<?php echo admin_url('admin-post.php') ?>">
                            <input type="hidden" name="action" value="add_logout">  
                            <input type="submit" name="btnlogout" value="Log out" class="button button-primary">
                          </form>  
                      </div>  
                       <div class="logo">
                            <img src="<?php echo plugins_url('Netumo_Plugin/Images/netumo-logo.svg') ?>">
                        </div>
                          <div class="monitor-info">
                              <h4>Website Name</h4>
                              <h5><?php echo $info_result[$i]->Name ?></h5>
                              <h4>Last Check</h4>
                              <h5><?php echo $info_result[$i]->LastCheckStr ?></h5>
                              <h4>Last Status</h4>
                              <?php
                                 switch($info_result[$i]->LastStatus){
                                    case 0: 
                                        ?> 
                                         <div class="DOWN-state">
                                            <h5>Failed&nbsp;&nbsp;&nbsp;<i class="fa-solid fa-circle-exclamation fa-color fa-customize"></i></h5>
                                          </div> 
                                        <?php
                                        break;
                                    case 1:
                                      ?> 
                                      
                                      <div class="OK-state">
                                       <h5 >Site is up and OK&nbsp;&nbsp;&nbsp;<i class="fa-sharp fa-solid fa-check fa-color fa-customize"></i></h5>
                                      </div> 
                                      
                                      <?php
                                      break;
                                    case 2:
                                      ?> 
                                      <div class="DOWN-state">
                                         <h5>NoMatch&nbsp;&nbsp;&nbsp;<i class="fa-solid fa-circle-exclamation fa-color fa-customize"></i></h5>
                                       </div> 
                                     <?php
                                      break;
                                    case 3:
                                      ?> 
                                      <div class="DOWN-state">
                                         <h5>No state set&nbsp;&nbsp;&nbsp;<i class="fa-solid fa-circle-exclamation fa-color fa-customize"></i></h5>
                                       </div> 
                                     <?php
                                      break;
                                    case 4:
                                      ?> 
                                      <div class="DOWN-state">
                                         <h5>Timeout&nbsp;&nbsp;&nbsp;<i class="fa-solid fa-circle-exclamation fa-color fa-customize"></i></h5>
                                       </div> 
                                     <?php
                                      break;
                                    case 5:
                                      ?> 
                                      <div class="CHECK-state">
                                         <h5>Checking&nbsp;&nbsp;&nbsp;<i class="fa-regular fa-question fa-color fa-customize"></i></h5>
                                       </div> 
                                     <?php
                                      break;
                                    case 6:
                                      ?> 
                                      <div class="DOWN-state">
                                         <h5>Monitor disabled&nbsp;&nbsp;&nbsp;<i class="fa-solid fa-circle-exclamation fa-color fa-customize"></i></h5>
                                       </div> 
                                     <?php
                                      break;
                                 }
                                
                              ?>     
                               <p>
                               <button class="button button-primary" onclick="refresh()">Refresh</button>
                              </p>  
                              <p class="delete" style="display: none;">
                                  <form action="<?php echo admin_url('admin-post.php') ?>">
                                        <input type="hidden" name="action" value="delete_monitor">
                                        <input class="button button-primary" type="submit" name="btndeletemonitor" value="Delete Monitor">
                                        &nbsp;&nbsp;
                                  </form>            
                              </p>      
                          </div>     
                    </div>
                    <?php
                    break;
                 }
              }
     }else{
      ?>
      <link rel="stylesheet" href="<?php echo plugins_url('Netumo_Plugin/CSS/monitor_cssv28.css') ?>">
      <script src="https://kit.fontawesome.com/c158fa20c0.js" crossorigin="anonymous"></script>
      <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
         <div class="flex-mont-cont">
         <div class="logout-bt">
              <form action="<?php echo admin_url('admin-post.php') ?>">
                <input type="hidden" name="action" value="add_logout">  
                <input type="submit" name="btnlogout" value="Log out" class="button button-primary">
              </form>  
          </div>  
         <div class="netumo-logomonitor">
             <img src="<?php echo plugins_url('Netumo_Plugin/Images/netumo-logo.svg') ?>">
          </div>
          <section class="monitor-container">
            <div class="mont-choices">
             <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST" class="select-fields">
              <input type="hidden" name="action" value="create_monitor">
              <div class="monitor-label">
                <h3>Create Monitor</h3>
              </div>
               <table class="form-table" role="presentation">
                     <tbody>
                         <tr>
                          <th scope="row">
                              <label for="webname">Name</label> 
                          </th>
                          <td>
                              <input name="webname" type="text" id="webname" value="<?php echo $monitor_name ?>" class="regular-text" readonly>
                          </td>
                         </tr>
                         <tr>
                          <th scope="row">
                              <label for="protocol">Protocol</label> 
                          </th>
                          <td>
                              <?php
                                if(isset($_SERVER['HTTPS'])){
                                  ?>
                                        <input name="protocol" type="text" id="protocol" value="HTTPS" class="regular-text" readonly>
                                  <?php
                                }else{
                                  ?>
                                        <input name="protocol" type="text" id="protocol" value="HTTP" class="regular-text" readonly>
                                  <?php
                                }
                            ?>
                          </td>
                         </tr>
                         <tr>
                            <th scope="row">
                              <label for="url">URL</label>
                            </th>
                            <td>
                               <input name="url" type="text" id="url" value="<?php echo $url ?>" class="regular-text" readonly>
                            </td>
                         </tr>
                         <tr>
                            <th scope="row">Message via</th>
                            <td>
                              <fieldset style="letter-spacing: 2px;">
                                <legend class="screen-reader-text">
                                  <span>Message via</span>
                                </legend>
                                <label>
                                  <input type="checkbox" id="EmailEnabled" name="emailenabled">
                                  <span>Email</span>
                                </label>
                                &nbsp;
                                <label>
                                <input type="checkbox" id="TwitterEnabled" name="twitterenabled">
                                  <span>Twitter</span>
                                </label>
                                &nbsp;
                                <label>
                                <input type="checkbox" id="SlackEnabled" name="slackenabled">
                                  <span>Slack</span>
                                </label>
                                &nbsp;
                                <label>
                                <input type="checkbox" id="MicrosoftTeamEnabled" name="microsoftteamsenabled">
                                  <span>MicrosoftTeams</span>
                                </label>
                                &nbsp;
                                <label>
                                <input type="checkbox" id="TelegramEnabled" name="telegramenabled">
                                  <span>Telegram</span>
                                </label>
                              </fieldset>
                            </td>
                         </tr>
                     </tbody>
               </table>
               <?php
                            if(isset($_GET['errormessage']) && $_GET['errormessage'] == 'notcreated'){
                                        ?>
                                           <div class="erm-pos">
                                               <h4 style="color: red;">ERROR: Monitor has not been created</h4>
                                           </div>
                                        <?php
                            }
               ?>
               <p class="submit">
                  <input type="submit" name="submit" id="submit" class="button button-primary" value="Create">
               </p> 
             </form>
            
            </div>
          </section>
         </div>
      <?php
     }
  }
}else{
  function loginHTML(){
       
                  ?>
                  <link rel="stylesheet" href="<?php echo plugins_url('Netumo_Plugin/CSS/netumo_loginv18.css') ?>">
                    <div class="flex-cont">
                       <div class="netumo-logo">
                           <img src="<?php echo plugins_url('Netumo_Plugin/Images/netumo-logo.svg') ?>">
                        </div>
                      <section>
                          <div>             
                            <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST" class="input-field-pos">
                              <input type="hidden" name="action" value="add_login">
                              <table class="form-table" role="presentation">
                                <tbody>
                                  <tr>
                                    <th scope="row">
                                      <label for="username">Username</label>
                                    </th>
                                    <td>
                                    <input name="username" type="text" id="username" placeholder="Username or Email" class="regular-text" require>
                                    </td>
                                  </tr>
                                  <tr>
                                    <th scope="row">
                                      <label for="pwd">Password</label>
                                    </th>
                                    <td>
                                      <input name="pwd" type="password" id="pwd" placeholder="Password" class="regular-text" require>
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                              <p class="buttons">
                                 <input type="submit" name="submit" id="submit" class="button button-primary" value="Log in">
                                 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                 <button class="button button-primary"><a style="color: #fff;text-decoration:none" href="https://www.netumo.app/account/register" id="">Create Account</a></button>
                              </p>
                              
                            </form>
                            
                            <?php
                            if(isset($_GET['errormessage']) && $_GET['errormessage'] == 'incorrect'){
                                        ?>
                                           <div class="erm-pos">
                                               <h4 style="color: red;">ERROR: Username or Password is incorrect</h4>
                                           </div>
                                        <?php
                            }
                            ?>
                           
                          </div>
                        </section>
                     </div> 
                <?php 
            
      }    
    
}