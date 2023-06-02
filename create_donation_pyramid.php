function createDonationPyramid($goal) {
  if ($goal == 5000000) {
    $rows = array(
      array('id' => 'row8', 'num_boxes' => 1, 'amount' => 1250000),
      array('id' => 'row7', 'num_boxes' => 1, 'amount' => 500000),
      array('id' => 'row6', 'num_boxes' => 3, 'amount' => 250000),
      array('id' => 'row5', 'num_boxes' => 4, 'amount' => 100000),
      array('id' => 'row4', 'num_boxes' => 6, 'amount' => 75000),
      array('id' => 'row3', 'num_boxes' => 7, 'amount' => 50000),
      array('id' => 'row3', 'num_boxes' => 7, 'amount' => 50000),
      array('id' => 'row2', 'num_boxes' => 8, 'amount' => 25000),
      array('id' => 'row2', 'num_boxes' => 8, 'amount' => 25000),
    );
  } elseif ($goal == 2000000) {
    $rows = array(
      array('id' => 'row7', 'num_boxes' => 1, 'amount' => 500000),
      array('id' => 'row6', 'num_boxes' => 2, 'amount' => 250000),
      array('id' => 'row5', 'num_boxes' => 3, 'amount' => 100000),
      array('id' => 'row3', 'num_boxes' => 5, 'amount' => 50000),
      array('id' => 'row2', 'num_boxes' => 6, 'amount' => 25000),
      array('id' => 'row1', 'num_boxes' => 6, 'amount' => 10000),
      array('id' => 'row1', 'num_boxes' => 6, 'amount' => 10000),
      array('id' => 'row0', 'num_boxes' => 7, 'amount' => 5000),
      array('id' => 'row0', 'num_boxes' => 8, 'amount' => 5000),
    );
  } elseif ($goal == 1000000) {
    $rows = array(
      array('id' => 'row6','num_boxes' => 1, 'amount' => 250000),
      array('id' => 'row5','num_boxes' => 2, 'amount' => 100000),
      array('id' => 'row3','num_boxes' => 4, 'amount' => 50000),
      array('id' => 'row2','num_boxes' => 5, 'amount' => 25000),
      array('id' => 'row1','num_boxes' => 6, 'amount' => 10000),
      array('id' => 'row0','num_boxes' => 7, 'amount' => 5000),
	  array('id' => 'row0', 'num_boxes' => 8, 'amount' => 5000),
    );
  } else {
    // Handle other goal amounts or throw an error
    return 'Invalid goal amount.';
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
$total_donated = 0;
$total_percentage = 0;
foreach ($rows as $row) {
  $donation_amount = $row['amount'] * $row['num_boxes'];
  $percentage = ($donation_amount / $goal) * 100;
  $total_donated += $donation_amount;
  $total_percentage += $percentage;

  echo '<div class="donation-row">';
  echo '<div class="donation-row-label">' . $row['num_boxes'] . ' x <b>$' . number_format($row['amount']) . '</b></div>';
  echo '<div class="donation-row-line"></div>';
  for ($i = 1; $i <= $row['num_boxes']; $i++) {
    echo '<div class="donation-box" data-row="' . $row['id'] . '"></div>';
  }
  echo '</div>';
}



echo '<div class="donation-row">';
if ($goal == 5000000) {
  echo '<div class="donation-row-label">Below $25,000 - 100%</div>';
  echo '<div class="donation-box">Donations Under $25,000</div>';
} else {
  echo '<div class="donation-row-label">Below $5,000 - 100%</div>';
  echo '<div class="donation-box">Donations Under $5,000</div>';
}
echo '</div>';
echo '</div>';
}
