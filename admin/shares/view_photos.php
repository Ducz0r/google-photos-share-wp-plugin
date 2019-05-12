<?php
/**********************************************************************************
 * SETTINGS PAGE: Shares list subpage - view share photos logic
 *********************************************************************************/

/** JavaScript logic */
add_action( 'admin_footer', 'lmgps_plugin_menu_shares_view_share_footer_js' );

function lmgps_plugin_menu_shares_view_share_footer_js() {
  $ajax_nonce = wp_create_nonce('lmgps-view-share-photos-ajax');
  ?>
  <script type="text/javascript">
  jQuery(document).ready(function($) {

    var mainWrapper = $('.lmgps-admin-shares');
    var viewPhotosHrefs = mainWrapper.find('a[data-action=view-photos]');

    var modalWindowMainContainer = $('#lmgps-view-share-photos-container');
    var photosContainer = modalWindowMainContainer.find('.photos-container');

    viewPhotosHrefs.off('click').on('click', function(e) {
      e.preventDefault();
      photosContainer.text('');

      var id = $(this).data('id');

      var data = {
        action: 'lmgps_shares_view_photos',
        security: '<?php echo $ajax_nonce; ?>',
        id: id
      };

      $.ajax({
        url: ajaxurl,
        data: data,
        dataType: 'JSON',
        method: 'POST',
        complete: function(data) {
          var photoUrls = data.responseJSON.photoUrls;

          // Display the images
          for (i = 0; i < photoUrls.length; i++) {
            var newDiv = document.createElement('div');
            photosContainer[0].appendChild(newDiv);
            var newImg = document.createElement('img');
            newImg.setAttribute('src', photoUrls[i] + '=h150-no');
            newDiv.appendChild(newImg);
          }
        }
      });
    });
  });
  </script> <?php
}

/** View share photos logic */
add_action('wp_ajax_lmgps_shares_view_photos', 'lmgps_shares_view_photos');

function lmgps_shares_view_photos() {
  global $wpdb;

  // Check for AJAX security origin
  check_ajax_referer('lmgps-view-share-photos-ajax', 'security');

  $share_id = $_POST['id'];

  if (!isset($share_id)) {
    wp_send_json_error('Could not retrieve ID from the post parameters.');
  } else {
    // Retrieve the photo URLs

    $data = $wpdb->get_row('SELECT photo_urls FROM ' . LMGPS_TABLE_NAME . ' WHERE id = ' . $share_id . ';');

    if (is_null($data)) {
      wp_send_json_error('Could not retrieve the share from the database.');
    } else {
      $photo_urls = maybe_unserialize($data->photo_urls);

      $output = array();
      $output['success'] = true;
      $output['photoUrls'] = array();
      foreach ($photo_urls as $val) {
        array_push($output['photoUrls'], $val);
      }

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
