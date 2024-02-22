<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/ejperez
 * @since             1.0.0
 * @package           Migrate_Sb
 *
 * @wordpress-plugin
 * Plugin Name:       Migrate SB
 * Plugin URI:        https://roiroi.com
 * Description:       Migrate posts to Storyblok.
 * Version:           1.0.0
 * Author:            EJ Perez
 * Author URI:        https://github.com/ejperez/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       migrate-sb
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MIGRATE_SB_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-migrate-sb-activator.php
 */
function activate_migrate_sb() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-migrate-sb-activator.php';
	Migrate_Sb_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-migrate-sb-deactivator.php
 */
function deactivate_migrate_sb() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-migrate-sb-deactivator.php';
	Migrate_Sb_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_migrate_sb' );
register_deactivation_hook( __FILE__, 'deactivate_migrate_sb' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-migrate-sb.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_migrate_sb() {

	$plugin = new Migrate_Sb();
	$plugin->run();

}
run_migrate_sb();
