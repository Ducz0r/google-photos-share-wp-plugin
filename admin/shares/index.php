<?php
/**********************************************************************************
 * SETTINGS PAGE: Shares list subpage
 *********************************************************************************/

if(!class_exists('WP_List_Table')){
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
add_thickbox();

function lmgps_plugin_menu_shares() {
  global $wpdb;

  // Permissions check
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page!'));
  }

  //Create an instance of our package class
  $table = new LMGPS_Menu_Table();
  $table->prepare_items();

/** TEMPLATE *********************************************************************/
?>

<!-- Add new Share Modal window contents -->
<div id="tb-lmgps-new-share-container" style="display: none;">
  <div class="wrap lmgps-new-share-container">
    <form id="lmgps-new-share-form">
      <p>Write your shared Google Photos URL in the form below.</p>

      <table class="form-table">
        <tbody>
          <tr class="form-field form-required">
            <th scope="row">
              <label for="share_url">URL <span class="description">(required)</span></label>
            </th>
            <td>
              <input name="share_url" type="text" autocorrect="off" aria-required="true" placeholder="https://photos.app.goo.gl/xxxxxxxxxxxxxxxxx">
            </td>
          </tr>
        </tbody>
        </table>
      <p class="submit">
        <input type="submit" class="button button-primary disabled" disabled="disabled" value="Fetch Share">
      </p>
      <div class="error-alert" style="display: none;"></div>
    </form>

    <form id="lmgps-new-share-save-form" style="display: none;">
      <input type="hidden" name="share_url">
      <input type="hidden" name="photo_urls">
      <p class="message"></p>
      <p class="submit">
        <input type="submit" class="button button-primary" value="Save Share to Wordpress">
      </p>
      <p class="photos-label">These are the extracted photos:</p>
      <div class="photos-container"></div>
    </form>
  </div>
</div>

<!-- View Share Photos Modal window contents -->
<div id="tb-lmgps-view-share-photos-container" style="display: none;">
  <div class="wrap" id="lmgps-view-share-photos-container">
    <div class="photos-container"></div>
  </form>
  </div>
</div>

<!-- Main page contents -->
<div class="wrap lmgps-admin-shares">
  <h1 class="wp-heading-inline"><span class="dashicons dashicons-images-alt"></span>&nbsp;Google Photos Share - Shares</h1>
  <a href="#TB_inline?&width=800&height=600&inlineId=tb-lmgps-new-share-container"
     title="Add New Google Photos Share"
     id="new-share-open-modal-btn"
     class="thickbox page-title-action">
    Add New
  </a>
  <hr class="wp-header-end">

  <form id="lmgps-menu-filter" method="get">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
    <?php $table->display() ?>
  </form>
</div>

<?php
/** END OF TEMPLATE **************************************************************/
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
        return $val;
      case 'share_url':
        return sprintf('<a href="%s" target="_blank">%s</a>', $val, $val);
      case 'photos_count':
        return $val;
      case 'status':
        if ($val == 1) {
          return 'Active';
        } else {
          return 'Inactive';
        }
      case 'options':
        return '';
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }

  function column_options($item) {
    //Build row actions
    $actions = array(
      'options' =>
        sprintf(
          '<span><a href="#TB_inline?&width=800&height=600&inlineId=tb-lmgps-view-share-photos-container" ' .
          'title="View Photos for Share %s" ' .
          'class="thickbox" ' .
          'data-action="view-photos" data-id="%s">View Photos</a> | </span>',
          $item['share_url'],
          $item['id']
        ) .
        sprintf(
          '<span><a href="#" data-action="delete" data-page="%s" data-id="%s">Delete</a></span>',
          $_REQUEST['page'],
          $item['id']
        )
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
      'options' => 'Options'
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