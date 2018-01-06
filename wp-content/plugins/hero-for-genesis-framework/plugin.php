<?php

/*
Plugin Name: Hero for Genesis Framework
Plugin URI: https://wordpress.org/plugins/hero-for-genesis-framework/
Description: Add Hero Image via widget on your Genesis Framework powered site
Version: 1.0.1
Author: phpbits
Author URI: http://codecanyon.net/user/phpbits/portfolio?ref=phpbits
Text Domain: hero4genesis
Domain Path: /languages/
*/

//avoid direct calls to this file
if ( !function_exists( 'add_action' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}
// Load translation (by JP)
load_plugin_textdomain( 'hero4genesis', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

/*##################################
  REQUIRE
################################## */
require_once( dirname( __FILE__ ) . '/core/functions.display.php');
require_once( dirname( __FILE__ ) . '/core/functions.enqueue.php');
require_once( dirname( __FILE__ ) . '/core/functions.widgets.php');
require_once( dirname( __FILE__ ) . '/core/functions.admin.php');
require_once( dirname( __FILE__ ) . '/core/widgets/widget.image.php');

/*##################################
  DEFAULT OPTION
################################## */
function hero4genesis_activate() {
  if(!get_option( 'hero4genesis_general_settings')){
    $general            = array();
    $general['full']        = 1;
    $general['behind']        = 1;
    $general['alignment']       = 'center';
    $general['style']['title']    = '#ffffff';
    $general['style']['paragraph']  = '#ffffff';
    add_option('hero4genesis_general_settings',$general);
  }
}
register_activation_hook( __FILE__, 'hero4genesis_activate' );