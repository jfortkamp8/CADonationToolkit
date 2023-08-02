
function display_donation_campaigns_shortcode($atts) {
	$is_admin = current_user_can('manage_options');
	
    $args = array(
        'post_type' => 'Campaigns',
        'posts_per_page' => -1,
    );

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
        $args['meta_query'] = false;
    }

    $campaigns_query = new WP_Query($args);

    ob_start();

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

    </style>';

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

        if (current_user_can('publish_posts')) {
echo '<a href="' . admin_url('post-new.php?post_type=campaigns') . '" target="_blank" class="add-campaign-button" style="display: inline-block; vertical-align: middle;">+</a>';



        }

    } else {
        echo '<p>No donation campaigns found.</p>';
    }

    wp_reset_postdata();
    $output = ob_get_clean();

    return $output;
}
add_shortcode('display_donation_campaigns', 'display_donation_campaigns_shortcode');
