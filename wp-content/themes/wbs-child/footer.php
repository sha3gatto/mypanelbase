<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WBS_Child
 */
?>
<?php if( ! is_page_template( 'blank-page.php' ) && ! is_page_template( 'blank-page-with-container.php' ) ):?>
	</div><!-- .row -->
	</div><!-- .container -->
	</div><!-- #content -->

	<footer class="site-footer">

		<div class="container">
			<div class="footer-content">
				<?php

				$mslBtn_PagesArray = [

					"login-0", "login-1", "anmelden", "login-3", "login-4", "logowanie", "вход", "login-7", "login-8", "login-9",
					"help-0", "help-1", "hilfe", "help-3", "help-4", "pomoc", "справка", "help-7", "help-8", "help-9" ];
				?>
				<?php if( is_front_page() || in_array( get_query_var( 'pagename' ), $mslBtn_PagesArray ) ) :?>
					<div class="mpb-button">
						<a href="http://mysurveylab.com" target="_blank" >
							<?php pll_e( 'Are you looking for online survey software? Go to mysurveylab.com' );?>
						</a>
					</div>
				<?php endif;?>
				<?php
				$darmowa_PagesArray = [ "help-0", "help-1", "hilfe", "help-3", "help-4", "pomoc", "справка", "help-7", "help-8", "help-9" ];

				?>
				<?php if( is_front_page() || in_array( get_query_var( 'pagename' ), $darmowa_PagesArray ) ) :?>
					<div id="darmowa-rejestracja">
						<h4 class="title"><?php pll_e( 'Sign up for free in mypanelbase.com. It will take just a few seconds.' );?></h4>
						<div class="mpb-button">
							<a href="<?php pll_e( 'https://panel.cint.com/69c24371-e69a-4f89-a8bc-fba2514e6104/panelists/new' );?>"><?php pll_e( 'FREE SIGN UP' );?></a>
						</div>
					</div>
				<?php endif;?>
			</div>
			<div class="clear"></div>
			<div class="footer-content">
				<div class="underline"></div>
				<nav id="footer-our-panels">
					<div id="our-panels-title"><?php pll_e( 'Our panels:' );?></div>
					<?php wp_nav_menu( array( 'theme_location' => 'footer-our-panels-menu' ) );?>
				</nav>
				<nav id="footer-menu">
					<?php wp_nav_menu( array( 'theme_location' => 'footer-menu' ) );?>
					<?php
						$url = 'http://7psh.com';
						$link = sprintf( wp_kses( pll__( '<a href="%s">7 Points</a>.' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
					?>
					<div id="copyrights"><?php printf( pll__( 'Copyright %04d by' ), date( 'Y' ) );?> <b><?php echo $link;?></b> <?php pll_e( 'All rights reserved.' );?></div>
				</nav>
			</div>
		</div>
	</footer>
<?php endif;?>
</div><!-- #page -->
<?php wp_footer();?>
</body>
</html>
