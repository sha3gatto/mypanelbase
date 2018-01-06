<?php
/*
 * Footr Admin Settings
 * Appearance > Footr
 */

class Settings_API_Hero4Genesis {
	
	/*
	 * For easier overriding we declared the keys
	 * here as well as our tabs array which is populated
	 * when registering settings
	 */
	private $general_settings_key 	= 'hero4genesis_general_settings';
	private $css_settings_key 		= 'hero4genesis_css_settings';
	private $plugin_options_key 	= 'hero4genesis_plugin_options';
	private $help_settings_key 		= 'hero4genesis_help_settings';
	private $plugin_settings_tabs 	= array();

	
	/*
	 * Fired during plugins_loaded (very very early),
	 * so don't miss-use this, only actions and filters,
	 * current ones speak for themselves.
	 */
	function __construct() {
		add_action( 'init', 		array( &$this, 'load_settings' ) );
		add_action( 'admin_init', 	array( &$this, 'register_general_settings' ) );
		add_action( 'admin_init', 	array( &$this, 'register_css_settings' ) );
		add_action( 'admin_init', 	array( &$this, 'register_help_settings' ) );
		add_action( 'admin_menu', 	array( &$this, 'add_admin_menus' ), 99 );
		add_action( 'admin_init' , 	array( &$this, 'on_load_page' ) );
		add_action( 'wp_footer' , 	array( &$this, 'footer_css' ), 99 );
	}

	function on_load_page(){
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script(
			'hero4genesis-admin-js',
			plugins_url( 'assets/js/hero4genesis-admin.js' , dirname(__FILE__) ),
			array( 'jquery' ),
			'',
			true
		);
	}
	
	/*
	 * Loads both the general and advanced settings from
	 * the database into their respective arrays. Uses
	 * array_merge to merge with default values if they're
	 * missing.
	 */
	function load_settings() {
		$this->general_settings = (array) get_option( $this->general_settings_key );
		$this->css_settings 	= (array) get_option( $this->css_settings_key );
	}
	
	/*
	 * Registers the general settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_general_settings() {
		$this->plugin_settings_tabs[$this->general_settings_key] = __('General', 'hero4genesis');
		
		register_setting( $this->general_settings_key, $this->general_settings_key );
		add_settings_section( 'general_section', __('General Options', 'hero4genesis'), array( &$this, 'general_options_section' ), $this->general_settings_key );
	}

	function general_options_section(){ 
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
			$this->override_css();
		}

		$options = array(
						'title'			=>	__('Widget Title', 'hero4genesis'),
						'titlebg'		=>	__('Widget Title Background', 'hero4genesis'),
						'links'			=>	__('Links', 'hero4genesis'),
						'linkhover'		=>	__('Link Hover', 'hero4genesis'),
						'paragraph'		=>	__('Paragraph', 'hero4genesis'),
						'label'			=>	__('Label', 'hero4genesis'),
						'lists'			=>	__('Lists', 'hero4genesis'),
						'textbox'		=>	__('Text Box & Text Area Text Color', 'hero4genesis'),
						'textboxbg'		=>	__('Text Box & Text Area Background Color', 'hero4genesis'),
						'button'		=>	__('Button Text', 'hero4genesis'),
						'buttonhover'	=>	__('Button Text Hover', 'hero4genesis'),
						'buttonbg'		=>	__('Button Background', 'hero4genesis'),
						'buttonbghover'	=>	__('Button Background Hover', 'hero4genesis'),
				   );
		?>
		<p><?php _e('General Option for Hero image on your Genesis Powered Site', 'hero4genesis');?></p>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="hero4genesis-height"><?php _e( 'Full Height', 'hero4genesis' );?></label></th>
					<td>
						<input type="checkbox" id="hero4genesis-height" name="<?php echo $this->general_settings_key; ?>[full]" value="1" <?php echo ( isset( $this->general_settings['full'] ) && $this->general_settings['full'] == '1' ) ? 'checked="checked"' : '';  ?> />
						<small><?php _e( 'Check to make the Hero Section 100% of the Browser Height', 'hero4genesis' );?></small>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="hero4genesis-behind"><?php _e( 'Behind Header', 'hero4genesis' );?></label></th>
					<td>
						<input type="checkbox" id="hero4genesis-behind" name="<?php echo $this->general_settings_key; ?>[behind]" value="1" <?php echo ( isset( $this->general_settings['behind'] ) && $this->general_settings['behind'] == '1' ) ? 'checked="checked"' : '';  ?> />
						<small><?php _e( 'Start Hero Section Behind the Header Section. This will also turns your .site-header background to transparent.', 'hero4genesis' );?></small>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="hero4genesis-alignment"><?php _e( 'Aligment', 'hero4genesis' );?></label></th>
					<td>
						<select id="hero4genesis-alignment" name="<?php echo $this->general_settings_key; ?>[alignment]">
							<option value=""><?php _e( 'Default', 'hero4genesis' );?></option>
							<option value="left" <?php echo ( isset( $this->general_settings['alignment'] ) && $this->general_settings['alignment'] == 'left' ) ? 'selected="selected"' : '';  ?>><?php _e( 'Left', 'hero4genesis' );?></option>
							<option value="center" <?php echo ( isset( $this->general_settings['alignment'] ) && $this->general_settings['alignment'] == 'center' ) ? 'selected="selected"' : '';  ?>><?php _e( 'Center', 'hero4genesis' );?></option>
							<option value="right" <?php echo ( isset( $this->general_settings['alignment'] ) && $this->general_settings['alignment'] == 'right' ) ? 'selected="selected"' : '';  ?>><?php _e( 'Right', 'hero4genesis' );?></option>
						</select>
					</td>
				</tr>			
			</tbody>
		</table>
		<h3><?php _e( 'Custom Styling', 'hero4genesis' );?></h3>
		<p><?php _e('Customize Color Scheme to fit your theme appearance.', 'hero4genesis');?></p>
		<table class="form-table">
			<tbody>
			<?php foreach ($options as $key => $value) { ?>
				<tr valign="top">
					<th scope="row"><label for="hero4genesis-<?php echo $key;?>"><?php echo $value;?></label></th>
					<td><input type="text" id="hero4genesis-<?php echo $key;?>" name="<?php echo $this->general_settings_key; ?>[style][<?php echo $key;?>]" class="hero4genesis-color" value="<?php echo ( isset( $this->general_settings['style'][$key] ) && !empty($this->general_settings['style'][$key])) ? $this->general_settings['style'][$key] : '' ; ?>" /></td>
				</tr>
			<?php  } ?>
			</tbody>
		</table>
		<?php
	}

	/*
	 * Registers the custom css settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_css_settings() {
		$this->plugin_settings_tabs[$this->css_settings_key] = __('Custom CSS', 'hero4genesis');
		
		register_setting( $this->css_settings_key, $this->css_settings_key );
		add_settings_section( 'css_section', __('Custom CSS', 'hero4genesis'), array( &$this, 'css_options_section' ), $this->css_settings_key );
	}

	function css_options_section(){ 
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true') {
			$this->override_custom_css();
		}
		?>
		<p><?php _e('Add Custom CSS code on the textarea below.', 'hero4genesis');?></p>
		<textarea class="widefat" rows="15" name="<?php echo $this->css_settings_key; ?>[css]"><?php echo ( isset( $this->css_settings['css'] ) ) ? $this->css_settings['css'] : '' ;?></textarea>
		<?php
	}

	function override_css(){
		$path 			= plugin_dir_path(__FILE__) . '../assets/css/appearance.css';
		$options 		= "/********* Do not edit this file *********/\n\n";
		if(is_writable($path)){
			$options	.= $this->appearance_css();
			$makecss 	= file_put_contents( $path , $options);
		}
		
	}

	function override_custom_css(){
		$path 			= plugin_dir_path(__FILE__) . '../assets/css/custom.css';
		$options 		= "/********* Do not edit this file *********/\n\n";
		if(is_writable($path)){
			$options 	.= $this->custom_css();
			$makecss 	= file_put_contents( $path , $options);
		}
		
	}

	function footer_css(){
		$path = plugin_dir_path(__FILE__) . '../assets/css/appearance.css';
		if(!is_writable($path)){
			echo '<style type="text/css">';
			echo $this->appearance_css();
			echo '</style>';
		}
		$custom = plugin_dir_path(__FILE__) . '../assets/css/custom.css';
		if(!is_writable($custom)){
			echo '<style type="text/css">';
			echo $this->custom_css();
			echo '</style>';
		}
	}

	/*
	 * Create Separate Function for CSS params
	 * just in case the file is not writable, we can call it again
	 */

	function appearance_css(){
		$css = '';

		if( isset( $this->general_settings['style']['title'] ) && !empty( $this->general_settings['style']['title'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner .widgettitle{ color: '. $this->general_settings['style']['title'] .' }';
		}
		if( isset( $this->general_settings['style']['titlebg'] ) && !empty( $this->general_settings['style']['titlebg'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner .widgettitle{ background: '. $this->general_settings['style']['titlebg'] .' }';
		}
		if( isset( $this->general_settings['style']['links'] ) && !empty( $this->general_settings['style']['links'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner a, .hero4genesis-container .hero4genesis-inner .widget a{ color: '. $this->general_settings['style']['links'] .' }';
		}
		if( isset( $this->general_settings['style']['linkhover'] ) && !empty( $this->general_settings['style']['linkhover'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner a:hover, .hero4genesis-container .hero4genesis-inner .widget a:hover{ color: '. $this->general_settings['style']['linkhover'] .' }';
		}
		if( isset( $this->general_settings['style']['paragraph'] ) && !empty( $this->general_settings['style']['paragraph'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner, .hero4genesis-container .hero4genesis-inner p, .hero4genesis-container .hero4genesis-inner .widget p{ color: '. $this->general_settings['style']['paragraph'] .' }';
		}
		if( isset( $this->general_settings['style']['label'] ) && !empty( $this->general_settings['style']['label'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner label{ color: '. $this->general_settings['style']['label'] .' }';
		}
		if( isset( $this->general_settings['style']['lists'] ) && !empty( $this->general_settings['style']['lists'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner ul li, .hero4genesis-container .hero4genesis-inner ol li, .hero4genesis-container .hero4genesis-inner li{ color: '. $this->general_settings['style']['lists'] .' }';
		}
		if( isset( $this->general_settings['style']['textbox'] ) && !empty( $this->general_settings['style']['textbox'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner input[type="text"], .hero4genesis-container .hero4genesis-inner input[type="email"], .hero4genesis-container .hero4genesis-inner textarea{ color: '. $this->general_settings['style']['textbox'] .' }';
		}
		if( isset( $this->general_settings['style']['textboxbg'] ) && !empty( $this->general_settings['style']['textboxbg'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner input[type="text"], .hero4genesis-container .hero4genesis-inner input[type="email"], .hero4genesis-container .hero4genesis-inner textarea{ background: '. $this->general_settings['style']['textboxbg'] .' }';
		}
		if( isset( $this->general_settings['style']['button'] ) && !empty( $this->general_settings['style']['button'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner input[type="button"], .hero4genesis-container .hero4genesis-inner button, .hero4genesis-container .hero4genesis-inner input[type="submit"]{ color: '. $this->general_settings['style']['button'] .' }';
		}
		if( isset( $this->general_settings['style']['buttonhover'] ) && !empty( $this->general_settings['style']['buttonhover'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner input[type="button"]:hover, .hero4genesis-container .hero4genesis-inner button:hover, .hero4genesis-container .hero4genesis-inner input[type="submit"]:hover{ color: '. $this->general_settings['style']['buttonhover'] .' }';
		}
		if( isset( $this->general_settings['style']['buttonbg'] ) && !empty( $this->general_settings['style']['buttonbg'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner input[type="button"], .hero4genesis-container .hero4genesis-inner button, .hero4genesis-container .hero4genesis-inner input[type="submit"]{ background: '. $this->general_settings['style']['buttonbg'] .' }';
		}
		if( isset( $this->general_settings['style']['buttonbghover'] ) && !empty( $this->general_settings['style']['buttonbghover'] ) ){
			$css .= '.hero4genesis-container .hero4genesis-inner input[type="button"]:hover, .hero4genesis-container .hero4genesis-inner button:hover, .hero4genesis-container .hero4genesis-inner input[type="submit"]:hover{ background: '. $this->general_settings['style']['buttonbghover'] .' }';
		}

		return $css;
	}
	function custom_css(){
		$css = '';
		if(isset($this->css_settings['css'])){
			$css .= $this->css_settings['css'];
		}
		return $css;
	}

	/*
	 * Registers the general settings via the Settings API,
	 * appends the setting to the tabs array of the object.
	 */
	function register_help_settings() {
		$this->plugin_settings_tabs[$this->help_settings_key] = __( 'Help', 'hero4genesis' );
		
		register_setting( $this->help_settings_key, $this->help_settings_key );
		add_settings_section( 'general_section', __('Need Help?', 'hero4genesis'), array( &$this, 'help_options_section' ), $this->help_settings_key );
	}

	function help_options_section(){
		_e( '<p>Having any trouble on setting up the hero section on your site or perhaps you need additional plugin features? Feel free to contact me <a href="http://phpbits.net/hire-me/" target="_blank">here</a> and will be very happy to assists you, not for free though ;)</p>', 'hero4genesis' );

		_e( '<p>Or not? If you\'ve got it all figured out and enjoying this plugin don\'t forget to leave a rating <a href="https://wordpress.org/support/view/plugin-reviews/hero-for-genesis-framework" target="_blank">here</a> or subscribe to our <a href="http://phpbits.net/blog/" target="_blank">newsletter</a> for updates. Thanks!</p>', 'hero4genesis' );
	}

	
	/*
	 * Called during admin_menu, adds an options
	 * page under Settings called My Settings, rendered
	 * using the wplftr_plugin_options_page method.
	 */
	function add_admin_menus() {
		add_submenu_page( 'genesis', __( 'Hero for Genesis', 'hero4genesis' ), __( 'Hero for Genesis', 'hero4genesis' ), 'manage_options', $this->plugin_options_key, array( &$this, 'plugin_options_page' ) );
	}
	
	/*
	 * Plugin Options page rendering goes here, checks
	 * for active tab and replaces key with the related
	 * settings key. Uses the wplftr_plugin_options_tabs method
	 * to render the tabs.
	 */
	function plugin_options_page() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
		?>
		<div class="wrap">
			<?php $this->plugin_options_tabs(); ?>
			<?php if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'):?>
				<!-- <div id="setting-error-settings_updated" class="updated settings-error"> 
					<p><strong><?php _e('Settings saved.','hero4genesis');?></strong></p>
				</div> -->
			<?php endif;?>
			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options' ); ?>
				<?php settings_fields( $tab ); ?>
				<?php do_settings_sections( $tab ); ?>
				<?php 
				if(function_exists('submit_button')) { submit_button(); } else { ?>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
				<?php }?>
			</form>
		</div>
		<?php
	}
	
	/*
	 * Renders our tabs in the plugin options page,
	 * walks through the object's tabs array and prints
	 * them one by one. Provides the heading for the
	 * wplftr_plugin_options_page method.
	 */
	function plugin_options_tabs() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;

		screen_icon();
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';	
		}
		echo '</h2>';
	}

};

// Initialize the plugin
add_action( 'plugins_loaded', create_function( '', '$Settings_API_Hero4Genesis = new Settings_API_Hero4Genesis;' ) );

?>