function fetch_donors_by_campaign() {
    global $wpdb;

    $campaign_id = sanitize_text_field($_POST['campaign_id']);
    $table_name = 'toolkit_donor_table';

    // Fetch donors for the given campaign ID
    $donors = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE campaign_id = %d", $campaign_id), ARRAY_A);

    if (!empty($donors)) {
        wp_send_json_success($donors);
    } else {
        wp_send_json_error('No donors found for this campaign.');
    }

    wp_die();
}
add_action('wp_ajax_fetch_donors_by_campaign', 'fetch_donors_by_campaign');
add_action('wp_ajax_nopriv_fetch_donors_by_campaign', 'fetch_donors_by_campaign');
