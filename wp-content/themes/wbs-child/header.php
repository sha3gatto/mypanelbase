<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WBS_Child
 */

?>

<!DOCTYPE html>
<html <?php language_attributes();?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' );?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' );?>">
		<?php wp_head();?>
	</head>

	<body <?php body_class();?>>
		<div id="page" class="site">
			<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'wp-bootstrap-starter' );?></a>
			<?php if( ! is_page_template( 'blank-page.php' ) && ! is_page_template( 'blank-page-with-container.php' ) ):?>
				<header id="masthead" class="site-header navbar-static-top">
					<div class="container">
						<nav id="header-menu-nav" class="navbar navbar-expand-lg navbar-light p-0">
							<div class="navbar-header">
								<div class="logo navbar-brand">
									<?php if( get_theme_mod( 'wp_bootstrap_starter_logo' ) ):?>
									<a href="<?php echo esc_url( home_url( '/' ) );?>">
										<img src="<?php echo esc_attr(get_theme_mod( 'wp_bootstrap_starter_logo' )); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) );?>">
									</a>
									<?php else :?>
									<a class="site-title" href="<?php echo esc_url( home_url( '/' ) );?>"><?php esc_url( bloginfo( 'name' ) );?></a>
									<?php endif;?>
								</div>
								<button id="resp-navbar-btn" class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target=".navbar-collapse" aria-controls="bs4navbar" aria-expanded="false" aria-label="Toggle navigation">
									<span class="navbar-toggler-icon"></span>
								</button>
							</div>
							<?php
							if( ! function_exists( 'append_polylang_func' ) ) {
								add_filter( 'wp_nav_menu_items', 'append_polylang_func', 100, 2 );

								function append_polylang_func( $items, $args ) {
									if( ( is_object( $args ) && $args->theme_location == 'header-menu' ) || ( is_string( $args ) && $args = "wp_bootstrap_navwalker::fallback" ) ) {
										$items .= pll_the_languages( [ 'echo' => 0 ] );
									}
									return $items;
								}
							}
							?>
							<?php
							wp_nav_menu(array(
								'menu'            => 'top_menu',
								'theme_location'  => 'header-menu',
								'container'       => 'div',
								'container_id'    => 'header-menu-wrapper',
								'container_class' => 'collapse navbar-collapse justify-content-end',
								'menu_id'         => 'header-menu',
								'menu_class'      => 'navbar-nav',
								'depth'           => 2,
								'fallback_cb'     => 'wp_bootstrap_navwalker::fallback',
								'walker'          => new wp_bootstrap_navwalker()
								));
							?>
						</nav>
					</div>
				</header><!-- #masthead -->
				<?php if( is_front_page() ):?>
					<div id="promo-image">
						<div id="page-sub-header" class="promo" style="background-image: url('<?php echo get_bloginfo( 'wpurl' )?>/wp-content/uploads/2017/08/promo.jpg');">
							<div class="social-icon-wrapper">
								<nav>
									<?php wp_nav_menu( array( 'theme_location' => 'header-social-icons', 'menu_id' => 'social-icons' ) );?>
								</nav>
							</div>
						</div>
					</div>
				<?php endif;?>
				<div id="content" class="site-content">
					<div class="container">
						<div class="row">
						<?php endif;?>
