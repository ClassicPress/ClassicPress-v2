<?php
/**
 * Blocks API: WP_Block_Type class Polyfill
 *
 * @package WordPress
 * @subpackage Blocks
 * @since 5.0.0
 */

/**
 * Core class representing a block type.
 *
 * @since 5.0.0
 *
 * @see register_block_type()
 */

class WP_Block_Type {
/*
				global $wp_compat;
				$wp_compat->using_block_function();
				return false;
*/

	public function __set( $name, $value ) {
		global $wp_compat;
		$wp_compat->using_block_function();
	}

	public function __get( $name ) {
		global $wp_compat;
		$wp_compat->using_block_function();
		return false;
	}

	public function __isset( $name ) {
		global $wp_compat;
		$wp_compat->using_block_function();
		return false;
	}

	public function __unset( $name ) {
		global $wp_compat;
		$wp_compat->using_block_function();
		return false;
	}

	public function __call( $name, $arguments ) {
		global $wp_compat;
		$wp_compat::using_block_function();
		return false;
	}

	public static function __callstatic( $name, $arguments ) {
		global $wp_compat;
		$wp_compat::using_block_function();
		return false;
	}

}
