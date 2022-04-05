<?php

/**
 * Custom Bootstrap 5 wp_nav_menu walker
 * - Enhanced submenu structure
 */
class TSS_WP_Nav_Menu_Walker extends Walker_Nav_menu {

	/**
	 * Current item 
	 */
	private $current_item;

	/**
	 * Initiate level
	 *
	 * @param string $output reference
	 * @param integer $depth default 0
	 * @param array $args default null
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {

        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = str_repeat( $t, $depth );
		
		// Initialise submenu
		$start = ( $depth === 0 ) ? '<div class="sub-menu-container"><div class="sub-menu-pointer d-none d-lg-block"></div><ul' : '<ul';

        // Default class.
        $classes = [ 'sub-menu', 'navbar-sub-nav' ];
 
        // Filters the CSS class(es) applied to a menu list element.
        $class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
 
        $output .= "{$n}{$indent}{$start}$class_names>{$n}";
    }

	/**
     * Ends the list of after the elements are added.
     *
     * @param string   $output Used to append additional content (passed by reference).
     * @param int      $depth  Depth of menu item. Used for padding.
     * @param stdClass $args   An object of wp_nav_menu() arguments.
     */
    public function end_lvl( &$output, $depth = 0, $args = null ) {

		// Initialise ending depending on depth
		$end = ( $depth === 0 ) ? '</ul></div>' : '</ul>'; 

		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent  = str_repeat( $t, $depth );
        $output .= "$indent${end}{$n}";
    }

	/**
     * Starts the element output.
     *
     * @param string   $output            Used to append additional content (passed by reference).
     * @param WP_Post  $data_object       Menu item data object.
     * @param int      $depth             Depth of menu item. Used for padding.
     * @param stdClass $args              An object of wp_nav_menu() arguments.
     * @param int      $current_object_id Optional. ID of the current menu item. Default 0.
     */
    public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {

		// Restores the more descriptive, specific name for use within this method.
        $menu_item = $data_object;
 
		// Generate spacing and indentation
        if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
            $t = '';
            $n = '';
        } else {
            $t = "\t";
            $n = "\n";
        }
        $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		// Initiate classes	
        $classes   = ( empty( $menu_item->classes ) ) ? [] : (array) $menu_item->classes;
	
		// Add custom elements
		$classes[] = 'nav-item';
		$classes[] = 'col-lg-auto';
 
		// Set custom screen reader link		
		$args->link_after = ( ! empty( $menu_item->current ) ) ? '<span class="sr-only">(current)</span>' : '';

        // Filters the arguments for a single nav menu item.
        $args = apply_filters( 'nav_menu_item_args', $args, $menu_item, $depth );
 
        // Filters the CSS classes applied to a menu item's list item element.
        $class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $menu_item, $args, $depth ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
 
        // Filters the ID applied to a menu item's list item element.
        $id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $menu_item->ID, $menu_item, $args, $depth );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		// Set current output
        $output .= $indent . '<li' . $id . $class_names . '>';

		// Set attributes
        $atts = [];
        $atts['title']  = ( ! empty( $menu_item->attr_title ) ) ? $menu_item->attr_title : '';
        $atts['target'] = ( ! empty( $menu_item->target ) ) ? $menu_item->target : '';
		$atts['rel'] 	= ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) ) ? 'noopener' : $menu_item->xfn;
        $atts['href']	= ! empty( $menu_item->url ) ? $menu_item->url : '';
        $atts['aria-current'] = $menu_item->current ? 'page' : '';
 
        // Filters the HTML attributes applied to a menu item's anchor element.
        $atts = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, $depth );

		// Construct attributes
        $attributes = '';
        foreach ( $atts as $attr => $value ) {
            if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
                $value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }
 
        // This filter is documented in wp-includes/post-template.php
        $title = apply_filters( 'the_title', $menu_item->title, $menu_item->ID );
 
        // Filters a menu item's title.
        $title = apply_filters( 'nav_menu_item_title', $title, $menu_item, $args, $depth );
 
        $item_output  = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . $title . $args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;
 
        // Filters a menu item's starting output.
        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $menu_item, $depth, $args );
    }
}
