<?php
/*
Plugin Name: WP Author Logo
Plugin URI: http://www.mjjdesignworks.com/
Description: Allow Authors to upload Logo from front end profile edit page. This plugin is ideal for Wordpress sites that utilize multi-agents (real estate, job boards etc). These agents can upload a company logo (or any other image) that can be displayed on their WP profile page.
Version: 0.3.5
Author: Martin James Jarvis
Author URI: http://www.mjjdesignworks.com/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/
require(ABSPATH . WPINC . '/pluggable.php');

define('WP-AUTHOR-LOGO_VERSION', '0.3.5');
define('WP-AUTHOR-LOGO_PLUGIN_URL', plugin_dir_url( __FILE__ ));

function wpal_css() {
  echo ( '<link rel="stylesheet" type="text/css" href="'.WP_PLUGIN_URL . '/wp-author-logo-front-end/wpal_logo.css" />' ); 
}
add_action('wp_head', 'wpal_css');


register_activation_hook(__FILE__, 'wpal_createfolder');

function wpal_createfolder() {
    $target = ABSPATH . 'wp-content/uploads/wpal_logos';
    wp_mkdir_p( $target );
}

// Directory for uploaded images 
$uploaddir = ABSPATH . 'wp-content/uploads/wpal_logos';  

// Allowed mimes    
$allowed_ext = get_option(wpal_mime);  

// Default is 50kb 
$max_size = get_option(wpal_size);  

// height in pixels, default is 175px 
$max_height = get_option(wpal_height);  

// width in pixels, default is 450px 
$max_width = get_option(wpal_width);  


// Check mime types are allowed  
$extension = pathinfo($_FILES['wpaluploader']['name']);  
$extension = $extension[extension];  
$allowed_paths = explode(", ", $allowed_ext);  
for($i = 0; $i < count($allowed_paths); $i++) {  
    if ($allowed_paths[$i] == "$extension") {  
        $ok = "1";  
    }  
}  

// Check File Size  
if ($ok == "1") {  
    if($_FILES['wpaluploader']['size'] > $max_size)  
    {  
        echo "Image size (kbs) is too big!, hit go back and try again";  
        exit;  
    }  

    // Check Height & Width  
    if ($max_width && $max_height) {  
        list($width, $height, $type, $w) = getimagesize($_FILES['wpaluploader']['tmp_name']);  
        if($width > $max_width || $height > $max_height)  
        {  
            echo "Image is too big! max allowable width is&nbsp;" . get_option(wpal_width) ."px and max allowable height is&nbsp;" . get_option(wpal_width) ."px, hit go back and try again";  
            exit;  
        }  
    }  
    global $user_id;
    get_currentuserinfo();
    $image_name=$current_user->id.'.'.$extension;
    
    //the new name will be containing the full path where will be stored (images folder)

    // Rename file and move to folder
    $newname="$uploaddir./".$image_name;  
    if(is_uploaded_file($_FILES['wpaluploader']['tmp_name']))  
    { 
        move_uploaded_file($_FILES['wpaluploader']['tmp_name'], $newname);  
    }   
}

   // Create Shortcode to show logo on author pages.
add_shortcode("wpal-authorpage-image", "wpaluploader_authorimage");
 
function wpaluploader_authorimage() {
    $wpaluploader_authorlogo = wpaluploader_showauthorimage();
    return $wpaluploader_authorlogo;
}

function wpaluploader_showauthorimage() { 
    global $author, $profileuser;
    if(isset($_GET['author_name'])) {
    $curauth = get_userdatabylogin(get_the_author_login());
    } else {
    $curauth = get_userdata(intval($author));
    }
// give the author the option to delete image from his own profile page    
   $wpalfileauth = ABSPATH. '/wp-content/uploads/wpal_logos/'.$curauth->ID .'.'.get_option(wpal_mime) .'';
 
 if (file_exists($wpalfileauth)) {
     
    $wpaluploader_authorlogo = '<img src="' . get_bloginfo('url'). '/wp-content/uploads/wpal_logos/'.$curauth->ID .'.'.get_option(wpal_mime) .'" alt="Spanish Property Agents" />';
 
  global $current_user;
    get_currentuserinfo();  
    
 if ($current_user->ID == $curauth->ID) {

    if(isset($_POST['wpaldelete'])){
    $img=$_POST['wpaldelete'];
    unlink(ABSPATH. '/wp-content/uploads/wpal_logos/'.$curauth->ID.'.'.get_option(wpal_mime) .'');
    } 
    
    echo '<form method="post" action="' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) .'" enctype="multipart/form-data">
    <input type="checkbox" name="wpaldelete" id="wpaldelete" />
    <input name="wpaldelete" type="submit" id="wpaldelete" class="submit button" value="Remove Image" />
    </form>
    ';    
    }
// Now give admin option to delete the image from the authors profile page       
 if ( current_user_can('manage_options') ) {
 
   $curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));    
     
 if(isset($_POST['wpaldeleteadmin'])){
    $wpalimg=$_POST['wpaldeleteadmin'];
    unlink(ABSPATH. '/wp-content/uploads/wpal_logos/'.$curauth->ID.'.'.get_option(wpal_mime) .'');
    }     
        
    echo '<form method="post" action="' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) .'" enctype="multipart/form-data">
    <input type="checkbox" name="wpaldeleteadmin" id="wpaldeleteadmin" />
    <input name="wpaldeleteadmin" type="submit" id="wpaldelete" class="submit button" value="Remove Image" />
    </form>
    ';    
    }         
    return $wpaluploader_authorlogo;
  } else {
     return '';
    } 
  }        

 // Create Shortcode to show logo on pages with loops.
 // the singles posts pages have no delete option for the user logo....
add_shortcode("wpal-single-image", "wpaluploader_image");

function wpaluploader_image() {
    $wpaluploader_logo = wpaluploader_showimage();
    return $wpaluploader_logo;
}

function wpaluploader_showimage() {
 
    global $current_user, $wp_roles;
    get_currentuserinfo();
    
    if(isset($_POST['wpaldelete'])){
    $img=$_POST['wpaldelete'];
    unlink(ABSPATH. '/wp-content/uploads/wpal_logos/'.get_the_author_meta('ID').'.'.get_option(wpal_mime) .'');
    }
    
    $wpalfile = ABSPATH. '/wp-content/uploads/wpal_logos/'.$current_user->ID.'.'.get_option(wpal_mime) .'';
  
 if (file_exists($wpalfile)) {
    $wpaluploader_logo = '<img src="' . get_bloginfo('url'). '/wp-content/uploads/wpal_logos/'.$current_user->ID.'.'.get_option(wpal_mime) .'" />';
    
    return $wpaluploader_logo; 
 } else {
     return '';
 } 
  }

 // Create Shortcode for full form
add_shortcode("wpal-author-logo", "wpaluploader_input");

function wpaluploader_input() {
    $wpaluploader_output = wpaluploader_showform();
    return $wpaluploader_output;
}

function wpaluploader_showform() { 
    $wpaluploader_output = '<div class="wpal_logo_upload"><h2>' . get_option(wpal_title) . '</h2><form method="post" id="adduser" action="' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) .'" enctype="multipart/form-data">
    <p><label for="wpauploader">' . get_option(wpal_title) . ':</label>&nbsp;
    <input type="file" name="wpaluploader" id="wpaluploader" />
    ' . wp_nonce_field( 'update-user' ) . '
    <input name="action" type="hidden" id="action" value="update-user" /></p>
    <p><input name="updateuser" type="submit" id="updateuser" class="submit button" value="Upload" /></p>
    <small class="wpal_logo_hints">Allowed image type is ' . get_option(wpal_mime) .' 
    , max image width ' . get_option(wpal_width) . 'px, Max image height ' . get_option(wpal_height) . 'px </small>
    </form>
    </div>';
    return $wpaluploader_output;
  }

add_action('admin_menu', 'wpal_menu');

function wpal_menu() {
    add_options_page('WP Author Logo', 'WP Author Logo', 'manage_options', 'wpal_wp-author-logo', 'wpal');
}

function wpal() {
    if (!current_user_can('manage_options'))  {
        wp_die( __('You do not have sufficient permissions to access this page.') ); 
    }
?>   
<div class="wrap">
    <div class="leftwrap" style="background:#ffffff; border:1px solid #dddddd; padding:10px; margin-top:20px; width:400px; float:left;">
        <?php    echo "<h2>" . __( 'Wordpress Author Logo Plugin', 'wpal_lang' ) . "</h2>"; ?>

        <?php  
            if($_POST['wpal_author_logo_success'] == 'Y') {  
                //Form data sent  
                $wpal_width = $_POST['wpal_width'];  
                update_option('wpal_width', $wpal_width);  

                $wpal_height = $_POST['wpal_height'];  
                update_option('wpal_height', $wpal_height);

                $wpal_size = $_POST['wpal_size'];  
                update_option('wpal_size', $wpal_size);
                
                $wpal_role = $_POST['wpal_role'];
                update_option('wpal_role', $wpal_role);
                
                $wpal_title = $_POST['wpal_title'];
                update_option('wpal_title', $wpal_title); 
                
                $wpal_mime = $_POST['wpal_mime'];
                update_option('wpal_mime', $wpal_mime);   

            ?>  
            <div class="updated"><p><strong><?php _e('WP Author Logo Plugin Options Saved.' ); ?></strong></p></div>  
            <?php  
            } else {  
                //Normal page display  
                $wpal_width = get_option('wpal_width');  
                $wpal_height = get_option('wpal_height');
                $wpal_size = get_option('wpal_size');
                $wpal_role = get_option('wpal_role');
                $wpal_title = get_option('wpal_title');
                $wpal_mime = get_option('wpal_mime');    
            }  
        ?>    

        <form name="wpal_settingsform" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
            <input type="hidden" name="wpal_author_logo_success" value="Y">  
            <h4>Wordpress Post Logo from front Settings</h4>
            <p><label for="wpal_width"><?php _e("Title For Upload Form: " ); ?></label><br /><input type="text" name="wpal_title" value="<?php echo $wpal_title; ?>" size="30">&nbsp;Ex: My Upload Form</p>  
            <p>
             <label for="wpal_mime">Allowed File Type:</label><br />
             <select name="wpal_mime" id="wpal_mime">
             <option value="<?php echo get_option(wpal_mime); ?>"><?php echo get_option(wpal_mime); ?></option>
            <option value="jpg">jpg</option>
            <option value="png">png</option>
            <option value="gif">gif</option>
            </select>&nbsp;Presently set to <?php echo get_option(wpal_mime); ?>
            </p>
            <p><label for="wpal_width"><?php _e("Maximum Width: " ); ?></label><br /><input type="text" name="wpal_width" value="<?php echo $wpal_width; ?>" size="20">&nbsp;px</p>  
            <p><label for="wpal_height"><?php _e("Maximum Height: " ); ?></label><br /><input type="text" name="wpal_height" value="<?php echo $wpal_height; ?>" size="20">&nbsp;px</p>
            <p><label for="wpal_size"><?php _e("Maximum Size: " ); ?></label><br /><input type="text" name="wpal_size" value="<?php echo $wpal_size; ?>" size="20">&nbsp;Bytes: hint 50000 bytes = 50Kbs</p>   
             <p>
             <label for="wpal_role">Set Minimum Capability Display:</label><br />
             <input type="text" name="wpal_role" id="wpal_role" value="<?php echo get_option(wpal_role); ?>" />&nbsp;Hint: Minimum capability or user role.<br />
            </p>
            <p class="submit">  
                <input type="submit" name="Submit" value="<?php _e('Update Options', 'wpal_lang' ) ?>" />  
            </p>  
        </form>
        <p>
           <a href="http://codex.wordpress.org/Roles_and_Capabilities" target="_blank">More about roles and capabilities</a><br />
         </p>  
         
           <h4>See readme.txt in plugin folder for list of templatetags</h4>
    </div><!-- / leftwrap -->
    </div><!-- / rightwrap -->
</div><!-- / wrap -->
<?php } ?>
