<?php
/*
Plugin Name: WP Profiler
Plugin URI: http://wordpress.org/extend/plugins/wp-profiler/
Description: Profile the plugins on your wordpress site.
Author: Kurt Payne, GoDaddy.com
Version: 0.1
Author URI: http://godaddy.com/
*/

/**************************************************************************/
/**        PACKAGE CONSTANTS                                             **/
/**************************************************************************/

// Shortcut for knowing our path
define('WPP_PATH',  realpath(dirname(__FILE__)));

// Flag file for enabling profile mode
define('WPP_FLAG_FILE', WPP_PATH . DIRECTORY_SEPARATOR . '.profiling_enabled');

// Directory for profiles
$uploads_dir = wp_upload_dir();
define('WPP_PROFILES_PATH', $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles');


/**************************************************************************/
/**        START PROFILING                                               **/
/**************************************************************************/

// Start profiling.  If it's already been started, this line won't do anything
require_once(WPP_PATH . '/start-profile.php');


/**************************************************************************/
/**        PLUGIN HOOKS                                                  **/
/**************************************************************************/

// Global plugin object
$wpp_profiler_plugin = new WP_Profiler();

// Admin hooks
if (is_admin()) {
	// Show the 'Profiler' option under the 'Plugins' menu
	add_action ('admin_menu', array($wpp_profiler_plugin, 'settings_menu'));

	// Ajax actions
	add_action('wp_ajax_wpp_start_scan', array($wpp_profiler_plugin, 'ajax_start_scan'));
	add_action('wp_ajax_wpp_stop_scan', array($wpp_profiler_plugin, 'ajax_stop_scan'));

	// Show any notices
	add_action('admin_notices', array($wpp_profiler_plugin, 'show_notices'));

	// Early init actions (processing bulk table actions, loading libraries, etc.)
	add_action('admin_head', array($wpp_profiler_plugin, 'early_init'));
}

// Remove the admin bar when in profiling mode
if (defined('WPP_PROFILING_STARTED')) {
	add_action('plugins_loaded', array($wpp_profiler_plugin, 'remove_admin_bar'));
}

// Install / uninstall hooks
register_activation_hook(WPP_PATH . DIRECTORY_SEPARATOR . 'wp-profiler.php', array($wpp_profiler_plugin, 'activate'));
register_deactivation_hook(WPP_PATH . DIRECTORY_SEPARATOR . 'wp-profiler.php', array($wpp_profiler_plugin, 'deactivate'));
register_uninstall_hook(WPP_PATH . DIRECTORY_SEPARATOR . 'wp-profiler.php', array('WP_Profiler', 'uninstall'));
if (function_exists('is_multisite') && is_multisite()) {
	add_action('wpmu_add_blog', array($wpp_profiler_plugin, 'sync_profiles_folder'));
	add_action('wpmu_delete_blog', array($wpp_profiler_plugin, 'sync_profiles_folder'));
}

/**
 * WordPress Plugin Profiler Plugin Controller
 *
 * @author Kurt Payne, GoDaddy.com
 * @version 1.0
 * @package WP_Profiler
 */
class WP_Profiler {
	
	/**
	 * List table of the profile scans
	 * @var wpp_profile_table
	 */
	public $wpp_scan_table = null;
	
	/**
	 * Remove the admin bar from the customer site when profiling is enabled
	 * to prevent skewing the numbers, as much as possible.  Also prevent ssl
	 * warnings by forcing content into ssl mode if the admin is in ssl mode
	 * @return void
	 */
	public function remove_admin_bar() {
		if (!is_admin()) {
			remove_action('wp_footer', 'wp_admin_bar_render', 1000);
			if (true === force_ssl_admin()) {
				add_filter('site_url', array($this, '_fix_url'));
				add_filter('admin_url', array($this, '_fix_url'));
				add_filter('post_link', array($this, '_fix_url'));
				add_filter('category_link', array($this, '_fix_url'));
				add_filter('get_archives_link', array($this, '_fix_url'));
				add_filter('tag_link', array($this, '_fix_url'));
				add_filter('home_url', array($this, '_fix_url'));
			}
		}
	}

	/**
	 * Replace http with https to avoid SSL warnings in the preview iframe if the admin is in SSL
	 * @param string $url
	 * @return string
	 */
	public function _fix_url($url) {
		return str_ireplace('http://', 'https://', $url);
	}

	/**
	 * Add the 'Profiler' option under the 'Plugins' menu
	 * @return void
	 */
	public function settings_menu() {
		if (function_exists ( 'add_submenu_page' )) {
			$page = add_submenu_page ( 'plugins.php', 'Plugin Performance', 'Performance', 'manage_options', basename(__FILE__), array($this, 'dispatcher'));
			add_action('load-' . $page, array($this, 'load_libraries'));
			add_action('admin_print_scripts-' . $page, array($this, 'load_scripts'));
			add_action('admin_print_styles-' . $page, array($this, 'load_styles'));
		}
	}

	/**
	 * Load the necessary resources
	 * @uses wp_enqueue_script
	 * @uses jquery, jquery-ui, jquery.corners
	 * @uses flot, flot.pie
	 * @return void
	 */
	public function load_libraries() {

		// Load php libraries libraries
		require_once (WPP_PATH . '/class.wpp-profile-table-sorter.php');
		require_once (WPP_PATH . '/class.wpp-profile-table.php');
		require_once (WPP_PATH . '/class.wpp-profile-reader.php');
	}
	
	/**
	 * Load javascripts
	 * @uses wp_enqueue_script
	 * @uses jquery, jquery-ui, jquery.corners
	 * @uses flot, flot.pie
	 * @return void
	 */
	public function load_scripts() {
		wp_enqueue_script('flot', plugins_url () . '/wp-profiler/js/jquery.flot.min.js', array('jquery'));
		wp_enqueue_script('flot.pie', plugins_url () . '/wp-profiler/js/jquery.flot.pie.min.js', array('jquery', 'flot'));
		wp_enqueue_script('wpp_jquery_ui', plugins_url () . '/wp-profiler/js/jquery-ui-1.8.16.custom.min.js', array('jquery'));
		wp_enqueue_script('wpp_corners', plugins_url() . '/wp-profiler/js/jquery.corner.js', array('jquery'));
	}

	/**
	 * Load styles
	 * @uses wp_enqueue_style
	 * @uses jquery-ui
	 * @return void
	 */
	public function load_styles() {
		wp_enqueue_style('wpp_jquery_ui_css', plugins_url () . '/wp-profiler/css/custom-theme/jquery-ui-1.8.16.custom.css');
		wp_enqueue_style('wpp_css', plugins_url () . '/wp-profiler/css/wpp.css');
	}
	
	/**
	 * Load the necessary resources
	 * @uses wp_enqueue_script
	 * @uses jquery, jquery-ui, jquery.corners
	 * @uses flot, flot.pie
	 * @return void
	 */
	public function early_init() {

		// Only for our page
		if (isset($_REQUEST['page']) && basename(__FILE__) == $_REQUEST['page']) {

			// Load the list table, let it handle any bulk actions
			$this->scan_table = new wpp_profile_table();
			$this->scan_table->prepare_items();

			// Usability message
			if (!defined('WPP_PROFILING_STARTED')) {
				$this->add_notice('Click "Start Scan" to run a performance scan of your website.');
			}
		}
	}

	/**
	 * Dispatcher function.  All requests enter through here
	 * and are routed based upon the wpp_action request variable
	 * @uses $_REQUEST['wpp_action']
	 * @return void
	 */
	public function dispatcher() {
		$wpp_action = '';
		if (! empty ( $_REQUEST ['wpp_action'] )) {
			$wpp_action = $_REQUEST ['wpp_action'];
		}
		switch ($wpp_action) {
			case 'list-scans' :
				$this->list_scans ();
				break;
			case 'view-scan' :
				$this->view_scan ();
				break;
			case 'start-scan' :
				$this->start_scan ();
				break;
			case 'help' :
				$this->show_help();
				break;
			default :
				$this->scan_settings_page();
		}
	}

	/**
	 * Get a list of pages for the auto-scanner
	 * @return array
	 */
	public function list_of_pages() {

		// Start off the scan with the home page
		$pages = array(get_home_url()); // Home page

		// Get the default RSS feed
		$pages[] = get_feed_link();

		// Search for 'e'
		$pages[] = home_url('?s=e');

		// Get the latest 10 posts
		$tmp = preg_split('/\s+/', wp_get_archives('type=postbypost&limit=10&echo=0'));
		if (!empty($tmp)) {
			foreach ($tmp as $page) {
				if (preg_match("/href='([^']+)'/", $page, $matches)) {
					$pages[] = $matches[1];
				}
			}
		}

		// Fix SSL
		if (true === force_ssl_admin()) {
			foreach ($pages as $k => $v) {
				$pages[$k] = str_replace('http://', 'https://', $v);
			}
		}

		// Done
		return $pages;
	}

	/**************************************************************/
	/** AJAX FUNCTIONS                                           **/
	/**************************************************************/

	/**
	 * Start scan
	 * @return void
	 */
	public function ajax_start_scan() {

		// Check nonce
		if (! wp_verify_nonce ( $_POST ['wpp_nonce'], 'wpp_ajax_start_scan' ))
			wp_die ( 'Invalid nonce' );

		// Sanitize the file name
		$filename = sanitize_file_name($_POST['wpp_scan_name']);

		// Create flag file
		if (file_exists(WPP_FLAG_FILE)) {
			$json = json_decode(file_get_contents(WPP_FLAG_FILE));
		} else {
			$json = array();
		}
		
		// Site url
		$site_url = parse_url(get_home_url(), PHP_URL_PATH);
		if (null === $site_url) {
			$site_url = '/';
		}

		// Add the entry (multisite installs can run more than one concurrent profile)
		$json[] = array(
			'ip'                   => $_POST['wpp_ip'],
			'disable_opcode_cache' => ('true' == $_POST['wpp_disable_opcode_cache']),
			'site_url'             => $site_url,
			'name'                 => $filename
		);
		$flag1 = file_put_contents(WPP_FLAG_FILE, json_encode($json));
		
		// Kick start the profile file
		if (!file_exists(WPP_PROFILES_PATH . "/$filename.json")) {
			$flag2 = file_put_contents(WPP_PROFILES_PATH . "/$filename.json", '');
		} else {
			$flag2 = true;
		}

		// Check if either operation failed
		if (false === $flag1 & $flag2) {
			wp_die(0);
		} else {
			echo 1;
			die();
		}
	}

	/**
	 * Stop scan
	 * @return void
	 */
	public function ajax_stop_scan() {

		// Check nonce
		if (! wp_verify_nonce ( $_POST ['wpp_nonce'], 'wpp_ajax_stop_scan' ))
			wp_die ( 'Invalid nonce' );

		// If there's no file, return an error
		if (!file_exists(WPP_FLAG_FILE)) {
			wp_die(0);
		}

		// Get the file
		$json = json_decode(file_get_contents(WPP_FLAG_FILE));
		
		// Stop all sites who match the current site's URL
		$site_url = parse_url(get_home_url(), PHP_URL_PATH);
		if (null === $site_url) {
			$site_url = '/';
		}
		foreach ($json as $k => $v) {
			if ($site_url == $v->site_url) {
				unset($json[$k]);
			}
		}

		// Rewrite the file
		$flag = file_put_contents(WPP_FLAG_FILE, json_encode($json));
		if (!$flag) {
			wp_die(0);
		}

		// Tell the user what happened
		$this->add_notice('Turned off performance scanning.');

		// Return the last filename
		echo $v->name . '.json';
		die();
	}


	/**************************************************************/
	/** CURRENT PAGE                                             **/
	/**************************************************************/

	/**
	 * Show the settings page.
	 * This is where the user can start/stop the scan
	 */
	public function scan_settings_page() {
		require_once (WPP_PATH . '/templates/template.php');
	}


	/**************************************************************/
	/** HELP PAGE                                                **/
	/**************************************************************/

	/**
	 * Show the help page.
	 */
	public function show_help() {
		require_once (WPP_PATH . '/templates/template.php');
	}


	/**************************************************************/
	/**  HISTORY PAGE                                            **/
	/**************************************************************/

	/**
	 * View the results of a scan
	 * @uses $_REQUEST['name']
	 * @return void
	 */
	public function view_scan() {
		require_once (WPP_PATH . '/templates/template.php');
	}

	/**
	 * Show a list of available scans.
	 * Uses WP List table to handle UI and sorting.
	 * Uses wpp_profile_table to handle deleting
	 * @uses WP_List_Table
	 * @uses jquery
	 * @uses wpp_profile_table
	 * @return void
	 */
	public function list_scans() {
		require_once (WPP_PATH . '/templates/template.php');	
	}

	/**
	 * Get the latest performance scan
	 * @return string|false
	 */
	public function get_latest_profile() {

		// Open the directory
		$dir = opendir(WPP_PROFILES_PATH);
		if (false === $dir) {
			wp_die('Cannot read profiles directory');
		}

		// Loop through the files, get the path and the last modified time
		$files = array();
		while (false !== ($file = readdir($dir))) {
			if ('.json' == substr($file, -5)) {
				$files[filemtime(WPP_PROFILES_PATH . "/$file")] = WPP_PROFILES_PATH . "/$file";
			}
		}
		closedir($dir);

		// If there are no files, return false
		if (empty($files)) {
			return false;
		}

		// Sort the files by the last modified time, return the latest
		ksort($files);
		return array_pop($files);
	}

	/**
	 * Add a notices
	 * @uses transients
	 * @param string $notice
	 * @return void
	 */
	public function add_notice($notice) {

		// Get any notices on the stack
		$notices = get_transient('wpp_notices');
		if (empty($notices)) {
			$notices = array();
		}

		// Add the notice to the stack
		$notices[] = $notice;

		// Save the stack
		set_transient('wpp_notices', $notices);
	}

	/**
	 * Display notices
	 * @uses transients
	 * @return voide
	 */
	public function show_notices() {
		$notices = get_transient('wpp_notices');
		if (!empty($notices)) {
			$notices = array_unique($notices);
			foreach ($notices as $notice) {
				echo '<div id="message" class="updated"><p>' . htmlentities($notice) . '</p></div>';
			}
		}
		set_transient('wpp_notices', array());
		if (false !== $this->scan_enabled()) {
			echo '<div id="message" class="updated"><p>Performance scanning is enabled.</p></div>';
		}
	}

	/**
	 * Activation hook
	 * Install the profiler loader in the most optimal place
	 * @return void
	 */
	public function activate() {
		$sapi = strtolower(php_sapi_name());

		// .htaccess for mod_php
		if ('apache2handler' == $sapi) {
			insert_with_markers(WPP_PATH . '/../../../.htaccess', 'wp-profiler', array('php_value auto_prepend_file "' . WPP_PATH . DIRECTORY_SEPARATOR . 'start-profile.php"'));

		//.user.ini for php 5.3.0+ if auto_prepend_file isn't set
		# Can't use this until there's a way to foce php to reload .user.ini settings, otherwise the user
		# can delete this plugin and the .user.ini file can be cached and still looking for start-profile.php
		# which has been deleted and will cause a fatal error
		# } elseif (version_compare(phpversion(), '5.3.0', '>=') && '' == ini_get('auto_prepend_file') && in_array($sapi, array('cgi', 'cgi-fcgi'))) {
		#	$file = WPP_PATH . '/../../../.user.ini';
		#	if (!file_exists($file) && is_writable(dirname($file))) {
		#		file_put_contents($file, 'auto_prepend_file = "' . WPP_PATH . DIRECTORY_SEPARATOR . 'start-profile.php"' . PHP_EOL);
		#	} elseif (file_exists($file) && is_writable($file)) {
		#		file_put_contents($file, PHP_EOL . 'auto_prepend_file = "' . WPP_PATH . DIRECTORY_SEPARATOR . 'start-profile.php"' . PHP_EOL);
		#	}
		}

		// Always try to create the mu-plugin loader in case either of the above methods fail

		// mu-plugins doesn't exist
		if (!file_exists(WPP_PATH . '/../../mu-plugins/') && is_writable(WPP_PATH . '/../../')) {
			$flag = wp_mkdir_p(WPP_PATH . '/../../mu-plugins/');
		}
		if (file_exists(WPP_PATH . '/../../mu-plugins/') && is_writable(WPP_PATH . '/../../mu-plugins')) {
			file_put_contents(WPP_PATH . '/../../mu-plugins/wp-profiler.php', '<' . "?php // Start profiling\nrequire_once(realpath(dirname(__FILE__)) . '/../plugins/wp-profiler/start-profile.php'); ?" . '>');
		}
		
		// Create the profiles folder
		$this->sync_profiles_folder();
	}

	/**
	 * Make the profiles folder
	 * @param string $path
	 * @return void
	 */
	private function _make_profiles_folder($path) {
		wp_mkdir_p($path);
		if (!file_exists("$path/.htaccess")) {
			file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', "Deny from all\n");
		}
		if (!file_exists("$path/index.php")) {
			file_put_contents($path. DIRECTORY_SEPARATOR . 'index.php', '<' . "?php header('Status: 404 Not found'); ?" . ">\nNot found");
		}
	}
	
	/**
	 * Delete the profiles folder
	 * @param string $path
	 * @return void
	 */
	private function _delete_profiles_folder($path) {
		$dir = opendir($path);
		while (($file = readdir($dir)) !== false) {
			if ($file != '.' && $file != '..') {
				unlink($path . DIRECTORY_SEPARATOR . $file);
			}
		}
		closedir($dir);
		rmdir($path);
	}	

	/**
	 * Deactivation hook
	 * Uninstall the profiler loader
	 * @return void
	 */
	public function deactivate() {

		// Remove any .htaccess modifications
		$file = WPP_PATH . '/../../../.htaccess';
		if (file_exists($file) && array() !== extract_from_markers($file, 'wp-profiler')) {
			insert_with_markers($file, 'wp-profiler', array('# removed during uninstall'));
		}

		// Can't use this until there's a way to foce php to reload .user.ini settings
		// Remove any .user.ini modifications
		#$file = WPP_PATH . '/../../../.user.ini';
		#$ini = file_get_contents($file);
		#if (file_exists($file) && preg_match('/auto_prepend_file\s*=\s*".*start-profile\.php"/', $ini)) {
		#	file_put_contents($file, preg_replace('/[\r\n]*auto_prepend_file\s*=\s*".*start-profile\.php"[\r\n]*/', '', $ini));
		#}

		// Remove mu-plugin
		if (file_exists(WPP_PATH . '/../../mu-plugins/wp-profiler.php')) {
			if (is_writable(WPP_PATH . '/../../mu-plugins/wp-profiler.php')) {
				// Some servers give write permission, but not delete permission.  Empty the file out, first, then try to delete it.
				file_put_contents(WPP_PATH . '/../../mu-plugins/wp-profiler.php', '');
				unlink(WPP_PATH . '/../../mu-plugins/wp-profiler.php');
			}
		}
	}
	
	/**
	 * Sync profiles folder
	 * Call whenever a blog is added / removed
	 * @return void
	 */
	public function sync_profiles_folder() {
		
		// Base blog profiles folder
		$uploads_dir = wp_upload_dir();
		$folder = $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles';
		$this->_make_profiles_folder($folder);

		// Only for multisite
		if (!function_exists('is_multisite') || !is_multisite()) {
			return;
		}

		// List profiles/<blog id> folders
		$folders = array();
		$dir = opendir($folder);
		while (($file = readdir($dir)) !== false) {
			if ($file != '.' && $file != '..' && is_dir("$folder/$file") && is_numeric($file)) {
				$folders[] = $file;
			}
		}
		closedir($dir);

		// List blogs
		$blogs = array();
		$blogs = get_blog_list( 0, 'all' );
		foreach ($blogs as $blog) {
			$blogs[] = $blog['blog_id'];
		}

		// Folders without a blog
		foreach (array_diff($folders, $blogs) as $id) {
			$this->_delete_profiles_folder($folder . DIRECTORY_SEPARATOR . $id);
		}
		
		// Blogs without a folder
		foreach (array_diff($blogs, $folders) as $id) {
			switch_to_blog($id);
			$uploads_dir = wp_upload_dir();
			$folder = $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles';
			$this->_make_profiles_folder($folder);
			restore_current_blog();
		}
	}
	
	/**
	 * Uninstall hook
	 * Remove profile data
	 * @return void
	 */
	public static function uninstall() {
		// This is a static function so it needs an instance
		// Since I'm myself, I can call my own private methods
		$class = __CLASS__;
		$me = new $class();
		
		// Delete the profiles folder
		if (function_exists('is_multisite') && is_multisite()) {
			$blogs = get_blog_list( 0, 'all' );
			foreach ($blogs as $blog) {
				switch_to_blog($blog['blog_id']);
				$uploads_dir = wp_upload_dir();
				$folder = $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR;
				$me->_delete_profiles_folder($folder);
			}
			restore_current_blog();
		} else {
			$me->_delete_profiles_folder(WPP_PROFILES_PATH);
		}
	}

	/**
	 * Check to see if a scan is enabled
	 * @return array|false
	 */
	public function scan_enabled() {
		if (!file_exists(WPP_FLAG_FILE)) {
			return false;
		}
		$site_url = parse_url(get_home_url(), PHP_URL_PATH);
		if (null === $site_url) {
			$site_url = '/';
		}
		$json = json_decode(file_get_contents(WPP_FLAG_FILE), true);
		foreach ($json as $v) {
			if ($site_url == $v['site_url']) {
				return $v;
			}
		}
		return false;
	}
}
