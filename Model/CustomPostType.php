<?php
/**
 * Helper class to register standard Custom Post Types
 *
 * @since   2.0.0
 * @version 2.0.0
 * @package CPT Maker
 * @author  Ryan Leeson
 * @license GPL v2
 */

namespace CPTMaker\Model;

class CustomPostType extends Entity {
	/**
	 * Custom post type argument array
	 * @var array
	 */
	public $arguments;
	
	/**
	 * Custom post type label array
	 * @var array
	 */
	public $labels;
	
	/**
	 * Set of associated taxonomy keys for the post type
	 * @var array
	 */
	public $taxonomies;
	
	/**
	 * CPT Constructor provides default arguments with optional overrides
	 * Arguments and labels are optional and are expected follow the standard structure of
	 * custom post type arrays.  If a $labels array is provided, it will selectively override
	 * the 'singular' and 'plural' names.
	 *
	 * @param string        $key           (Req) Key for custom post type
	 * @param string        $plural        (Opt) Optional Plural name override
	 * @param string        $key_delimiter (Opt) Delimiter used in name key, defaults to hyphen
	 * @param array         $arguments     (Opt) Custom post type argument array
	 * @param array         $labels        (Opt) Custom post type labels array
	 */
	public function __construct( string $key, string $plural = '', string $key_delimiter = '-',
			array $arguments = array(), array $labels = array() ) {
		// Set the key delimiter
		$this->process_delimiter( $key_delimiter );
		
		// Sanitize the key/name array
		$this->process_key( $key, $plural );
		
		// Build the label array, using overrides for specific labels in $this->labels
		if ( !is_array( $labels ) ) {
			$labels = array();
		}
		$this->labels = array_merge( array(
			'name'               => _x( $this->plural, 'post type general name', 'cpt-maker' ),
			'singular_name'      => _x( $this->singular, 'post type singular name', 'cpt-maker' ),
			'menu_name'          => _x( $this->plural, 'admin menu', 'cpt-maker' ),
			'name_admin_bar'     => _x( $this->singular, 'add new on admin bar', 'cpt-maker' ),
			'add_new'            => _x( sprintf( 'Add New %s', $this->singular ), 'cpt-maker' ),
			'add_new_item'       => __( sprintf( 'Add New %s', $this->singular ), 'cpt-maker' ),
			'new_item'           => __( sprintf( 'New %s', $this->singular ), 'cpt-maker' ),
			'edit_item'          => __( sprintf( 'Edit %s', $this->singular ), 'cpt-maker' ),
			'view_item'          => __( sprintf( 'View %s', $this->singular ), 'cpt-maker' ),
			'all_items'          => __( sprintf( 'All %s', $this->plural ), 'cpt-maker' ),
			'search_items'       => __( sprintf( 'Search %s', $this->plural ), 'cpt-maker' ),
			'parent_item_colon'  => __( sprintf( 'Parent %s:', $this->plural ), 'cpt-maker' ),
			'not_found'          => __( sprintf( 'No %s found', strtolower( $this->plural ) ),
				'cpt-maker' ),
			'not_found_in_trash' => __( sprintf( 'No %s found in Trash', strtolower( $this->plural ) ),
				'cpt-maker' ),
		), $labels );
		
		// Build the argument array, using overrides for specific arguments in $this->arguments
		if ( ! is_array( $arguments ) ) {
			$arguments = array();
		}
		$this->arguments = array_merge( array(
			'labels'           => $this->labels,
			'public'           => true,
			'public_queryable' => true,
			'show_ui'          => true,
			'show_in_menu'     => true,
			'query_var'        => true,
			'rewrite'          => array( 'slug' => $this->key ),
			'capability_type'  => 'post',
			'has_archive'      => true,
			'hierarchical'     => false,
			'menu_position'    => null,
			'supports'         => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		), $arguments );
		
		// Create empty taxonomy association
		$this->taxonomies = array();
	}
	
	/**
	 * Public access to the argument array
	 *
	 * @return array
	 */
	public function get_arguments() {
		return $this->arguments;
	}
	
	/**
	 * Public access to the taxonomy array
	 *
	 * @return array
	 */
	public function get_taxonomies() {
		return $this->taxonomies;
	}
	
	/**
	 * Attach a taxonomy to the custom post type via key
	 *
	 * @param   string  $key    Key of the taxonomy to register
	 * @return  bool            True on success, false otherwise
	 */
	public function add_taxonomy( string $key ) {
		// Skip on empty key registration
		if ( empty( $key ) ) {
			return false;
		}
		
		// Skip duplicate registration
		if ( in_array( $key, $this->taxonomies ) ) {
			return true;
		}
		
		$this->taxonomies[] = $key;
		return true;
	}
}