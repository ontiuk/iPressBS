<?php

/**
 * iPress - WordPress Theme Framework
 * ==========================================================
 *
 * Theme core & admin hooks - actions and filters
 *
 * @package iPress\Includes
 * @link    http://ipress.uk
 * @license GPL-2.0+
 */

// Deny unauthorised access
defined( 'ABSPATH' ) ||	exit;

if ( ! class_exists( 'IPR_Hooks' ) ) :

	/**
	 * Set up theme template hooks
	 */
	final class IPR_Hooks {

		/**
		 * Class constructor
		 */
		public function __construct() {

			//----------------------------------------------
			//	Core Hooks: Actions & Filters
			//----------------------------------------------

			// Modify custom logo css
			add_filter( 'get_custom_logo', [ $this, 'custom_logo' ], 10, 1 ); 

			// Modify nav link, css, id & attr
			add_filter( 'nav_menu_css_class', [ $this, 'nav_menu_css_class' ], 10, 2 );
			add_filter( 'nav_menu_item_id', [ $this, 'nav_menu_item_id' ], 10, 3 );
			add_filter( 'nav_menu_link_attributes', [ $this, 'nav_menu_link_attributes' ], 10, 3 );

			// Widget: Format category dropdown count
			add_filter( 'wp_list_categories', [ $this, 'category_list_count' ],	10, 1 );
				
			// Widget: Format archive list count
			add_filter( 'get_archives_link', [ $this, 'archive_list_count' ], 10, 6 );

			//----------------------------------------------
			//	Admin UI Hooks: Actions & Filters
			//----------------------------------------------

			// Admin: Add phone number to general settings
			add_action( 'admin_init', [ $this, 'register_setting' ], 10 );
		}

		//----------------------------------------------
		//  Core Hook Functions
		//----------------------------------------------

		/**
		 * Swap generic logo attribute for bootstrap navbar-brand
		 *
		 * @param string $str
		 * @return string
		 */
		public function custom_logo( $str ) {
			$str = str_replace( 'custom-logo-link', 'navbar-brand', $str );
			return str_replace( 'custom-logo', 'img-fluid', $str );
		}

		/**
		 * Trim injected nav classes
		 *
		 * @param array $classes
		 * @param object $item
		 * @return array $classes
		 */
		public function nav_menu_css_class( $classes, $item ) {

			// List of class keys
			$nav_classes = [
				'menu-item-type-post_type',
				'menu-item-type-taxonomy',
				'menu-item-type-custom',
				'menu-item-object-page',
				'menu-item-object-category',
				'menu-item-object-custom',
				'menu-item-has-children',
				'menu-item-home',
				'current_page_item',
				'current-menu-item',
				'page_item',
				'menu-item'
			];

			// Tidy up li classes
			foreach ( $nav_classes as $class ) {
				if ( false !== ( $key = array_search( $class, $classes ) ) ) {
					unset( $classes[$key] );
				}
			}

			// Additional numerical li classes
			foreach ( $classes as $k=>$v ) {
				if ( preg_match( '/page-item-(\d+)$/', $v ) ) { unset( $classes[$k] ); }
				if ( preg_match( '/menu-item-(\d+)$/', $v ) ) { unset( $classes[$k] ); }
				if ( preg_match( '/nav-item-(\d+)$/', $v ) ) { unset( $classes[$k] ); }
			}

			// Add custom classes, if needed
    		if ( ! in_array( 'nav-item', $classes ) ) {
				array_push( $classes, 'nav-item' );
    		}
			
			// Ok, compile returned class list
			return $classes;    
		}

		/**
		 * Remove nav item ID
		 *
		 * @param string $id
		 * @param object $item
		 * @return array $classes
		 */
		public function nav_menu_item_id( $id, $item, $classes ) {
			return '';
		}
		
		/**
		 * Add extra active class to list item
		 *  
		 * @param array $atts
		 * @param object $item
		 * @param array $args
		 * @return array $atts
		 */
		public function nav_menu_link_attributes( $atts, $item, $args ) {
			
			error_log( 'ATTR[' . print_r( $atts, true ) . '] ITEM[' . print_r( $item, true ) . ']' );

			// Set nav-link
			$atts['class'] = ( empty( $atts['class'] ) ) ? 'nav-link' : $atts['class'] . ' nav-link';
   
			// Set active link for current item
			$classes = empty( $item->classes ) ? [] : (array) $item->classes;
			if ( in_array( 'current-menu-item', $classes ) || ! empty( $item->current ) ) {
				$atts['class'] .= ' active';
			}

			return $atts;
		}

		/**
		 * Format category dropdown clount
		 *
		 * @param string $link
		 * @return string $link
		 */
		public function category_list_count( $link ) {
			return str_replace( '</a> (', '</a> <span>(', str_replace( ')', ')</span>', $link ) );
		}

		/**
		 * Format archive list count
		 * 
		 * @param	string $link
		 * @param	string $url
		 * @param	string $text
		 * @param	string $format
		 * @param	string $before
		 * @param	string $after
		 * @return	string $link
		 */
		public function archive_list_count( $link, $url, $text, $format, $before, $after ) {
			return str_replace( '&nbsp;(', '<span>(', str_replace( ')', ')</span>', $link ) );
		}

		//----------------------------------------------
		//  Admin UI Functions
		//----------------------------------------------

		/**
		 * Registers a custom field setting
		 */
		public function register_setting() {

			// Admin phone text field
			$args = [
				'type'              => 'string', 
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => null
			];

			// Register Admin phone field
			register_setting( 'general', 'admin_phone_number', $args ); 

			// Add Admin phone field
			add_settings_field (
				'admin_phone_number',
				'<label for="admin_phone_number">' . __( 'Admin Phone No.' , 'ipress-child' ) . '</label>',
				[ $this, 'admin_phone_number' ],
				'general'
			);
		} 

		/**
		 * Output custom admin phone field
		 */
		public function admin_phone_number() {
			$admin_phone = get_option( 'admin_phone_number' );
			echo sprintf( '<input type="text" id="admin_phone_number" name="admin_phone_number" class="regular-text ltr" value="%s" />', esc_attr( $admin_phone ) );
		}
	}

endif;

// Instantiate Hooks Class
return new IPR_Hooks;
