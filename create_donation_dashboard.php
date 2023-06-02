function createDonationDashboard($goal) {

    $formattedGoal = '$'.number_format($goal);
    
    $totalDonations = 500000;
    $formattedTotalDonations = '$'.number_format($totalDonations);
    $pendingAmount = $totalDonations - $goal;
    $formattedPendingAmount = '$'.number_format($pendingAmount);
    $percent = $totalDonations / $goal * 100;
    $meterFillStyle = "width: " . $percent . "%";
    $meterFillContent = '<div class="fill" style="width: ' . $percent . '%">' . ($percent > 100 ? '<p>' . round($percent) . '%</p>' : '') . '</div>';
    
    // HTML code for the donation dashboard
    $dashboard = '
        <div style="display: flex; flex-direction: column; background-color: #FFFFFF; border-radius: 10px; padding: 20px;">
            <div style="width: 100%; height: 200px; background-color: #7866A1; border-radius: 10px; margin-bottom: 20px;">
                <!-- Campaign goal box -->
                <div style="text-align: center; padding-top: 50px;">
                    <h2 style="color: #FFFFFF;">' .$formattedGoal. ' Goal</h2>
                </div>
            </div>
            <div style="width: 100%; height: 50px; background-color: #77C4D5; border-radius: 10px; margin-bottom: 20px;">
                <!-- Donation meter box -->
                <div style="text-align: center; padding-top: 10px;">
                    <h3 style="color: #FFFFFF;">Donation Meter</h3>
                    <div class="dashboard-meter">
                        ' . $meterFillContent . '
                    </div>
                </div>
            </div>
            <div style="display: flex; flex-direction: row; width: 100%; margin-bottom: 20px;">
                <div style="width: 50%; background-color: #00758D; border-radius: 10px;">
                    <!-- Amount pledged box -->
                    <div style="text-align: center;">
                        <h3 style="color: #FFFFFF;">Amount Pledged</h3>
                        <p style="color: #FFFFFF; font-weight: bold; font-size: 20px;">' .$formattedTotalDonations. ' Pledged</p>
                    </div>
                </div>
                <div style="width: 50%; background-color: #F78D2D; border-radius: 10px;">
                    <!-- Amount pending box -->
                    <div style="text-align: center;">
                        <h3 style="color: #FFFFFF;">Amount Pending</h3>
                        <p style="color: #FFFFFF; font-weight: bold; font-size: 20px;">' .$formattedPendingAmount. ' Pending</p>
                    </div>
                </div>
            </div>
            <div style="width: 100%; height: 200px; background-color: #77C4D5; border-radius: 10px;">
                <!-- Bar graph chart -->
                <h3 style="color: #FFFFFF;">Bar Graph Chart</h3>
                <!-- Add bar graph chart here -->
            </div>
        </div>
        
        <style>
            .dashboard-meter {
                width: 100%;
                height: 20px;
                background: #F78D2D;
                position: relative;
                border-radius: 13px;
                overflow: hidden;
            }

            .dashboard-meter .fill {
                transition: width 0.5s ease-out;
                border-radius: 13px;
                position: inherit;
                background: #F78D2D;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: flex-end;
                padding-right: 10px;
                box-sizing: border-box;
                animation: fillAnimation 2s ease-in-out;
            }

            @keyframes fillAnimation {
                0% { width: 0%; }
                100% { width: ' . $percent . '%; }
            }
        </style>
    ';

    // Output the donation dashboard
    echo $dashboard;
}
