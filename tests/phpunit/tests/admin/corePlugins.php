<?php
/**
 * @group plugins
 * @group admin
 */
class Tests_Admin_CorePlugins extends WP_UnitTestCase {
	public function test_core_plugins_load_order() {
		activate_plugin( 'core1.php' );
		activate_plugin( 'hello.php' );
		activate_plugin( 'core2.php' );

		global $cp_customization;
		$cp_customization->cp_core_plugins = array(
			'core2.php',
			'core1.php',
		);

		$expected_order = array(
			'core2.php',
			'core1.php',
			'hello.php',
		);
		$active_order = get_option( 'active_plugins' );
		$this->assertEquals( $expected_order, $active_order );

		remove_filter( 'option_active_plugins', array( $cp_customization, 'cp_sort_plugins' ) );
		$active_order = get_option( 'active_plugins' );
		$this->assertNotEquals( $expected_order, $active_order );
	}
}
