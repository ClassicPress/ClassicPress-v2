<?php
/**
 * Outputs the matching ClassicPress URL.
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

function _fix_cp_links( $translated_text, $untranslated_text, $domain ) {
	if ( strpos( $untranslated_text, 'https://' ) === false ) {
		return $translated_text;
	}
	$wp_to_cp_urls = array(
		'https://wordpress.org/support/forums/' => 'https://forums.classicpress.net/c/support/',
	);
	$translated = $translated_text;
	foreach ( $wp_to_cp_urls as $src_url => $dst_url ) {
		$translated = str_replace( $src_url, $dst_url, $translated );
	}
	return $translated;
}
