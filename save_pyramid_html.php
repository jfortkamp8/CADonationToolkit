add_action('wp_ajax_save_pyramid_html', 'save_pyramid_html');
add_action('wp_ajax_nopriv_save_pyramid_html', 'save_pyramid_html');

function save_pyramid_html() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pyramid_nonce')) {
        error_log('Invalid nonce');
        wp_send_json_error('Invalid nonce');
        return;
    }

    if (!isset($_POST['campaign_id']) || !isset($_POST['html_content'])) {
        error_log('Missing campaign_id or html_content');
        wp_send_json_error('Missing campaign_id or html_content');
        return;
    }

    global $wpdb;
    $campaign_id = intval($_POST['campaign_id']);
    $html_content = base64_decode($_POST['html_content']); // Decode HTML content

    // Log the data for debugging
    error_log('Saving pyramid HTML. Campaign ID: ' . $campaign_id . ', HTML Content Length: ' . strlen($html_content));
    if (strlen($html_content) > 500) {
        error_log('HTML Content Preview: ' . substr($html_content, 0, 500) . '...');
    } else {
        error_log('HTML Content: ' . $html_content);
    }

    $table = $wpdb->prefix . 'toolkit_pyramid_state';
    $data = array(
        'campaign_id' => $campaign_id,
        'html_content' => wp_kses_post($html_content) // Sanitize HTML content
    );

    $result = $wpdb->replace($table, $data);
    if ($result === false) {
        error_log('Database error: ' . $wpdb->last_error);
        wp_send_json_error('Database error: ' . $wpdb->last_error);
    } else {
        wp_send_json_success('Pyramid HTML saved successfully.');
    }
}
