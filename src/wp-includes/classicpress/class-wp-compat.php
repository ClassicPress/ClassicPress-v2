<?php
/**
 * ClassicPress polyfills for blocks.
 *
 * @package ClassicPress
 * @since CP-2.0.0
 */

class WP_Compat {

	public $blocks_compatibility_level = null;

	public function __construct() {

		if ( $this->blocks_compatibility_level === null ) {
			$this->blocks_compatibility_level = (int) get_option( 'blocks_compatibility_level', 1 );
		}

		add_action( 'update_option_blocks_compatibility_level', array( $this, 'purge_options' ), 10, 2 );

		if ( $this->blocks_compatibility_level === 0 ) {
			return;
		}

		$this->define_polyfills();

		if ( $this->blocks_compatibility_level === 1 ) {
			return;
		}

		// Define hooks to be used to warn users
		add_action( 'after_plugin_row', array( $this, 'using_block_function_row' ), 10, 2 );
		add_action( 'upgrader_process_complete', array( $this, 'update_who_uses_blocks' ), 10, 2 );
		add_action( 'delete_plugin', array( $this, 'delete_who_uses_blocks' ), 10, 1 );
		add_action( 'admin_notices', array( $this, 'using_block_function_theme' ), 10, 0 );
		add_action( 'after_switch_theme', array( $this, 'delete_theme_uses_blocks' ), 10, 0 );

	}

	public function purge_options( $old_value, $value ) {
		if ( $value === 2 ) {
			return;
		}
		delete_option( 'plugins_using_blocks' );
		delete_option( 'theme_using_blocks' );
	}

	/**
	 * Action hooked to after_plugin_row to display plugins that may not work properly.
	 *
	 * @param string $plugin_file
	 * @param array  $plugin_data
	 * @return void
	 */
	public function using_block_function_row( $plugin_file, $plugin_data ) {
		$plugins_using_blocks = get_option( 'plugins_using_blocks', array() );
		if ( ! array_key_exists( $plugin_file, $plugins_using_blocks ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		$active        = is_plugin_active( $plugin_file ) ? 'active' : '';
		?>
		<tr class="plugin-update-tr <?php echo $active; ?>">
			<td colspan="<?php echo $wp_list_table->get_column_count(); ?>" class="plugin-update colspanchange" style="box-shadow: none;">
				<div class="notice inline notice-alt notice-warning">
					<p>
						<?php
						// Translators: %1$s is the plugin name.
						printf( esc_html__( '%1$s uses block-related functions and may not work correctly.' ), $plugin_data['Name'] );
						?>
					</p>
				</div>
			</td>
		</tr>
		<script>
			jQuery('tr[data-plugin="<?php echo $plugin_file; ?>"]').addClass('update');
		</script>
		<?php
	}

	/**
	 * Action hooked to upgrader_process_complete to clean up the list of plugins
	 * that may not work properly.
	 *
	 * @param WP_Upgrader $upgrader
	 * @param array       $options
	 * @return void
	 */
	public function update_who_uses_blocks( $upgrader, $options ) {
		if ( $options['action'] !== 'update' ) {
			return;
		}

		if ( $options['type'] === 'theme' ) {
			update_option( 'theme_using_blocks', false );
			return;
		}

		if ( $options['type'] !== 'plugin' ) {
			return;
		}

		$plugins_using_blocks = get_option( 'plugins_using_blocks', array() );
		foreach ( $options['plugins'] as $plugin ) {
			if ( array_key_exists( $plugin, $plugins_using_blocks ) ) {
				unset( $plugins_using_blocks[ $plugin ] );
			}
		}
		update_option( 'plugins_using_blocks', $plugins_using_blocks );
	}

	/**
	 * Action hooked to delete_plugin to remove the plugin
	 * that may not work properly.
	 *
	 * @param string       $options
	 * @return void
	 */
	public function delete_who_uses_blocks( $plugin_file ) {
		$plugins_using_blocks = get_option( 'plugins_using_blocks', array() );
		if ( array_key_exists( $plugin_file, $plugins_using_blocks ) ) {
			unset( $plugins_using_blocks[ $plugin_file ] );
		}
		update_option( 'plugins_using_blocks', $plugins_using_blocks );
	}

	/**
	 * Action hooked to admin_notices to display an admin notice
	 * on themes page.
	 *
	 * @return void
	 */
	public function using_block_function_theme() {
		global $pagenow;
		if ( $pagenow !== 'themes.php' ) {
			return;
		}
		$theme_using_blocks = get_option( 'theme_using_blocks', '0' );
		if ( ! in_array( $theme_using_blocks, array( '1', '2' ), true ) ) {
			return;
		}

		if ( $theme_using_blocks === '1' ) {
			// Translators: %1$s is the theme name.
			$message = sprintf( esc_html__( '%1$s uses block-related functions and may not work correctly.' ), wp_get_theme()->get( 'Name' ) );
		} else {
			// Translators: %1$s is the theme name, %1$s is the parent theme name.
			$message = sprintf( esc_html__( '%1$s parent theme (%2$s) uses block-related functions and may not work correctly.' ), wp_get_theme()->get( 'Name' ), wp_get_theme()->parent()->get( 'Name' ) );
		}

		?>
		<div class="notice notice-alt notice-warning">
			<p>
			<?php
				echo $message;
			?>
		</div>
		<?php
	}

	/**
	 * Action hooked to after_switch_theme to remove the theme
	 * that may not work properly.
	 *
	 * @return void
	 */
	public function delete_theme_uses_blocks() {
		update_option( 'theme_using_blocks', '0' );
	}


	/**
	 * This function have to be called from a polyfill
	 * to map themes and plugins calling those functions.
	 *
	 * @return void
	 */
	public function using_block_function() {
		if ( $this->blocks_compatibility_level !== 2 ) {
			return;
		}
		$trace = debug_backtrace();
		if ( strpos( $trace[1]['file'], get_stylesheet_directory() ) === 0 ) {
			// Current theme is calling the function
			update_option( 'theme_using_blocks', '1' );
		} elseif ( strpos( $trace[1]['file'], get_template_directory() ) === 0 ) {
			// Parent theme is calling the function
			update_option( 'theme_using_blocks', '2' );
		} else {
			// A plugin is calling the function
			$plugins = array_intersect( array_column( $trace, 'file' ), wp_get_active_and_valid_plugins() );
			unset( $plugins[ array_search( __FILE__, $plugins ) ] ); // Remove ourself
			$plugin = array_pop( $plugins );
			if ( $plugin === null ) {
				// Nothing found? Bail.
				return;
			}
			$plugins_using_blocks = get_option( 'plugins_using_blocks', array() );
			if ( ! array_key_exists( plugin_basename( $plugin ), $plugins_using_blocks ) ) {
				$plugins_using_blocks[ plugin_basename( $plugin ) ] = true;
				update_option( 'plugins_using_blocks', $plugins_using_blocks );
			}
		}
	}

	/**
	 * Polyfills that can not be defined elsewhere go here.
	 * that may not work properly.
	 *
	 * @param string       $options
	 * @return void
	 */
	private function define_polyfills() {
		// Polyfills that must not have to be defined elsewhere go there.
		if ( ! function_exists( 'register_block_type' ) ) :
			/**
			 * Polyfill for block functions.
			 *
			 * @since CP-2.0.0
			 *
			 * @return bool False.
			 */
			function register_block_type( ...$args ) {
				global $wp_compat;
				$wp_compat->using_block_function();
				return false;
			}

		endif;

		if ( ! function_exists( 'register_block_type_from_metadata' ) ) :
			/**
			 * Polyfill for block functions.
			 *
			 * @since CP-2.0.0
			 *
			 * @return bool False.
			 */
			function register_block_type_from_metadata( ...$args ) {
				global $wp_compat;
				$wp_compat->using_block_function();
				return false;
			}

		endif;

		if ( ! function_exists( 'register_block_pattern' ) ) :
			/**
			 * Polyfill for block functions.
			 *
			 * @since CP-2.0.0
			 *
			 * @return bool False.
			 */
			function register_block_pattern( ...$args ) {
				global $wp_compat;
				$wp_compat->using_block_function();
				return false;
			}

		endif;
	}

}

$wp_compat = new WP_Compat;
