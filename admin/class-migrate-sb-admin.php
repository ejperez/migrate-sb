<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/ejperez
 * @since      1.0.0
 *
 * @package    Migrate_Sb
 * @subpackage Migrate_Sb/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Migrate_Sb
 * @subpackage Migrate_Sb/admin
 * @author     EJ Perez <ej.perez@stok.se>
 */
class Migrate_Sb_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_init', function () {
			register_setting('migrate_sb_settings_group', 'migrate_sb_settings');
		});
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Migrate_Sb_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Migrate_Sb_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/migrate-sb-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Migrate_Sb_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Migrate_Sb_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/migrate-sb-admin.js', array('jquery'), $this->version, false);
	}

	public function display_admin_page()
	{
		add_menu_page(
			'Migrate SB',
			'Migrate SB',
			'manage_options',
			'migrate-sb',
			[$this, 'admin_page'],
			'dashicons-update',
			'80'
		);

		add_submenu_page(
			'migrate-sb',
			'Storyblok Settings',
			'Storyblok Settings',
			'manage_options',
			'storyblok-settings',
			[$this, 'admin_subpage'],
		);
	}

	public function admin_subpage()
	{
		include 'partials/migrate-sb-admin-display-sub.php';
	}

	public function admin_page()
	{
		include 'partials/migrate-sb-admin-display.php';
	}

	public function process_migration()
	{
		if (!isset($_POST['action']) || (isset($_POST['action']) && $_POST['action'] !== 'do_sb_migration')) {
			return;
		}

		ini_set('max_execution_time', 3600);

		require __DIR__ . '/../includes/class-migrate-sb-storyblok.php';

		$settings = get_option('migrate_sb_settings');
		$sb = new Migrate_Sb_Storyblok($settings['api_token'], $settings['space_id']);
		$sb->postStories($_POST);

		exit;
	}
}
