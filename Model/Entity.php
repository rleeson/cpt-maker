<?php
/**
 * Base entity class for content types
 *
 * @since   2.0.0
 * @version 2.0.0
 * @package CPT Maker
 * @author  Ryan Leeson
 * @license GPL v2
 */

namespace CPTMaker\Model;

abstract class Entity {
	/**
	 * Default delimiter used in the key
	 * @var string
	 */
	const DEFAULT_DELIMITER = '-';
	
	/**
	 * Delimiter used in key
	 * @var string
	 */
	protected $key_delimiter;
	
	/**
	 * Key for the entity
	 * @var string
	 */
	protected $key = '';
	
	/**
	 * Readable plural version of the name
	 * @var string
	 */
	protected $plural = '';
	
	/**
	 * Readable plural version of the name
	 * @var string
	 */
	protected $singular = '';
	
	/**
	 * Public access to the entity key
	 *
	 * @return string
	 */
	public function get_key() {
		return $this->key;
	}
	
	/**
	 * Indicate whether the current entity is valid
	 *
	 * @return bool
	 */
	public function is_valid() {
		return !( empty( $this->key ) || empty( $this->singular ) || empty( $this->plural ) );
	}
	
	/**
	 * Sanitize and set the key delimiter, if none is supplied uses self::DEFAULT_DELIMITER
	 *
	 * @param string $key_delimiter Supplied delimiter
	 */
	protected function process_delimiter( $key_delimiter ) {
		$this->key_delimiter = !is_string( $key_delimiter ) ? self::DEFAULT_DELIMITER :
			wp_strip_all_tags( $key_delimiter );
	}
	
	/**
	 * Key will be stored as the registration key (e.g. library-book) and automatically generate the
	 * human readable singular and plural versions of the name into properties of the Entity
	 *
	 * @param string $key       (Req) Key string or key/name array
	 * @param string $plural    (Opt) Optional Plural name override
	 */
	protected function process_key( string $key, string $plural = '' ) {
		// Cancel if missing any default parameters
		if ( empty( $key ) ) {
			return;
		}
		
		// Sanitize a key name and generate automatic names
		$this->key      = strtolower( str_replace( ' ', $this->key_delimiter, wp_strip_all_tags( $key ) ) );
		$this->singular = ucwords( str_replace( $this->key_delimiter, ' ', $key ) );
		$this->plural   = empty( $plural ) ? sprintf( '%ss', $this->singular ) : $plural;
	}
}