<?php
/**
 * Plugin Name: Google Photos Share WP Plugin
 * Plugin URI:  https://github.com/Ducz0r/google-photos-share-wp-plugin
 * Description: This plugin enables sharing of Google Photos Shared images within WP (Gutenberg) posts.
 * Version:     0.0.1
 * Author:      Luka Murn <murn.luka@gmail.com>
 * Author URI:  https://github.com/Ducz0r
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 */

global $wpdb;
define('LMGPS_TABLE_NAME', $wpdb->prefix . 'lm_google_photo_shares');
define('LMGPS_MENU_SLUG_TABLE', 'lm-google-photo-shares-menu-table');
define('LMGPS_MENU_SLUG_SETTINGS', 'lm-google-photo-shares-menu-settings');

if(!defined('ABSPATH')) die();

if(!class_exists('WP_List_Table')){
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

register_activation_hook(__FILE__, 'lmgps_init_plugin');
add_action('init', 'lmgps_register_block');

add_action('admin_menu', 'lmgps_plugin_menu');
add_action('admin_init', 'bs_init_settings');

/**********************************************************************************
 * INIT PLUGIN: this call mainly initializes the custom database table.
 *********************************************************************************/
function lmgps_init_plugin() {
  $charset_collate = '';
  if (!empty($wpdb->charset)) {
    $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
  }
  if (!empty($wpdb->collate)) {
    $charset_collate .= " COLLATE {$wpdb->collate}";
  }
  // Status: 0 - Inactive, 1 - Active
  $sql = "CREATE TABLE " . LMGPS_TABLE_NAME . " (
    id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
    timestamp DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    share_url TINYTEXT NOT NULL,
    photos_count SMALLINT DEFAULT 0 NOT NULL,
    photo_urls MEDIUMTEXT DEFAULT 'a:0:{}' NOT NULL,
    status TINYINT DEFAULT 1 NOT NULL,
    UNIQUE KEY id (id)
    ) $charset_collate;";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

/**********************************************************************************
 * CUSTOM GUTENBERG BLOCK
 *********************************************************************************/

/**
 * Registers all block assets so that they can be enqueued through Gutenberg in
 * the corresponding context.
 */
function lmgps_register_block() {
  if (!function_exists('register_block_type')) {
    // Gutenberg is not active
    return;
  }

  wp_register_script(
    'lmgps-block-editor-script',
    plugins_url('block.js', __FILE__),
    array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
    filemtime(plugin_dir_path(__FILE__) . 'block.js')
  );

  wp_register_style(
    'lmgps-block-editor-style',
    plugins_url('editor.css', __FILE__),
    array('wp-edit-blocks'),
    filemtime(plugin_dir_path(__FILE__) . 'editor.css')
  );

  wp_register_style(
    'lmgps-block-style',
    plugins_url('style.css', __FILE__),
    array(),
    filemtime(plugin_dir_path(__FILE__) . 'style.css')
  );

  register_block_type('lmgps/block', array(
    'style' => 'lmgps-block-style',
    'editor_style' => 'lmgps-block-editor-style',
    'editor_script' => 'lmgps-block-editor-script',
  ));
}

/**********************************************************************************
 * CUSTOM ADMIN MENU SECTION
 *********************************************************************************/

function lmgps_plugin_menu() {
  add_menu_page('Google Photos Share', 'Google Photos Share', 'manage_options', LMGPS_MENU_SLUG_TABLE);
  add_submenu_page(LMGPS_MENU_SLUG_TABLE, 'Google Photos Share - Shares', 'View shares', 'manage_options', LMGPS_MENU_SLUG_TABLE, 'lmgps_plugin_menu_table');
  add_submenu_page(LMGPS_MENU_SLUG_TABLE, 'Google Photos Share - Settings', 'Settings', 'manage_options', LMGPS_MENU_SLUG_SETTINGS, 'lmgps_plugin_menu_settings');
}

function lmgps_plugin_menu_table() {
  global $wpdb;

  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page!'));
  }

  if (isset($_REQUEST['delete']) && isset($_REQUEST['id'])) {
    // Delete the entry with the specified ID
    $wpdb->delete(LMGPS_TABLE_NAME, array('id' => $_REQUEST['id']));
  }

  //Create an instance of our package class
    $table = new LMGPS_Menu_Table();
    //Fetch, prepare, sort, and filter our data...
    $table->prepare_items();

  ?>
  <div class="wrap">
    <h1><span class="dashicons dashicons-images-alt"></span>&nbsp;Google Photos Share - Shares</h1>

    <form id="lmgps-menu-filter" method="get">
      <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
      <?php $table->display() ?>
    </form>

  </div>
  <?php
}

/**
 * The custom table to be used for displaying registration entries.
 */
class LMGPS_Menu_Table extends WP_List_Table {
  function __construct() {
    global $status, $page;

    //Set parent defaults
    parent::__construct(array(
      'singular' => 'share', //singular name of the listed records
      'plural' => 'shares', //plural name of the listed records
      'ajax' => true //does this table support ajax?
    ));
  }

  function column_default($item, $column_name) {
    $val = $item[$column_name];

    switch($column_name) {
      case 'id':
      case 'timestamp':
      case 'share_url':
      case 'photos_count':
        return $val;
      case 'status':
        if ($val == 1) {
          return 'Active';
        } else {
          return 'Inactive';
        }
      case 'delete':
        return '';
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }

  function column_delete($item) {
    //Build row actions
    $actions = array(
      'delete' => sprintf('<a href="?page=%s&delete&id=%s">Delete</a>',$_REQUEST['page'], $item['id'])
    );

    //Return the title contents
    return sprintf($this->row_actions($actions));
  }

  function get_columns() {
    $columns = array(
      'id' => 'ID', //Render a checkbox instead of text
      'timestamp' => 'Time added',
      'share_url' => 'Share URL',
      'photos_count' => '# of shared photos',
      'status'  => 'Status',
      'delete' => 'Delete'
    );
    return $columns;
  }

  function get_sortable_columns() {
    $sortable_columns = array(
      'id' => array('id', false), //true means it's already sorted
      'timestamp' => array('timestamp', false),
      'photos_count' => array('photos_count', false),
      'status' => array('status', false)
    );
    return $sortable_columns;
  }

  function prepare_items() {
    global $wpdb;

    $per_page = 20;
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array($columns, $hidden, $sortable);
    $data = $wpdb->get_results('SELECT * FROM ' . LMGPS_TABLE_NAME . ' ORDER BY ' . ((!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'id') . ' ' . ((!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'ASC') . ';', ARRAY_A);
    $current_page = $this->get_pagenum();
    $total_items = count($data);
    $data = array_slice($data, (($current_page-1)*$per_page), $per_page);
    $this->items = $data;
    $this->set_pagination_args(array(
      'total_items' => $total_items,
      'per_page' => $per_page,
      'total_pages' => ceil($total_items/$per_page)
    ));
  }
}

function lmgps_plugin_menu_settings() {
  ?>
  <div class="wrap">
    <h1><span class="dashicons dashicons-admin-settings"></span>&nbsp;Google Photos Share - Settings</h1>
    <p><i>Currently, there are no settings for this plugin.</i></p>
  </div>
  <?php
}