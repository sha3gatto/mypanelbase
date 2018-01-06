<?php

add_action( 'genesis_after_header', 'do_hero4genesis', 99 );
function do_hero4genesis(){
	$general_settings = (array) get_option( 'hero4genesis_general_settings' );
	$class = array( 'hero4genesis-container' );
	if( isset( $general_settings['full'] ) ){
		$class[] = 'hero4genesis-full';
	}
	if( isset( $general_settings['behind'] ) ){
		$class[] = 'hero4genesis-behind';
	}
	if( isset( $general_settings['alignment'] ) ){
		$class[] = 'hero4genesis-align-'. $general_settings['alignment'];
	}
	$class = apply_filters( 'hero4genesis_class', $class );
	?>
	<div class="<?php echo implode( ' ', $class );?>">
		<?php do_action( 'before_hero4genesis_content' ); ?>
		<div class="site-inner">
			<?php do_action( 'before_hero4genesis_inner' ); ?>
			<div class="hero4genesis-inner">
				<?php
					do_action( 'before_hero4genesis_widget' );
					if(!dynamic_sidebar('hero-genesis-widget')): endif;
					do_action( 'after_hero4genesis_widget' );
				?>
			</div>
			<?php do_action( 'after_hero4genesis_inner' ); ?>
			<div class="clearfix"></div>
		</div>
		<?php do_action( 'after_hero4genesis_content' ); ?>
	</div>
	<?php
}

// Add specific CSS class by filter
add_filter( 'body_class', 'hero4genesis_body_class' );
function hero4genesis_body_class( $classes ) {
	$general_settings = (array) get_option( 'hero4genesis_general_settings' );
	if( isset( $general_settings['behind'] ) ){
		$classes[] = 'hero4genesis-behind-header';
	}
	return $classes;
}

//sample action
// remove_action( 'genesis_after_header', 'do_hero4genesis', 99 );
// add_action( 'genesis_before_footer', 'do_hero4genesis', 99 );

//sample filter
// add_filter('hero4genesis_class', 'sample_f');
// function sample_f($class){
// 	if( is_page() && !is_front_page() ){
// 		$key = array_search('hero4genesis-full', $class);
// 		unset($class[ $key ]);
// 	}
// 	return $class;
// }

//frontpage only
// add_action( 'genesis_header', 'modify_action', 99 );
// function modify_action(){
// 	if( !is_front_page() ){
// 		remove_action( 'genesis_after_header', 'do_hero4genesis', 99 );
// 	}
// }
?>