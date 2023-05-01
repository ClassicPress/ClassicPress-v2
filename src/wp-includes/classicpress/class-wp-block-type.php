<?php
/**
 * Blocks API: WP_Block_Type class Polyfill
 *
 * @package ClassicPress
 * @subpackage Blocks
 * @since 2.0.0
 */

/**
 * Core class representing a block type.
 *
 * @since 2.0.0
 *
 * @see register_block_type()
 */

class WP_Block_Type {
	public function __set( ...$args ) {
		global $wp_compat;
		$wp_compat->using_block_function();
	}

	public function __get( ...$args ) {
		global $wp_compat;
		$wp_compat->using_block_function();
		return false;
	}

	public function __isset( ...$args ) {
		global $wp_compat;
		$wp_compat->using_block_function();
		return false;
	}

	public function __unset( ...$args ) {
		global $wp_compat;
		$wp_compat->using_block_function();
		return false;
	}

	public function __call( ...$args ) {
		global $wp_compat;
		$wp_compat::using_block_function();
		return false;
	}

	public static function __callstatic( ...$args ) {
		global $wp_compat;
		$wp_compat::using_block_function();
		return false;
	}
}
