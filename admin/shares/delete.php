<?php
/**********************************************************************************
 * SETTINGS PAGE: Shares list subpage - delete share logic
 *********************************************************************************/

/** JavaScript logic */
add_action( 'admin_footer', 'lmgps_plugin_menu_shares_delete_footer_js' );

function lmgps_plugin_menu_shares_delete_footer_js() {
  $ajax_nonce = wp_create_nonce('lmgps-delete-share-ajax');
  ?>
  <script type="text/javascript">
  jQuery(document).ready(function($) {

    var mainWrapper = $('.lmgps-admin-shares');
    var deleteHrefs = mainWrapper.find('a[data-action=delete]');

    deleteHrefs.off('click').on('click', function(e) {
      e.preventDefault();

      var id = $(this).data('id');
      var page = $(this).data('page');

      var data = {
        action: 'lmgps_shares_delete',
        security: '<?php echo $ajax_nonce; ?>',
        id: id,
        page: page
      };

      $.ajax({
        url: ajaxurl,
        data: data,
        dataType: 'JSON',
        method: 'POST',
        complete: function(data) {
          // Reload page to the given page
          window.location.href = 'admin.php?page=' + data.responseJSON.page;
        }
      });
    });
  });
  </script> <?php
}

/** Delete share logic */
add_action('wp_ajax_lmgps_shares_delete', 'lmgps_shares_delete');

function lmgps_shares_delete() {
  global $wpdb;

  // Check for AJAX security origin
  check_ajax_referer('lmgps-delete-share-ajax', 'security');

  $share_id = $_POST['id'];
  $page = $_POST['page'];

  if (!isset($share_id)) {
    wp_send_json_error('Could not retrieve ID from the post parameters.');
  } else {
    // Delete entry
    $result = $wpdb->delete(LMGPS_TABLE_NAME, array('id' => $share_id));

    if (!$result) {
      wp_send_json_error('Could not save the share from the database.');
    } else {
      $output = array();
      $output['success'] = true;
      $output['page'] = $page;

      // Return the return object
      $output = json_encode($output);
      if(is_array($output)) {
        print_r($output);
      } else {
        echo $output;
      }
    }
  }

  // Finally, end the request
  wp_die();
}
