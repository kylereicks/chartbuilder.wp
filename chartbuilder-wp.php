<?php
/*
Plugin Name: Chartbuilder.WP
Plugin URI: TODO: Include GitHub repository
Description: TODO: Add description
Author: Kyle Reicks
Version: 0.0.0
Author URI: http://github.com/kylereicks/
*/

define('CHARTBUILDER_WP_PATH', plugin_dir_path(__FILE__));
define('CHARTBUILDER_WP_URL', plugins_url('/', __FILE__));
define('CHARTBUILDER_WP_VERSION', '0.0.0');

require_once(CHARTBUILDER_WP_PATH . 'inc/class-chartbuilder-wp.php');

register_deactivation_hook(__FILE__, array('Chartbuilder_WP', 'deactivate'));

Chartbuilder_WP::get_instance();
