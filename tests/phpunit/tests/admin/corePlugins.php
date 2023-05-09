<?php
/**
 * @group plugins
 * @group admin
 */
class Tests_Admin_CorePlugins extends WP_UnitTestCase {
	public function test_core_plugins_load_order() {
		$core1_dir = WP_PLUGIN_DIR . '/core1';
		mkdir( $core1_dir );
		$core1 = $this->_create_plugin( "<?php\n/*\n* Plugin Name: YOUR PLUGIN NAME\n*/\n", 'core1.php', WP_PLUGIN_DIR . '/core1' );
		$core2_dir = WP_PLUGIN_DIR . '/core2';
		mkdir( $core2_dir );
		$core2 = $this->_create_plugin( "<?php\n/*\n* Plugin Name: YOUR PLUGIN NAME\n*/\n", 'core2.php', WP_PLUGIN_DIR . '/core2' );

		activate_plugin( 'core1/core1.php' );
		activate_plugin( 'hello.php' );
		activate_plugin( 'core2/core2.php' );

		global $cp_customization;
		$cp_customization->cp_core_plugins = array(
			'core2/core2.php',
			'core1/core1.php',
		);

		$expected_order = array(
			'core2/core2.php',
			'core1/core1.php',
			'hello.php',
		);
		$active_order = get_option( 'active_plugins' );
		$this->assertEquals( $expected_order, $active_order );

		remove_filter( 'option_active_plugins', array( $cp_customization, 'cp_sort_plugins' ) );
		$active_order = get_option( 'active_plugins' );
		$this->assertNotEquals( $expected_order, $active_order );

		unlink( $core1[1] );
		rmdir( $core1_dir );
		unlink( $core2[1] );
		rmdir( $core2_dir );
	}

	private function _create_plugin( $data = "<?php\n/*\nPlugin Name: Test\n*/", $filename = false, $dir_path = false ) {
		if ( false === $filename ) {
			$filename = __FUNCTION__ . '.php';
		}

		if ( false === $dir_path ) {
			$dir_path = WP_PLUGIN_DIR;
		}

		$filename  = wp_unique_filename( $dir_path, $filename );
		$full_name = $dir_path . '/' . $filename;

		$file = fopen( $full_name, 'w' );
		fwrite( $file, $data );
		fclose( $file );

		return array( $filename, $full_name );
	}
}
