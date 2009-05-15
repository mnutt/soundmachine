<?php
require('../../../wp-blog-header.php');
$popout = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" type="text/css" href="<?php bloginfo('stylesheet_url')?>" />
    <script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/assets/javascripts/jquery.js"></script>
    <script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/assets/javascripts/application.js"></script>
  </head>

  <body>
    <div id="layout">
    <div class="widget_flash_mp3player" style="font-size: 1.1em;">
      <?php widget_flash_mp3player(array()); ?>
    </div>
    </div>
  </body>
</html>