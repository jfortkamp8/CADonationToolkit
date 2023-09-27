function createDonationPyramid($goal) {
    $row_amounts = array(5000, 10000, 25000, 50000, 75000, 100000, 250000, 500000, 1000000, 1500000, 2000000, 3000000, 5000000);
    rsort($row_amounts);

    $rows = [];
    $totalDonations = 0;

    // Adjusting max goal to be between 20-25%
    $top_box_percent = 25 / 100;
    $top_box_amount = $goal * $top_box_percent;

    foreach ($row_amounts as $key => $amount) {
        if ($amount <= $top_box_amount) {
            $top_box_amount = $amount;
            unset($row_amounts[$key]);
            break;
        }
    }

    array_push($rows, array('id' => 'row' . ($top_box_amount / 1000), 'num_boxes' => 1, 'amount' => $top_box_amount));
    $goal -= $top_box_amount;
    $totalDonations += $top_box_amount;

    $remaining_goal_percentages = [0.3, 0.4];
    $current_goal_percentage_index = 0;

    foreach ($row_amounts as $key => $amount) {
        if ($goal <= 0 || count($rows) >= 8) break;

        $target_for_this_row = $goal * $remaining_goal_percentages[$current_goal_percentage_index];
        $num_boxes = min(floor($target_for_this_row / $amount), 8); // Ensure we do not exceed 8 boxes

        if ($num_boxes > 0) {
            $goal -= $num_boxes * $amount;
            array_push($rows, array('id' => 'row' . ($amount / 1000), 'num_boxes' => $num_boxes, 'amount' => $amount));
            $totalDonations += $num_boxes * $amount;

            if (count($rows) == 4) {
                $current_goal_percentage_index = 1;
            }

            unset($row_amounts[$key]); // Remove the used amount from the row_amounts
        }
    }

    // Go back and fill in more boxes to meet the goal
    foreach (array_reverse($rows) as $index => $row) {
        while ($goal > 0 && $rows[count($rows) - 1 - $index]['num_boxes'] < 8) {
            if ($goal >= $row['amount']) {
                $goal -= $row['amount'];
                $totalDonations += $row['amount'];
                $rows[count($rows) - 1 - $index]['num_boxes'] += 1;
            } else {
                break;
            }
        }
    }

    // Ensure at least 7 rows
    $remaining_row_amounts = array_values($row_amounts); // Reindex the array
    while (count($rows) < 7 && !empty($remaining_row_amounts)) {
        $amount = array_pop($remaining_row_amounts);
        array_push($rows, array('id' => 'row' . ($amount / 1000), 'num_boxes' => 1, 'amount' => $amount));
    }
	
  echo '<div class="donation-pyramid">';
  echo '<div class="donation-key">';
  echo '<div class="donation-key-item">';
  echo '<div class="status-circle pledged"></div>';
  echo '<div class="status-label">Pledged</div>';
  echo '</div>';
  echo '<div class="donation-key-item">';
  echo '<div class="status-circle pending"></div>';
  echo '<div class="status-label">Pending</div>';
  echo '</div>';
  echo '<div class="donation-key-item">';
  echo '<div class="status-circle engaged"></div>';
  echo '<div class="status-label">Engaged</div>';
  echo '</div>';
  echo '<div class="donation-key-item">';
  echo '<div class="status-circle identified"></div>';
  echo '<div class="status-label">Identified</div>';
  echo '</div>';
  echo '</div>';

foreach ($rows as $row) {
        echo '<div class="donation-row">';
        if (isset($row['id'])) {
            echo '<div class="donation-row-label" onclick="editNumBoxes(this)">' . $row['num_boxes'] . ' x $' . number_format($row['amount']) . '</div>';
        } else {
            echo '<div class="donation-row-label"></div>';
        }
        echo '<div class="donation-row-line"></div>';
        for ($i = 0; $i < $row['num_boxes']; $i++) {
            echo '<div class="donation-box" data-amount="' . $row['amount'] . '">';
            echo '<div class="donation-box-inner">';
            if (isset($row['id'])) {
                echo '<div class="donation-box-front" data-row="' . $row['id'] . '"></div>';
            } else {
                echo '<div class="donation-box-front"></div>';
            }
            echo '<div class="donation-box-back"></div>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    }

    echo '<div class="donation-row">';
    echo '<div class="donation-row-label"> <b>Total: $' . number_format($totalDonations) . '</b></div>'; // Update the total donations amount
    echo '<div class="donation-box">Donations Under $5,000</div>';
    echo '</div>';
    echo '</div>';
}
