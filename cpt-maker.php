<?php
/**
 * Plugin Name: CPT Maker
 * Plugin URI: https://github.com/rleeson/cpt-maker.git
 * Description: WordPress plugin helps quickly create and initialize custom post types
 * Version: 2.0.0
 * Author: Ryan Leeson
 * Author URI: http://ryanleeson.com
 * License: GPLv2
 */

/**
 * Auto-loader checks finds classes using current file name format
 *      - Namespace Based: Search for CPTMaker\Name\Space\Class in /name/space/class.php
 */
spl_autoload_register( function ( $class_name )  {
	$root_namespace = 'CPTMaker\\';
	if ( false !== strpos( $class_name, $root_namespace ) ) {
		$file_name = str_replace( '\\', '/', str_replace( $root_namespace, '\\', $class_name ) );
		cpt_maker_require_class_file( sprintf( '%s/%s.php', __DIR__, $file_name ) );
		return;
	}
} );

/**
 * Require a class file
 *
 * @param string $path Path to file
 */
function cpt_maker_require_class_file( $path ) {
	if ( !file_exists( $path ) ) {
		return;
	}
	require_once( $path );
}

use CPTMaker\Registration;

/**
 * Associate a new post type of slug $key with the slugs of each key entry in $taxonomies
 *
 * @param string $key           Post type key
 * @param array  $taxonomies    List of taxonomy keys
 * @param string $plural        Optional plural name for post types
 * @return bool
 */
function cpt_register_post_type( string $key, array $taxonomies, string $plural = '' ) {
	return Registration::register_post_type( $key, $plural, '-', array(), array(), $taxonomies );
}

/**
 * Associate a new taxonomy of slug $key
 *
 * @param string $key           Post type key
 * @param string $plural        Optional plural name for post types
 * @return bool
 */
function cpt_register_taxonomy( string $key, string $plural = '' ) {
	return Registration::register_taxonomy( $key, $plural, '-', array(), array(), true );
}

// Action hook used to load custom post types and taxonomies prior to WordPress registration
add_action( 'plugins_loaded', function () {
	do_action( 'cpt-registration' );
} );
add_action( 'plugins_loaded', array( Registration::class, 'register' ), 20 );