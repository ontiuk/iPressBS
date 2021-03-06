<?php

/**
 * iPress - WordPress Theme Framework
 * ==========================================================
 *
 * Theme settings functionality via customizer.
 *
 * @package iPress\Includes
 * @link    http://ipress.uk
 * @license GPL-2.0+
 */

// Deny unauthorised access
defined( 'ABSPATH' ) ||	exit;

if ( ! class_exists( 'IPR_Settings' ) ) :

	/**
	 * Set up ajax features
	 */
	final class IPR_Settings {

		/**
		 * Class constructor
		 */
		public function __construct() {

			// Hook into parent theme settings
			add_action( 'ipress_customize_register', [ $this, 'parent_customize_register' ] );

			// Theme customizer custom JS registration
			add_action( 'customize_register', [ $this, 'customize_register_js' ] );

			// Theme customizer api registration
			add_action( 'customize_register', [ $this, 'customize_register_theme' ] );

			// Theme customizer api registration
			add_action( 'customize_register', [ $this, 'customize_register_hero' ] );

			// Theme customizer api registration
			add_action( 'customize_register', [ $this, 'customize_register_partials' ], 12 );

			// Enqueue scripts for customizer controls and settings
			add_action( 'customize_controls_enqueue_scripts', [ $this, 'customize_controls_enqueue_scripts' ] );

			// Enqueue scripts for customizer preview
			add_action( 'customize_preview_init', [ $this, 'customize_preview_init' ] );

			// Output custom hero css to header
			add_action( 'wp_head', [ 'IPR_Settings', 'hero_css_output' ] );

			// Add custom hero image size
			add_filter( 'ipress_add_image_size', [ $this, 'hero_image_size' ], 10, 1 );
		}

		//----------------------------------------------
		//	Customizer Settings Actions
		//----------------------------------------------

		/**
		 * Register customizer settings via parent theme action
		 *
		 * @param object $wp_customize WP_Customise_Manager
		 */
		public function parent_customize_register( WP_Customize_Manager $wp_customize ) {

			// Custom background & header usage?
			$ip_custom_background = (bool) apply_filters( 'ipress_custom_background', false );
			$ip_custom_header     = (bool) apply_filters( 'ipress_custom_header', false );

			// Modify background section if custom background available
			if ( true === $ip_custom_background ) {

				// Change background image section title & priority if custom background image is active
				$wp_customize->get_section( 'background_image' )->title    = __( 'Background', 'ipress-child' );
				$wp_customize->get_section( 'background_image' )->priority = 30;

				// Move background color setting alongside background image if custom background image is active
				$wp_customize->get_control( 'background_color' )->section  = 'background_image';
				$wp_customize->get_control( 'background_color' )->priority = 20;
			}

			// Change header image section title & priority if custom header image is active
			if ( true === $ip_custom_header ) {
				$wp_customize->get_section( 'header_image' )->title    = __( 'Header', 'ipress-child' );
				$wp_customize->get_section( 'header_image' )->priority = 25;
			}

			// Change the default priority if custom header or background is available
			if ( true === $ip_custom_header || true === $ip_custom_background ) {
				$wp_customize->get_section( 'colors' )->priority = 90;
			}

			// Add a footer/copyright information section.
			$wp_customize->add_section(
				'ipress_footer',
				[
					'title'       => __( 'Footer', 'ipress-child' ),
					'description' => esc_html__( 'Footer content', 'ipress-child' ),
					'priority'    => 150, // Before Default & After Front Page.
				]
			);

			// Add "display_title_and_tagline" setting for displaying the site-title & tagline.
			$wp_customize->add_setting(
				'ipress_title_and_tagline',
				[
					'capability'        => 'edit_theme_options',
					'default'           => true,
					'sanitize_callback' => [ $this, 'sanitize_checkbox' ],			
				]
			);

			// Add control for the "display_title_and_tagline" setting.
			$wp_customize->add_control(
				'ipress_title_and_tagline',
				[
					'type'    => 'checkbox',
					'section' => 'title_tagline',
					'label'   => esc_html__( 'Display Site Title & Tagline', 'ipress-child' ),
				]
			);

			// Plugable registrations - pass customizer manager object to child theme settings filter
			do_action( 'ipress_parent_customize_register', $wp_customize );
		}

		/**
		 * Set up customizer custom JS settings
		 *
		 * @param object $wp_customize WP_Customise_Manager
		 */
		public function customize_register_js( WP_Customize_Manager $wp_customize ) {

			// Filterable custom JS sections
			$ip_custom_js = (bool) apply_filters( 'ipress_custom_js', false );
			if ( true !== $ip_custom_js ) {
				return;
			}

			// Add section for additional scripts: header & footer
			$wp_customize->add_section(
				'ipress_custom_js',
				[
					'title'       => __( 'Additional JS', 'ipress-child' ),
					'description' => esc_html__( 'Add custom header & footer js.', 'ipress-child' ),
					'priority'    => 210,
				]
			);

			// Add settings for custom header scripts section
			$wp_customize->add_setting(
				'ipress_header_js',
				[
					'transport'         => 'refresh',
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'wp_kses_post',
				]
			);

			// Add control for custom header scripts section
			$wp_customize->add_control(
				new WP_Customize_Code_Editor_Control(
					$wp_customize,
					'ipress_header_js_html',
					[
						'label'       => __( 'Custom header JS', 'ipress-child' ),
						'description' => esc_html__( 'Custom inline header js. Exclude <script></script> tag.', 'ipress-child' ),
						'code_type'   => 'javascript',
						'section'     => 'ipress_custom_js',
						'settings'    => 'ipress_header_js',
						'priority'    => 10,
					]
				)
			);

			// Add settings and controls for custom scripts section
			$wp_customize->add_setting(
				'ipress_footer_js',
				[
					'transport'         => 'refresh',
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'wp_kses_post',
				]
			);

			// Add control for custom footer scripts section
			$wp_customize->add_control(
				new WP_Customize_Code_Editor_Control(
					$wp_customize,
					'ipress_footer_js_html',
					[
						'label'       => __( 'Custom footer JS', 'ipress-child' ),
						'description' => esc_html__( 'Custom inline footer js. Exclude <script></script> tag.', 'ipress-child' ),
						'code_type'   => 'javascript',
						'section'     => 'ipress_custom_js',
						'settings'    => 'ipress_footer_js',
						'priority'    => 12,
					]
				)
			);

			// Add settings and controls for custom scripts section
			$wp_customize->add_setting(
				'ipress_header_admin_js',
				[
					'transport'         => 'refresh',
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'wp_kses_post',
				]
			);

			// Add controls for custom header admin scripts section
			$wp_customize->add_control(
				new WP_Customize_Code_Editor_Control(
					$wp_customize,
					'ipress_header_admin_js_html',
					[
						'label'       => __( 'Custom admin header JS', 'ipress-child' ),
						'description' => esc_html__( 'Custom inline admin header js. Exclude <script></script> tag.', 'ipress-child' ),
						'code_type'   => 'javascript',
						'section'     => 'ipress_custom_js',
						'settings'    => 'ipress_header_admin_js',
						'priority'    => 14,
					]
				)
			);

			// Add settings and controls for custom scripts section
			$wp_customize->add_setting(
				'ipress_footer_admin_js',
				[
					'transport'         => 'refresh',
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'sanitize_callback' => 'wp_kses_post',
				]
			);

			// Add controls for custom footer admin scripts section
			$wp_customize->add_control(
				new WP_Customize_Code_Editor_Control(
					$wp_customize,
					'ipress_footer_admin_js_html',
					[
						'label'       => __( 'Custom admin footer JS', 'ipress-child' ),
						'description' => esc_html__( 'Custom inline admin footer js. Exclude <script></script> tag.', 'ipress-child' ),
						'code_type'   => 'javascript',
						'section'     => 'ipress_custom_js',
						'settings'    => 'ipress_footer_admin_js',
						'priority'    => 16,
					]
				)
			);

			// Plugable registrations - pass customizer manager object to child theme settings filter
			do_action( 'ipress_customize_register_js', $wp_customize );
		}

		/**
		 * Set up customizer and theme specific settings
		 * - Fonts & typography
		 * - Background & header colours
		 * - Button and text colours
		 *
		 * @param object $wp_customize WP_Customise_Manager
		 */
		public function customize_register_theme( WP_Customize_Manager $wp_customize ) {

			// Define settings & partials based on if selective refresh is active
			$transport = ( $wp_customize->selective_refresh ) ? 'postMessage' : 'refresh';

			// Add the primary theme section. Won't show until settings & controls added
			$wp_customize->add_section(
				'ipress_theme',
				[
					'title'       => __( 'Theme', 'ipress-child' ),
					'description' => esc_html__( 'Add theme specific settings.', 'ipress-child' ),
					'capability'  => 'edit_theme_options',
					'priority'    => 250,
				]
			);

			// ----------------------------------------------
			// Theme setting: breadcrumbs
			// ----------------------------------------------

			// Add setting for breadcrumbs
			$wp_customize->add_setting(
				'ipress_breadcrumbs',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => false,
					'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
				]
			);

			// Add checkbox control for breadcrumbs setting
			$wp_customize->add_control(
				'ipress_breadcrumbs',
				[
					'label'       => __( 'Breadcrumbs', 'ipress-child' ),
					'description' => esc_html__( 'Display or hide the inner page breadcrumbs.', 'ipress-child' ),
					'type'        => 'checkbox',
					'section'     => 'ipress_theme',
					'priority'    => 20,
				]
			);

			// Section separator
			$wp_customize->add_setting(
				'ipress_breadcrumbs_sep',
				[ 'sanitize_callback' => '__return_true' ]
			);

			$wp_customize->add_control(
				new IPR_Separator_Control(
					$wp_customize,
					'ipress_breadcrumbs_sep',
					[
						'section'  => 'ipress_theme',
						'priority' => 25,
					]
				)
			);

			// ----------------------------------------------
			// Theme settings
			// ----------------------------------------------

			// Plugable registrations - pass customizer manager object to child theme settings filter
			do_action( 'ipress_customize_register_theme', $wp_customize );
		}

		/**
		 * Set up customizer hero section and settings
		 *
		 * @param object $wp_customize WP_Customise_Manager
		 */
		public function customize_register_hero( WP_Customize_Manager $wp_customize ) {

			// Does the theme utilise a front-page hero section? Default, true
			$ip_custom_hero = (bool) apply_filters( 'ipress_custom_hero', true );
			if ( true !== $ip_custom_hero ) {
				return;
			}

			// Define settings & partials based on if selective refresh is active
			$transport = ( $wp_customize->selective_refresh ) ? 'postMessage' : 'refresh';

			// Add front page hero. Most themes will have this, block above if not required
			$wp_customize->add_section(
				'ipress_hero',
				[
					'title'           => __( 'Hero', 'ipress-child' ),
					'description'     => esc_html__( 'Front page hero image and details', 'ipress-child' ),
					'priority'        => 260,
					'active_callback' => function() use ( $wp_customize ) {
						return ( is_front_page() && true === $wp_customize->get_setting( 'ipress_hero' )->value() );
					},
				]
			);

			// Add setting for frontpage hero section
			$wp_customize->add_setting(
				'ipress_hero',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => false,
					'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
				]
			);

			// Add checkbox control for frontpage hero section
			$wp_customize->add_control(
				'ipress_hero',
				[
					'label'       => __( 'Front Page Hero Area', 'ipress-child' ),
					'description' => esc_html__( 'Display or hide the front page hero section.', 'ipress-child' ),
					'type'        => 'checkbox',
					'section'     => 'ipress_theme',
					'settings'    => 'ipress_hero',					
					'priority'    => 10,
				]
			);

			// Section separator
			$wp_customize->add_setting(
				'ipress_hero_sep',
				[ 'sanitize_callback' => '__return_true' ]
			);

			$wp_customize->add_control(
				new IPR_Separator_Control(
					$wp_customize,
					'ipress_hero_sep',
					[
						'section'  => 'ipress_theme',
						'priority' => 15,
					]
				)
			);

			// ----------------------------------------------
			// Set up Hero settings, modify as appropriate
			// ----------------------------------------------

			// Add setting for hero title
			$wp_customize->add_setting(
				'ipress_hero_title',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				]
			);

			// Add text control for hero title
			$wp_customize->add_control(
				'ipress_hero_title',
				[
					'label'       => __( 'Hero Title', 'ipress-child' ),
					'description' => esc_html__( 'Modify the front page hero section title', 'ipress-child' ),
					'section'     => 'ipress_hero',
					'settings'    => 'ipress_hero_title',
					'type'        => 'text',
					'priority'    => 10,
				]
			);

			// Add setting & control for hero description
			$wp_customize->add_setting(
				'ipress_hero_description',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => '',
					'sanitize_callback' => 'sanitize_textarea_field',
				]
			);

			// Add control for hero
			$wp_customize->add_control(
				'ipress_hero_description',
				[
					'label'       => __( 'Hero Description', 'ipress-child' ),
					'description' => esc_html__( 'Modify the front page hero section description', 'ipress-child' ),
					'section'     => 'ipress_hero',
					'settings'    => 'ipress_hero_description',
					'type'        => 'textarea',
					'priority'    => 12,
				]
			);

			// Add setting for button page link
			$wp_customize->add_setting(
				'ipress_hero_button_link',
				[
					'transport'         => 'none',
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => '',
					'sanitize_callback' => 'absint',
				]
			);

			// Add control for hero button page link
			$wp_customize->add_control(
				'ipress_hero_button_link',
				[
					'label'       => __( 'Button Page Link', 'ipress-child' ),
					'description' => esc_html__( 'Link to page via button', 'ipress-child' ),
					'section'     => 'ipress_hero',
					'settings'    => 'ipress_hero_button_link',
					'type'        => 'dropdown-pages',
					'priority'    => 14,
				]
			);

			// Add setting for hero button text
			$wp_customize->add_setting(
				'ipress_hero_button_text',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => esc_attr__( 'Learn More', 'ipress-child' ),
					'sanitize_callback' => 'sanitize_text_field',
				]
			);

			// Add control for hero button text
			$wp_customize->add_control(
				'ipress_hero_button_text',
				[
					'label'       => __( 'Hero Button Text', 'ipress-child' ),
					'description' => esc_html__( 'Hero button text', 'ipress-child' ),
					'section'     => 'ipress_hero',
					'settings'    => 'ipress_hero_button_text',
					'type'        => 'text',
					'priority'    => 16,
				]
			);

			// Add setting & control for hero image
			$wp_customize->add_setting(
				'ipress_hero_image',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => IPRESS_CHILD_IMAGES_URL . '/hero.svg',
					'sanitize_callback' => [ $this, 'sanitize_image' ],
				]
			);

			// Add control for hero image
			$wp_customize->add_control(
				new WP_Customize_Cropped_Image_Control(
					$wp_customize,
					'ipress_hero_image',
					[
						'label'       => __( 'Hero Image', 'ipress-child' ),
						'description' => esc_html__( 'Add the hero section background image', 'ipress-child' ),
						'section'     => 'ipress_hero',
						'settings'    => 'ipress_hero_image',
						'context'     => 'hero-image',
						'flex_width'  => true,
						'flex_height' => true,
						'width'       => 1240,
						'height'      => 480,
						'priority'    => 18,
					]
				)
			);

			// Add setting for hero background color
			$wp_customize->add_setting(
				'ipress_hero_background_color',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => '#c3f2f5',
					'sanitize_callback' => 'sanitize_hex_color',
				]
			);

			// Add control for hero background color
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'ipress_hero_background_color',
					[
						'label'       => __( 'Hero Background Color', 'ipress-child' ),
						'description' => esc_html__( 'Hero background color', 'ipress-child' ),
						'section'     => 'ipress_hero',
						'settings'    => 'ipress_hero_background_color',
						'priority'    => 20,
					]
				)
			);

			// Add setting & control for hero image overlay
			$wp_customize->add_setting(
				'ipress_hero_overlay',
				[
					'transport'         => 'refresh',
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => false,
					'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
				]
			);

			// Add control for hero overlay
			$wp_customize->add_control(
				'ipress_hero_overlay',
				[
					'label'       => __( 'Hero Overlay', 'ipress-child' ),
					'description' => esc_html__( 'Display an overlay with opacity on the hero image.', 'ipress-child' ),
					'section'     => 'ipress_hero',
					'settings'    => 'ipress_hero_overlay',
					'type'		  => 'checkbox',
					'priority'    => 22,
				]
			);

			// Add setting & control for hero overlay color
			$wp_customize->add_setting(
				'ipress_hero_overlay_color',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => '#000',
					'sanitize_callback' => 'sanitize_hex_color',
				]
			);

			// Add control for hero overlay color
			$wp_customize->add_control(
				new WP_Customize_Color_Control(
					$wp_customize,
					'ipress_hero_overlay_color',
					[
						'label'       => __( 'Hero Overlay Color', 'ipress-child' ),
						'description' => esc_html__( 'Hero overlay color', 'ipress-child' ),
						'section'     => 'ipress_hero',
						'settings'    => 'ipress_hero_overlay_color',
						'priority'    => 24,
					]
				)
			);

			// Add setting for hero overlay opacity
			$wp_customize->add_setting(
				'ipress_hero_overlay_opacity',
				[
					'transport'         => $transport,
					'type'              => 'theme_mod',
					'capability'        => 'edit_theme_options',
					'default'           => '80',
					'sanitize_callback' => 'absint',
					'validate_callback' => function ( $validity, $value ) {
						$value = intval( $value );
						if ( $value < 0 || $value > 100 ) {
							$validity->add( 'out_of_range', __( 'Value must be between 0 and 100', 'ipress-child' ) );
						}
						return $validity;
					},
				]
			);

			// Add control for hero overlay opacity
			$wp_customize->add_control(
				'ipress_hero_overlay_opacity',
				[
					'label'       => __( 'Hero Overlay Opacity', 'ipress-child' ),
					'description' => esc_html__( 'Hero overlay opacity', 'ipress-child' ),
					'section'     => 'ipress_hero',
					'settings'    => 'ipress_hero_overlay_opacity',
					'type'        => 'range',
					'input_attrs' => [
						'min'   => 0,
						'max'   => 100,
						'step'  => 5,
						'style' => 'width: 100%',
					],
					'priority'    => 26,
				]
			);

			// Plugable registrations - pass customizer manager object to child theme settings filter
			do_action( 'ipress_customize_register_hero', $wp_customize );
		}

		/**
		 * Set up customizer and theme partials
		 * - Hero
		 *
		 * @param object $wp_customize WP_Customise_Manager
		 */
		public function customize_register_partials( WP_Customize_Manager $wp_customize ) {

			// Abort if selective refresh is not available.
			if ( ! isset( $wp_customize->selective_refresh ) ) {
				return;
			}

			// Does the theme utilise a front-page hero section? Default, true
			$ip_custom_hero = (bool) apply_filters( 'ipress_custom_hero', true );
			if ( true === $ip_custom_hero ) {

				// Hero title markup
				$wp_customize->selective_refresh->add_partial(
					'ipress_hero_title',
					[
						'selector'        => '.hero-title',
						'settings'        => 'ipress_hero_title',
						'render_callback' => function() {
							return get_theme_mod( 'ipress_hero_title' );
						},
					]
				);

				// Hero description markup
				$wp_customize->selective_refresh->add_partial(
					'ipress_hero_description',
					[
						'selector'        => '.hero-description',
						'settings'        => 'ipress_hero_description',
						'render_callback' => function() {
							return get_theme_mod( 'ipress_hero_description' );
						},
					]
				);

				// Hero image markup
				$wp_customize->selective_refresh->add_partial(
					'ipress_hero_image',
					[
						'selector'        => '.hero-image img',
						'settings'        => 'ipress_hero_image',
						'render_callback' => self::hero_image(),
					]
				);

				// Hero section background colour
				$wp_customize->selective_refresh->add_partial(
					'ipress_hero_background_color',
					[
						'selector'        => '#hero-css',
						'settings'        => 'ipress_hero_background_color',
						'render_callback' => self::hero_css(),
					]
				);

				// Hero section button text
				$wp_customize->selective_refresh->add_partial(
					'ipress_hero_button_text',
					[
						'selector'        => '.hero .button',
						'settings'        => 'ipress_hero_button_text',
						'render_callback' => function() {
							return get_theme_mod( 'ipress_hero_button_text' );
						},
					]
				);

				// Hero section overlay colour
				$wp_customize->selective_refresh->add_partial(
					'ipress_hero_overlay_color',
					[
						'selector'        => '#hero-css',
						'settings'        => 'ipress_hero_overlay_color',
						'render_callback' => self::hero_css(),
					]
				);

				// Hero section overlay opacity
				$wp_customize->selective_refresh->add_partial(
					'ipress_hero_overlay_opacity',
					[
						'selector'        => '#hero-css',
						'settings'        => 'ipress_hero_overlay_opacity',
						'render_callback' => self::hero_css(),
					]
				);
			}

			// Plugable registrations - pass customizer manager object to child theme settings filter
			do_action( 'ipress_customize_register_partials', $wp_customize );
		}

		//----------------------------------------------
		// Load Control and Preview Scripts & Styles
		//----------------------------------------------

		/**
		 * Enqueue scripts for the customizer settings & controls
		 */
		public function customize_controls_enqueue_scripts() {
			$theme_version = wp_get_theme()->get( 'Version' );
			wp_enqueue_script( 'ipress-customize', IPRESS_CHILD_JS_URL . '/customize.js', [ 'jquery' ], $theme_version, false );
			wp_enqueue_script( 'ipress-customize-controls', IPRESS_CHILD_JS_URL . '/customize-controls.js', [ 'customize-controls', 'underscore', 'jquery' ], $theme_version, false );
		}

		/**
		 * Enqueue scripts for the customizer preview
		 */
		public function customize_preview_init() {
			$theme_version = wp_get_theme()->get( 'Version' );
			wp_enqueue_script( 'ipress-customize-preview', IPRESS_CHILD_JS_URL . '/customize-preview.js', [ 'customize-preview', 'customize-selective-refresh', 'jquery' ], $theme_version, true );
		}

		//----------------------------------------------
		// Additional Control Sanitization Functions
		//----------------------------------------------

		/**
		 * Sanitize select
		 *
		 * @param string $input The input from the setting
		 * @param object $setting The selected setting
		 * @return string $input|$setting->default The input from the setting or the default setting
		 */
		public function sanitize_select( $input, $setting ) {
			$input   = sanitize_key( $input );
			$choices = $setting->manager->get_control( $setting->id )->choices;
			return ( array_key_exists( $input, $choices ) ) ? $input : $setting->default;
		}

		/**
		 * Sanitize boolean for checkbox
		 *
		 * @param bool $checked Whether or not a box is checked
		 * @return bool
		 */
		public function sanitize_checkbox( $checked ) {
			return ( isset( $checked ) && true === $checked );
		}

		/**
		 * Sanitize integer for image ID
		 *
		 * @param integer $image
		 * @param object $setting The selected setting
		 * @return integer
		 */
		public function sanitize_image( $image, $setting ) {
			return intval( $image );
		}

		//----------------------------------------------
		// Additional Control Validation Functions
		//----------------------------------------------

		/**
		 * Validate categories list
		 *
		 * @param object $validity
		 * @param string $categories
		 * @return bool $validity
		 */
		public function validate_categories( $validity, $categories ) {

			// Must be format xx,xx,xx +/- spaces
			if ( ! preg_match( '/^[\d\s,]+$/', $categories ) ) {
				$validity->add( 'categories_format', __( 'Comma separated list of category IDs only.', 'ipress-child' ) );
			}
			return $validity;
		}

		//----------------------------------
		// Hero Images & Background
		//----------------------------------

		/**
		 * Retrieve hero image if set
		 *
		 * @return string
		 */
		public static function hero_image() {

			// Get hero image if set
			$ip_hero_image_id = (int) get_theme_mod( 'ipress_hero_image' );

			if ( $ip_hero_image_id > 0 ) {

				// Hero image details
				$hero_image     = wp_get_attachment_image_src( $ip_hero_image_id, 'hero-image' );
				$hero_image_alt = trim( strip_tags( get_post_meta( $ip_hero_image_id, '_wp_attachment_image_alt', true ) ) );

				// Reconstruct image params
				list( $hero_image_src, $hero_image_width, $hero_image_height ) = $hero_image;

				// Set hero image class
				$ip_hero_image_class = apply_filters( 'ipress_hero_image_class', '' );

				// Set hero image
				$hero_image_hw = image_hwstring( $hero_image_width, $hero_image_height );

				return sprintf( '<img class="%1$s" src="%2$s" %3$s alt="%4$s" />', $ip_hero_image_class, $hero_image_src, trim( $hero_image_hw ), $hero_image_alt );
			}

			return false;
		}

		/**
		 * Add custom hero image size
		 *
		 * @param array $sizes
		 * @return array $sizes
		 */
		public function hero_image_size( $sizes ) {
			$ip_custom_hero = (bool) apply_filters( 'ipress_custom_hero', true );
			if ( true === $ip_custom_hero ) {
				$sizes['hero-image'] = [ 1080 ];
			}
			return $sizes;
		}

		//----------------------------------
		// Hero CSS
		//----------------------------------

		/**
		 * Generate css partials for Hero section
		 *
		 * @return string
		 */
		public static function hero_css() {

			// Initiate output: hero
			$output  = self::css( '.hero', 'background-color', 'ipress_hero_background_color' );
			$output .= self::css( '.hero-overlay', 'background-color', 'ipress_hero_overlay_color' );
			$output .= self::opacity( '.hero-overlay', 'opacity', 'ipress_hero_overlay_opacity' );

			return $output;
		}

		/**
		 * Generate css by selector & theme mod
		 *
		 * @uses get_theme_mod()
		 * @param string $selector CSS selector
		 * @param string $property The name of the identitier to modify
		 * @param string $theme_mod The name of the 'theme_mod' option to fetch
		 * @return string|void
		 */
		public static function css( $selector, $property, $theme_mod ) {

			// Get the theme mod value, with empty default
			$theme_mod = get_theme_mod( $theme_mod, '' );

			// Return value?
			return ( empty( $theme_mod ) ) ? '' : sprintf( '%s { %s:%s; }', $selector, $property, $theme_mod );
		}

		/**
		 * Generate opacity by selector & theme mod
		 *
		 * @uses get_theme_mod()
		 * @param string $selector CSS selector
		 * @param string $property The name of the identitier to modify
		 * @param string $theme_mod The name of the 'theme_mod' option to fetch
		 * @return string|void
		 */
		public static function opacity( $selector, $property, $theme_mod ) {

			// Get the theme mod value, with empty default
			$theme_mod = absint( get_theme_mod( $theme_mod, '' ) );

			// Return value?
			return ( 0 === $theme_mod ) ? '' : sprintf( '%s { %s:%s; }', $selector, $property, number_format( $theme_mod / 100, 2 ) );
		}

		/**
		 * Output header css for hero section if in use
		 */
		public static function hero_css_output() {
			$ip_custom_hero = (bool) apply_filters( 'ipress_custom_hero', true );
			if ( true === $ip_custom_hero ) {
				echo sprintf( '<style id="hero-css">%s</style>', esc_html( wp_strip_all_tags( self::hero_css() ) ) );
			}
		}
	}
endif;

// Instantiate Theme Settings Class
return new IPR_Settings;
