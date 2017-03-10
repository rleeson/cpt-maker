<?php
/**
 * Helper class to register standard WordPress taxonomies
 *
 * @since   2.0.0
 * @version 2.0.0
 * @package CPT Maker
 * @author  Ryan Leeson
 * @license GPL v2
 */

namespace CPTMaker\Model;

class Taxonomy extends Entity {
	/**
	 * Taxonomy registration argument array
	 * @var array
	 */
	public $arguments;
	
	/**
	 * Taxonomy registration label array
	 * @var array
	 */
	public $labels;
	
	/**
	 * Flag to show/hide the taxonomy term drop-down filter on associate post type edit listings
	 * @var bool
	 */
	public $show_filter;
	
	/**
	 * CPT Constructor provides default arguments with optional overrides
	 * Arguments and labels are optional and are expected follow the standard structure of
	 * custom post type arrays.  If a $labels array is provided, it will selectively override
	 * the 'singular' and 'plural' names.
	 *
	 * @param string        $key            (Req) Key for custom post type
	 * @param string        $plural         (Opt) Optional Plural name override
	 * @param string        $key_delimiter  (Opt) Delimiter used in name key, defaults to hyphen
	 * @param array         $arguments      (Opt) Custom post type argument array
	 * @param array         $labels         (Opt) Custom post type labels array
	 * @param bool          $show_filter    (Opt) Show/hide the filter drop-down on associated post type edit lists
	 */
	public function __construct( string $key, string $plural = '', string $key_delimiter = '-',
			array $arguments = array(), array $labels = array(), bool $show_filter = true ) {
		// Sanitize the key delimiter
		$this->process_delimiter( $key_delimiter );
		
		// Sanitize the key/name array
		$this->process_key( $key, $plural );
		
		// Verify any label array override is an array otherwise default to empty
		if ( !is_array( $labels ) ) {
			$labels = array();
		}
		$this->labels = array_merge( array(
			'name'                       => _x( $this->plural, 'taxonomy general name', 'cpt-maker' ),
			'singular_name'              => _x( $this->singular, 'taxonomy singular name', 'cpt-maker' ),
			'search_items'               => __( sprintf( 'Search %s', $this->plural ), 'cpt-maker' ),
			'popular_items'              => __( sprintf( 'Popular %s', $this->plural ), 'cpt-maker' ),
			'menu_name'                  => __( $this->plural, 'cpt-maker' ),
			'all_items'                  => __( sprintf( 'All %s', $this->plural ), 'cpt-maker' ),
			'parent_item'                => __( sprintf( 'Parent %s', $this->singular ), 'cpt-maker' ),
			'parent_item_colon'          => __( sprintf( 'Parent %s:', $this->singular ), 'cpt-maker' ),
			'edit_item'                  => __( sprintf( 'Edit %s', $this->singular ), 'cpt-maker' ),
			'update_item'                => __( sprintf( 'Update %s', $this->singular ), 'cpt-maker' ),
			'add_new_item'               => __( sprintf( 'Add New %s', $this->singular ), 'cpt-maker' ),
			'new_item_name'              => __( sprintf( 'New %s Name', $this->singular ), 'cpt-maker' ),
			'separate_items_with_commas' => __( sprintf( 'Separate %s with commas', $this->plural ),
				'cpt-maker' ),
			'add_or_remove_items'        => __( sprintf( 'Add or remove %s', $this->plural ),
				'cpt-maker' ),
			'choose_from_most_used'      => __( sprintf( 'Choose from most used %s', $this->plural ),
				'cpt-maker' ),
			'not_found'                  => __( sprintf( 'No %s found.', strtolower( $this->plural ) ),
				'cpt-maker' ),
		), $labels );

		// Verify any argument array override is an array otherwise default to empty
		if ( !is_array( $arguments ) ) {
			$arguments = array();
		}
		$this->arguments = array_merge( array(
			'hierarchical'      => true,
			'labels'            => $this->labels,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $key ),
		), $arguments );
		
		$this->show_filter = $show_filter;
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
	 * Show a taxonomy term filter at the top of the post listing page
	 *
	 * @param string $key  Taxonomy key/slug
	 * @param string $type Post type key/slug
	 */
	public function taxonomy_filter( $key, $type ) {
		global $typenow;
		
		if ( false === $this->show_filter ) {
			return;
		}
		
		if ( empty( $key ) || empty( $type ) ) {
			return;
		}
		
		// Show the filter only on the matching post types and show_admin_column is set for the taxonomy
		if ( $typenow === $type && true === $this->arguments[ 'show_admin_column' ] ) {
			$terms = get_terms( $key );
			if ( count( $terms ) > 0 ) {
				printf( '<select name="%s" id="%s" class="postform">', esc_attr( $key ), esc_attr( $key ) );
				printf( '<option value="">Show All %s</option>',
					esc_html( $this->plural ) );
				foreach ( $terms as $term ) {
					$selected = selected( esc_attr( get_query_var( $key ) ), $term->slug, false );
					printf( '<option value="%s" %s>%s (%s)</option>', esc_attr( $term->slug ), $selected,
						$term->name, $term->count );
				}
				printf( '</select>' );
			}
		}
	}
}
