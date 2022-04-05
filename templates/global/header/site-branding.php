<?php

/**
 * iPress - WordPress Theme Framework
 * ==========================================================
 *
 * Template for displaying the generic site logo.
 *
 * @see     https://codex.wordpress.org/Template_Hierarchy
 *
 * @package iPress\Templates
 * @link    http://ipress.uk
 * @license GPL-2.0+
 */
?>

<?php the_custom_logo(); ?>

<?php do_action( 'ipress_site_branding' ); // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterOpen
