<?php
/**********************************************************************************
 * SETTINGS PAGE: Shares list subpage - new share UI & logic
 *********************************************************************************/

/** JavaScript logic */
add_action( 'admin_footer', 'lmgps_plugin_menu_shares_new_share_footer_js' );

function lmgps_plugin_menu_shares_new_share_footer_js() {
  $ajax_nonce_1 = wp_create_nonce('lmgps-new-share-fetch-ajax');
  $ajax_nonce_2 = wp_create_nonce('lmgps-new-share-save-ajax');
  ?>
  <script type="text/javascript">
  jQuery(document).ready(function($) {
    var openModalBtn = $('.lmgps-admin-shares #new-share-open-modal-btn');

    var fetchForm = $('#lmgps-new-share-form');
    var inputUrl = fetchForm.find('input[type=text]');
    var submitBtn = fetchForm.find('input[type=submit]');
    var errorAlert = fetchForm.find('.error-alert');

    var saveForm = $('#lmgps-new-share-save-form');
    var saveFormMessage = saveForm.find('.message');
    var saveFormDataInput = saveForm.find('input[type=hidden][name=photo_urls]');
    var saveFormDataShareUrl = saveForm.find('input[type=hidden][name=share_url]');
    var saveFormDataPhotoUrls = saveForm.find('input[type=hidden][name=photo_urls]');
    var saveFormSubmitBtn = saveForm.find('input[type=submit]');
    var saveFormPhotoContainer = saveForm.find('.photos-container');

    // Reset modal window state upon being opened
    openModalBtn.on('click', function() {
      fetchForm.show();
      inputUrl.val('').removeAttr('disabled').removeClass('disabled');
      submitBtn.attr('disabled', 'disabled').addClass('disabled');
      errorAlert.text('').hide();

      saveForm.hide();
      saveFormMessage.text('');
      saveFormDataShareUrl.val('');
      saveFormDataPhotoUrls.val('');
      saveFormSubmitBtn.removeAttr('disabled').removeClass('disabled');
      saveFormPhotoContainer.text('');
    });

    inputUrl.off('change paste keyup').on('change paste keyup', function() {
      if ($(this).val().length > 0) {
        submitBtn.removeAttr('disabled').removeClass('disabled');
      } else {
        submitBtn.attr('disabled', 'disabled').addClass('disabled');
      }
    });

    fetchForm.off('submit').on('submit', function(e) {
      e.preventDefault();

      inputUrl.attr('disabled', 'disabled').addClass('disabled');
      submitBtn.attr('disabled', 'disabled').addClass('disabled');
      errorAlert.text('').hide();

      var data = {
        action: 'lmgps_new_share_submit_form',
        security: '<?php echo $ajax_nonce_1; ?>',
        inputUrl: inputUrl.val()
      };

      $.ajax({
        url: ajaxurl,
        data: data,
        dataType: 'JSON',
        method: 'POST',
        success: function(data) {
          if (!data.success) {
            errorAlert.text(data.data).show();
          } else {
            fetchForm.hide();

            saveFormMessage.text(data.message);
            saveFormDataShareUrl.val(inputUrl.val());
            saveFormDataPhotoUrls.val(JSON.stringify(data.photo_urls));
            saveFormSubmitBtn.removeAttr('disabled').removeClass('disabled');

            // Display the images
            for (i = 0; i < data.photo_urls.length; i++) {
              var newDiv = document.createElement('div');
              saveFormPhotoContainer[0].appendChild(newDiv);
              var newImg = document.createElement('img');
              newImg.setAttribute('src', data.photo_urls[i] + '=h150-no');
              newDiv.appendChild(newImg);
            }

            saveForm.show();
          }
        },
        complete: function() {
          inputUrl.removeAttr('disabled').removeClass('disabled');
          submitBtn.removeAttr('disabled').removeClass('disabled');
        }
      });
    });

    saveForm.off('submit').on('submit', function(e) {
      e.preventDefault();

      saveFormSubmitBtn.attr('disabled', 'disabled').addClass('disabled');

      var data = {
        action: 'lmgps_new_share_submit_save_form',
        security: '<?php echo $ajax_nonce_2; ?>',
        shareUrl: saveFormDataShareUrl.val(),
        photoUrls: JSON.parse(saveFormDataPhotoUrls.val())
      };

      $.ajax({
        url: ajaxurl,
        data: data,
        dataType: 'JSON',
        method: 'POST',
        complete: function() {
          location.reload;
        }
      });
    });
  });
  </script> <?php
}

/** Fetch share logic */
add_action('wp_ajax_lmgps_new_share_submit_form', 'lmgps_new_share_submit_form');

function lmgps_new_share_submit_form() {
  global $wpdb;

  // Check for AJAX security origin
  check_ajax_referer('lmgps-new-share-fetch-ajax', 'security');

  // Call the remote endpoint/URL
  $response = wp_remote_get(
    sanitize_text_field($_POST['inputUrl']),
    array('timeout' => 40, 'redirection' => 20)
  );

  if (is_wp_error($response)) {
    // Throw error back
    wp_send_json_error('Invalid URL, could not fetch the provided URL.');
  } else {
    // Remove new lines, to a big regex check to find the photo URLs
    $bodyWithoutNewLines = preg_replace(LMGPS_REGEX_NEWLINE, '', $response['body']);
    $rx_result = preg_match_all(LMGPS_REGEX_PHOTOS, $bodyWithoutNewLines, $matches, PREG_SET_ORDER);

    if ($rx_result == false || $rx_result < 1) {
      // No photos could be found using regex
      wp_send_json_error('URL was valid, but could not fetch any shared Google Photos from it.');
    } else {
      $output = array();
      $output['success'] = true;

      // Parse the matches
      $output['photo_urls'] = array();
      $output['message'] = 'Fetch successful! ' . $rx_result . ' photos were fetched.';
      foreach ($matches as $val) {
        array_push($output['photo_urls'], $val[1]);
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

/** Save Share logic */
add_action('wp_ajax_lmgps_new_share_submit_save_form', 'lmgps_new_share_submit_save_form');

function lmgps_new_share_submit_save_form() {
  global $wpdb;

  // Check for AJAX security origin
  check_ajax_referer('lmgps-new-share-save-ajax', 'security');

  $photo_urls = $_POST['photoUrls'];

  // Save the user submitted data into the database TODO: ERROR!
  $result = $wpdb->insert(LMGPS_TABLE_NAME,
    array(
      'timestamp' => current_time('mysql'),
      'share_url' => $_POST['shareUrl'],
      'photos_count' => sizeof($photo_urls),
      'photo_urls' => $photo_urls,
      'status' => 1
    )
  );

  if (!$result) {
    wp_send_json_error('');
  } else {
    print_r(json_encode(array('success' => true)));
  }

  // Finally, end the request
  wp_die();
}