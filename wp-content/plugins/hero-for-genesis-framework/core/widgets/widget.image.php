<?php
/*##################################
	CLEAR FIX WIDGET
################################## */

/**
 * Create widget
 *
 * @since 1.0
 */
class HERO4GENESIS_IMAGE extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'hero4genesis_image', // Base ID
			__('Hero: Background Image', 'hero4genesis'), // Name
			array( 'description' => __( 'Fix Footr Widgets Float or ADD Widget Spacing', 'hero4genesis' ), ) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		extract( $args );

		if( isset( $instance['image_url'] ) && !empty( $instance['image_url'] ) ){
			echo '<div class="hero4genesis_image" style="background-image: url('. $instance['image_url'] .');"></div>';
		}
		
	}

	/**
	 * Ouputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {

		$image_url = ( isset( $instance['image_url'] ) ) ? $instance['image_url'] : '';
		?>
		<p>
			<input type='button' class="button-primary hero4genesis_upload" id="<?php echo $this->get_field_id('uploader'); ?>" value="Upload Image" data-uploader_title="<?php _e( 'Choose Image', 'hero4genesis' ); ?>" data-uploader_button_text="<?php _e( 'Use Image', 'hero4genesis' ); ?>" />
			 <input type="button" class="button-secondary hero4genesis_remove_image" value="<?php _e( 'Remove Image', 'hero4genesis' );?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('image_url'); ?>"><?php _e('Image Url', 'hero4genesis'); ?></label><br />
			<input type="text" name="<?php echo $this->get_field_name('image_url'); ?>" id="<?php echo $this->get_field_id('image_url'); ?>" class="widefat hero4genesis_imageurl" value="<?php echo $image_url; ?>" />
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		// Fields
		$instance['image_url'] = strip_tags($new_instance['image_url']);

		return $instance;
	}
}

// register WPAUTBOX_Widget widget
function hero4genesis_image_widget() {
    register_widget( 'HERO4GENESIS_IMAGE' );
}
add_action( 'widgets_init', 'hero4genesis_image_widget' );
?>