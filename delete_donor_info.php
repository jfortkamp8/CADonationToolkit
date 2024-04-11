function delete_donor_info() {
    global $wpdb;

    // Check if donor_id is provided and not empty
    if (isset($_POST['donor_id']) && !empty($_POST['donor_id'])) {
        $donor_id = sanitize_text_field($_POST['donor_id']);
        $table_name = 'toolkit_donor_table';

        // Attempt to delete the donor with the provided ID
        $success = $wpdb->delete($table_name, ['donor_id' => $donor_id], ['%d']);

        if ($success !== false) {
            echo 'Donor information deleted successfully.';
        } else {
            echo 'An error occurred: ' . $wpdb->last_error;
        }
    } else {
        echo 'No donor ID provided.';
    }

    wp_die();
}
add_action('wp_ajax_delete_donor_info', 'delete_donor_info');
