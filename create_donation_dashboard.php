function createDonationDashboard($goal) {

    $formattedGoal = '$' . number_format($goal);

    $totalDonations = 500000;
    $percent = $totalDonations / $goal * 100;
    $meterFillStyle = "width: " . $percent . "%";
    $meterFillContent = '<div class="fill" style="width: ' . $percent . '%">' . ($percent > 100 ? '<p>' . round($percent) . '%</p>' : '') . '</div>';


    $pledgedAmount = '$' . number_format($totalDonations);
    $pendingAmount = '$123,456'; // Replace with your pending amount variable

    // HTML code for the donation dashboard
    $dashboard = '
        <div style="display: flex; flex-direction: column; background-color: #F0F0F0; border-radius: 10px; padding: 20px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3); margin-bottom: 20px;">
            <div style="width: 100%; height: 140px; background-color: #77C4D5; border-radius: 10px; margin-bottom: 28px; display: flex; justify-content: center; align-items: center;">
                <!-- Campaign goal box -->
                <h2 style="color: #FFFFFF; font-size: 52px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); padding: 0 20px;">' . $formattedGoal . ' Campaign Goal</h2>
            </div>
            <div style="width: 100%; height: 90px; background-color: #7866A1; border-radius: 10px; margin-bottom: 28px; display: flex; justify-content: center; align-items: center; padding: 10px;">
                <!-- Donation meter box -->
                <div class="dashboard-meter">
                    ' . $meterFillContent . '
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 28px; align-items: center;">
        <div style="width: 49%; background-color: #00758D; height: 80px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            <!-- Amount pledged box -->
            <div>
                <h3 style="color: #FFFFFF; font-size: 36px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">' . $pledgedAmount . '  Pledged</h3>
            </div>
        </div>
        <div style="width: 49%; background-color: #F78D2D; height: 80px; border-radius: 10px; display: flex;  justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            <!-- Amount pending box -->
            <div>
                <h3 style="color: #FFFFFF; font-size: 36px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">' . $pendingAmount . '  Pending</h3>
            </div>
        </div>
    </div>
            <div style="width: 100%; height: 200px; background-color: #77C4D5; border-radius: 10px; display: flex; justify-content: center; align-items: center;">
                <!-- Bar graph chart -->
                <h3 style="color: #FFFFFF; font-size: 36px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">Bar Graph Chart</h3>
            </div>
        </div>

        <style>
            .dashboard-meter {
                width: 97%;
                height: 52px;
                background: #FFFFFF;
                position: relative;
                overflow: hidden;
                border-radius: 10px;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 10px;
                box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            }

            .dashboard-meter .fill {
                transition: width 0.5s ease-out;
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                background: linear-gradient(to right,#F78D2D,#FFC897);
                display: flex;
                align-items: center;
                justify-content: flex-end;
                padding-right: 10px;
                box-sizing: border-box;
                animation: fillAnimation 2s ease-in-out;
            }

            .dashboard-meter .fill p {
                color: #FFFFFF;
                font-size: 18px;
                font-weight: bold;
                margin-right: 10px;
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
