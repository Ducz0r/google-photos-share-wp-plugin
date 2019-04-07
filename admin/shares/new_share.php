<?php
/**********************************************************************************
 * SETTINGS PAGE: Shares list subpage - new share UI & logic
 *********************************************************************************/

/** JavaScript logic */
add_action( 'admin_footer', 'lmgps_plugin_menu_shares_new_share_footer_js' );

function lmgps_plugin_menu_shares_new_share_footer_js() {
  $ajax_nonce = wp_create_nonce('lmgps-new-share-ajax');
  ?>
  <script type="text/javascript" >
  jQuery(document).ready(function($) {
    var openModalBtn = $('.lmgps-admin-shares #new-share-open-modal-btn');

    var fetchForm = $('#lmgps-new-share-form');
    var inputUrl = fetchForm.find('input[type=text]');
    var submitBtn = fetchForm.find('input[type=submit]');
    var errorAlert = fetchForm.find('.error-alert');

    var saveForm = $('#lmgps-new-share-save-form');
    var saveFormMessage = saveForm.find('.message');
    var saveFormDataInput = saveForm.find('input[type=hidden].photo_urls');
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
      saveFormDataInput.val('');
      saveFormPhotoContainer.html('');
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
        security: '<?php echo $ajax_nonce; ?>',
        inputUrl: inputUrl.val()
      };

      $.ajax({
        url: ajaxurl,
        data: data,
        dataType: 'JSON',
        method: 'POST',
        success: function(data, textStatus, jqXHR) {
          if (!data.success) {
            errorAlert.text(data.data).show();
          } else {
            fetchForm.hide();

            saveFormMessage.text(data.message);
            saveFormDataInput.val(JSON.stringify(data.photo_urls));
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

  });
  </script> <?php
}

/** Actual endpoint logic */
add_action('wp_ajax_lmgps_new_share_submit_form', 'lmgps_new_share_submit_form');

function lmgps_new_share_submit_form() {
  global $wpdb;

  // Check for AJAX security origin
  check_ajax_referer('lmgps-new-share-ajax', 'security');

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
    $bodyWithoutNewLines = preg_replace('/(\r\n|\n|\r)/m', '', $response['body']);
    $rx_result = preg_match_all(
      '/\["(https:\/\/.{3}\.googleusercontent\.com\/.{139})",\d{1,8},\d{1,8},null,null,null,null,null,null,\[\d{1,16}\]\]/m',
      $bodyWithoutNewLines,
      $matches,
      PREG_SET_ORDER
    );

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

      // TODO: Check if such a share entry already exists in DB

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