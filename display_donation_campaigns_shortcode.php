function display_donation_campaigns_shortcode($atts) {
	$is_admin = current_user_can('manage_options');
	
    // Set the query arguments
    $args = array(
        'post_type' => 'Campaigns',
        'posts_per_page' => -1,
    );

    // If the user is not an admin, filter campaigns by the current user's ID or assigned user ACF field value
    if (!$is_admin) {
        $user_id = get_current_user_id();
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key' => 'assigned_user',
                'value' => $user_id,
                'compare' => '=',
            ),
            array(
                'key' => 'assigned_user',
                'value' => get_user_meta($user_id, 'nickname', true),
                'compare' => '=',
            ),
        );
    } else {
        // If the user is an admin, remove the meta_query
        $args['meta_query'] = false;
    }

    // Run the query
    $campaigns_query = new WP_Query($args);

    // Start the output buffer
    ob_start();

    // Add the CSS styles
    echo '<style>
.donation-campaigns-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  padding: 20px 0;
}

.donation-campaign-box {
  background-color: #f5f5f5;
  border-radius: 10px;
  box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
  margin: 10px;
  width: calc(25% - 15px);
  display: flex;
  flex-direction: column;
}

.campaign-image {
  position: relative;
  height: 150px;
  overflow: hidden;
  border-radius: 10px 10px 0 0;
}

.campaign-image img {
  height: 100%;
  width: 100%;
  object-fit: cover;
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

.campaign-info {
  flex-grow: 1;
  padding: 0 10px;
}

.campaign-info ul {
  font-size: 14px;
  margin-bottom: 15px;
  margin-top: 0;
  padding-left: 20px;
}

.campaign-info li {
  list-style-type: disc;
}

.campaign-button {
  background-color: #F78D2D;
  border: none;
  border-radius: 25px;
  color: #fff !important;
  cursor: pointer;
  display: block;
  font-size: 16px;
  padding: 12px 25px;
  margin: 0 auto 25px;
  text-align: center;
  width: 70%;
  height: 50px;
  line-height: 25px;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.3);
}

.campaign-button:hover {
  background-color: #00758D;
  transition: background-color 0.3s ease-in-out;
}

    </style>';

    // Display the campaigns
    if ($campaigns_query->have_posts()) {
        echo '<div class="row donation-campaigns-grid">'; // Update the div class according to your theme's grid system

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
                echo '</div>';
                echo '<div class="campaign-title">' . get_field('campaign_name') . '</div>';
                echo '<div class="campaign-info">';
                    echo '<ul>';
                        echo '<li>' . get_field('campaign_description') . '</li>';
                        //echo '<li>Location: ' . get_field('campaign_location') . '</li>';
                    echo '<ul>';
                echo '</div>';
                $campaign_id = get_the_ID();
					$campaign_slug = get_post_field('post_name', $campaign_id);
                    $campaign_page_slug = 'campaign-toolkit-'. $campaign_slug . ''; // Generate a unique page slug based on campaign ID
					$campaign_page_title = get_the_title() . ' Campaign Page'; // Set the page title
                    $campaign_page_content = '[campaign-toolkit id="' . $campaign_slug . '"]'; // Set the page content with dynamic campaign ID
                    $campaign_page = get_page_by_path($campaign_page_slug); // Check if page with the same slug exists

                    // If page doesn't exist, create a new page and add the shortcode to it
                    if (!$campaign_page) {
                        $new_campaign_page = array(
                            'post_title' => $campaign_page_title,
                            'post_name' => $campaign_page_slug,
                            'post_content' => $campaign_page_content,
                            'post_status' => 'private',
                            'post_type' => 'page',
							'post_excerpt' => '',
        					'post_title' => wp_strip_all_tags(get_the_title()),
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
    } else {
        echo '<p>No donation campaigns found.</p>';
    }

    // Clean up after the query
    wp_reset_postdata();

    // Get the output buffer contents and stop buffering
    $output = ob_get_clean();

    return $output;
}
add_shortcode('display_donation_campaigns', 'display_donation_campaigns_shortcode');
