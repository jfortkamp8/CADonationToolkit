function createDonationPyramid($goal) {
  $row_amounts = array(5000, 10000, 25000, 50000, 75000, 100000, 250000, 500000, 1250000, 2500000);
  rsort($row_amounts); // Sort in descending order to start with the highest row amounts

  $unadjusted_goal = $goal;

  // Adjust max_boxes based on the goal
  if ($goal <= 1000000) {
    $max_boxes = array(
      5000 => 8,
      10000 => 8,
      25000 => 6,
      50000 => 4,
	  75000 => 2,
      100000 => 1,
      250000 => 1,
    );
	  $rowCount = count($row_amounts) - 4;
  } elseif ($goal <= 2000000 && $goal > 1000000) {
    $max_boxes = array(
      5000 => 8,
	  10000 => 8,
      25000 => 7,
      50000 => 6,
      75000 => 4,
      100000 => 3,
      250000 => 1,
      500000 => 1,
    );
	  $rowCount = count($row_amounts) - 3;
  } else {
    $max_boxes = array(
	  10000 => 8,
      25000 => 8,
      50000 => 7,
      75000 => 7,
      100000 => 6,
      250000 => 4,
      500000 => 2,
      1250000 => 1,
    );
	  $rowCount = count($row_amounts) - 3;
  }

  $rows = array();
  foreach ($row_amounts as $amount) {
    if (isset($max_boxes[$amount])) {
      $num_boxes = min(floor($goal / $amount), $max_boxes[$amount]);
      if ($num_boxes > 0) {
        $goal -= $num_boxes * $amount; // Subtract the total of this row from the goal
        array_push($rows, array('id' => 'row' . ($rowCount--), 'num_boxes' => $num_boxes, 'amount' => $amount));
      }
      if ($goal == 0) {
        break; // Stop if we've reached the goal
      }
    }
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
    echo '<div class="donation-row-label">' . $row['num_boxes'] . ' x $' . number_format($row['amount']) . '</div>';
    echo '<div class="donation-row-line"></div>';
    for ($i = 0; $i < $row['num_boxes']; $i++) { // Adjusted the loop to start from 0
      echo '<div class="donation-box" data-row="' . $row['id'] . '"></div>';
    }
    echo '</div>';
  }

  echo '<div class="donation-row">';
  echo '<div class="donation-row-label"> <b>Total: $'. number_format($unadjusted_goal - $goal) . '</b></div>';
  echo '<div class="donation-box">Donations Under $5,000</div>';
  echo '</div>';
  echo '</div>';
}
