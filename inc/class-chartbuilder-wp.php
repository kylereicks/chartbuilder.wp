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
      add_action('wp_enqueue_media', array($this, 'chartbuilder_wp_admin_scripts'));
      add_action('wp_ajax_chartbuilder_upload', array($this, 'chartbuilder_upload'));
    }

    public function register_chartbuilder_wp_tab($tabs){
      $chartbuilder_tab = array('chartbuilder_wp' => 'Chartbuilder');
      return array_merge($tabs, $chartbuilder_tab);
    }

    public function chartbuilder_wp_tab_view(){
      include(CHARTBUILDER_WP_PATH . 'chartbuilder/index.php');
?>
      <script>
      window.onload = function(){
        parent.chartbuilderIframeLoaded();
      }
      </script>
<?php
    }

    public function chartbuilder_wp_admin_scripts(){
      wp_enqueue_script('chartbuilder-wp-image-upload', CHARTBUILDER_WP_URL . 'js/chartbuilder-wp-image-upload.js', array('jquery', 'underscore', 'backbone', 'plupload'), CHARTBUILDER_WP_VERSION, true);
    }

    public function chartbuilder_upload(){
      if(!function_exists('wp_handle_sideload')){
        require_once(ABSPATH . 'wp-admin/includes/file.php');
      }

      if(!function_exists('wp_get_current_user')){
        require_once(ABSPATH . 'wp-includes/pluggable.php');
      }

      if(!function_exists('wp_generate_attachment_metadata')){
        require_once(ABSPATH . 'wp-admin/includes/image.php');
      }

      $post_id = $_POST['postID'];
      $image_data = base64_decode($_POST['imageBase64Data']);
      $image_title = $_POST['imageTitle'];
      $image_filename = str_replace(' ', '_', $image_title) . '.png';

      $upload_dir = wp_upload_dir();
      $year_month = str_replace($upload_dir['basedir'] . '/', '', $upload_dir['path'] . '/');
      $upload_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) . DIRECTORY_SEPARATOR;
      $time = time();

      $image_upload = file_put_contents($upload_path . $time . $image_filename, $image_data);

      $file = array();
      $file['error'] = '';
      $file['tmp_name'] = $upload_path . $time . $image_filename;
      $file['name'] = $image_filename;
      $file['type'] = 'image/png';
      $file['size'] = filesize($upload_path . $time . $image_title);

      $file_return = wp_handle_sideload($file, array('test_form' => false));

      if(!isset($file_return['error'])){
        unlink($file['tmp_name']);
      }

      $attachment = array(
        'post_mime_type' => $file_return['type'],
        'post_title' => $image_title,
        'post_content' => '',
        'post_status' => 'inherit',
        'guid' => $file_return['url']
      );
      $attachment_id = wp_insert_attachment($attachment, $image_title, $post_id);
      $attachment_data = wp_generate_attachment_metadata($attachment_id, $file_return['file']);

      foreach($attachment_data['sizes'] as $size => $object){
        $attachment_data['sizes'][$size]['file'] = $year_month . $attachment_data['sizes'][$size]['file'];
      }

      $success = wp_update_attachment_metadata($attachment_id, $attachment_data);

      if(false !== $success){
        wp_send_json_success();
      }else{
        $data = array(
          'post_id' => $post_id,
          'image_title' => $image_title,
          'post_image_title' => $_POST['imageTitle'],
          'file_return' => $file_return,
          'upload_dir' => $upload_dir,
          'upload_path' => $upload_path,
          'image_upload' => $image_upload,
          'file' => $file,
          'attachment' => $attachment,
          'attachment_id' => $attachment_id,
          'attachment_data' => $attachment_data,
          'success' => $success
        );
        wp_send_json_error($data);
      }
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
