<?php
/**
 * Helper class to register standard Custom Post Types
*
* @since 1.0.0
* @version 1.1.0
* @package CPT Maker
* @author Ryan Leeson
* @license GPL v2
*/

class CPT_Maker {
	private static $default_delimiter	= '-';
	private static $meta_prefix			= 'cpt-';
	private static $nonce_prefix		= 'cpt-nonce-';
	
	public $key;
	public $key_delimiter;
	public $names;
	public $arguments;
	public $labels;
	public $taxonomies;

	/**
	 * CPT Constructor provides default arguments with optional overrides
	 * 
	 * Arguments and labels are optional and are expected follow the standard structure of
	 * custom post type arrays.  If a $labels array is provided, it will selectively override
	 * the 'singular' and 'plural' names.
	 *
	 * @param string|mixed $name (Req) Key string or key/name array
	 * @param string $key_delimiter (Opt) Delimiter used in name key, defaults to hyphen if not passed a string
	 * @param array $args (Opt) Custom post type argument array 
	 * @param array $labels (Opt) Custom post type labels array 
	 */
	public function __construct( $name, $key_delimiter = null, $arguments = array(), $labels = array() ) {
		// Sanitize the key delimiter
		$this->key_delimiter = $this->process_delimiter( $key_delimiter );

		// Sanitize the key/name array
		$this->names = $this->process_name( $name, $key_delimiter );
		
		// Invalid/duplicate name, canceling post type registration
		if ( empty( $this->names ) || post_type_exists( $this->names[ 'key' ] ) ) {
			_doing_it_wrong( 'Custom post type key is invalid', 'cpt-helper', '1.0.0' );
			return;
		}
				
		// Stores post type key as property
		$this->key	= $this->names[ 'key' ];
		
		// Build the label array, using overrides for specific labels in $this->labels
		if ( !is_array( $labels ) ) {
			$labels = array();
		}
		$this->labels = array_merge( array (
			'name'					=> _x( $this->names[ 'plural' ], 'post type general name', 'cpt-maker' ),
			'singular_name'			=> _x( $this->names[ 'singular' ], 'post type singular name', 'cpt-maker' ),
			'menu_name'				=> _x( $this->names[ 'plural' ], 'admin menu', 'cpt-maker' ),
			'name_admin_bar'		=> _x( $this->names[ 'singular' ], 'add new on admin bar', 'cpt-maker' ),
			'add_new'				=> _x( sprintf( 'Add New %s', strtolower( $this->names[ 'singular' ] ) ), strtolower( $this->names[ 'singular' ] ),'cpt-maker'  ),
			'add_new_item'			=> __( sprintf( 'Add New %s', $this->names[ 'singular' ] ), 'cpt-maker' ),
			'new_item'				=> __( sprintf( 'New %s', $this->names[ 'singular' ] ), 'cpt-maker' ),
			'edit_item'				=> __( sprintf( 'Edit %s', $this->names[ 'singular' ] ), 'cpt-maker' ),
			'view_item'				=> __( sprintf( 'View %s', $this->names[ 'singular' ] ), 'cpt-maker' ),
			'all_items'				=> __( sprintf( 'All %s', $this->names[ 'plural' ] ), 'cpt-maker' ),
			'search_items'			=> __( sprintf( 'Search %s', $this->names[ 'plural' ] ), 'cpt-maker' ),
			'parent_item_colon'		=> __( sprintf( 'Parent %s:', $this->names[ 'plural' ] ), 'cpt-maker' ),
			'not_found'				=> __( sprintf( 'No %s found', strtolower( $this->names[ 'plural' ] ) ), 'cpt-maker' ),
			'not_found_in_trash'	=> __( sprintf( 'No %s found in Trash', strtolower( $this->names[ 'plural' ] ) ), 'cpt-maker' )
		), $labels );

		// Build the argument array, using overrides for specific argumetns in $this->arguments	
		if ( !is_array( $arguments ) ) {
			$arguments = array();
		}
		$this->arguments = array_merge( array (
			'labels'			=> $this->labels,
			'public'			=> true,
			'public_queryable'	=> true,
			'show_ui'			=> true,
			'show_in_menu'		=> true,
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => $this->key ),
			'capability_type'	=> 'post',
			'has_archive'		=> true,
			'hierarchical'		=> false,
			'menu_position'		=> null,
			'supports'			=> array ( 'title', 'editor', 'thumbnail', 'excerpt' )
		), $arguments );
		
		// Create empty taxonomy association
		$this->taxonomies = array();
	}
	
	/**
	 * Call after all post types and taxonomies are registered
	 */
	public function register() {
		$pt = $this;
		// Register the post type and taxonomy actions
		add_action( 'init', function() use ( $pt ){
			register_post_type( $pt->key, $pt->arguments );
			if ( !empty( $pt->taxonomies ) ) {
				foreach( $pt->taxonomies as $key => $taxonomy ) {
					register_taxonomy( $key, $pt->key, $taxonomy[ 'arguments' ] );
				}
			}
		});
		add_action( 'restrict_manage_posts', function() use ( $pt ){
			if ( !empty( $pt->taxonomies ) ) {
				foreach( $pt->taxonomies as $key => $taxonomy ) {
					$pt->taxonomy_filter( $key, $pt->key );
				}
			}
		});
	}

	/**
	 * Attach a taxonomy to the custom post type
	 * 
	 * @param unknown $name
	 * @param string $key_delimiter
	 * @param unknown $arguments
	 * @param unknown $labels
	 */
	public function add_taxonomy( $name, $key_delimiter = null, $arguments = array(), $labels = array() ) {
		// Sanitize the key delimiter
		$key_delimiter = $this->process_delimiter( $key_delimiter );
		
		// Sanitize the key/name array
		$name_array = $this->process_name( $name, $key_delimiter );
		
		// Invalid/duplicate name, canceling taxonomy registration
		if ( empty( $name_array ) || taxonomy_exists( $name_array[ 'key' ] ) ) {
			return;
		}
		
		// Store the taxonomy key
		$key = $name_array[ 'key' ];
		
		// Verify any label array override is an array otherwise default to empty
		if ( !is_array( $labels ) ) {
			$labels = array();
		}
		$labels = array_merge( array (
			'name'							=> _x( $name_array[ 'plural' ], 'taxonomy general name', 'cpt-maker' ),
			'singular_name'					=> _x( $name_array[ 'singular' ], 'taxonomy singular name','cpt-maker'  ),
			'search_items'					=> __( sprintf( 'Search %s', $name_array[ 'plural' ] ), 'cpt-maker' ),
			'popular_items'					=> __( sprintf( 'Popular %s', $name_array[ 'plural' ] ), 'cpt-maker' ),
			'menu_name'						=> __( $name_array[ 'plural' ], 'cpt-maker' ),
			'all_items'						=> __( sprintf( 'All %s', $name_array[ 'plural' ] ), 'cpt-maker' ),
			'parent_item'					=> __( sprintf( 'Parent %s', $name_array[ 'singular' ] ), 'cpt-maker' ),
			'parent_item_colon'				=> __( sprintf( 'Parent %s:', $name_array[ 'singular' ] ), 'cpt-maker'  ),
			'edit_item'						=> __( sprintf( 'Edit %s', $name_array[ 'singular' ] ), 'cpt-maker' ),
			'update_item'					=> __( sprintf( 'Update %s', $name_array[ 'singular' ] ), 'cpt-maker' ),
			'add_new_item'					=> __( sprintf( 'Add New %s', $name_array[ 'singular' ] ), 'cpt-maker' ),
			'new_item_name'					=> __( sprintf( 'New %s Name', $name_array[ 'singular' ] ), 'cpt-maker' ),
			'separate_items_with_commas'	=> __( sprintf( 'Separate %s with commas', $name_array[ 'plural' ] ), 'cpt-maker' ),
			'add_or_remove_items'			=> __( sprintf( 'Add or remove %s', $name_array[ 'plural' ] ), 'cpt-maker' ),
			'choose_from_most_used'			=> __( sprintf( 'Choose from most used %s', $name_array[ 'plural' ] ), 'cpt-maker' ),
			'not_found'						=> __( sprintf( 'No %s found.', strtolower( $name_array[ 'plural' ] ) ), 'cpt-maker' )
		), $labels );
		
		// Verify any argument array override is an array otherwise default to empty
		if ( !is_array( $arguments ) ) {
			$arguments = array();
		}
		$arguments = array_merge( array (
			'hierarchical'		=> true,
			'labels'			=> $labels,
			'public'			=> true,
			'show_ui'			=> true,
			'show_admin_column'	=> true,
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => $key )
		), $arguments );
		
		// Build the taxonomy array
		$this->taxonomies[ $key ] = array(
			'name'		=> $name_array,
			'arguments'	=> $arguments,
			'labels'	=> $labels
		);
	}
	
	/**
	 * Show a taxonomy term filter at the top of the post listing page
	 * 
	 * @param string $key Taxonomy key/slug
	 * @param string $type Post type key/slug
	 */
	public function taxonomy_filter( $key, $type ) {
		global $typenow;
		
		if ( empty( $key ) || empty( $type ) ) {
			return;
		}
		
		// Show the filter only on the matching post types and show_admin_column is set for the taxonomy
		if( $typenow === $type && true === $this->taxonomies[ $key ][ 'arguments' ][ 'show_admin_column' ] ){
			$terms = get_terms( $key );
			if( count( $terms ) > 0 ) {
				printf( '<select name=%s id=%s class="postform">', esc_attr( $key ), esc_attr( $key ) );
				printf( '<option value="">Show All %s</option>', esc_html( $this->taxonomies[ $key ][ 'name' ][ 'plural' ] ) );
				foreach ( $terms as $term ) {
					$selected = selected( esc_attr( get_query_var( $key ) ), $term->slug, false );
					printf( '<option value=%s %s>%s (%s)</option>', esc_attr( $term->slug ), $selected, $term->name, $term->count );
				}
				printf( '</select>' );
			}
		}
	}
		
	/**
	 * Sanitize a key delimiter
	 * 
	 * @param string $key_delimiter Supplied delimiter
	 * @return string Sanitized delimiter
	 */
	private function process_delimiter( $key_delimiter ) {
		if ( !is_string( $key_delimiter ) ) {
			$key_delimiter = self::$default_delimiter;
		}
		else {
			$key_delimiter = wp_strip_all_tags( $key_delimiter );
		}
		return $key_delimiter;
	}
	
	/**
	 * Name $name may be provided as a valid key (e.g. library-book) or array with three
	 * key value pairs: 
	 * 		'key' 		= (Req) $key_delimiter separated string to reference the custom post type
	 * 		'singular'	= (Req) Human readable singular name
	 * 		'plural'	= (Opt) Human readable plural name, if not provided, singular name with an 's'
	 * Human readable names will be escaped (spaces to $key_delimiter) and set to lowercase.
	 * 
	 * @param string|mixed $name (Req) Key string or key/name array
	 * @param string $key_delimiter (Req) Delimiter used in name key
	 */
	private function process_name( $name, $key_delimiter ) {
		// Cancel if missing any default parameters
		if ( empty( $name ) || empty( $key_delimiter ) ) {
			return array();
		}
		
		// Defaults for the key/name array
		$key		= '';
		$singular	= '';
		$plural		= '';
	
		// Process a key string
		if ( is_string( $name ) ) {
			$key		= strtolower( str_replace( ' ', $key_delimiter, wp_strip_all_tags( $name ) ) );
			$singular	= ucwords( str_replace( $key_delimiter, ' ', $name ) );
			$plural		= sprintf( '%ss', $singular );
		}
		// Process a key/name array
		else if ( is_array( $name ) && !empty( $name[ 'key' ] ) && !empty( $name[ 'singular' ] ) ) {
			$key		= wp_strip_all_tags( $name[ 'key' ] );
			$singular	= esc_html( $name[ 'singular' ] );
		
			if ( empty( $name[ 'plural' ] ) ) {
				$plural	=  sprintf( '%ss', $singular );
			}
			else {
				$plural	= esc_html( $name[ 'plural' ] );
			}
		}
		// Invalid array, return empty
		else {
			return array();
		}
		
		$name_array = array(
			'key'		=> $key,
			'singular'	=> $singular,
			'plural'	=> $plural
		);
		return $name_array;		
	}
}
