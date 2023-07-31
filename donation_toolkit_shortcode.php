function donation_toolkit_shortcode($atts) {
	wp_enqueue_script( 'js-pdf', 'https://cdn.jsdelivr.net/npm/jspdf@latest/dist/jspdf.min.js', array(), 'latest', true );
	wp_enqueue_script( 'html2canvas', 'https://cdn.jsdelivr.net/npm/html2canvas@1.3.2/dist/html2canvas.min.js', array(), 'latest', true );
	$campaign_slug = isset($atts['id']) ? sanitize_text_field($atts['id']) : '';
	$campaign = get_page_by_path($campaign_slug, OBJECT, 'campaigns');
	if (!$campaign) {
		return 'Campaign not found.';
	}
	$campaign_id = $campaign->ID;
	$goal = floatval(str_replace(array('$', ','), '', get_field('donation_goal', $campaign_id)));
	
	$imgurl = get_field('client_logo',$campaign_id);

    if (filter_var($imgurl, FILTER_VALIDATE_URL) === FALSE)
    {
      $imgurl = wp_get_attachment_url($imgurl);
    }

	ob_start();
	createDonationPyramid($goal);
	$donation_pyramid = ob_get_clean();

	?>
	<script>
		
	//TABS JS
	function activateTab(){
		const tabLinks = document.querySelectorAll('.tabbed-menu .tab-link');
		const tabContents = document.querySelectorAll('.tabbed-content .tab');

		tabLinks.forEach(tabLink => {
  			tabLink.addEventListener('click', () => {
    	
			// Remove active class from all tab links
    		tabLinks.forEach(link => {
      			link.classList.remove('active');
    		});
    	
			// Add active class to clicked tab link
    		tabLink.classList.add('active');

    		// Hide all tab contents
    		tabContents.forEach(content => {
      			content.classList.remove('active');
    		});
    	
			// Show the tab content corresponding to the clicked tab link
    		const tabContentId = tabLink.dataset.tab;
    		const tabContent = document.getElementById(tabContentId);
    		tabContent.classList.add('active');
			});
		});
	}
		
	document.addEventListener('DOMContentLoaded', activateTab);
		
	function addCircleCheckbox() {
  		const checkbox = document.createElement("input");
  		checkbox.type = "checkbox";
  		checkbox.className = "circle-checkbox";
  		checkbox.style.width = "12px";
  		checkbox.style.height = "12px";
  		checkbox.style.marginRight = "5px";
  		checkbox.style.verticalAlign = "middle";
		checkbox.style.backgroundColor = "#F78D2D";
  		return checkbox;
	}
		

	//MOVES MANAGEMENT AND PP JS
	function addRow() {
		
  		const tabLinks = document.querySelectorAll('.tabbed-menu .tab-link');
  		const tabContents = document.querySelectorAll('.tabbed-content .tab');
  		tabLinks.forEach(link => {
      		link.classList.remove('active');
    	});
    	// Add active class to "moves-management" tab link
    	const movesManagementTabLink = document.querySelector('.tab-link[data-tab="moves-management"]');
    	movesManagementTabLink.classList.add('active');
    	// Hide all tab contents
    	tabContents.forEach(content => {
      		content.classList.remove('active');
    	});
		// Show the "moves-management" tab content
    	const movesManagementTabContent = document.getElementById('moves-management');
    	movesManagementTabContent.classList.add('active');
  		// calculate remaining space between donation container and moves-management table
  		const donationContainer = document.querySelector(".donation-container");
  		const movesManagementTable = document.querySelector("#moves-management table");
  		const pledgePendingTables = document.querySelector("#pledges-pending table");
  		const remainingSpaceMM = donationContainer.getBoundingClientRect().bottom - movesManagementTable.getBoundingClientRect().bottom - 95;
  		const remainingSpacePP = donationContainer.getBoundingClientRect().bottom - movesManagementTable.getBoundingClientRect().bottom - 130;
  		// set margin-bottom of moves-management table to remaining space, or minimum of 15px
  		movesManagementTable.style.marginBottom = `${Math.max(remainingSpaceMM, 30)}px`;
  		pledgePendingTables.style.marginBottom = `${Math.max(remainingSpacePP, 20)}px`;
  		const newRow = document.createElement("tr");
  		const cells = Array.from({ length: 10 }, () => document.createElement("td"));
  		// Add dropdown for pledge or pending column
  		const pledgePendingSelect = document.createElement("select");
  		pledgePendingSelect.innerHTML = `
  			<option value="pledge">Pledged</option>
  			<option value="pending">Pending</option>
  			<option value="engaged">Engaged</option> 
  			<option value="identified">Identified</option>
  			<option value="denied">Declined</option>
  		`;
  		pledgePendingSelect.style.fontSize = "10px";
		const donationTypeSelect = document.createElement("select");
		donationTypeSelect.className = "donation-type-select";
  		donationTypeSelect.innerHTML = `
  			<option value="individual">Individuals</option>
  			<option value="foundation">Foundations</option>
			<option value="corporation">Corporations</option>
			<option value="public">Public</option>
			<option value="board">Board</option>
			<option value="other">Other</option>
  		`;
  		donationTypeSelect.style.fontSize = "10px";
		cells[0].style.textAlign = "center";
		cells[0].style.verticalAlign = "middle";
		cells[0].appendChild(pledgePendingSelect);
		cells[1].style.textAlign = "center";
		cells[1].style.verticalAlign = "middle";
		cells[1].appendChild(donationTypeSelect);
  		cells[0].width = '12%';
  		cells[1].width = '12%';
		const fullNameCheckbox = addCircleCheckbox();
		const orgNameCheckbox = addCircleCheckbox();

		const fullNameContainer = document.createElement("div");
		fullNameContainer.style.display = "flex";
		fullNameContainer.style.alignItems = "center";

		const orgNameContainer = document.createElement("div");
		orgNameContainer.style.display = "flex";
		orgNameContainer.style.alignItems = "center";

		cells[2].innerHTML = "";
		cells[2].appendChild(fullNameContainer);

		cells[3].innerHTML = "";
		cells[3].appendChild(orgNameContainer);

		cells[2].width = '14%';
		cells[3].width = '12%';
		cells[4].width = '11%';
		cells[5].width = '9%';
		cells[6].width = '9%';
		cells[7].width = '9%';
		cells[1].style.verticalAlign = 'middle';
		cells[2].style.verticalAlign = 'middle';
		cells[3].style.verticalAlign = 'middle';
		cells[4].style.verticalAlign = 'middle';
		cells[5].style.verticalAlign = 'middle';
		cells[6].style.verticalAlign = 'middle';
		cells[7].style.verticalAlign = 'middle';
		
		const docIndex = Array.from(cells).indexOf(cells[8]);
		const inputs = cells.slice(2, 9).map(cell => {
    		const input = document.createElement("input");
    		input.type = "text";
    		input.style.fontSize = "11px";
    		input.style.width = "100%";
			input.style.boxSizing = "border-box";
    		cell.appendChild(input);
    		return input;
 		});
		
		fullNameContainer.appendChild(fullNameCheckbox);
		fullNameContainer.appendChild(inputs[0]);

		orgNameContainer.appendChild(orgNameCheckbox);
		orgNameContainer.appendChild(inputs[1]);
		
		let displayName = "";
		
		fullNameCheckbox.addEventListener("change", function () {
  			if (fullNameCheckbox.checked) {
    			orgNameCheckbox.checked = false;
    			displayName = inputs[0].value;
  			}
		});

		orgNameCheckbox.addEventListener("change", function () {
  			if (orgNameCheckbox.checked) {
    			fullNameCheckbox.checked = false;
    			displayName = inputs[1].value;
  			}
		});
				
		
  		// Add column to attach files to donation
		const attachFiles = document.createElement("td");
		attachFiles.innerHTML = '<button class="attach-button" style="display:inline-block;width:35px;height:35px;background-color:lightgrey;border:none;margin-right:10px;"><img src="https://cdn-icons-png.flaticon.com/512/6583/6583130.png" alt="Attach files"></button>' +
                        		'<button class="download-button" style="display:inline-block;width:35px;height:35px;background-color:lightgrey;border:none;"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/OOjs_UI_icon_download.svg/2048px-OOjs_UI_icon_download.svg.png" 								  alt="Download files"></button>';
		attachFiles.width = '10%';
		attachFiles.style.textAlign = 'center';
		attachFiles.style.verticalAlign = 'middle';
		cells[8] = attachFiles;
		const attachButton = cells[8].querySelector(".attach-button");
		const downloadButton = cells[8].querySelector(".download-button");
  		// Attach button click event listener
  		attachButton.addEventListener("click", function handleAttachButtonClick() {
    		const fileInput = document.createElement("input");
    		fileInput.type = "file";
    		fileInput.multiple = true;
    		fileInput.accept = "image/*, .pdf, .doc, .docx";
    		fileInput.style.display = "none";
   		 	document.body.appendChild(fileInput);
    		fileInput.addEventListener("change", function handleFileInputChange() {
      			const fileList = fileInput.files;
      			const files = Array.from(fileList);
      			if (files.length > 0) {
        			downloadButton.disabled = false;
        			downloadButton.addEventListener("click", function handleDownloadButtonClick() {
          				files.forEach(file => {
            				const url = URL.createObjectURL(file);
            				const link = document.createElement("a");
            				link.href = url;
            				link.download = file.name;
            				link.style.display = "none";
            				document.body.appendChild(link);
            				link.click();
            				document.body.removeChild(link);
            				URL.revokeObjectURL(url);
          				});
        			});
      			}
    		});
			fileInput.click();
  		});

		const saveButton = document.createElement("button");
  		saveButton.className = "save-button";
  		saveButton.innerText = "Save";
  		cells[9].appendChild(saveButton);
  		cells[9].style.textAlign = "center";
  		cells[9].style.verticalAlign = "middle";
		
  		cells.forEach(cell => newRow.appendChild(cell));

  		const tableBody = document.querySelector("#moves-management table tbody");
  		tableBody.insertBefore(newRow, tableBody.firstChild);
	
  		saveButton.addEventListener("click", function handleSaveButtonClick() {
			
    		const values = inputs.map(input => input.value);

    		cells.slice(2, 8).forEach((cell, index) => {
      			cell.innerHTML = values[index];
   			});
  
    		const editButton = document.createElement("button");
    		editButton.className = "edit-button";
    		editButton.innerText = "Edit";
    		cells[9].innerHTML = "";
    		cells[9].appendChild(editButton);
    		cells[9].style.textAlign = "center";
    		cells[9].style.verticalAlign = "middle";

    		editButton.addEventListener("click", function handleEditButtonClick() {
		
    			//Remove the donation from the donation pyramid
    			const pyramidRows = document.querySelectorAll('.donation-row');
    			pyramidRows.forEach(row => {
        		const box = row.querySelector('.donation-box');
			
        		const boxDisplayName = box.innerHTML.trim().replace(/<br>/g, ' ');
        			if (boxDisplayName === displayName) {
           				box.innerHTML = ''; // Remove the donation from the box
					 	box.style.backgroundColor = ''; // Reset the box color
            			box.style.color = ''; // Reset the text color
            			box.style.fontWeight = ''; // Reset the font weight
            			box.style.textAlign = ''; // Reset the text alignment
            			box.style.display = ''; // Reset the display property
            			box.style.justifyContent = ''; // Reset the justify content property
            			box.style.alignItems = ''; // Reset the align items property
            			box.style.fontSize = ''; // Reset the font size
            			box.style.padding = ''; // Reset the padding
        			}
    			});

    			// Remove the donation from the pledges table
    			const pledgesTable = document.querySelector(".pledges-table tbody");
    			const pledgesRows = pledgesTable.querySelectorAll("tr");
    			pledgesRows.forEach(row => {
        			const donationCell = row.querySelector("td:nth-child(1)");
        			if (donationCell.innerHTML.trim() === displayName) {
            			row.remove(); // Remove the row from the table
        			}
    			});

    			// Remove the donation from the pending table
    			const pendingTable = document.querySelector(".pending-table tbody");
    			const pendingRows = pendingTable.querySelectorAll("tr");
    			pendingRows.forEach(row => {
        			const donationCell = row.querySelector("td:nth-child(1)");
        			if (donationCell.innerHTML.trim() === displayName) {
            			row.remove(); // Remove the row from the table
        			}
	    		});
				
				// Remove the donation from the pipeline table
    			const pipelineTable = document.querySelector(".pipeline-table tbody");
    			const pipelineRows = pipelineTable.querySelectorAll("tr");
    			pendingRows.forEach(row => {
        			const donationCell = row.querySelector("td:nth-child(1)");
        			if (donationCell.innerHTML.trim() === displayName) {
            			row.remove(); // Remove the row from the table
        			}
	    		});
			
				pledgePendingSelect.disabled = false;
				donationTypeSelect.disabled = false;

      			inputs.forEach((input, index) => {
  					if (index !== 6) {
   						const value = cells[index + 2].innerHTML;
						cells[index + 2].innerHTML = "";
    					cells[index + 2].appendChild(input);
      					input.value = value;
  					}
				});
				
				// Recreate and append the checkboxes
    			const fullNameCheckbox = addCircleCheckbox();
    			const orgNameCheckbox = addCircleCheckbox();

    			const fullNameContainer = document.createElement("div");
    			fullNameContainer.style.display = "flex";
    			fullNameContainer.style.alignItems = "center";

    			const orgNameContainer = document.createElement("div");
    			orgNameContainer.style.display = "flex";
    			orgNameContainer.style.alignItems = "center";

    			cells[2].innerHTML = "";
    			cells[2].appendChild(fullNameContainer);

    			cells[3].innerHTML = "";
   				cells[3].appendChild(orgNameContainer);

    			fullNameContainer.appendChild(fullNameCheckbox);
    			fullNameContainer.appendChild(inputs[0]);

    			orgNameContainer.appendChild(orgNameCheckbox);
    			orgNameContainer.appendChild(inputs[1]);
			
    			fullNameCheckbox.addEventListener("change", function () {
        			if (fullNameCheckbox.checked) {
            			orgNameCheckbox.checked = false;
						displayName = inputs[0].value;
        			}
    			});

    			orgNameCheckbox.addEventListener("change", function () {
        			if (orgNameCheckbox.checked) {
            			fullNameCheckbox.checked = false;
						displayName = inputs[1].value;
        			}
    			});
				
				// Set the initial state of the checkboxes
    			if (displayName === inputs[0].value) {
        			fullNameCheckbox.checked = true;
        			orgNameCheckbox.checked = false;
    			} else if (displayName === inputs[1].value) {
        			fullNameCheckbox.checked = false;
        			orgNameCheckbox.checked = true;
    			}
				
      			const saveButton = document.createElement("button");
      			saveButton.className = "save-button";
      			saveButton.innerText = "Save";
      			cells[9].innerHTML = "";
      			cells[9].appendChild(saveButton);
      			cells[9].style.textAlign = "center";
      			cells[9].style.verticalAlign = "middle";
				
      			saveButton.addEventListener("click", handleSaveButtonClick);
				
				if (fullNameCheckbox.checked) {
            		displayName = inputs[0].value;
        		} else if (orgNameCheckbox.checked) {
            		displayName = inputs[1].value;
        		}
    		});
			
    		pledgePendingSelect.disabled = true;
			donationTypeSelect.disabled = true;
			
  		// Set font size for saved values
  		cells.slice(2, 8).forEach(cell => {
    		cell.classList.add("saved-value");
  		});
			
  		// CSS style for saved values
  		const savedValueCells = Array.from(document.querySelectorAll("#moves-management table tbody .saved-value"));
    		savedValueCells.forEach(cell => {
      		cell.style.fontSize = "12px";
  		});
			
		const pledgePendingValue = pledgePendingSelect.value;
		const targetTable = document.querySelector(`.${pledgePendingValue}-table tbody`);
		const targetRow = document.createElement("tr");
		const targetCells = [
  			document.createElement("td"),
  			document.createElement("td"),
  			document.createElement("td"),
		];
		console.log(displayName);
		targetCells[0].innerHTML = displayName;
		targetCells[1].innerHTML = values[2];
		targetCells[2].innerHTML = new Date().toLocaleDateString();

		// Add event listener to date cell
		targetCells[2].addEventListener('click', (event) => {
  			// Allow editing
 	 		event.target.contentEditable = true;
  			event.target.focus();
  			targetCells[2].style.color = '#00758D';
		});

		// Save edited date and lock cell when focus is lost
		targetCells[2].addEventListener('blur', (event) => {
  			event.target.contentEditable = false;
  			targetCells[2].style.color = '#333';
  		// Save the edited date to your database or wherever you're storing the data
		});

		targetRow.appendChild(targetCells[0]);
		targetRow.appendChild(targetCells[1]);
		targetRow.appendChild(targetCells[2]);
			
const donationName = values[1];
const donationAmount = parseInt(values[2].replace(/[^0-9.-]+/g, ""));
let donationColor = '';
switch (pledgePendingValue) {
  case 'pledge':
    donationColor = '#7866A1';
    break;
  case 'pending':
    donationColor = '#5DABBC';
    break;
  case 'engaged':
    donationColor = '#00728A';
    break;
  case 'identified':
    donationColor = '#F78D2D';
    break;
  default:
    console.log('Invalid value for pledge/pending column');
    return;
}

const boxesToFill = 1;
const rows = document.querySelectorAll('.donation-row');
const donationAmounts = [5000, 10000, 25000, 50000, 75000, 100000, 250000, 500000, 1250000, 2500000, 5000000];
const closestAmount = donationAmounts.reduce((prev, curr) => Math.abs(curr - donationAmount) < Math.abs(prev - donationAmount) ? curr : prev);
const rowIndex = 'row' + donationAmounts.indexOf(closestAmount);
const boxes = document.querySelectorAll('.donation-box-front[data-row="' + rowIndex + '"]');

const filledBoxes = [];
boxes.forEach((box, index) => {
  if (box.innerHTML.trim() !== "") {
    filledBoxes.push(index);
  }
});

let emptyIndex = -1;
for (let i = 0; i < boxes.length; i++) {
  if (!filledBoxes.includes(i)) {
    emptyIndex = i;
    break;
  }
}
	

if (emptyIndex !== -1) {
  const box = boxes[emptyIndex];
  box.style.backgroundColor = donationColor;
  box.style.color = "#fff";
  box.style.fontWeight = "500";
  box.style.textAlign = "center";
  box.style.display = "flex";
  box.style.justifyContent = "center";
  box.style.alignItems = "center";

  const words = displayName.split(" ");
  if (words.length === 2) {
    displayName = words.join("<br>");
  }

  const boxWidth = 80;
  const boxHeight = 38;

  const span = document.createElement("span");
  span.innerHTML = displayName;
  document.body.appendChild(span);

  let fontSize = 18;

  while (span.offsetHeight > boxHeight) {
    fontSize--;
    span.style.fontSize = fontSize + "px";
  }

  while (span.offsetWidth > boxWidth) {
    fontSize--;
    span.style.fontSize = fontSize + "px";
  }

  box.style.fontSize = fontSize + "px";
  box.style.padding = "10px";
  box.innerHTML = displayName;

  document.body.removeChild(span);

  const donationBox = box.parentElement;
  const donationBoxAmount = parseInt(donationBox.getAttribute('data-amount'));
  const donationValue = donationAmount < donationBoxAmount ? donationAmount : donationBoxAmount;
  const donationLabel = document.createElement('div');
  donationLabel.className = 'donation-label';
  box.appendChild(donationLabel);
} else {
	const repopulate = confirm("Do you want to automatically repopulate the donation pyramid?"); // Adding a prompt
	if (repopulate) {
		// Get the boxes in the row corresponding to the donation amount
const rowBoxes = document.querySelectorAll('.donation-box-front[data-row="' + rowIndex + '"]');

let filledRowBoxes = 0;
rowBoxes.forEach((box) => {
  if (box.innerHTML.trim() !== "") {
    filledRowBoxes++;
  }
});

// If all boxes are filled in the current row, then create a new donation box
if (filledRowBoxes === rowBoxes.length) {
  // Find the row in the DOM
  const row = rowBoxes[0] ? rowBoxes[0].closest('.donation-row') : null;
  
  if (row) {
    // Create and add a new donation box to this row
    // Note: You'll need to define addDonationBox yourself based on how your HTML is structured
    addDonationBox(row, donationName, donationAmount, donationColor);
  }
}
  
	}
			
else {
  console.log('Donation not saved. Please manually add a new box to the row.');
}
}
			
    	if (pledgePendingValue === "pending") {
  			const tableBody = document.querySelector(".pending-table tbody");
  			tableBody.insertBefore(targetRow, tableBody.firstChild);
		}
		if (pledgePendingValue === "engaged" || pledgePendingValue === "identified") {
  			const tableBody = document.querySelector(".pipeline-table tbody");
  			tableBody.insertBefore(targetRow, tableBody.firstChild);
		} else if (pledgePendingValue === "pledge") {
  			const tableBody = document.querySelector(".pledges-table tbody");
  			tableBody.insertBefore(targetRow, tableBody.firstChild);
		}
		const pledgesCells = Array.from(document.querySelectorAll(".pledges-table tbody td:nth-child(2)"));
		const totalDonations = pledgesCells.reduce((acc, curr) => {
  			const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
  			return isNaN(amount) ? acc : acc + amount;
		}, 0);
			
		const pendingCells = Array.from(document.querySelectorAll(".pending-table tbody td:nth-child(2)"));
		const pendingDonations = pendingCells.reduce((acc, curr) => {
  			const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
  			return isNaN(amount) ? acc : acc + amount;
		}, 0);
		
		const goal = <?php echo $goal; ?>;
		const percent = totalDonations / goal * 100;
		const meterFill = document.getElementById("donation-meter-fill");
		meterFill.style.width = `${percent}%`;
		meterFill.innerHTML = `
  			<div class="fill" style="width: ${percent}%">
    		${percent > 100 ? `<p>${percent.toFixed()}%</p>` : ''}
  			</div>
		`;
		const meterText = document.getElementById("donation-meter-text");
	    const meterTexthead = document.getElementById("donation-meter-head");
		meterTexthead.innerHTML = `$${totalDonations.toLocaleString()} Raised To-Date (${percent.toFixed()}%)`;
		meterText.innerHTML = `$${goal.toLocaleString()} Campaign Goal <span class="percent"></span>`;
			
		// Initialize count variables for each donation type
		let individualCount = 0;
		let foundationCount = 0;
		let corporationCount = 0;
		let publicCount = 0;
		let boardCount = 0;
		let otherCount = 0;

		// Select all the rows in the moves management table
		const rowsType = document.querySelectorAll('#moves-management table tbody tr');
		
			
		// Iterate over each row and update the count variables
		rowsType.forEach(row => {
  			const donationTypeSelect = row.querySelector('.donation-type-select');
 			const donationTypeValue = donationTypeSelect.value;

  			// Update the count variables based on the donation type value
  			if (donationTypeValue === "individual") {
    			individualCount++;
  			} else if (donationTypeValue === "foundation") {
    			foundationCount++;
  			} else if (donationTypeValue === "corporation") {
    			corporationCount++;
  			} else if (donationTypeValue === "public") {
    			publicCount++;
  			} else if (donationTypeValue === "board") {
    			boardCount++;
  			} else if (donationTypeValue === "other") {
    			otherCount++;
  			}
		});

	
		const highestCount = Math.max(individualCount, foundationCount, corporationCount, publicCount, boardCount, otherCount);
			
        const individualBar = ((individualCount * 100)/(highestCount));
		const foundationBar = ((foundationCount * 100)/(highestCount));
		const corporationBar = ((corporationCount * 100)/(highestCount));
		const publicBar = ((publicCount * 100)/(highestCount));
		const boardBar = ((boardCount * 100)/(highestCount));
		const otherBar = ((otherCount * 100)/(highestCount));
			
		const formattedGoal = '$' + goal.toLocaleString();
		const formattedPledged = '$' + totalDonations.toLocaleString();
		const formattedPending = '$' + pendingDonations.toLocaleString();
    	const meterFillStyle = "width: " + percent + "%";
    	const meterFillContent = `<div class="fill" style="width: ${percent}%">${percent > 100 ? `<p>${Math.round(percent)}%</p>` : ''}</div>`;
		const dashboard = document.getElementById("dashboard-html");
		dashboard.innerHTML = `
       	 	<div style="display: flex; flex-direction: column; background-color: #F0F0F0C1; border-radius: 10px; padding: 20px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3); margin-bottom: 20px;">
            <div style="width: 100%; height: 100px; background-color: #77C4D5; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
                <!-- Campaign goal box -->
                <h2 style="color: #FFFFFF; font-size: 37px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); padding: 0 20px;">${formattedGoal} Campaign Goal</h2>
            </div>
            <div style="width: 100%; height: 90px; background-color: #7866A1; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center; padding: 10px;">
                <!-- Donation meter box -->
                <div class="dashboard-meter">
                    ${meterFillContent}
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 15px; align-items: center;">
        		<div style="width: 49%; background-color: #00758D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            		<!-- Amount pledged box -->
            		<div>
               	 		<h3 style="color: #FFFFFF; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">${formattedPledged} Pledged</h3>
            		</div>
        		</div>
        		<div style="width: 49%; background-color: #F78D2D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            		<!-- Amount pending box -->
            		<div>
                		<h3 style="color: #FFFFFF; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">${formattedPending} Pending</h3>
            		</div>
        		</div>
    		</div>
			<div style="width: 100%; height: 225px; background-color: #77C4D5; border-radius: 10px; display: flex; justify-content: center; align-items: center;">
    <!-- Bar graph chart -->
   <div style="width: 85%; height: 80%; background-color: rgb(255,255,255); border: 7px solid #00758D; border-radius: 8px; display: flex; align-items: flex-end; justify-content: space-around; padding: 10px;">
    <div style="display: flex; margin-top: 0px; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 70px; border-radius: 5px 5px 0 0; height: ${individualBar}px; max-height: 100%; background-color: #7866A1;"></div>
        <span style="font-size: 13px; margin-top: 10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Individuals: ${individualCount}</span>
    </div>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 70px; border-radius: 5px 5px 0 0; height: ${foundationBar}px; max-height: 100%; background-color: #77C4D5;"></div>
        <span style="font-size: 13px;margin-top: 10px;  padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Foundations: ${foundationCount}</span>
    </div>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 70px; border-radius: 5px 5px 0 0; height: ${corporationBar}px; max-height: 100%; background-color: #00758D;"></div>
        <span style="font-size: 13px;margin-top: 10px;  padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Corporations: ${corporationCount}</span>
    </div>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 70px; border-radius: 5px 5px 0 0; height: ${publicBar}px; max-height: 100%; background-color: #FF8C00;"></div>
        <span style="font-size: 13px;margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Public: ${publicCount}</span>
    </div>
   <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 70px; border-radius: 5px 5px 0 0; height: ${boardBar}px; max-height: 100%; background-color: #FFEB3B;"></div>
        <span style="font-size: 13px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Board: ${boardCount}</span>
    </div>
	<div style="display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 70px; border-radius: 5px 5px 0 0; height: ${otherBar}px; max-height: 100%; background-color: #4CAF50;"></div>
        <span style="font-size: 13px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Other: ${otherCount}</span>
    </div>
</div>

</div>

</div>

        </div>
    `;

const donationBoxes = document.querySelectorAll(".donation-box");

  donationBoxes.forEach(function (box) {
    box.addEventListener("click", function () {
      if (!box.classList.contains("flipped") && box.innerHTML.trim() !== "") {
        box.classList.add("flipped");
      } else {
        box.classList.remove("flipped");
      }
    });
    const backElement = box.querySelector(".donation-box-back");
    backElement.innerHTML = "$" + donationAmount.toLocaleString();
  }); 

		}, 0);
		
	}

		
	document.addEventListener("DOMContentLoaded", function() {
  		const goal = <?php echo $goal; ?>;
  		
		const meterText = document.getElementById("donation-meter-text");
		 const meterTexthead = document.getElementById("donation-meter-head");
		meterTexthead.innerHTML = `$0 Raised To-Date (0%)`;
  		meterText.innerHTML = `$${goal.toLocaleString()} Campaign Goal <span class="percent"></span>`;

		const percent = 0;
		const formattedGoal = '$' + goal.toLocaleString();
    	const meterFillStyle = "width: " + percent + "%";
    	const meterFillContent = `<div class="fill" style="width: ${percent}%">${percent > 100 ? `<p>${Math.round(percent)}%</p>` : ''}</div>`;
		const dashboard = document.getElementById("dashboard-html");
		dashboard.innerHTML = `
       	 	<div style="display: flex; flex-direction: column; background-color: #F0F0F0; border-radius: 10px; padding: 20px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3); margin-bottom: 20px;">
            <div style="width: 100%; height: 100px; background-color: #77C4D5; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
                <!-- Campaign goal box -->
                <h2 style="color: #FFFFFF; font-size: 37px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); padding: 0 20px;">${formattedGoal} Campaign Goal</h2>
            </div>
            <div style="width: 100%; height: 90px; background-color: #7866A1; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center; padding: 10px;">
                <!-- Donation meter box -->
                <div class="dashboard-meter">
                    ${meterFillContent}
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 15px; align-items: center;">
        		<div style="width: 49%; background-color: #00758D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            		<!-- Amount pledged box -->
            		<div>
               	 		<h3 style="color: #FFFFFF; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">$0 Pledged</h3>
            		</div>
        		</div>
        		<div style="width: 49%; background-color: #F78D2D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            		<!-- Amount pending box -->
            		<div>
                		<h3 style="color: #FFFFFF; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">$0 Pending</h3>
            		</div>
        		</div>
    		</div>
     
 <div style="width: 100%; height: 225px; background-color: #77C4D5; border-radius: 10px; display: flex; justify-content: center; align-items: center;">
                <!-- Bar graph chart -->
        <div style="width: 85%; height: 80%; background-color: rgb(255,255,255); border: 7px solid #00758D; border-radius: 8px; display: flex; align-items: flex-end; justify-content: space-around; padding: 10px;">
    <div style="display: flex; margin-top: 0px; flex-direction: column; align-items: center;">
        <div style="width: 70px; border-radius: 5px 5px 0 0; height: 100px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 13px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Individuals: 0</span>
    </div>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="width: 70px; border-radius: 5px 5px 0 0; height: 100px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 13px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Foundations: 0</span>
    </div>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="width: 70px; border-radius: 5px 5px 0 0; height: 100px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 13px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Corporations: 0</span>
    </div>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="width: 70px; border-radius: 5px 5px 0 0; height: 100px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 13px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Public: 0</span>
    </div>
    <div style="display: flex; flex-direction: column; align-items: center;">
        <div style="width: 70px; border-radius: 5px 5px 0 0; height: 100px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 13px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Board: 0</span>
    </div>
	<div style="display: flex; flex-direction: column; align-items: center;">
        <div style="width: 70px; border-radius: 5px 5px 0 0; height: 100px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 13px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Other: 0</span>
    </div>
    </div>
        </div>
    `;

	});
		

	//SAVE AS PDF
	function generatePDF() {
  		const tab = document.querySelector('.tab.active');
  		const tabName = tab.getAttribute('id');
  		const date = new Date().toLocaleDateString().replaceAll('/', '-');
  		const pdfName = `MyPDF_${tabName}_${date}.pdf`; // Update the PDF name as desired

  		html2canvas(tab, { scrollY: -window.scrollY }).then(canvas => {
    		const imgData = canvas.toDataURL('image/png');
   		 	const imgWidth = 250; // width of image inside the margins
    		const pageWidth = 290; // A4 page width in mm
    		const pageHeight = 320; // A4 page height in mm
    		const marginLeft = (pageWidth - imgWidth) / 2;
    		const marginTop = 25;
    		const imgHeight = canvas.height * imgWidth / canvas.width;
    		const doc = new jsPDF('l', 'mm');
    		const position = 0;

    		doc.addImage(imgData, 'PNG', marginLeft, marginTop, imgWidth, imgHeight);
    		const pdfBlob = doc.output('blob');
    		const objectUrl = URL.createObjectURL(pdfBlob);

    		// Open the PDF in a new tab
    		const pdfWindow = window.open();
   	 		pdfWindow.location.href = objectUrl;
  		});
	}


function addDonationBox(row, donationName, donationAmount, donationColor) {

  console.log(row);
  const rowLabel = row.querySelector('.donation-row-label');
  const numBoxes = parseInt(rowLabel.innerText.split(' ')[0]);

  rowLabel.innerText = (numBoxes + 1) + ' x ' + rowLabel.innerText.split(' ')[2];
  const amount = parseInt(rowLabel.innerText.split('$')[1].replace(/[^0-9.-]+/g, ''));
  const box = document.createElement('div');
  box.className = 'donation-box';
  box.setAttribute('data-amount', amount);

  const boxInner = document.createElement('div');
  boxInner.className = 'donation-box-inner';

  const boxFront = document.createElement('div');
  boxFront.className = 'donation-box-front';
  boxFront.setAttribute('data-row', 'row' + row);
  boxFront.style.backgroundColor = donationColor;
  boxFront.style.color = "#fff";
  boxFront.style.fontWeight = "500";
  boxFront.style.textAlign = "center";
  boxFront.style.display = "flex";
  boxFront.style.justifyContent = "center";
  boxFront.style.alignItems = "center";
  boxFront.innerHTML = donationName;

  const boxBack = document.createElement('div');
  boxBack.className = 'donation-box-back';

  boxInner.appendChild(boxFront);
  boxInner.appendChild(boxBack);
  box.appendChild(boxInner);
  row.appendChild(box);

  // Update the total donations amount
  const totalDonationsLabel = document.querySelector('.donation-row-label b');
  const totalDonations = calculateTotalDonations(); // Function to calculate the total donations
  totalDonationsLabel.innerText = 'Total: $' + numberWithCommas(totalDonations); // Helper function to add commas to the number
}
	
		
function editNumBoxes(element) {
	
  const rowLabel = element;
  const numBoxes = parseInt(rowLabel.innerText.split(' ')[0]);
  const newRowLabel = prompt('Enter the new number of boxes:', numBoxes);
  if (newRowLabel !== null) {
    const newNumBoxes = parseInt(newRowLabel);
    if (!isNaN(newNumBoxes) && newNumBoxes > 0) {
      const row = rowLabel.parentElement;
      const currentNumBoxes = row.querySelectorAll('.donation-box').length;
      const filledBoxes = row.querySelectorAll('.donation-box-front:not(:empty)');

      if (filledBoxes.length > 0 && newNumBoxes < currentNumBoxes) {
        alert('Error: Cannot reduce the number of boxes to less than the amount of boxes already filled.');
        return;
      }
      if (newNumBoxes > 8) {
        alert('Error: Maximum number of boxes in each row is 8.');
        return;
      }

      rowLabel.innerText = newNumBoxes + ' x ' + rowLabel.innerText.split(' ')[2];
      const existingBox = row.querySelector('.donation-box-front');
      const rowId = existingBox ? existingBox.getAttribute('data-row') : '';

      if (newNumBoxes > currentNumBoxes) {
        const amount = parseInt(rowLabel.innerText.split('$')[1].replace(/[^0-9.-]+/g, ''));
        const boxesToAdd = newNumBoxes - currentNumBoxes;

        for (let i = 0; i < boxesToAdd; i++) {
          const box = document.createElement('div');
          box.className = 'donation-box';
          box.setAttribute('data-amount', amount);

          const boxInner = document.createElement('div');
          boxInner.className = 'donation-box-inner';

          const boxFront = document.createElement('div');
          boxFront.className = 'donation-box-front';
          boxFront.setAttribute('data-row', rowId);

          const boxBack = document.createElement('div');
          boxBack.className = 'donation-box-back';

          boxInner.appendChild(boxFront);
          boxInner.appendChild(boxBack);
          box.appendChild(boxInner);
          row.appendChild(box);
        }
      } else if (newNumBoxes < currentNumBoxes) {
        const boxesToRemove = currentNumBoxes - newNumBoxes;
        const boxes = row.querySelectorAll('.donation-box');

        for (let i = 0; i < boxesToRemove; i++) {
          row.removeChild(boxes[boxes.length - 1]);
        }
      }

      // Update the total donations amount
      const totalDonationsLabel = document.querySelector('.donation-row-label b');
      const totalDonations = calculateTotalDonations(); // Function to calculate the total donations
      totalDonationsLabel.innerText = 'Total: $' + numberWithCommas(totalDonations); // Helper function to add commas to the number
      rowLabel.classList.add('button-pressed');
rowLabel.classList.add('flash-animation');
setTimeout(() => {
  rowLabel.classList.remove('button-pressed');
  setTimeout(() => {
    rowLabel.classList.remove('flash-animation');
  }, 300);
}, 200);
    } else {
      alert('Invalid input. Please enter a positive number.');
    }
  }
}
		
function calculateTotalDonations() {
  const rows = document.querySelectorAll('.donation-row');
  let totalDonations = 0;

rows.forEach(row => {
  const rowLabel = row.querySelector('.donation-row-label');
  const isTotalRow = rowLabel.innerHTML.includes('Total:');
  if (!isTotalRow) {
    const numBoxes = parseInt(rowLabel.innerText.split(' ')[0]);
    const amount = parseInt(rowLabel.innerText.split('$')[1].replace(/[^0-9.-]+/g, ''));
    totalDonations += numBoxes * amount;
  }
});

  return totalDonations;
	
}

function numberWithCommas(number) {
  return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
		
// JavaScript function to toggle the settings menu
function toggleSettingsMenu() {
  const popupContainer = document.getElementById("settingsPopup");
  if (popupContainer.style.display === "none" || popupContainer.style.display === '') {
    popupContainer.style.display = "block";
  } else {
    popupContainer.style.display = "none";
  }
}

// You may also want to close the settings menu if the user clicks outside of it
document.addEventListener("click", function (event) {
  const popupContainer = document.getElementById("settingsPopup");
  const settingsButton = document.querySelector(".settings-button");
  if (!event.target.closest(".settings-button") && !event.target.closest(".settings-popup")) {
    popupContainer.style.display = "none";
  }
});

		
	</script>
	<?php
	
// Define the HTML and CSS output
$output = '
<style>

.donation-row-label {
  transition: background-color 0.3s;
  position: relative;
}

.donation-row-label::after {
  content: "";
  position: absolute;
  top: -10%; /* Adjust the positioning to center the flash animation */
  left: -10%; /* Adjust the positioning to center the flash animation */
  width: 120%;
  height: 120%;
  border-radius: 15px;
  background-color: rgba(0,0,0,0.09); /* Adjust the color as needed */
  opacity: 0;
  transition: opacity 0.2s, background-color 0.2s;
  pointer-events: none;
}

.donation-row-label:active::after {
  opacity: 1;
}

@keyframes flash-effect {
  0% {
    transform: scale(0.95); /* Adjust the scale factor as needed */
  }
  100% {
    transform: scale(1); /* Adjust the scale factor as needed */
  }
}

.flash-animation {
  animation: flash-effect 0.2s linear;
}



.tabbed-content .table-column h2 {
  background-color: #00758D;
  color: #fff;
  text-align: center;
  padding: 10px 0;
  margin: 0;
  border-radius: 10px 10px 0 0;
}

.tabbed-content table {
  width: 100%;
  padding: 10px;
  border-radius: 0 0 10px 10px;
  border-collapse: separate;
  border-spacing: 0;
  box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.1);
}
	
.logout-button {
  margin-top: 0;
  position: absolute;
  top: 165px;
  right: 40px;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.logout-button .button {
    background-color: #F78D2D;
    color: white;
    display: block;
    padding: 10px;
    text-align: center;
    text-decoration: none;
}

.donation-container {
  margin-right: 40px;
  margin-top: 50px;
  position: absolute;
  top: 180px;
  right: 0;
  width: 20%;
}

.donation-meter {
  right: -160px;
  background-color: #00758D;
  border-radius: 10px;
  padding: 10px;
  text-align: center;
  box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.1);
}

.donation-meter h2 {
  color: white;
  margin: 0;
  font-size: 18px;
  font-weight: bold;
}

.donation-meter p {
  color: white;
  margin: 0;
  font-size: 14px;
}

.donation-meter .meter {
  background-color: white;
  height: 20px;
  border-radius: 10px;
  margin-top: 8px;
  display: flex;
  justify-content: space-between;
}

.donation-meter .fill {
   transition: width 0.5s ease-out;
   border-radius: 13px;
   width: ${percent}%;
   position: inherit;
   background:#FFC897;
   background-color: -webkit-linear-gradient(-45deg, #F78D2D 25%, #FFC897 25%, #FFC897 50%, #F78D2D 50%, #F78D2D 75%, #FFC897 75%);
   background: -moz-linear-gradient(-45deg, #F78D2D 25%, #FFC897 25%, #FFC897 50%,#F78D2D 50%, #F78D2D 75%, #FFC897 75%); 
   background: -o-linear-gradient(-45deg, #F78D2D 25%, #FFC897 25%, #FFC897 50%, #F78D2D 50%,#F78D2D 75%, #FFC897 75%);
   background: linear-gradient(-45deg,#F78D2D 25%, #FFC897 25%, #FFC897 50%, #F78D2D 50%, #F78D2D 75%, #FFC897 75%);
   background-size: 27px 27px;
   -webkit-animation: barberpole 4s infinite linear;
}

@-webkit-keyframes barberpole {
  from { background-position: 0; }
  to { background-position: -27px 0; }
}

.percent {
  font-size: 14px;
  text-transform: uppercase;
  margin-top: 4px;
  font-weight: bold;
}

#donation-meter-text {
  display: flex;
  margin-top: 4px;
  flex-direction: column;
}


.donation-buttons {
  margin-top: 12.5px;
  margin-bottom: 10px;
  clear: both;
  z-index: 2;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.donation-buttons .button {
  background-color: #F78D2D;
  border-radius: 25px;
  color: white;
  font-size: 14px;
  font-weight: bold;
  display: block;
  padding: 10px;
  text-align: center;
  text-decoration: none;
}

.tabbed-container {
  display: flex;
  flex-direction: column;
  margin-left: -110px; /* add margin-left to move container to the left */
  height: 100%;
  width: 91%;
}

.tabbed-menu {
margin-top: 15px;
margin-bottom: 10px;
}

.tabbed-menu ul {
list-style: none;
margin: 0;
padding: 0;
display: flex;
}

.tabbed-menu li {
margin-right: 5px;
  padding: 10px 15px;
  cursor: pointer;
  margin-left: 5px;
  text-transform: uppercase;
  font-size: 12px;
  font-weight: bold;
  letter-spacing: 1px;
  transition: background-color 0.2s ease-in-out;
  border-radius: 15px;

}

.tabbed-menu li.active {
background-color: #F78D2D;
color: white;
font-weight: bold;
}

.tabbed-menu li.active a {
color: white;
font-weight: bold;
}

.tabbed-menu li:not(.active) a {
color: black;
font-weight: bold;
}

.tabbed-menu li:not(.active) {
background-color: rgb(231,231,231);
color: black;
font-weight: bold;
}

.tabbed-content .tab {
display: none;
}

.tabbed-content .tab.active {
display: block;
animation: fade-in 0.5s;
}

@keyframes fade-in {
from { opacity: 0; }
to { opacity: 1; }
}

.donation-table thead{
 border-radius: 10px;
 
}

#moves-management table {
  margin-left: 0px; /* Spacing from donation meter */
  border-collapse: collapse;
  border-radius: 10px;
  margin-bottom: 283px; //this value should be changing
}

#moves-management table th {
  background-color: #00758D;
  font-size: 12px;
  color: white;
  padding: 7px;
  text-align: center;
  vertical-align: middle;
  
}

#moves-management table td {
  border: 1px solid #ddd;
  padding: 10px;
}

.save-button, .edit-button {
  font-size: 12px !important;
  padding: 7px 7px !important;
  border-radius: 4px !important;
  display: flex;
  justify-content: center;  !important;
  align-items: center; !important;
}

.attach-button, .download-button {
  padding: 7px 7px !important;
  border-radius: 4px !important;
  display: flex;
  justify-content: center;
  align-items: center;
}

  .table-container {
    display: flex;
    justify-content: center;
    margin-top: 20px;
  }

  .table-column {
    margin: 0 20px;
    width: 50%;
  }
  
  .pledges-table,
  .pending-table {
    padding: 10px;
  border-radius: 10px;
  }

  @media (max-width: 767px) {
    .table-container {
      flex-direction: column;
      align-items: center;
    }

    .table-column {
      margin: 20px 0;
      width: 90%;
    }
  }

  
#pledges-pending .table-container h2 {
  background-color: #00758D;
  color: #fff;
  text-align: center;
  padding: 5px 0;
  margin: 0;
}

#pledges-pending .pledges-table thead {
  background-color: #77C4D5;
}

#pledges-pending .pending-table thead {
  background-color: #77C4D5;
}

#pledges-pending .pipeline-table thead {
  background-color: #77C4D5;
}

#pledges-pending table {
  width: 100%;
  padding: 10px;
  border-collapse: collapse;
   margin-bottom: 210px; //this value should be changing
}

#pledges-pending th, #pledges-pending td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: center;
}

#pledges-pending th {
  font-weight: bold;
}

.donation-pyramid {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  margin-left: 130px;
}

donation-pyramid::before {
  content: "";
  display: block;
  position: absolute;
  top: 270px;
  left: 60px;
  right: 380px;
  bottom: 37.7%;
  background-color: #D4F0FF89;
  z-index: -1;
  filter: blur(2px); /* add a blur filter with 10px radius */
}


.donation-row {
  display: flex;
  justify-content: center;
}

.donation-row-label {
  position: absolute;
  left: 0;
  margin-top: 17px;
  margin-left: 75px;
  font-size: 15px;
}


.donation-box:nth-last-child(2) {
  margin-right: auto;
  margin-left: auto;
}

.donation-row:first-child .donation-box {
  margin-bottom: auto;
}

.donation-row:last-child .donation-box {
  font-size: 13px;
  margin-top: 7px;
  margin-bottom: 15px; 
  width: 250px; 
  height: 38px; /* adjust when i add it back in */
  border: none;
  background-color: #F3F3F3DB;;
  
}

.donation-row:last-child .donation-label {

 top: 50px;
  
}

  .donation-key {
    position: absolute;
    top: 280px;
    right: 400px;
  }

  .donation-key-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
  }

  .donation-key-item .status-circle {
    display: inline-block;
    width: 28px;
    height: 28px;
    margin-right: 12px;
    border-radius: 50%;
	box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.1);
  }

  .donation-key-item .status-label {
    font-weight: normal;
	font-size: 15px;
  }

  .donation-key-item .pledged {
    background-color: #7866A1;
  }

  .donation-key-item .pending {
    background-color: #77C4D5;
  }

  .donation-key-item .engaged {
    background-color: #00758D;
  }

  .donation-key-item .identified {
    background-color: #F78D2D;
  }
	
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
                100% { width: ${percent}%; }
            }

.donation-box {
  position: relative;
  width: 90px;
  height: 38px;
  border-radius: 25px;
  margin: 7px;
  overflow: hidden;
  cursor: pointer;
  justify-content: center;
  align-items: center;
  text-align: center;
  display: flex;
}

.donation-box-inner {
  position: absolute;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  transition: transform 0.5s;
}

.donation-box-front,
.donation-box-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
}

.donation-box-front {
  background-color: #d4d4d4;
  display: flex;
  border-radius: 25px;
  justify-content: center;
  align-items: center;
  font-weight: 500;
  text-align: center;
  font-size: 18px;
  padding: 10px;
  color: #000;
}

.donation-box-back {
  background-color: #939393;
  display: flex;
  border-radius: 25px;
  justify-content: center;
  align-items: center;
  font-weight: 500;
  text-align: center;
  font-size: 18px;
  padding: 10px;
  color: #fff;
  transform: rotateX(180deg);
}

.flipped .donation-box-inner {
  transform: rotateX(180deg);
}

/* Style for the popup settings menu */
.popup-container {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 9999;
}

.settings-popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #fff;
  padding: 20px;
  border-radius: 5px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
  z-index: 10000; /* Make sure the popup is on top of other elements */
}

.settings-popup h2 {
  margin-top: 0;
}

.settings-popup button {
  margin-top: 10px;
}
/* Additional style for the settings button */
.settings-button {
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px;
  position: absolute;
  top: 165px;
  right: 160px;
  width: 41px; 
  height: 41px;
  background-color: #B2B2B2; /* Added background color to match the original style */
  cursor: pointer;
}

.settings-button:hover {
	background-color: #707070;
	transition: background-color 0.3s;
}


</style>

<div class="settings-button" onclick="toggleSettingsMenu()">
  <img src="https://icon-library.com/images/white-gear-icon/white-gear-icon-6.jpg" alt="Settings" style="width: 20px; height: 20px; align-items: center;  justify-content: center; align-items: center;">
</div>

<!-- Placeholder for the popup settings menu -->
<div class="popup-container" id="settingsPopup">
  <div class="settings-popup">
    <h2>Settings</h2>
    <p>Placeholder for settings options</p>
    <button onclick="toggleSettingsMenu()">Close</button>
  </div>
</div>

<div class="logout-button">
	' . add_logout_button() . '
</div>

<div class="donation-container">
  <div class="donation-meter">
    <h2 id="donation-meter-head"></h2>
    <p id="donation-meter-text"></p>
    <div class="meter">
      <div class="fill" id="donation-meter-fill"></div>
    </div>
  </div>
  <div class="donation-buttons" style="text-align:center">
    <button id="add-donation-button" style="width:100%" onclick="addRow()">ADD NEW DONOR</button>
    <button id="pdf-button" style="width:100%" onclick="generatePDF()">SAVE AS PDF</button>
  </div>
  <div class="logo-container" style="text-align:center; margin-top: 30px;">
    <img src="' . $imgurl . '" alt="Client Logo" style="width: 70%; display: block; margin: 0 auto;">
  </div>
</div>


<div class="tabbed-container">
  <div class="tabbed-content">
	<div class="tab active" id="donation-pyramid">
      <h2>Gift Pyramid</h2>
      ' . $donation_pyramid . '
    </div>
    <div class="tab" id="pledges-pending">
      <h2>Pledges, Pending, & Pipeline</h2>
      <div class="table-container">
        <div class="table-column">
          <h2>Pledges</h2>
          <table class="pledges-table" id="pledged-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Amount</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
        <div class="table-column">
          <h2>Pending</h2>
          <table class="pending-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Amount</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
		<div class="table-column">
          <h2>Pipeline</h2>
          <table class="pipeline-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Amount</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
<div class="tab" id="moves-management">
      <h2>Moves Management</h2>
      <table id="donation-table">
        <thead>
          <tr>
		  	<th>Status</th>
            <th>Type</th>
            <th>Full Name</th>
            <th>Organization</th>
            <th>Gift Request</th>
            <th>Next Step</th>
            <th>Recent Involvement</th>
            <th>Notes</th>
			<th>Documents</th>
			<th></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
<div class="tab" id="dashboard">
	<h2>Campaign Dashboard</h2>
	<div class="fill" id="dashboard-html"></div>
</div>

<div class="tabbed-menu">
  <ul>
    <li class="tab-link active" data-tab="donation-pyramid" onclick="activateTab()">Gift Pyramid</li>
    <li class="tab-link" data-tab="pledges-pending" onclick="activateTab()">Pledges, Pending, & Pipeline</li>
    <li class="tab-link" data-tab="moves-management" onclick="activateTab()">Moves Management</li>
	<li class="tab-link" data-tab="dashboard" onclick="activateTab()">Campaign Dashboard</li>
  </ul>
</div>

  </div>
</div>
';
// Return the output
return $output;

}

add_filter( 'the_title', function( $title ) {
    if ( is_page() && ! is_admin() ) { // Only remove title from front-end display
        return '';
    }
    return $title;
} );

add_shortcode('campaign-toolkit', 'donation_toolkit_shortcode');
