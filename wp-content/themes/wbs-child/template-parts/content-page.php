<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WP_Bootstrap_Starter
 */

?>
<article id="post-<?php the_ID();?>" <?php post_class();?>>
	<div class="entry-content">
		<div id="mypanelbase">
			<div id="content-article">
				<div id="pomoc">
					<?php the_content();?>
				</div>
			</div>
		</div>
	</div>
</article>