<?php

/**
 * iPress - WordPress Theme Framework
 * ==========================================================
 *
 * Set up and load child theme requirements.
 *
 * @package iPress\Bootstrap
 * @link    http://ipress.uk
 * @license GPL-2.0+
 */

// phpcs:disable

// Initialise hooks
do_action( 'ipress_child_bootstrap' );

//----------------------------------------------
//	Theme Defines
//----------------------------------------------

// Theme Name & Versioning: Change as required.
define( 'IPRESS_CHILD_THEME_NAME',  'iPress Child' );
define( 'IPRESS_CHILD_TEXT_DOMAIN', 'ipress-child' );
define( 'IPRESS_CHILD_THEME_WC',    6.0 ); // WooCommerce minimum version required.

// Directory Structure
define( 'IPRESS_CHILD_DIR',           get_theme_file_path() );
define( 'IPRESS_CHILD_ASSETS_DIR',    IPRESS_CHILD_DIR . '/assets' );
define( 'IPRESS_CHILD_INCLUDES_DIR',  IPRESS_CHILD_DIR . '/inc' );
define( 'IPRESS_CHILD_TEMPLATES_DIR', IPRESS_CHILD_DIR . '/templates' );
define( 'IPRESS_CHILD_LANG_DIR',      IPRESS_CHILD_DIR . '/languages' );

// Assets Directory Structure
define( 'IPRESS_CHILD_CSS_DIR',       IPRESS_CHILD_ASSETS_DIR . '/css' );
define( 'IPRESS_CHILD_JS_DIR',        IPRESS_CHILD_ASSETS_DIR . '/js' );
define( 'IPRESS_CHILD_IMAGES_DIR',    IPRESS_CHILD_ASSETS_DIR . '/images' );
define( 'IPRESS_CHILD_FONTS_DIR',     IPRESS_CHILD_ASSETS_DIR . '/fonts' );

// Includes Directory Structure
define( 'IPRESS_CHILD_LIB_DIR',       IPRESS_CHILD_INCLUDES_DIR . '/lib' );
define( 'IPRESS_CHILD_ADMIN_DIR',     IPRESS_CHILD_INCLUDES_DIR . '/admin' );
define( 'IPRESS_CHILD_CLASSES_DIR',   IPRESS_CHILD_INCLUDES_DIR . '/classes' );
define( 'IPRESS_CHILD_BLOCKS_DIR',    IPRESS_CHILD_INCLUDES_DIR . '/blocks' );
define( 'IPRESS_CHILD_FUNCTIONS_DIR', IPRESS_CHILD_INCLUDES_DIR . '/functions' );
define( 'IPRESS_CHILD_WIDGETS_DIR',   IPRESS_CHILD_INCLUDES_DIR . '/widgets' );

// Directory Paths
define( 'IPRESS_CHILD_URL',           get_theme_file_uri() );
define( 'IPRESS_CHILD_ASSETS_URL',    IPRESS_CHILD_URL . '/assets' );
define( 'IPRESS_CHILD_INCLUDES_URL',  IPRESS_CHILD_URL . '/inc' );
define( 'IPRESS_CHILD_LANG_URL',      IPRESS_CHILD_URL . '/languages' );

// Assets Directory Paths
define( 'IPRESS_CHILD_CSS_URL',       IPRESS_CHILD_ASSETS_URL . '/css' );
define( 'IPRESS_CHILD_JS_URL',        IPRESS_CHILD_ASSETS_URL . '/js' );
define( 'IPRESS_CHILD_IMAGES_URL',    IPRESS_CHILD_ASSETS_URL . '/images' );
define( 'IPRESS_CHILD_FONTS_URL',     IPRESS_CHILD_ASSETS_URL . '/fonts' );

// Includes Directory Paths
define( 'IPRESS_CHILD_LIB_URL',       IPRESS_CHILD_INCLUDES_URL . '/lib' );

//----------------------------------------------
//	Theme SetUp & Configuration
//----------------------------------------------

// Theme localization i18n - loads wp-content/themes/child-theme-name/languages/xx_XX.mo.
add_action( 'after_setup_theme', function() {
	load_child_theme_textdomain( 'ipress-child', get_stylesheet_directory() . '/languages' );
}, 8 );

//------------------------------------------------------
//	Includes - Functions, Blocks, Template
//------------------------------------------------------

// Functions
require_once IPRESS_CHILD_INCLUDES_DIR . '/functions.php';

// Blocks: custom guttenberg blocks
require_once IPRESS_CHILD_INCLUDES_DIR . '/blocks.php';

// Functions: theme template hooks & functions
require_once IPRESS_CHILD_INCLUDES_DIR . '/template-hooks.php';
require_once IPRESS_CHILD_INCLUDES_DIR . '/template-functions.php';

//----------------------------------------------
//	Init
//----------------------------------------------

// Initialization
do_action( 'ipress_child_init' );

// Initiate Main Registry, Scripts & Styles
$ipress = (object)[];

// Register theme settings via customizer
$ipress->settings = require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-settings.php';

//----------------------------------------------
//	Includes - Classes
//----------------------------------------------

// Theme hooks & functionality
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-hooks.php';

// Ajax Functionality
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-ajax.php';

// REST API functionality
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-api.php';

// Scheduled cron tasks
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-cron.php';

// Query modification
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-query.php';

// Redirect functionality
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-redirect.php';

// Rewrite rules & query vars
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-rules.php';

// Register theme setup requirements
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-startup.php';

// Template includes & redirects
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-template.php';

// User functionality
require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-user.php';

// Admin UI functionality
if ( is_admin() ) {
	require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-admin.php';
}

//----------------------------------------------
//	Libraries & Plugins
//----------------------------------------------

// Advanced Custom Fields active?
if ( ipress_acf_plugin_active() ) {
	require_once IPRESS_CHILD_CLASSES_DIR . '/class-ipr-acf.php';
}

// WooCommerce active?
if ( ipress_wc_active() ) {

	// Do versioning check
	if ( ipress_wc_version_check( IPRESS_CHILD_THEME_WC ) ) {

		// Main WooCommerce class
		$ipress->woocommerce = require_once IPRESS_CHILD_INCLUDES_DIR . '/woocommerce/class-ipr-woocommerce.php';

		// WooCommerce customizer functionality
		$ipress->woocommerce_customizer = require IPRESS_CHILD_INCLUDES_DIR . '/woocommerce/class-ipr-woocommerce-customizer.php';

		// Include WooCommerce REST API functionality
		require IPRESS_CHILD_INCLUDES_DIR . '/woocommerce/class-ipr-woocommerce-api.php';

		// WooCommerce product pagination functionality
		require IPRESS_CHILD_INCLUDES_DIR . '/woocommerce/class-ipr-woocommerce-adjacent-products.php';
		require IPRESS_CHILD_INCLUDES_DIR . '/woocommerce/functions.php';

		// WooCommerce template hooks & functions
		require IPRESS_CHILD_INCLUDES_DIR . '/woocommerce/template-hooks.php';
		require IPRESS_CHILD_INCLUDES_DIR . '/woocommerce/template-functions.php';
	} else {
		add_action( 'admin_notices', ipress_wc_version_notice() );
	}
}

// Bootstrap Navwalkers
require_once IPRESS_CHILD_LIB_DIR . '/class-bootstrap-5-wp-nav-menu-walker.php';
require_once IPRESS_CHILD_LIB_DIR . '/class-wp-bootstrap-navwalker.php';

//TSS Temp
require_once IPRESS_CHILD_LIB_DIR . '/class-tss-wp-nav-menu-walker.php';

//----------------------------------------------
//	Theme Settings & Configuration
//----------------------------------------------

// Configuration
do_action( 'ipress_child_config' );

// Theme Setup Configuration: actions, filters etc
include_once IPRESS_CHILD_INCLUDES_DIR . '/config.php';
