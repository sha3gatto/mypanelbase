<?php
/*
 * Enqueue Scripts and Style
 */

function hero4genesis_scripts() {
	wp_enqueue_style( 'hero4genesis-css', plugins_url( 'assets/css/hero4genesis.css' , dirname(__FILE__) ) , array(), null );
	wp_enqueue_script(
		'jquery-hero4genesis',
		plugins_url( 'assets/js/hero4genesis.js' , dirname(__FILE__) ),
		array( 'jquery' ),
		'',
		true
	);

	$general_settings 		= (array) get_option( 'hero4genesis_general_settings' );
	$css_settings 	= (array) get_option( 'hero4genesis_css_settings' );
	if( isset( $general_settings['style'] ) && !empty( $general_settings['style'] ) ){
		wp_enqueue_style( 'hero4genesis-style', plugins_url( 'assets/css/appearance.css' , dirname(__FILE__) ) , array(), null );
	}
	if( isset( $css_settings['css'] ) && !empty( $css_settings['css'] ) ){
		wp_enqueue_style( 'hero4genesis-custom', plugins_url( 'assets/css/custom.css' , dirname(__FILE__) ) , array(), null );
	}
}

add_action( 'wp_enqueue_scripts', 'hero4genesis_scripts' );
?>