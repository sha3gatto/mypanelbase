<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package WBS_Child
 */
get_header();
?>
<div class="entry-content">
	<div id="mypanelbase">
		<div id="content-article">
			<div id="pomoc">
				<section class="error-404 not-found">
					<header class="page-header">
						<h1 class="page-title"><?php pll_e( 'That page can&rsquo;t be found.' );?></h1>
					</header><!-- .page-header -->
					<div class="page-content">
						<p><?php pll_e( 'It looks like nothing was found at this location.' );?></p>
						<a href="<?php echo esc_url( home_url( '/' ) );?>"><?php pll_e( 'Back to home page.' );?></a>
					</div><!-- .page-content -->
				</section><!-- .error-404 -->
			</div>
		</div>
	</div>
</div>
<?php
get_footer();
