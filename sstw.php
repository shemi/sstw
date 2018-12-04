<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/shemi
 * @since             1.0.0
 * @package           Sstw
 *
 * @wordpress-plugin
 * Plugin Name:       Search The WP
 * Plugin URI:        https://github.com/shemi/search-the-wp
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Shemi Perez
 * Author URI:        https://github.com/shemi
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sstw
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

define('SSTW_VERSION', '1.0.0');

global $sstwInst;

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sstw-activator.php
 */
function activate_sstw()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-sstw-activator.php';

    Sstw_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sstw-deactivator.php
 */
function deactivate_sstw()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-sstw-deactivator.php';
    Sstw_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_sstw');
register_deactivation_hook(__FILE__, 'deactivate_sstw');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-sstw.php';


/**
 * @return Sstw
 */
function sstw()
{
    global $sstwInst;

    if(! $sstwInst) {
        $sstwInst = new Sstw();
    }

    return $sstwInst;
}

function run_sstw()
{
    sstw()->run();
}

run_sstw();
