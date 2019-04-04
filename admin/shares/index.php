<?php

if(!class_exists('WP_List_Table')){
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function lmgps_plugin_menu_shares() {
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