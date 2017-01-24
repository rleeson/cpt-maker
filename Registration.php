<?php
/**
 * Central registration for standard Custom Post Types and taxonomies
 *
 * @since   2.0.0
 * @version 2.0.0
 * @package CPT Maker
 * @author  Ryan Leeson
 * @license GPL v2
 */

namespace CPTMaker;

use CPTMaker\Model;

final class Registration {
	/**
	 * Set of registered post types
	 *
	 * @var array
	 */
	private static $post_types = array();
	
	/**
	 * Set of registered taxonomies
	 *
	 * @var array
	 */
	private static $taxonomies = array();
	
	/**
	 * Add taxonomy registration to an existing post type (will not automatically register a post type)
	 *
	 * @param   string  $post_type  Post type key
	 * @param   string  $taxonomy   Taxonomy key
	 *
	 * @return  bool    True on successful addition, false otherwise
	 */
	public static function add_taxonomy_to_post_type( string $post_type, string $taxonomy ) {
		if ( empty( $post_type ) || empty( $taxonomy ) ) {
			return false;
		}
		
		if ( !isset( self::$post_types[ $post_type ] ) ) {
			return false;
		}
	}
	
	/**
	 * Register a taxonomy by key
	 *
	 * @param   string  $key            Taxonomy key
	 * @param   string  $plural         (Opt) Optional Plural name override
	 * @param   string  $key_delimiter  Key delimiter used (default -)
	 * @param   array   $arguments      Set of arguments for the taxonomy
	 * @param   array   $labels         Set of labels for the taxonomy
	 * @param   bool    $show_filter    Show/hide taxonomy term filter on associated post type listing
	 *
	 * @return  bool                    True on successful addition, false otherwise
	 */
	public static function register_taxonomy( string $key, string $plural = '', string $key_delimiter = '-',
			array $arguments = array(), array $labels = array(), bool $show_filter = true ) {
		// Sanitize key and verify
		$key = sanitize_key( $key );
		if ( empty( $key ) ) {
			return false;
		}

		// Avoid duplicates, but acknowledge it is registered
		if ( isset( self::$taxonomies[ $key ] ) ) {
			return true;
		}
		
		// Register a valid taxonomy
		$taxonomy = new Model\Taxonomy( $key, $plural, $key_delimiter, $arguments, $labels, $show_filter );
		if ( !$taxonomy->is_valid() ) {
			return false;
		}
		self::$taxonomies[ $key ] = $taxonomy;
		
		return true;
	}
	
	/**
	 * Register a post type by key, optionally add a set of taxonomies to the post type
	 * If any taxonomy needs to be customized, register it manually before adding to the post type
	 *
	 * @param   string  $key            Key of the post type
	 * @param   string  $plural         (Opt) Optional Plural name override
	 * @param   string  $key_delimiter  Key delimiter used (default -)
	 * @param   array   $arguments      Set of arguments for the post type
	 * @param   array   $labels         Set of labels for the post type
	 * @param   array   $taxonomies     Set of taxonomy keys
	 *
	 * @return  bool                    True on successful addition, false otherwise
	 */
	public static function register_post_type( string $key, string $plural = '', string $key_delimiter = '-',
			array $arguments = array(), array $labels = array(), array $taxonomies = array() ) {
		// Sanitize key and verify
		$key = sanitize_key( $key );
		if ( empty( $key ) ) {
			return false;
		}
		
		// Avoid duplicates, but acknowledge it is registered
		if ( isset( self::$post_types[ $key ] ) ) {
			return true;
		}
		
		// Register and validate the post type
		$post_type = new Model\CustomPostType( $key, $plural, $key_delimiter, $arguments, $labels );
		if ( !$post_type->is_valid() ) {
			return false;
		}
		
		// Assign any taxonomies
		if ( !empty( $taxonomies ) && is_array( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				self::register_taxonomy( $taxonomy );
				$post_type->add_taxonomy( $taxonomy );
			}
		}
		self::$post_types[ $key ] = $post_type;
		
		return true;
	}
	
	/**
	 * Call after all post types and taxonomies are registered
	 */
	public static function register() {
		// Register the post type and taxonomy actions
		add_action( 'init', function() {
			// Register all taxonomies first, associated with nothing
			foreach( self::$taxonomies as $key => $taxonomy ) {
				if ( taxonomy_exists( $key ) ) {
					continue;
				}
				register_taxonomy( $key, null, $taxonomy->get_arguments() );
			}
			// Register all post types with the associated taxonomies
			foreach( self::$post_types as $key =>$post_type ) {
				register_post_type( $key, $post_type->get_arguments() );
				if ( !empty( $post_type->get_taxonomies() ) ) {
					foreach ( $post_type->get_taxonomies() as $taxonomy ) {
						register_taxonomy_for_object_type( $taxonomy, $key );
					}
				}
			}
		} );
		
		// Setup the post type filters for each registered taxonomy
		add_action( 'restrict_manage_posts', function( $post_type ) {
			if ( !isset( self::$post_types[ $post_type ] ) ) {
				return;
			}

			$taxonomies = self::$post_types[ $post_type ]->get_taxonomies();
			if ( empty( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {
					if ( isset( self::$taxonomies[ $taxonomies ] ) ) {
						self::$taxonomies[ $taxonomies ]->taxonomy_filter( $taxonomy, $post_type );
					}
				}
			}
		} );
	}
}