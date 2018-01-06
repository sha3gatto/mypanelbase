<?php get_header();?>
<article id="post-<?php the_ID();?>" <?php post_class();?>>
	<div class="entry-content">
		<div id="mypanelbase">
			<div id="content-article">
				<div id="pomoc">
					<?php $latest_blog_posts = new WP_Query( array( 'posts_per_page' => 1 ) );?>
					<?php if( $latest_blog_posts->have_posts() ) :?>
						<?php while( $latest_blog_posts->have_posts() ) : $latest_blog_posts->the_post();?>
							<?php the_content();?>
							<?php the_post_navigation();?>
							<?php if( comments_open() || get_comments_number() ) :
								comments_template();
							endif;?>
						<?php endwhile;?>
					<?php endif;?>
				</div>
			</div>
		</div>
	</div>
</article>
<?php
get_footer();