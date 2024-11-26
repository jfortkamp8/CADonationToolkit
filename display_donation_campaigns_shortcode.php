// Shortcode to display donation campaigns
function display_donation_campaigns_shortcode($atts) {
    $is_admin = current_user_can('manage_options');
    $user_id = get_current_user_id();

    $args = array(
        'post_type' => 'Campaigns',
        'posts_per_page' => -1,
    );

    $meta_queries = array();

	
    // Add archived campaign criteria to meta query
    $meta_queries[] = array(
        'key' => 'archive_campaign',
        'value' => '0',
        'compare' => '='
    );

    // Check if user is not an admin
    if (!$is_admin) {
        $meta_queries['relation'] = 'AND';

        // User-specific criteria
        $meta_queries[] = array(
            'relation' => 'OR',
            array(
                'key' => 'assigned_user',
                'value' => $user_id,
                'compare' => '='
            ),
            array(
                'key' => 'assigned_user',
                'value' => get_user_meta($user_id, 'nickname', true),
                'compare' => '='
            )
        );
    }

    $args['meta_query'] = $meta_queries;
    $campaigns_query = new WP_Query($args);
    ob_start();

    echo '<style>
        .donation-campaigns-grid {
            display: grid;
            grid-gap: 20px;
            padding: 2%;
            justify-content: center;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            overflow-x: auto;
        }

        .donation-campaign-box {
            background-color: #f5f5f5;
            border-radius: 1em;
            box-shadow: 0px 0.2em 0.5em rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            margin: auto;
        }

        .campaign-image {
            position: relative;
            height: 150px;
            overflow: hidden;
        }

        .campaign-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .campaign-info {
            flex-grow: 1;
            padding: 0 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media (max-width: 768px) {
            .donation-campaigns-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        .campaign-goal {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #00758d;
            color: #fff;
            border-radius: 5px;
            padding: 5px 10px;
            font-size: 14px;
        }

        .campaign-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
            padding: 0 10px;
        }

        .campaign-info ul {
            font-size: 14px;
            margin-bottom: 5px;
            margin-top: 0;
            padding-left: 20px;
        }

        .campaign-info li {
            list-style-type: disc;
        }

        .campaign-info {
            padding: 0 10px;
            margin-bottom: 60px;
        }

        .campaign-button {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            width: 70%;
            padding: 12px 0;
            text-align: center;
            background-color: #F78D2D;
            color: #fff !important;
            border-radius: 25px;
            margin-bottom: 10px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
            border: none;
            cursor: pointer;
        }

        .campaign-button:hover {
            background-color: #00758D;
            transition: background-color 0.3s ease-in-out;
        }

        .campaign-edit-button,
        .campaign-delete-button {
            position: absolute;
            top: 8px;
            display: inline-block;
            background-color: #00758d;
            border-radius: 50%;
            color: #fff;
            text-align: center;
            width: 32px;
            height: 32px;
            line-height: 30px;
        }

        .campaign-edit-button {
            right: 45px;
        }

        .campaign-delete-button {
            right: 10px;
        }

        .add-campaign-button {
            position: absolute;
            top: -88px;
            right: 153px;
            background-color: #D8D8D8;
            border-radius: 50%;
            color: #fff;
            vertical-align: middle;
            padding: 6px;
            text-align: center;
            font-size: 40px;
            width: 50px;
            height: 50px;
            line-height: 30px;
        }

        .archive-button {
            position: absolute;
            top: -88px;
            right: 215px;
            background-color: #D8D8D8;
            border-radius: 50%;
            color: #fff;
            vertical-align: middle;
            padding: 6px;
            text-align: center;
            font-size: 40px;
            width: 50px;
            height: 50px;
            line-height: 30px;
            cursor: pointer;
        }

        .archive-button img {
            width: 65%;
            height: 65%;
            object-fit: cover;
            filter: hue-rotate(90deg);
        }

        .archive-modal {
            display: none;
            position: fixed;
            top: 0;
            border-radius: 10px;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            z-index: 999;
            overflow-y: auto;
            padding-top: 50px;
        }
        .archive-content {
            background-color: #E1E1E1;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 60px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
            color: #333;
        }

        .close-btn:hover {
            color: #555;
            cursor: pointer;
        }

        .archive-modal .donation-campaign-box {
            width: calc(100% - 15px);
        }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 999;
        align-items: center;
        justify-content: center;
        padding: 20px; /* Adds a buffer around the modal */
        box-sizing: border-box;
    }

    /* Modal content styling */
    .modal-content {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh; /* Limits height to avoid overflow */
        overflow-y: auto; /* Adds vertical scroll if content exceeds available space */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        font-family: Arial, sans-serif;
    }

    /* Close button styling */
    .close-modal {
        cursor: pointer;
        float: right;
        font-size: 24px;
        color: #333;
        font-weight: bold;
    }

    .close-modal:hover {
        color: #555;
    }

    /* Modal header */
    .modal-content h3 {
        text-align: center;
        color: #00758d;
        margin-bottom: 20px;
        font-size: 24px;
        font-weight: 600;
    }

    /* Form styling */
    .modal-content form {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        grid-gap: 20px;
    }

    /* Full-width fields */
    .modal-content form input[type="file"],
    .modal-content form textarea,
    .modal-content form .full-width {
        grid-column: span 2;
    }

    /* Input and label styling */
    .modal-content label {
        color: #333;
        font-weight: bold;
        margin-bottom: 5px;
        font-size: 14px;
    }

    .modal-content input,
    .modal-content textarea,
    .modal-content button {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        outline: none;
    }

    /* Submit button */
    .modal-content button[type="submit"] {
        background-color: #F78D2D;
        color: #fff;
        border: none;
        font-weight: bold;
        text-align: center;
        cursor: pointer;
        transition: background-color 0.3s ease;
        grid-column: span 2;
        margin-top: 10px;
    }

    .modal-content button[type="submit"]:hover {
        background-color: #00758D;
    }

    /* Checkboxes */
    .modal-content input[type="checkbox"] {
        margin-top: 15px;
        align-self: center;
    }
    </style>';

    // Campaign Creation Modal
echo '<div id="campaignCreationModal" class="modal-overlay">
    <div class="modal-content">
        <span class="close-modal" onclick="document.getElementById(\'campaignCreationModal\').style.display=\'none\'">&times;</span>
        <h3>Create New Campaign</h3>
        <form id="campaignCreationForm" method="post" enctype="multipart/form-data">
            <label>Campaign Name:</label>
            <input type="text" name="campaign_name" placeholder="Campaign Name" required>
            <label>Description:</label>
            <textarea name="campaign_description" placeholder="Campaign Description" required></textarea>
            <label>Location:</label>
            <input type="text" name="location" placeholder="Location" required>
            <label>Donation Goal:</label>
            <input type="number" name="donation_goal" placeholder="Campaign Goal" required>
            <label>Pie Chart Slice 1:</label>
            <input type="text" name="slice_1_name" placeholder="Slice 1 Name">
            <input type="number" name="slice_1_amount" placeholder="Slice 1 Amount">
            <label>Pie Chart Slice 2:</label>
            <input type="text" name="slice_2_name" placeholder="Slice 2 Name">
            <input type="number" name="slice_2_amount" placeholder="Slice 2 Amount">
            <label>Pie Chart Slice 3:</label>
            <input type="text" name="slice_3_name" placeholder="Slice 3 Name">
            <input type="number" name="slice_3_amount" placeholder="Slice 3 Amount">
            <label>Assigned Users:</label>
           
            <select name="assigned_users[]" multiple>';
$users = get_users(); // Fetch all WordPress users
foreach ($users as $user) {
    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
}
echo '</select>
            <label>Archive:</label>
            <input type="checkbox" name="archive_campaign" value="1">
            <label>Featured Image:</label>
            <input type="file" name="featured_image" accept="image/*">
            <label>Client Logo:</label>
            <input type="file" name="client_logo" accept="image/*">
            <button type="submit">Create Campaign</button>
        </form>
    </div>
</div>';


    // Archive Modal
    echo '<div id="archiveModal" class="archive-modal">';
    echo '<div class="archive-content">';
    echo '<a class="close" onclick="document.getElementById(\'archiveModal\').style.display=\'none\'">&times;</a>';

    // Get archived campaigns
    $archived_campaigns = new WP_Query(array(
        'post_type' => 'Campaigns',
        'meta_query' => array(
            array(
                'key' => 'archive_campaign',
                'value' => '1',
                'compare' => '='
            )
        )
    ));

    // Display archived campaigns in the modal
    if ($archived_campaigns->have_posts()) {
        echo '<div class="row donation-campaigns-grid">';
        while ($archived_campaigns->have_posts()) {
            $archived_campaigns->the_post();
            $campaign_id = get_the_ID();
            $campaign_name = get_field('campaign_name');
            $campaign_slug = get_post_field('post_name', $campaign_id);
            $location = get_field('location');
            $goal_amount = get_field('donation_goal');
            $description = get_field('campaign_description');

            // Get the featured image
            $image_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large')[0];

            // Display the campaign info in a box
            echo '<div class="col-md-4 col-sm-6 donation-campaign-box">';
            echo '<div class="campaign-inner">';
            echo '<div class="campaign-image">';
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($campaign_name) . '">';
            echo '<div class="campaign-goal">' . esc_html($goal_amount) . ' Goal</div>';
            echo '<div class="campaign-button-container">';

            // Edit Button opens modal instead of linking to backend
            if (current_user_can('edit_post', $campaign_id)) {
                echo '<a href="javascript:void(0);" onclick="document.getElementById(\'editCampaignModal_' . esc_attr($campaign_id) . '\').style.display=\'flex\'" class="campaign-edit-button" style="display: flex; justify-content: center; align-items: center;">
                <img src="https://www.freeiconspng.com/uploads/edit-editor-pen-pencil-write-icon--4.png" style="width: 50%; height: 50%; object-fit: cover; filter: invert(100%);"></a>';
            }

            if (current_user_can('delete_post', $campaign_id)) {
                echo '<a href="' . get_delete_post_link($campaign_id) . '" class="campaign-delete-button" onclick="return confirm(\'Are you sure you want to delete this campaign?\')" style="display: flex; justify-content: center; align-items: center;">
                <img src="https://www.pinclipart.com/picdir/big/159-1597907_delete-garbage-remove-trash-trash-can-icon-delete.png" 
                style="width: 50%; height: 50%; object-fit: cover; filter: invert(100%);"></a>';
            }

            echo '</div>';
            echo '</div>';
            echo '<div class="campaign-title">' . esc_html($campaign_name) . '</div>';
            echo '<div class="campaign-info">';
            echo '<ul>';
            echo '<li>' . esc_html($description) . '</li>';
            echo '<li>Location: ' . esc_html($location) . '</li>';
            echo '</ul>';
            echo '</div>';

            // Edit Campaign Modal for Each Archived Campaign
            echo '<div id="editCampaignModal_' . esc_attr($campaign_id) . '" class="modal-overlay">
                <div class="modal-content">
                    <span class="close-modal" onclick="document.getElementById(\'editCampaignModal_' . esc_attr($campaign_id) . '\').style.display=\'none\'">&times;</span>
                    <h3>Edit Campaign: ' . esc_html($campaign_name) . '</h3>
                    <form method="post" class="editCampaignForm" data-campaign-id="' . esc_attr($campaign_id) . '" enctype="multipart/form-data">
                        <input type="hidden" name="campaign_id" value="' . esc_attr($campaign_id) . '">
                        <input type="text" name="campaign_name" value="' . esc_attr($campaign_name) . '" required>
                        <textarea name="campaign_description" required>' . esc_textarea($description) . '</textarea>
                        <input type="text" name="location" value="' . esc_attr($location) . '" required>
                        <input type="number" name="donation_goal" value="' . esc_attr($goal_amount) . '" required>
                        <label>Featured Image:</label>
                        <input type="file" name="featured_image" accept="image/*">
                        <label>Client Logo:</label>
                        <input type="file" name="client_logo" accept="image/*">
                        <button type="submit">Update Campaign</button>
                    </form>
                </div>
            </div>';

            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>No archived campaigns found.</p>';
    }

    wp_reset_postdata();
    echo '</div>';
    echo '</div>';

    if ($campaigns_query->have_posts()) {
        echo '<div class="row donation-campaigns-grid">';

        while ($campaigns_query->have_posts()) {
            $campaigns_query->the_post();
            $campaign_id = get_the_ID();
            $campaign_name = get_field('campaign_name');
            $campaign_slug = get_post_field('post_name', $campaign_id);
            $location = get_field('location');
            $goal_amount = get_field('donation_goal');
            $description = get_field('campaign_description');

            // Get the featured image
            $image_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large')[0];

            // Display the campaign info in a box
            echo '<div class="col-md-4 col-sm-6 donation-campaign-box">';
            echo '<div class="campaign-inner">';
            echo '<div class="campaign-image">';
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($campaign_name) . '">';
            echo '<div class="campaign-goal">' . esc_html($goal_amount) . ' Goal</div>';
            echo '<div class="campaign-button-container">';

            // Edit Button opens modal instead of linking to backend
            if (current_user_can('edit_post', $campaign_id)) {
                echo '<a href="javascript:void(0);" onclick="document.getElementById(\'editCampaignModal_' . esc_attr($campaign_id) . '\').style.display=\'flex\'" class="campaign-edit-button" style="display: flex; justify-content: center; align-items: center;">
                <img src="https://www.freeiconspng.com/uploads/edit-editor-pen-pencil-write-icon--4.png" 
                style="width: 50%; height: 50%; object-fit: cover; filter: invert(100%);"></a>';
            }

            if (current_user_can('delete_post', $campaign_id)) {
                echo '<a href="' . get_delete_post_link($campaign_id) . '" class="campaign-delete-button" onclick="return confirm(\'Are you sure you want to delete this campaign?\')" style="display: flex; justify-content: center; align-items: center;">
                <img src="https://www.pinclipart.com/picdir/big/159-1597907_delete-garbage-remove-trash-trash-can-icon-delete.png" 
                style="width: 50%; height: 50%; object-fit: cover; filter: invert(100%);"></a>';
            }

            echo '</div>';
            echo '</div>';
            echo '<div class="campaign-title">' . esc_html($campaign_name) . '</div>';
            echo '<div class="campaign-info">';
            echo '<ul>';
            echo '<li>' . esc_html($description) . '</li>';
            echo '<li>Location: ' . esc_html($location) . '</li>';
            echo '</ul>';
            echo '</div>';

            // Create or get the Campaign Toolkit Page
            $campaign_page_slug = 'campaign-toolkit-' . $campaign_slug;
            $campaign_page_title = $campaign_name;
            $campaign_page_content = '[campaign-toolkit id="' . $campaign_slug . '"]';
            $campaign_page = get_page_by_path($campaign_page_slug);

            if (!$campaign_page) {
                $new_campaign_page = array(
                    'post_title' => $campaign_page_title,
                    'post_name' => $campaign_page_slug,
                    'post_content' => $campaign_page_content,
                    'post_status' => 'private',
                    'post_type' => 'page',
                    'post_excerpt' => '',
                );
                $campaign_page_id = wp_insert_post($new_campaign_page);
                $campaign_button_url = get_permalink($campaign_page_id);
            } else {
                $campaign_button_url = get_permalink($campaign_page->ID);
            }

            echo '<a href="' . esc_url($campaign_button_url) . '" class="campaign-button">Launch Campaign Toolkit</a>';

            // Edit Campaign Modal for Each Campaign
            echo '<div id="editCampaignModal_' . esc_attr($campaign_id) . '" class="modal-overlay">
    <div class="modal-content">
        <span class="close-modal" onclick="document.getElementById(\'editCampaignModal_' . esc_attr($campaign_id) . '\').style.display=\'none\'">&times;</span>
        <h3>Edit Campaign: ' . esc_html($campaign_name) . '</h3>
        <form method="post" class="editCampaignForm" data-campaign-id="' . esc_attr($campaign_id) . '" enctype="multipart/form-data">
            <input type="hidden" name="campaign_id" value="' . esc_attr($campaign_id) . '">
            <input type="text" name="campaign_name" value="' . esc_attr($campaign_name) . '" required>
            <textarea name="campaign_description" required>' . esc_textarea($description) . '</textarea>
            <input type="text" name="location" value="' . esc_attr($location) . '" required>
            <input type="text" name="donation_goal" value="' . esc_attr(get_field('donation_goal')) . '" required>

            <!-- Featured Image -->
            <label>Featured Image:</label>';
$featured_image_id = get_post_thumbnail_id($campaign_id); // Get the featured image ID
if ($featured_image_id) {
    $featured_image_url = wp_get_attachment_url($featured_image_id);
    echo '<img src="' . esc_url($featured_image_url) . '" alt="Featured Image" style="max-width: 100%; margin-bottom: 10px;"><br>';
} else {
    echo '<p>No featured image set.</p>';
}
echo '<input type="file" name="featured_image" accept="image/*">

            <!-- Client Logo -->
            <label>Client Logo:</label>';
$client_logo = get_field('client_logo', $campaign_id); // Get the client_logo field

    // Handle different ACF image return types
if ($client_logo) {
    if (is_array($client_logo) && isset($client_logo['url'])) {
        // If ACF returns an array, get the URL
        $client_logo_url = $client_logo['url'];
    } elseif (is_string($client_logo)) {
        // If ACF returns a URL directly
        $client_logo_url = $client_logo;
    } elseif (is_numeric($client_logo)) {
        // If ACF returns an ID, get the attachment URL
        $client_logo_url = wp_get_attachment_url($client_logo);
    } else {
        $client_logo_url = false; // Fallback in case of unexpected type
    }

    if ($client_logo_url) {
        // Display the image if the URL is valid
        echo '<img src="' . esc_url($client_logo_url) . '" alt="Client Logo" style="max-width: 100%; margin-bottom: 10px;"><br>';
    } else {
        echo '<p>Client logo URL not valid.</p>';
    }
} else {
    echo '<p>No client logo set.</p>';
}
echo '<input type="file" name="client_logo" accept="image/*">

            <label>Pie Chart Slice 1 Name:</label>
            <input type="text" name="slice_1_name" value="' . esc_attr(get_field('slice_1_name')) . '">
            <label>Pie Chart Slice 1 Amount:</label>
            <input type="text" name="slice_1_amount" value="' . esc_attr(get_field('slice_1_amount')) . '">
            <label>Pie Chart Slice 2 Name:</label>
            <input type="text" name="slice_2_name" value="' . esc_attr(get_field('slice_2_name')) . '">
            <label>Pie Chart Slice 2 Amount:</label>
            <input type="text" name="slice_2_amount" value="' . esc_attr(get_field('slice_2_amount')) . '">
            <label>Pie Chart Slice 3 Name:</label>
            <input type="text" name="slice_3_name" value="' . esc_attr(get_field('slice_3_name')) . '">
            <label>Pie Chart Slice 3 Amount:</label>
            <input type="text" name="slice_3_amount" value="' . esc_attr(get_field('slice_3_amount')) . '">
            <label>Assigned User(s):</label>
            <!-- Assigned Users -->
            <label>Assigned Users:</label>
            <select name="assigned_users[]" multiple>';
$assigned_users = get_field('assigned_users', $campaign_id); // Get the assigned users
$users = get_users(); // Fetch all WordPress users
foreach ($users as $user) {
    $selected = is_array($assigned_users) && in_array($user->ID, $assigned_users) ? 'selected' : '';
    echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
}
echo '</select>
            <!-- Archive Checkbox -->
            <label>Archive:</label>
            <input type="checkbox" name="archive_campaign" value="1" ' . (get_field('archive_campaign') ? 'checked' : '') . '>

            <button type="submit">Update Campaign</button>
        </form>
    </div>
</div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';

        if (current_user_can('publish_posts')) {
            echo '<a href="javascript:void(0);" onclick="document.getElementById(\'campaignCreationModal\').style.display=\'flex\'" class="add-campaign-button" style="display: inline-block; vertical-align: middle;">+</a>';
            echo '<a class="archive-button" onclick="document.getElementById(\'archiveModal\').style.display=\'inline-block\'">
                <img src="https://icon-library.com/images/archive-icon-png/archive-icon-png-8.jpg" alt="Archive"></a>';
        }
    } else {
        echo '<p>No donation campaigns found.</p>';
    }

    wp_reset_postdata();
    $output = ob_get_clean();

    return $output;
}
add_shortcode('display_donation_campaigns', 'display_donation_campaigns_shortcode');

// JavaScript for AJAX and UI handling
function campaign_management_scripts() {
    ?>
    <script>
      
	document.getElementById('campaignCreationForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const payload = {
        title: formData.get('campaign_name'),
        status: 'publish',
        acf: {}
    };

    // Helper function to upload a file
    async function uploadFile(file) {
        const uploadData = new FormData();
        uploadData.append('file', file);

        try {
            const uploadResponse = await fetch('/wp-json/wp/v2/media', {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>',
                },
                body: uploadData,
            });
            const uploadResult = await uploadResponse.json();
            if (uploadResponse.ok) {
                return uploadResult.id; // Return the attachment ID
            } else {
                console.error('File upload failed:', uploadResult);
                return null;
            }
        } catch (error) {
            console.error('Error uploading file:', error);
            return null;
        }
    }

    // Handle client_logo field
    const clientLogoFile = formData.get('client_logo');
    if (clientLogoFile && clientLogoFile.size > 0) {
        const uploadedLogoId = await uploadFile(clientLogoFile);
        if (uploadedLogoId) {
            payload.acf.client_logo = uploadedLogoId;
        } else {
            alert('Failed to upload client logo.');
            return; // Stop the process if file upload fails
        }
    } else {
        payload.acf.client_logo = null;
    }

		// Handle featured_image field
    const featuredImageFile = formData.get('featured_image');
    if (featuredImageFile && featuredImageFile.size > 0) {
        const uploadedFeaturedImageId = await uploadFile(featuredImageFile);
        if (uploadedFeaturedImageId) {
            payload.featured_media = uploadedFeaturedImageId; // Set the uploaded image as featured_media
        } else {
            alert('Failed to upload featured image.');
            return; // Stop the process if file upload fails
        }
    }
		
    // Handle other ACF fields
    payload.acf.campaign_name = formData.get('campaign_name') || '';
    payload.acf.campaign_description = formData.get('campaign_description') || '';
    payload.acf.location = formData.get('location') || '';
    payload.acf.donation_goal = formData.get('donation_goal') || '';
    payload.acf.slice_1_name = formData.get('slice_1_name') || '';
    payload.acf.slice_1_amount = formData.get('slice_1_amount') || '';
    payload.acf.slice_2_name = formData.get('slice_2_name') || '';
    payload.acf.slice_2_amount = formData.get('slice_2_amount') || '';
    payload.acf.slice_3_name = formData.get('slice_3_name') || '';
    payload.acf.slice_3_amount = formData.get('slice_3_amount') || '';

    // Handle assigned_user field
    const assignedUsers = formData.getAll('assigned_users[]'); // Get selected users
    if (assignedUsers.length > 0) {
        payload.acf.assigned_user = assignedUsers.map(Number); // Convert to integers
    } else {
        delete payload.acf.assigned_user; // Remove field if no users are assigned
    }

    // Handle archive_campaign field
    const archiveField = formData.get('archive_campaign');
    payload.acf.archive_campaign = archiveField === '1' ? 1 : 0; // Ensure it's an integer (1 or 0)

    // Log the payload for debugging
    console.log('Payload being sent to API:', JSON.stringify(payload, null, 2));

    // Send the REST API request
    try {
        const response = await fetch('/wp-json/wp/v2/campaigns', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>',
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json();

        // Log the API response
        console.log('API Response:', result);

        if (response.ok) {
            location.reload();
        } else {
            console.error('Error:', result);
            alert(result.message || 'An error occurred while creating the campaign.');
        }
    } catch (error) {
        console.error('Fetch error:', error);
        alert('Unable to create the campaign. Please try again later.');
    }
});


		
		
document.querySelectorAll('.editCampaignForm').forEach((form) => {
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const campaignId = form.getAttribute('data-campaign-id');
        const formData = new FormData(form);
        const payload = { acf: {} };

        // Helper function to upload a file
        async function uploadFile(file) {
            const uploadData = new FormData();
            uploadData.append('file', file);

            try {
                const uploadResponse = await fetch('/wp-json/wp/v2/media', {
                    method: 'POST',
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>',
                    },
                    body: uploadData,
                });
                const uploadResult = await uploadResponse.json();
                if (uploadResponse.ok) {
                    return uploadResult.id; // Return the attachment ID
                } else {
                    console.error('File upload failed:', uploadResult);
                    return null;
                }
            } catch (error) {
                console.error('Error uploading file:', error);
                return null;
            }
        }

        // Handle client_logo field
        const clientLogoFile = formData.get('client_logo');
        if (clientLogoFile && clientLogoFile.size > 0) {
            const uploadedLogoId = await uploadFile(clientLogoFile);
            if (uploadedLogoId) {
                payload.acf.client_logo = uploadedLogoId; // Set uploaded file ID
            } else {
                alert('Failed to upload client logo.');
                return; // Stop the process if file upload fails
            }
        } else {
            payload.acf.client_logo = null; // Set to null if no file provided
        }

        // Populate other fields
        payload.acf.campaign_name = formData.get('campaign_name') || '';
        payload.acf.donation_goal = formData.get('donation_goal') || '';
        payload.acf.campaign_description = formData.get('campaign_description') || '';
        payload.acf.location = formData.get('location') || '';
        payload.acf.slice_1_name = formData.get('slice_1_name') || '';
        payload.acf.slice_1_amount = formData.get('slice_1_amount') || '';
        payload.acf.slice_2_name = formData.get('slice_2_name') || '';
        payload.acf.slice_2_amount = formData.get('slice_2_amount') || '';
        payload.acf.slice_3_name = formData.get('slice_3_name') || '';
        payload.acf.slice_3_amount = formData.get('slice_3_amount') || '';

        // Handle assigned_user field
        const assignedUsers = formData.getAll('assigned_users[]'); // Get selected users
        if (assignedUsers.length > 0) {
            payload.acf.assigned_user = assignedUsers.map(Number); // Convert to integers
        } else {
            delete payload.acf.assigned_user; // Remove the field entirely if no users are selected
        }
		
		 // Handle archive_campaign field
    const archiveField = formData.get('archive_campaign');
    payload.acf.archive_campaign = archiveField === '1' ? 1 : 0; // Ensure it's an integer (1 or 0)


        // Log the payload for debugging
        console.log('Payload being sent to API:', JSON.stringify(payload, null, 2));

        // Send the REST API request
        try {
            const response = await fetch(`/wp-json/wp/v2/campaigns/${campaignId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce("wp_rest"); ?>',
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();

            // Log the API response
            console.log('API Response:', result);

            if (response.ok) {
                location.reload();
            } else {
                console.error('Error:', result);
                alert(result.message || 'An error occurred while updating the campaign.');
            }
        } catch (error) {
            console.error('Fetch error:', error);
            alert('Unable to update the campaign. Please try again later.');
        }
    });
});


    </script>
    <?php
}
add_action('wp_footer', 'campaign_management_scripts');
