<?php
/*
Plugin Name: SoundMachine
Plugin URI: http://blog.limewire.com
Description: An mp3 player widget based on the excellent SoundManager2.  Add links in wp-admin and they show up as a fully-customizable html mp3 player.  Loosely adapted from Flash MP3 Widget by Charles Tang (email : charlestang@foxmail.com).
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

//init the flash mp3 player widget
function soundmachine_init() {
  if (!function_exists('register_sidebar_widget')) {
    return;
  }

  function soundmachine($args) {
    extract($args);
    global $fmp_listfile_url;
    global $popout;
    $options = get_option('soundmachine');
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
      <?php if ( !$popout ) { ?>
        <span class="sm-player-popout"><a href="#">Pop out</a></span>
      <?php } ?>
      
      <script type="text/javascript">
        jQuery('.sm-player-popout').click(function() {
          var href = "<?php echo get_option('siteurl'); ?>/wp-content/plugins/soundmachine/popout.php";
          var idParam = "id="+smPlayer.selectedId();
          var positionParam = "position="+smPlayer.position;
          var stateParam = "state="+smPlayer.state();
          href = href + "?" + idParam + "&" + positionParam + "&" + stateParam;
          smPlayer.stop();
	  window.open(href, "Music Player", 'width=190,height=250,scrollbars=no,toolbar=no,location=no,menubar=no,resizable=no,status=no,directories=no');
          return false;
        });

      </script>

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
      <?php } ?>
	  

      <div class="sm-playlist">
        <ul class="playlist">
          <?php

          if( !empty($songs) ) {
	    foreach($songs as $song) {
              ?>
              <li>
                <a class="sm-player-song <?php if($song['show_download']) echo 'can-download'; ?>" data-link="<?php echo $song['buy_link']; ?>" data-cover="<?php echo $song['album_cover']; ?>" href="<?php echo $song['path']; ?>"><?php echo stripslashes($song['title']); ?></a>
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
	$options = array('title' => __('Flash MP3 Player','fmp'), 'width' => 177, 'height' => 280);
      }
      if ($_POST['fmp-submit']) {
	$options['title'] = strip_tags($_POST['fmp-title']);
	$options['width'] = intval($_POST['fmp-width']);
	$options['height'] = intval($_POST['fmp-height']);
	update_option('soundmachine', $options);
      }
      echo '<p style="text-align: left;"><label for="fmp-title">';
      _e('Title: ','fmp');
      echo '</label><input type="text" id="fmp-title" name="fmp-title" value="'.htmlspecialchars(stripslashes($options['title'])).'" /></p>'."\n";
      echo '<p style="text-align: left;"><label for="fmp-width">';
      _e('Width: ','fmp');
      echo '</label><input type="text" id="fmp-width" name="fmp-width" value="'.intval($options['width']).'" size="3" /></p>'."\n";
      echo '<p style="text-align: left;"><label for="fmp-height">';
      _e('Height: ','fmp');
      echo '</label><input type="text" id="fmp-height" name="fmp-height" value="'.intval($options['height']).'" size="3" /></p>'."\n";
      echo '<input type="hidden" id="fmp-submit" name="fmp-submit" value="1" />'."\n";
    }
	
    $widget_ops =  array('classname' => 'soundmachine', 'description' => __( 'Display a flash made MP3 Player.', 'fmp'));
    // Register Widgets
    wp_register_sidebar_widget('flash_mp3player', __('MP3 Player','fmp'), 'soundmachine', $widget_ops);
    wp_register_widget_control('flash_mp3player', __('MP3 Player','fmp'), 'soundmachine_options', 400, 200);
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

function sm_adminMenu()
{
  add_options_page( __('SoundManager MP3 List', 'sm'), __('MP3 list', 'sm'), 'admin_sm', 'sm_options', 'sm_pageOptions');
}

function load_now_list_in(&$properties, &$songs)
{
  $songs = get_option('sm_songs');
  $properties = get_option('sm_properties');
}

function sm_save_the_playlist($properties, $songs){
  update_option('sm_songs', $songs);
  update_option('sm_properties', $properties);
}

$sm_message = '';
$sm_status ='';

function sm_pageOptions()
{
  global $sm_message, $sm_status;
  $sm_admin_base_url = get_option('siteurl') . '/wp-admin/admin.php?page=';
  
  $phpver = phpversion();
  
  load_now_list_in($properties, $songs);
  
  if ( isset($_POST['updateoptions']) ) {
    $songs = array();
    unset($songs);
    $songs = array();
    for($k = 0; $k < 15; $k++){
      if($_POST["song-title-$k"] != '' && $_POST["song-path-$k"] != '')
	{
	  $songs[] = array('title'         => $_POST["song-title-$k"],
			   'path'          => stripslashes($_POST["song-path-$k"]),
			   'show_download' => $_POST["song-show_download-$k"],
			   'buy_link'      => stripslashes($_POST["song-buy_link-$k"]),
			   'album_cover'   => stripslashes($_POST["song-album_cover-$k"])
			   );
	}
    }
    
    $properties['showDisplay'] = $_POST['sm-show-dispay'];
    $properties['showPlaylist'] = $_POST['sm-show-playlist'];
    $properties['autoStart'] = $_POST['sm-auto-play'];
    
    sm_save_the_playlist($properties, $songs);
    
    $sm_message = __('Options saved', 'sm');
    $sm_status = 'updated';
  }
  sm_displayMessage();
  $rows = count($songs);
  $left = 10 - $rows;
  $i=0;
  
  ?>
  <div class="wrap">
    <h2><?php _e('MP3 Player','sm'); ?></h2>
      <p><?php _e('Visit the <a href="http://www.charlestang.cn/flash-player-widget.htm">plugin\'s homepage</a> for further details. If you find a bug, or have a fantastic idea for this plugin, <a href="mailto:charlestang@foxmail.com">ask me</a> !', 'sm'); ?></p>
      <p><?php 
            _e("Your PHP version is ",'sm'); echo $phpver;
            global $post_ID, $temp_ID;
            $uploading_iframe_ID = (int) (0 == $post_ID ? $temp_ID : $post_ID);
            $media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";
            $audio_upload_iframe_src = apply_filters('audio_upload_iframe_src', "$media_upload_iframe_src&amp;type=audio");
            $audio_title = __('Add Audio');
            
          ?>
      </p>

      <script src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/soundman-mp3-list/js/sm-admin.js" type="text/javascript"></script>

      <link rel="stylesheet" href="<?php echo get_option('siteurl'); ?>/wp-content/plugins/soundman-mp3-list/css/sm-admin.css" type="text/css"></style>
        
      <form method="post" action="<?php echo $sm_admin_base_url . 'sm_options';?>">
        <table class="form-table">
          <tbody>
            <tr>
              <th scope="row"><?php _e('Player Properties','sm'); ?></th>
              <td>
                <fieldset>
                  <legend class="hidden"><?php _e('Player Properties','sm'); ?></legend>
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
                </fieldset>
              </td>
            </tr>
            <tr>
              <th scope="row"><?php _e('Songs List','sm'); ?></th>
              <td>
                <fieldset>
                  <legend class="hidden"><?php _e('Songs List','sm'); ?></legend>
                  <p>
                    <a href="<?php echo $audio_upload_iframe_src; ?>&am;TB_iframe=true" id="add_audio" class="thickbox" title="<?php echo $audio_title; ?>"><img src='images/media-button-music.gif' alt='<?php echo $audio_title; ?>' /> Add Song</a>
                    &#8212;
                    <a href="#" onClick="condenseSongs(); return false;">Condense Song List</a>
                  </p>
                  
                  <?php for($i = 0; $i < 15; $i++) { ?>
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
                             
                </fieldset>
              </td>
            </tr>
                 
          </tbody>
        </table>
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
  <p class="footer_st" style="font-size:0.8em;text-align:center;"><?php printf(__('&copy; Copyright 2008 <a href="http://www.charlestang.cn/" title="Here With Me">Charles Tang</a> | <a href="http://www.charlestang.cn/flash-mp3-player.htm">Flash MP3 Player</a> | Version %s', 'sm'), '8.8.7'); ?></p>
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
