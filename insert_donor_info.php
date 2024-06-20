function insert_donor_info() {
    global $wpdb;

    // Sanitize input data
    $donor_id = sanitize_text_field($_POST['donor_id']);
    $campaign_id = sanitize_text_field($_POST['campaign_id']);
    $status = sanitize_text_field($_POST['status']);
    $type = sanitize_text_field($_POST['type']);
    $full_name = sanitize_text_field($_POST['full_name']);
    $organization = sanitize_text_field($_POST['organization']);
    $amount = floatval(preg_replace('/[^\d.]/', '', $_POST['amount']));
    $next_step = sanitize_text_field($_POST['next_step']);
    $recent_involvement = sanitize_text_field($_POST['recent_involvement']);
    $notes = sanitize_textarea_field($_POST['notes']);

    $table_name = 'toolkit_donor_table';

    // Prepare data for insertion or update
    $data = [
        'donor_id' => $donor_id,
        'campaign_id' => $campaign_id,
        'status' => $status,
        'type' => $type,
        'full_name' => $full_name,
        'organization' => $organization,
        'amount' => $amount,
        'next_step' => $next_step,
        'recent_involvement' => $recent_involvement,
        'notes' => $notes,
    ];

    // Check if a donor with the provided ID already exists
    $existing_donor = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE donor_id = %d AND campaign_id = %d", $donor_id, $campaign_id));

    if ($existing_donor) {
        // Update existing donor
        $success = $wpdb->update($table_name, $data, ['donor_id' => $donor_id, 'campaign_id' => $campaign_id]);
    } else {
        // Insert new donor
        $success = $wpdb->insert($table_name, $data);
    }

    if ($success !== false) {
        echo 'Donor information saved successfully.';
    } else {
        echo 'An error occurred: ' . $wpdb->last_error;
    }

    wp_die();
}
add_action('wp_ajax_insert_donor_info', 'insert_donor_info');
