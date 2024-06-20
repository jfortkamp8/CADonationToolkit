add_action('wp_ajax_load_pyramid_html', 'load_pyramid_html');
add_action('wp_ajax_nopriv_load_pyramid_html', 'load_pyramid_html');

function load_pyramid_html() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pyramid_nonce')) {
        error_log('Invalid nonce');
        wp_send_json_error('Invalid nonce');
        return;
    }

    if (!isset($_POST['campaign_id'])) {
        error_log('Missing campaign_id');
        wp_send_json_error('Missing campaign_id');
        return;
    }

    global $wpdb;
    $campaign_id = intval($_POST['campaign_id']);
    $table = $wpdb->prefix . 'toolkit_pyramid_state';
    $result = $wpdb->get_row($wpdb->prepare("SELECT html_content FROM $table WHERE campaign_id = %d", $campaign_id), ARRAY_A);

    if ($result) {
        wp_send_json_success(base64_encode($result['html_content'])); // Encode HTML content before sending
    } else {
        error_log('Failed to load pyramid HTML. Database error: ' . $wpdb->last_error);
        wp_send_json_error('Failed to load pyramid HTML. Database error: ' . $wpdb->last_error);
    }
}
