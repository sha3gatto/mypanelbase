<?php
/**
 * Template Name: Home
 *
 * @package WBS_Child
*/
?>
<?php
//Display the header
get_header();
//Display the page content/body
?>
<article id="post-<?php the_ID();?>" <?php post_class();?>>
	<div class="entry-content">
		<div id="mypanelbase">
			<div id="content-article">
				<div id="tagline">
					<h1><?php bloginfo( 'description' );?></h1>
					<h2><?php pll_e( 'Sign up for free, participate in paid surveys and make money online without leaving your home.' );?></h2>
				</div>
				<div id="rotator">
					<div id="rotator-left"></div>
					<div id="rotator-content">
						<div class="wrapper">
							<ul>
								<li>
									<div class="rotator-element-image">
										<img src="<?php echo get_bloginfo( 'wpurl' )?>/wp-content/uploads/2017/07/icon1-1.png" alt="<?php pll_e( 'icon sign up' );?>" width="91" height="92"/>
									</div>
									<div class="rotator-element-title"><?php pll_e( 'Sign up' );?></div>
									<div class="rotator-element-copy"><?php pll_e( 'Sign up for free and join the other panel members.' );?></div>
								</li>
								<li>
									<div class="rotator-element-image">
										<img src="<?php echo get_bloginfo( 'wpurl' )?>/wp-content/uploads/2017/07/icon2.png" alt="<?php pll_e( 'icon participate in surveys' );?>" width="91" height="92"/>
									</div>
									<div class="rotator-element-title"><?php pll_e( 'Participate in surveys' );?></div>
									<div class="rotator-element-copy"><?php pll_e( 'Participate in online paid surveys.' );?></div>
								</li>
								<li>
									<div class="rotator-element-image">
										<img src="<?php echo get_bloginfo( 'wpurl' )?>/wp-content/uploads/2017/07/icon3.png" alt="<?php pll_e( 'icon money' );?>" width="91" height="92"/>
									</div>
									<div class="rotator-element-title"><?php pll_e( 'Make money' );?></div>
									<div class="rotator-element-copy"><?php pll_e( 'Make money without leaving your home. On your terms.' );?></div>
								</li>
							</ul>
						</div>
					</div>
					<div id="rotator-right"></div>
				</div>
				<div id="prompt">
					<div id="dlaczego-warto">
						<h4 class="title">
							<span class="title-part-1"><?php pll_e( 'Why should you sign up?' );?></span>
							<?php pll_e( 'Find out reasons why you should join mypanelbase online survey panel' );?>
						</h4>
						<ul>
							<li><?php pll_e( 'Quick and free registration' );?></li>
							<li><?php pll_e( 'Participate in paid online surveys' );?></li>
							<li><?php pll_e( 'Share your opinion and support your favourite brands' );?></li>
							<li><?php pll_e( 'You earn money with each completed survey' );?></li>
							<li><?php pll_e( 'Your opinion is important to us' );?></li>
						</ul>
						<ul>
							<li><?php pll_e( 'Participate in paid surveys' );?></li>
							<li><?php pll_e( 'No obligations, you can opt-out at any time' );?></li>
							<li><?php pll_e( 'Only online surveys, we don’t send ads' );?></li>
							<li><?php pll_e( 'Make money without leaving your home, use your computer or tablet' );?></li>
							<li><?php pll_e( 'Support selected charity organizations' );?></li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
</article>
<?php
//Display the footer
get_footer();

?>
