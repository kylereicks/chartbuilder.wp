<?php
if(!class_exists('Chartbuilder_WP')){
  class Chartbuilder_WP{

    // Setup singleton pattern
    public static function get_instance(){
      static $instance;

      if(null === $instance){
        $instance = new self();
      }

      return $instance;
    }

    private function __clone(){
      return null;
    }

    private function __wakeup(){
      return null;
    }

    public static function deactivate(){
      self::clear_options();
    }

    private static function clear_options(){
      global $wpdb;
      $options = $wpdb->get_col('SELECT option_name FROM ' . $wpdb->options . ' WHERE option_name LIKE \'%chartbuilder_wp%\'');
      foreach($options as $option){
        delete_option($option);
      }
    }

    // Constructor, add actions and filters
    private function __construct(){
      add_action('init', array($this, 'add_update_hook'));
      add_filter('media_upload_tabs', array($this, 'register_chartbuilder_wp_tab'));
      add_action('media_upload_chartbuilder_wp', array($this, 'chartbuilder_wp_tab_view'));
    }

    public function register_chartbuilder_wp_tab($tabs){
      $chartbuilder_tab = array('chartbuilder_wp' => 'Chartbuilder');
      return array_merge($tabs, $chartbuilder_tab);
    }

    public function chartbuilder_wp_tab_view(){
      include(CHARTBUILDER_WP_PATH . 'chartbuilder/index.php');
    }

    public function add_update_hook(){
      if(get_option('chartbuilder_wp_version') !== CHARTBUILDER_WP_VERSION){
        update_option('chartbuilder_wp_update_timestamp', time());
        update_option('chartbuilder_wp_version', CHARTBUILDER_WP_VERSION);
        do_action('chartbuilder_wp_updated');
      }
    }

  }
}
