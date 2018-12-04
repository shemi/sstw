<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/shemi
 * @since      1.0.0
 *
 * @package    Sstw
 * @subpackage Sstw/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Sstw
 * @subpackage Sstw/includes
 * @author     Shemi Perez <shemi.perez@gmail.com>
 */
class Sstw_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'sstw',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

	public static function formTranslations()
    {
        return [
            'pluginName' => __('Search the WP', 'sstw'),
            'startTyping' => __('Start typing to search', 'sstw'),
            'notFound' => __('No results found :(', 'sstw'),
            'loading' => __('Loading', 'sstw'),
        ];
    }

}
