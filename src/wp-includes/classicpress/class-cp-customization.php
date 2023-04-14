<?php
/**
 * ClassicPress Customization class
 *
 * @package ClassicPress
 * @subpackage admin
 * @since CP-2.0.0
 */

class CP_Customization {
	public function __construct() {
		add_action( 'load-edit.php', array( $this, 'add_id_init' ) );
	}

	/**
	 * Returns the matching ClassicPress URL.
	 *
	 * Searches for WP URLs in $wp_to_cp_urls and replaces with the corresponding CP URL.
	 * Intended to be used as gettext_default filter.
	 *
	 * @since CP-2.0.0
	 *
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Text to translate.
	 * @param string $domain      Text domain. Unique identifier for retrieving translated strings.
	 * @return string Translated string with corrected URL.
	 */

	public static function cp_translations( $translated_text, $untranslated_text, $domain ) {
		if ( strpos( $untranslated_text, 'https://' ) === false ) {
			return $translated_text;
		}

		$wp_to_cp = array(
			'https://wordpress.org/support/forums/' => 'https://forums.classicpress.net/c/support/',
		);

		$translated = $translated_text;

		foreach ( $wp_to_cp as $wp_src => $cp_dst ) {
			$translated = str_replace( $wp_src, $cp_dst, $translated );
		}

		return $translated;
	}

	/**
	 * Add ID column to Post / Page Tables
	 */
	public function add_id_init() {
		$screen = get_current_screen();

		if ( ! isset( $screen->post_type ) || ! in_array( $screen->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		add_filter( "manage_{$screen->id}_columns", array( $this, 'add_id_column' ) );
		add_action( 'admin_head', array( $this, 'add_id_style' ) );
		add_action( "manage_{$screen->post_type}_posts_custom_column", array( $this, 'add_id_data_cb' ), 10, 2 );
		add_filter( "manage_{$screen->id}_sortable_columns", array( $this, 'add_id_data_sortable' ) );
	}

	public function add_id_column( $cols ) {
		$cols = array_reverse( $cols, true );
		$cb   = array_pop( $cols );

		$cols['id'] = 'ID';
		$cols['cb'] = 'cb';

		return array_reverse( $cols, true );
	}

	public function add_id_style() {
		echo '<style>.wp-list-table .column-id { width: 5%; }</style>';
	}

	public function add_id_data_cb( $col, $post_id ) {
		if ( 'id' === $col ) {
			echo esc_html( $post_id );
		}
	}

	public function add_id_data_sortable( $cols ) {
		$cols['id'] = 'template';
		return $cols;
	}
}

$cp_customization = new CP_Customization();
