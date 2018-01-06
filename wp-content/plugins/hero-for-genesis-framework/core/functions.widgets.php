<?php
/*
 * Create Custom Widget Sidebar
 */
class Hero4Genesis_Sidebar {
    public function __construct() {
        add_action( 'wp_loaded', array( &$this,'register_sidebar') );
        add_action( 'admin_enqueue_scripts', array( &$this,'widget_js') );
    }
    /**
     * Register Custom Widget Sidebar 
     */
    function register_sidebar(){
        register_sidebar(array(
          'name' => __( 'Hero for Genesis', 'hero4genesis' ),
          'id' => 'hero-genesis-widget',
          'description' => __( 'Widgets in this area will be shown as Hero Image on your Genesis Framework powered site.', 'hero4genesis' ),
          'before_widget' => '<div id="%1$s" class="widget %2$s">',
          'after_widget'  => '</div>'
        ));
    }

    function widget_js( $hook_suffix ){
        if ( 'widgets.php' !== $hook_suffix ) {
          return;
        }
        wp_enqueue_media();
        wp_enqueue_script( 'hero4genesis-widget', plugins_url( 'assets/js/widgets.js' , dirname(__FILE__) ) );
    }
}
new Hero4Genesis_Sidebar();
?>