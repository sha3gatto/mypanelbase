<?php

/**
 * WP Bootstrap Starter functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WBS_Child
 */

if( ! function_exists( 'wp_bootstrap_starter_setup' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function wp_bootstrap_starter_setup() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// check if polylang exist & enabled
		if( is_plugin_active( 'polylang/polylang.php' ) ) {
			//plugin is activated
			add_filter( 'pll_the_languages', 'mpb_dropdown', 10, 3 );

			function mpb_dropdown( $output, $args ) {

				$translations = pll_the_languages( array( 'raw' => 1 ) );

				foreach( $translations as $key => $value ) {

					if( $value[ "current_lang" ] ) {

						$lang = $value[ 'name' ];
					}
				}

				$output = '';
				$output .= '<li id="header-menu-lang">'
					. '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
					. $lang
					. '<span class="caret"></span>'
					. '</button>'
					. '<ul class="dropdown-menu">';

				foreach( $translations as $key => $value ) {
					$output .= '<li><a href="' . $value[ 'url' ] . '">' . $value[ 'name' ] . '</a></li>';
				}

				$output .= '</ul></li>';
				return $output;
			}

		}

		// check Polylang exist
		if( function_exists( 'pll_register_string' ) ) {
			pll_register_string( "That page can&rsquo;t be found.", "That page can&rsquo;t be found." );
			pll_register_string( "It looks like nothing was found at this location.", "It looks like nothing was found at this location." );
			pll_register_string( "Back to home page.", "Back to home page." );
			pll_register_string( "Are you looking for online survey software? Go to mysurveylab.com", "Are you looking for online survey software? Go to mysurveylab.com" );
			pll_register_string( "Sign up for free in mypanelbase.com. It will take just a few seconds.", "Sign up for free in mypanelbase.com. It will take just a few seconds." );
			pll_register_string( "https://panel.cint.com/69c24371-e69a-4f89-a8bc-fba2514e6104/panelists/new", "https://panel.cint.com/69c24371-e69a-4f89-a8bc-fba2514e6104/panelists/new" );
			pll_register_string( "https://panel.cint.com/69c24371-e69a-4f89-a8bc-fba2514e6104", "https://panel.cint.com/69c24371-e69a-4f89-a8bc-fba2514e6104" );
			pll_register_string( "FREE SIGN UP", "FREE SIGN UP" );
			pll_register_string( "Our panels:", "Our panels:" );
			pll_register_string( "<a href=\"%s\">7 Points</a>.", "<a href=\"%s\">7 Points</a>." );
			pll_register_string( "Copyright %04d by", "Copyright %04d by" );
			pll_register_string( "All rights reserved.", "All rights reserved." );
			pll_register_string( "Sign up for free, participate in paid surveys and make money online without leaving your home.", "Sign up for free, participate in paid surveys and make money online without leaving your home." );
			pll_register_string( "icon sign up", "icon sign up" );
			pll_register_string( "Sign up", "Sign up" );
			pll_register_string( "Rejestracja", "Rejestracja" );
			pll_register_string( "Logowanie", "Logowanie" );
			pll_register_string( "Sign up for free and join the other panel members.", "Sign up for free and join the other panel members." );
			pll_register_string( "icon participate in surveys", "icon participate in surveys" );
			pll_register_string( "Participate in surveys", "Participate in surveys" );
			pll_register_string( "Participate in online paid surveys.", "Participate in online paid surveys." );
			pll_register_string( "icon money", "icon money" );
			pll_register_string( "Make money", "Make money" );
			pll_register_string( "Make money without leaving your home. On your terms.", "Make money without leaving your home. On your terms." );
			pll_register_string( "Why should you sign up?", "Why should you sign up?" );
			pll_register_string( "Find out reasons why you should join mypanelbase online survey panel", "Find out reasons why you should join mypanelbase online survey panel" );
			pll_register_string( "Quick and free registration", "Quick and free registration" );
			pll_register_string( "Participate in paid online surveys", "Participate in paid online surveys" );
			pll_register_string( "Share your opinion and support your favourite brands", "Share your opinion and support your favourite brands" );
			pll_register_string( "You earn money with each completed survey", "You earn money with each completed survey" );
			pll_register_string( "Your opinion is important to us", "Your opinion is important to us" );
			pll_register_string( "Participate in paid surveys", "Participate in paid surveys" );
			pll_register_string( "No obligations, you can opt-out at any time", "No obligations, you can opt-out at any time" );
			pll_register_string( "Only online surveys, we don’t send ads", "Only online surveys, we don’t send ads" );
			pll_register_string( "Make money without leaving your home, use your computer or tablet", "Make money without leaving your home, use your computer or tablet" );
			pll_register_string( "Support selected charity organizations", "Support selected charity organizations" );
		}

		load_theme_textdomain( 'wp-bootstrap-starter', get_template_directory() . '/languages' );
		//load_theme_textdomain( 'MPB_WP_Szablon', get_template_directory() . '/localization' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		register_nav_menus(
			array(
				'header-menu' => esc_html__( 'Header Menu', 'wbs-child' ),
				'footer-menu' => esc_html__( 'Footer Menu', 'wbs-child' ),
				'footer-our-panels-menu' => esc_html__( 'Footer Our Panels Menu', 'wbs-child' ),
				'header-social-icons' => esc_html__( 'Header Social Icons', 'wbs-child' )
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Set up the WordPress core custom background feature.
		add_theme_support( 'custom-background', apply_filters( 'wp_bootstrap_starter_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		) ) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );
	}

endif;
add_action( 'after_setup_theme', 'wp_bootstrap_starter_setup' );

/**
 * Enqueue scripts and styles.
 */
function wp_bootstrap_starter_child_scripts() {
	wp_enqueue_script( 'wbs-child', get_bloginfo( 'stylesheet_directory' ) . '/js/MPB-script.js', array( 'jquery' ), null, true );
}

add_action( 'wp_enqueue_scripts', 'wp_bootstrap_starter_child_scripts' );
