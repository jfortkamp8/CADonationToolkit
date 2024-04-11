function display_donation_campaigns_shortcode($atts) {
	$is_admin = current_user_can('manage_options');
	
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

$args = array(
    'post_type' => 'Campaigns',
    'meta_query' => $meta_queries
);

$campaigns_query = new WP_Query($args);
    ob_start();

    echo '<style>
	
	
  .donation-campaigns-grid {
    display: grid;
    grid-gap: 20px; /* Adjust the gap between items */
    padding: 2%;
    justify-content: center;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); /* Creates columns that are at least 250px wide */
  }

  .donation-campaign-box {
    background-color: #f5f5f5;
    border-radius: 1em;
    box-shadow: 0px 0.2em 0.5em rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
	position: relative; /* Added to position the button absolutely within */
    overflow: hidden; /* Ensures content fits within the border radius */
  }

  .campaign-image {
    position: relative;
    height: 150px; /* Fixed height for all images */
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
    justify-content: space-between; /* Adjusts spacing inside the box */
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .donation-campaigns-grid {
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Adjust for smaller screens */
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
    margin-bottom: 60px; /* Space for the button */
  }

  .campaign-button {
    position: absolute; /* Position the button absolutely within the campaign box */
    bottom: 10px; /* Distance from the bottom */
    left: 50%;
    transform: translateX(-50%); /* Center the button horizontally */
    width: 70%; /* Adjust the width as necessary */
    padding: 12px 0; /* Padding for height */
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


    /* Additional CSS for new buttons */
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
    width: 65%;  /* Adjusting size for better visibility */
    height: 65%;
    object-fit: cover;
    filter: hue-rotate(90deg);  /* Changing the PNG color to orange */
}


    /* CSS for archive modal */
    .archive-modal {
        display: none;
        position: fixed;
        top: 0;
		border-radius:10px;
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
		border-radius:10px;
        width: 80%;
        max-width: 500px;
    }
    /* Styles for the close button */
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
    color: #333; /* Adjust color as required */
}

.close-btn:hover {
    color: #555; /* Adjust hover color as required */
	cursor: pointer;
	
}
    /* Scale down campaign boxes for archive */
    .archive-modal .donation-campaign-box {
        width: calc(100% - 15px);
    }
	
    </style>';



// Archive modal
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

                       
// Get the featured image
$image_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large')[0];

// Get the custom fields values
$goal_amount = get_field('donation_goal');
$description = get_field('campaign_description');
$campaign_status = get_field('campaign_status');
$campaign_name = get_field('campaign_name');
$lead_gift = get_field('lead_gift');

// Display the campaign info in a box
echo '<div class="col-md-4 col-sm-6 donation-campaign-box">';
            echo '<div class="campaign-inner">';
                echo '<div class="campaign-image">';
                    echo '<img src="' . $image_url . '" alt="' . get_the_title() . '">';
                    echo '<div class="campaign-goal">' . get_field('donation_goal') . ' Goal</div>';
			echo '<div class="campaign-button-container">';

if (current_user_can('edit_post', get_the_ID())) {
    echo '<a href="' . admin_url('post.php?post=' . get_the_ID() . '&action=edit') . '" target="_blank" class="campaign-edit-button" style="display: flex; justify-content: center; align-items: center;"><img src="https://www.freeiconspng.com/uploads/edit-editor-pen-pencil-write-icon--4.png" style="width: 50%; height: 50%; object-fit: cover; filter: invert(100%);"></a>';
}

if (current_user_can('delete_post', get_the_ID())) {
    echo '<a href="' . get_delete_post_link(get_the_ID()) . '" class="campaign-delete-button" onclick="return confirm(\'Are you sure you want to delete this campaign?\')" style="display: flex; justify-content: center; align-items: center;"><img src="https://www.pinclipart.com/picdir/big/159-1597907_delete-garbage-remove-trash-trash-can-icon-delete.png" style="width: 50%; height: 50%; object-fit: cover; filter: invert(100%);"></a>';
}


            echo '</div>';
                echo '</div>';
                echo '<div class="campaign-title">' . get_field('campaign_name') . '</div>';
                echo '<div class="campaign-info">';
                    echo '<ul>';
                        echo '<li>' . get_field('campaign_description') . '</li>';
                        echo '<li>Location: ' . get_field('location') . '</li>';
                    echo '<ul>';
                echo '</div>';
                $campaign_id = get_the_ID();
					$campaign_slug = get_post_field('post_name', $campaign_id);
                    $campaign_page_slug = 'campaign-toolkit-'. $campaign_slug . ''; // Generate a unique page slug based on campaign ID
					$campaign_page_title = get_field('campaign_name');
                    $campaign_page_content = '[campaign-toolkit id="' . $campaign_slug . '"]'; // Set the page content with dynamic campaign ID
                    $campaign_page = get_page_by_path($campaign_page_slug); // Check if page with the same slug exists

                    // If page doesn't exist, create a new page and add the shortcode to it
                    if (!$campaign_page) {
    $new_campaign_page = array(
        'post_title'    => $campaign_page_title,
        'post_name'     => $campaign_page_slug,
        'post_content'  => $campaign_page_content,
        'post_status'   => 'private',
        'post_type'     => 'page',
        'post_excerpt'  => ''
    );
    $campaign_page_id = wp_insert_post($new_campaign_page);
    $campaign_button_url = get_permalink($campaign_page_id);
} else {
    $campaign_button_url = get_permalink($campaign_page->ID);
}

                   
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

                       
// Get the featured image
$image_url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large')[0];

// Get the custom fields values
$goal_amount = get_field('donation_goal');
$description = get_field('campaign_description');
$campaign_status = get_field('campaign_status');
$campaign_name = get_field('campaign_name');
$lead_gift = get_field('lead_gift');

// Display the campaign info in a box
echo '<div class="col-md-4 col-sm-6 donation-campaign-box">';
            echo '<div class="campaign-inner">';
                echo '<div class="campaign-image">';
                    echo '<img src="' . $image_url . '" alt="' . get_the_title() . '">';
                    echo '<div class="campaign-goal">' . get_field('donation_goal') . ' Goal</div>';
			echo '<div class="campaign-button-container">';

if (current_user_can('edit_post', get_the_ID())) {
    echo '<a href="' . admin_url('post.php?post=' . get_the_ID() . '&action=edit') . '" target="_blank" class="campaign-edit-button" style="display: flex; justify-content: center; align-items: center;"><img src="https://www.freeiconspng.com/uploads/edit-editor-pen-pencil-write-icon--4.png" style="width: 50%; height: 50%; object-fit: cover; filter: invert(100%);"></a>';
}

if (current_user_can('delete_post', get_the_ID())) {
    echo '<a href="' . get_delete_post_link(get_the_ID()) . '" class="campaign-delete-button" onclick="return confirm(\'Are you sure you want to delete this campaign?\')" style="display: flex; justify-content: center; align-items: center;"><img src="https://www.pinclipart.com/picdir/big/159-1597907_delete-garbage-remove-trash-trash-can-icon-delete.png" style="width: 50%; height: 50%; object-fit: cover; filter: invert(100%);"></a>';
}


            echo '</div>';
                echo '</div>';
                echo '<div class="campaign-title">' . get_field('campaign_name') . '</div>';
                echo '<div class="campaign-info">';
                    echo '<ul>';
                        echo '<li>' . get_field('campaign_description') . '</li>';
                        echo '<li>Location: ' . get_field('location') . '</li>';
                    echo '<ul>';
                echo '</div>';
                $campaign_id = get_the_ID();
$campaign_slug = get_post_field('post_name', $campaign_id);
$campaign_page_slug = 'campaign-toolkit-' . $campaign_slug . ''; // Generate a unique page slug based on campaign ID
$campaign_page_title = $campaign_name; // Set the page title to the campaign name
$campaign_page_content = '[campaign-toolkit id="' . $campaign_slug . '"]'; // Set the page content with dynamic campaign ID
$campaign_page = get_page_by_path($campaign_page_slug); // Check if a page with the same slug exists
 
// If the page doesn't exist, create a new page and add the shortcode to it
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

                    echo '<a href="' . $campaign_button_url . '" style="font-size: 13px; font-style: bold;" class="campaign-button">Launch Campaign Toolkit</a>';
                echo '</div>';
            echo '</div>';

        }

        echo '</div>';

        if (current_user_can('publish_posts')) {
echo '<a href="' . admin_url('post-new.php?post_type=campaigns') . '" target="_blank" class="add-campaign-button" style="display: inline-block; vertical-align: middle;">+</a>';
			
echo '<a class="archive-button" onclick="document.getElementById(\'archiveModal\').style.display=\'inline-block\'"><img src="https://icon-library.com/images/archive-icon-png/archive-icon-png-8.jpg" alt="Archive"></a>';
        }

    } else {
        echo '<p>No donation campaigns found.</p>';
    }

    wp_reset_postdata();
    $output = ob_get_clean();

    return $output;
}
add_shortcode('display_donation_campaigns', 'display_donation_campaigns_shortcode');
