<?php

/**
 * iPress - WordPress Theme Framework
 * ==========================================================
 *
 * Template for displaying the generic site menu
 * - Bootstrap 5 Version using WP_Bootstrap_Navwalker
 *
 * @see     https://codex.wordpress.org/Template_Hierarchy
 *
 * @package iPress\Templates
 * @link    http://ipress.uk
 * @license GPL-2.0+
 */
?>

<nav id="site-navigation" class="navbar navbar-expand-lg navbar-light" role="navigation" aria-label="<?php echo esc_attr__( 'Primary Navigation', 'ipress-child' ); ?>">
    <div class="container-fluid g-0">
		<?php do_action( 'ipress_site_navigation_top' ); ?>

		<button class="navbar-toggler me-3" type="button" data-bs-toggle="collapse" data-bs-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
			<div class="hamburger-icon">
				<span class="hamburger-bar"></span>
				<span class="hamburger-bar"></span>
				<span class="hamburger-bar"></span>
			</div>
		</button>
			
		<div class="collapse navbar-collapse me-lg-3 main-navigation" id="main-menu">
			<?php
			wp_nav_menu( [
					'theme_location'    => 'primary', 
					'depth'				=> 2, 
					'container' 		=> false,
					'menu_class' 		=> 'navbar-nav mx-auto',
					'fallback_cb' 		=> '__return_false',
					'items_wrap' 		=> '<ul id="%1$s" class="%2$s">%3$s</ul>',
					'walker' 			=> new bootstrap_5_wp_nav_menu_walker()
			] );
			?>
		</div>
		<?php do_action( 'ipress_site_navigation' ); ?>
    </div>
</nav><!-- #site-navigation -->

<?php do_action( 'ipress_site_navigation_bottom' ); // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterOpen
