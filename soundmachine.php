<?php
/*
Plugin Name: SoundMachine
Plugin URI: http://blog.limewire.com
Description: An mp3 player widget based on the excellent SoundManager2.  Add links in wp-admin and they show up as a fully-customizable html mp3 player.  Loosely adapted from Flash MP3 Widget by Charles Tang.
Version: 0.2
Author: Michael Nutt
Author URI: http://nuttnet.net
*/

/*  
	Copyright 2009 Michael Nutt <michael@nuttnet.net>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//define the WP_CONTENT_URL and WP_CONTENT_DIR
if (!defined('WP_CONTENT_URL')){ 
  $SITEURL = get_bloginfo('siteurl');
  define('WP_CONTENT_URL', $SITEURL . '/wp-content'); 
}

if (!defined('WP_CONTENT_DIR')){
  define('WP_CONTENT_DIR', ABSPATH . '/wp-content');
}

$sm_dirname = dirname(__FILE__);

function soundmachine_init() {
  if (!function_exists('register_sidebar_widget')) {
    return;
  }

  function soundmachine($args) {
    extract($args);
    global $popout;
    $options = get_option('sm_soundmachine');
    $properties = get_option('sm_properties');
    $songs = get_option('sm_songs');
    $title = htmlspecialchars(stripslashes($options['title']));	

    if (!$popout) echo $before_widget.$before_title.$title.$after_title;

    $base_name = WP_CONTENT_URL . '/plugins/soundmachine';
    ?>
    
    <div class="sm-player corners cf">
      <div class="sm-player-cover"></div>
      <div class="sm-player-current"></div>
      <div class="sm-player-progress">
        <span class="sm-player-progress-inner">&nbsp;</span>
      </div>
      
      <span class="sm-player-play">Play</span>
      <span class="sm-player-stop">Stop</span>
      <span class="sm-player-prev">Previous</span>
      <span class="sm-player-next">Next</span>
      <span class="sm-player-download-wrapper"></span>
      <span class="sm-player-volume"></span>

      <?php if($popout) { ?>
        <script type="text/javascript">
	  var query = {};
          var params = location.search.replace(/^\?/,'').split('&');
	  for( var i = params.length-1;  i >= 0;  i-- ) {
            var p = params[i].split('='), key = p[0];
            if( key ) query[key] = p[1];
          } 
	  var popout_player_songId = query['id'];
          var popout_player_position = query['position'];
	  var popout_player_state = query['state'];
        </script>
      <?php } else { ?>
        <span class="sm-player-popout"><a href="#">Pop out</a></span>
      <?php } ?>
	  

      <div class="sm-playlist">
        <ul class="playlist">
          <?php

          if( !empty($songs) ) {
	    foreach($songs as $song) {
            ?>
              <li>
                <a class="sm-player-song <?php if($song['show_download']) echo 'can-download'; ?>" 
                   data-link="<?php echo $song['buy_link']; ?>" 
                   data-cover="<?php echo $song['album_cover']; ?>" 
                   href="<?php echo $song['path']; ?>">
                     <?php echo stripslashes($song['title']); ?>
                 </a>
              </li>
	    <?php
            }
          }
	  ?>
        </ul>
      </div>

      <div class="tl"></div>
      <div class="tr"></div>
      <div class="br"></div>
      <div class="bl"></div>
    </div>

    <script type="text/javascript" src="<?php echo $base_name."/js/soundmanager2.js"; ?>"></script>
    <script type="text/javascript" src="<?php echo $base_name."/js/sm-player.js"; ?>"></script>
    <script type="text/javascript">
      soundManager.url = "<?php echo $base_name."/flash/"; ?>";
      soundManager.debugMode = false;
    </script>

    <?php
    echo $after_widget;
  }

  function soundmachine_options() {
    $options = get_option('soundmachine');
    if (!is_array($options)) {
      $options = array('title' => __('SoundMachine','sm'), 'width' => 177, 'height' => 280);
    }
    if ($_POST['sm-submit']) {
      $options['title'] = strip_tags($_POST['sm-title']);
      $options['width'] = intval($_POST['sm-width']);
      $options['height'] = intval($_POST['sm-height']);
      update_option('soundmachine', $options);
    }
    echo '<p style="text-align: left;"><label for="sm-title">';
    _e('Title: ','sm');
    echo '</label><input type="text" id="sm-title" name="sm-title" value="'.htmlspecialchars(stripslashes($options['title'])).'" /></p>'."\n";
    echo '<p style="text-align: left;"><label for="sm-width">';
    _e('Width: ','sm');
    echo '</label><input type="text" id="sm-width" name="sm-width" value="'.intval($options['width']).'" size="3" /></p>'."\n";
    echo '<p style="text-align: left;"><label for="sm-height">';
    _e('Height: ','sm');
    echo '</label><input type="text" id="sm-height" name="sm-height" value="'.intval($options['height']).'" size="3" /></p>'."\n";
    echo '<input type="hidden" id="sm-submit" name="sm-submit" value="1" />'."\n";
  }
        
  $widget_ops =  array('classname' => 'soundmachine', 
                       'description' => __( 'SoundMachine.', 'sm'));
  // Register Widgets
  wp_register_sidebar_widget('soundmachine', __('SoundMachine MP3 Player','sm'), 'soundmachine', $widget_ops);
  wp_register_widget_control('soundmachine', __('SoundMachine MP3 Player','sm'), 'soundmachine_options', 400, 200);
}

add_action('plugins_loaded','soundmachine_init');

if(is_admin())
{
  add_action('init',  'sm_initRoles');
  add_action('admin_menu', 'sm_adminMenu');
  add_action('admin_notices', 'sm_displayMessage');
}

function sm_initRoles()
{
  add_thickbox();
  wp_enqueue_script('media-upload');
  if ( function_exists('get_role') ) {
    $role = get_role('administrator');
    if( $role != null && !$role->has_cap('admin_sm') ) {
      $role->add_cap('admin_sm');
    }
    // Clean var
    unset($role);
  }
}

function sm_adminMenu() {
  add_options_page( __('SoundMachine', 'sm'), 
                    __('MP3 Player', 'sm'), 
                    'admin_sm', 
                    'sm_options', 
                    'sm_pageOptions');
}

$sm_message = '';
$sm_status ='';

function sm_pageOptions() {
  global $sm_message, $sm_status;
  $sm_admin_base_url = get_option('siteurl') . '/wp-admin/admin.php?page=';
  
  $phpver = phpversion();
  
  $songs = get_option('sm_songs');
  $properties = get_option('sm_properties');
  
  if ( isset($_POST) ) {

    // Create a new song
    if ( $_POST['action'] == "create" ) {
      $song = array('id'            => sizeof($songs) + 1,
		    'title'         => $_POST["song-title"],
		    'path'          => stripslashes($_POST["song-path"]),
		    'show_download' => $_POST["song-show_download"],
		    'buy_link'      => stripslashes($_POST["song-buy_link"]),
		    'album_cover'   => stripslashes($_POST["song-album_cover"])
		    );
      $songs[] = $song;
      update_option('sm_songs', $songs);      
    }

    // Update the properties of a song
    if ( $_POST['action'] == "update" ) {
      $id = $_POST['id'];
      foreach($songs as &$song) {
	if($song['id'] == $id) {
	  $song['title'] = $_POST["song-title"];
	  $song['path'] = $_POST["song-path"];
	  $song['show_download'] = $_POST["song-show_download"];
	  $song['buy_link'] = $_POST["song-buy_link"];
	  break;
	}
      }
    }

    // Delete a song
    if ( $_POST['action'] == "destroy" ) {
      $id = $_POST['id'];

      for($i = 0; $i < sizeof($songs); $i++) {
	if($songs[$i]['id'] == $id) {
	  unset($songs[$i]);
	  break;
	}
      }

      update_option('sm_songs', array_values($songs));
    }

    // Re-order the song list
    if ( $_POST['action'] == "order" ) {
      $ids = $_POST['ids'];
      $sorted_songs = array();

      foreach($ids as $id) {
	foreach($songs as $song) {
	  if($song['id'] == $id) { $sorted_songs[] = $song; break; }
	}
      }

      update_option('sm_songs', $sorted_songs);
    }

    // Update the player options
    if ( $_POST['action'] == "updateoptions" ) {
      $properties['showDisplay'] = $_POST['sm-show-dispay'];
      $properties['showPlaylist'] = $_POST['sm-show-playlist'];
      $properties['autoStart'] = $_POST['sm-auto-play'];
      update_option('sm_properties', $properties);
    }

    $sm_message = __('Options saved', 'sm');
    $sm_status = 'updated';
  }

  sm_displayMessage();
  
  ?>
  <div class="wrap">
    <h2><?php _e('SoundMachine','sm'); ?></h2>
      <p><?php _e('Visit the <a href="http://www.charlestang.cn/flash-player-widget.htm">plugin\'s homepage</a> for further details. If you find a bug, or have a fantastic idea for this plugin, <a href="mailto:charlestang@foxmail.com">ask me</a> !', 'sm'); ?></p>
      <p><?php 
            _e("Your PHP version is ",'sm'); echo $phpver;
            global $post_ID, $temp_ID;
            $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
            $media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
            $audio_upload_iframe_src = apply_filters('audio_upload_iframe_src', "$media_upload_iframe_src&amp;type=audio");
            $audio_title = __('Add Audio');

	    $sm_url = get_option('siteurl')."/wp-content/plugins/soundmachine/js";
	    wp_register_script( 'sm-admin', $sm_url, "sm-admin.js", array("jquery") );
          ?>
      </p>

      <script>
        function reloadSMScripts() {
          var script = document.createElement('script');
	  script.type = "text/javascript";
	  script.url = "<?php echo get_option('siteurl'); ?>/wp-content/plugins/soundmachine/js/sm-admin.js";	  
console.log(script);
	  document.getElementsByTagName('head')[0].appendChild(script);
	}
      </script>

      <div id="editor"></div>

      <script src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/soundmachine/js/sm-admin.js" type="text/javascript"></script>
      <script src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/soundmachine/js/jquery.yql.js" type="text/javascript"></script>

      <link rel="stylesheet" href="<?php echo get_option('siteurl'); ?>/wp-content/plugins/soundmachine/css/sm-admin.css" type="text/css"></style>
        
      <form class="form-table" method="post" action="<?php echo $sm_admin_base_url . 'sm_options';?>">
        <p><?php _e('Player Properties','sm'); ?></p>

        <p>
          <input type="checkbox" id="sm-show-dispay" name="sm-show-dispay" value="yes" <?php if( $properties['showDisplay'] == 'yes' ) echo "checked='checked'"; ?> size="5"/>
          <label for="sm-show-dispay"><?php _e('Show upper panel','sm'); ?></label>
        </p>
        <p>
          <input type="checkbox" id="sm-show-playlist" name="sm-show-playlist" value="yes" <?php if( $properties['showPlaylist'] == 'yes' ) echo "checked='checked'"; ?> size="5"/>
          <label for="sm-show-playlist"><?php _e('Show play list','sm'); ?></label>
        </p>
        <p>
          <input type="checkbox" id="sm-auto-play" name="sm-auto-play" value="yes" <?php if( $properties['autoStart'] == 'yes' ) echo "checked='checked'"; ?> size="5"/>
          <label for="sm-auto-play"><?php _e('Auto start','sm'); ?></label>
        </p>

        <p><?php _e('Songs List','sm'); ?></p>
        
	<p>
          <a href="<?php echo $audio_upload_iframe_src; ?>&am;TB_iframe=true" id="add_audio" class="thickbox" title="<?php echo $audio_title; ?>"><img src='images/media-button-music.gif' alt='<?php echo $audio_title; ?>' /> Add Song</a>
          &#8212;
          <a href="#" onClick="condenseSongs(); return false;">Condense Song List</a>
        </p>
                  
        <?php for($i = 0; $i < sizeof($songs); $i++) { ?>
          <?php $song = $songs[$i]; ?>
          <div class="song-field">
            <div>
              <label for="song-title-<?php echo $i;?>">
                <?php _e('Title:','sm');?>
              </label>
              <input class="song-title" type="text" name="song-title-<?php echo $i;?>" id="song-title-<?php echo $i;?>" value="<?php echo stripslashes($song['title']); ?>" size="75"/>
            </div>
            
            <div>
              <label for="song-path-<?php echo $i;?>">
                <?php _e('URL:','sm');?>
              </label>
              <input class="song-path" type="text" name="song-path-<?php echo $i;?>" id="song-path-<?php echo $i;?>" value="<?php echo $song['path']; ?>" size="75"/>
            </div>
            <table class="inline-table">
              <tr>
                <td>
                  <div>
                    <label for="song-buy_link-<?php echo $i;?>">
                      <?php _e('Buy Link:','sm');?>
                    </label>
                    <input class="song-buy_link" type="text" name="song-buy_link-<?php echo $i;?>" id="song-buy_link-<?php echo $i;?>" value="<?php echo $song['buy_link']; ?>" size="75"/>
                  </div>
                </td>
                <td>
                  <div>
                    <label for="song-album_cover-<?php echo $i;?>">
                      <?php _e('Album Cover:','sm');?>
                    </label>
                    <input class="song-album_cover" type="text" name="song-album_cover-<?php echo $i;?>" id="song-album_cover-<?php echo $i;?>" value="<?php echo $song['album_cover']; ?>" size="75"/>
                  </div>
                </td>
                <td class="album_cover_preview"><div class="preview_wrapper"><img src="<?php echo $song['album_cover']; ?>"/></div></td>
                <td>
                  <div class="song-checkbox">
                    <input class="song-show_download" type="checkbox" name="song-show_download-<?php echo $i;?>" id="song-show_download-<?php echo $i;?>" <?php if($song['show_download'] == true) echo "checked='checked'"; ?> />&nbsp;<label for="song-show-download-<?php echo $i;?>"><?php _e('Show&nbsp;Download','sm');?></label>
                  </div>
                </td>
                <td>
                  <button class="fetch-song">Fetch</button>
                </td>
              </tr>
            </table>
          </div>
        <?php } ?>
                             
        <input type="hidden" name="action" value="update" />                    
        <p class="submit">
          <input type="submit" name="updateoptions" value="<?php _e('Save Changes') ?>" />
        </p>
      </form>
      <?php sm_printAdminFooter(); ?>
    </div><!--/wrap-->
  <?php
}

############## Admin WP Helper ##############
/**
 * Display plugin Copyright
 *
 */
function sm_printAdminFooter() {
?>
  <p class="footer_st" style="font-size:0.8em;text-align:center;"><?php printf(__('&copy; Copyright 2008 <a href="http://www.charlestang.cn/" title="Here With Me">Charles Tang</a> | <a href="http://www.charlestang.cn/flash-mp3-player.htm">SoundMachine</a> | Version %s', 'sm'), '8.8.7'); ?></p>
  <?php
} 

/**
 * Display WP alert
 *
 */
function sm_displayMessage() {
  global $sm_message, $sm_status;
  if ( $mess != '') {
    $message = $sm_message;
    $status = $sm_status;
    $sm_message = $sm_status = ''; // Reset
  }

  if ( $message ) {
  ?>
    <div id="message" class="<?php echo ($status != '') ? $status :'updated'; ?> fade">
      <p><strong><?php echo $message; ?></strong></p>
    </div>
  <?php
  }
}

?>
