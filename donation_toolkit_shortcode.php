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
	
	$imgurl = get_field('client_logo', $campaign_id);

	$slice1 = get_field('slice_1', $campaign_id);
	$slice2 = get_field('slice_2', $campaign_id);
	$slice3 = get_field('slice_3', $campaign_id);
	
	// Split the input string by ': $' to get two parts
	$part1 = explode(': $', $slice1);
	$part2 = explode(': $', $slice2);
	$part3 = explode(': $', $slice3);
	
    $field1name = "'" . trim($part1[0]) . "'";
    $field1amount = str_replace(',', '', trim($part1[1])); 
	$field2name = "'" . trim($part2[0]) . "'";
    $field2amount = str_replace(',', '', trim($part2[1])); 
	$field3name = "'" . trim($part3[0]) . "'";
    $field3amount = str_replace(',', '', trim($part3[1])); 

    if (filter_var($imgurl, FILTER_VALIDATE_URL) === FALSE)
    {
      $imgurl = wp_get_attachment_url($imgurl);
    }

	ob_start();
	createDonationPyramid($goal);
	$donation_pyramid = ob_get_clean();

	?>
	<script>
// Global array to store donors
let donors = [];
		
function numberWithCommas(number) {
  return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
		

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
    const boxInner = row.querySelector('.donation-box-inner');
    const frontElement = row.querySelector('.donation-box-front');
    const backElement = row.querySelector('.donation-box-back');

    if (boxInner) {
        const boxDisplayName = boxInner.innerText.trim().replace(/\s+/g, ' ');
        if (boxDisplayName === displayName) {
            // Clear the inner text of the front and back elements
            if (frontElement) {
                frontElement.innerText = '';
				frontElement.style.backgroundColor = "#d4d4d4";
            frontElement.style.color = '';
            frontElement.style.fontWeight = '';
            frontElement.style.textAlign = '';
          frontElement.style.display = '';
            frontElement.style.justifyContent = '';
            }
            if (backElement) {
                backElement.innerText = '';
				backElement.style.backgroundColor = "#939393;";
            backElement.style.color = '';
           backElement.style.fontWeight = '';
            backElement.style.textAlign = '';
         backElement.style.display = '';
            backElement.style.justifyContent = '';
            }
        }
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
    			pipelineRows.forEach(row => {
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

// Create a new donor object and add to donors array
let newDonor = {
  name: donationName,
  amount: donationAmount
};
donors.push(newDonor);
			
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

const repopulate = confirm("This row is already full. Would you like to add an additional donator box and automatically repopulate the Gift Pyramid?"); // Adding a prompt
console.log(repopulate);
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
        addDonationBox(row, rowIndex, donationName, donationAmount, donationColor);
      }
    }
  } else {
    const modal = document.getElementById('alertModule');
    modal.style.display = "block";
	  return;
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
		// ... previous code ...

const pledgesTotalElement = document.querySelector(".pledges-total");
const pendingTotalElement = document.querySelector(".pending-total");
const pipelineTotalElement = document.querySelector(".pipeline-total");

const formatCurrency = (amount) => {
    // You can format this as per your requirements
    return "$" + numberWithCommas(amount);
};

// Populate the totals for Pledges
const pledgesCells = Array.from(document.querySelectorAll(".pledges-table tbody td:nth-child(2)"));
const totalDonations = pledgesCells.reduce((acc, curr) => {
    const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
   return isNaN(amount) ? acc : acc + amount;
}, 0);
pledgesTotalElement.innerText = formatCurrency(totalDonations);

// Populate the totals for Pending
const pendingCells = Array.from(document.querySelectorAll(".pending-table tbody td:nth-child(2)"));
const pendingDonations = pendingCells.reduce((acc, curr) => {
    const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
return isNaN(amount) ? acc : acc + amount;
}, 0);
pendingTotalElement.innerText = formatCurrency(pendingDonations);

// Populate the totals for Pipeline
const pipelineCells = Array.from(document.querySelectorAll(".pipeline-table tbody td:nth-child(2)"));
const pipelineDonations = pipelineCells.reduce((acc, curr) => {
    const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
  return isNaN(amount) ? acc : acc + amount;
}, 0);
pipelineTotalElement.innerText = formatCurrency(pipelineDonations);

		
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
			
        const individualBar = ((individualCount * 180)/(highestCount));
		const foundationBar = ((foundationCount * 180)/(highestCount));
		const corporationBar = ((corporationCount * 180)/(highestCount));
		const publicBar = ((publicCount * 180)/(highestCount));
		const boardBar = ((boardCount * 180)/(highestCount));
		const otherBar = ((otherCount * 180)/(highestCount));
			
		const formattedGoal = '$' + goal.toLocaleString();
		const formattedPledged = '$' + totalDonations.toLocaleString();
		const formattedPending = '$' + pendingDonations.toLocaleString();
		const pendpledge = pendingDonations + totalDonations;
		const formattedPP = '$' + pendpledge.toLocaleString();
		let percentFix = Math.round(percent);
    	const meterFillStyle = "width: " + percent + "%";
    	const meterFillContent = `<div class="fill" style="width: ${percent}%">${percent > 100 ? `<p>${Math.round(percent)}%</p>` : ''}</div>`;
		const dashboard = document.getElementById("dashboard-html");
		dashboard.innerHTML = `
       	 	<div style="display: flex; flex-direction: column; background-color: #F0F0F0; border-radius: 10px; padding: 20px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3); margin-bottom: 20px;">
                        <div style="width: 100%; height: 70px; background-color: #F0F0F0; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
                <!-- Campaign goal box -->
                <h2 style="color: #00758D; font-size: 41px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); padding: 0 20px;">${formattedPledged} Pledged (${percentFix}% to Goal)</h2>
            </div>
            <div class="dashboard-meter" style="width: 100%; height: 90px; background-color: #FFFFFF; border: 5px solid #00758D; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
              
                    ${meterFillContent}
         
            </div>
            <div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 15px; align-items: center;">
        		<div style="width: 49%; background-color: #FFFFFF; border: 5px solid #00758D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            		<!-- Amount pledged box -->
            		<div>
               	 		<h3 style="color: #00758D; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">${formattedPending} Pending</h3>
            		</div>
        		</div>
        		<div style="width: 49%; background-color: #FFFFFF; border: 5px solid #00758D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            		<!-- Amount pending box -->
            		<div>
                		<h3 style="color: #00758D; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">${formattedPP} Pledged & Pending</h3>
            		</div>
        		</div>
    		</div>
<div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 15px; align-items: center;">		

<div style="width: 37%; height: 250px; background-color: rgb(255, 255, 255); border: 5px solid #00758D; border-radius: 8px; display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; padding: 10px;">
<div style="width: 100%; display: flex; align-items: center; flex-direction: column;">
    <h3 style="color: #00758D; font-size: 20px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">
        ${formattedGoal} Campaign Goal
    </h3>
    <div id="pieChartPlaceholder" style="width: 65%; height: 65%;">
    <!-- Pie Chart Using SVG -->
    <svg width="100%" height="100%" viewBox="0 0 42 42">
        <!-- Endowment slice -->
        <path id="endowmentSlice" d="M21 21 L21 3 A18 18 0 0 1 39 21 Z" fill="#00758D"></path>
        <text id="endowmentText" x="22" y="10" font-size="2" fill="white"></text>

        <!-- Capital slice -->
        <path id="capitalSlice" d="M21 21 L39 21 A18 18 0 0 1 3 21 Z" fill="#7866A1"></path>
        <text id="capitalText" x="30" y="30" font-size="2" fill="white"></text>

        <!-- Operating slice -->
        <path id="operatingSlice" d="M21 21 L3 21 A18 18 0 0 1 21 3 Z" fill="#FF8C00"></path>
        <text id="operatingText" x="8" y="30" font-size="2" fill="white"></text>
    </svg>
</div>
   </div>
   </div>

<!-- Bar graph chart -->
<div style="width: 61%; height: 250px; background-color: rgb(255,255,255); border: 5px solid #00758D; border-radius: 8px; display: flex; align-items: flex-end; justify-content: space-between; padding: 10px;">
    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 50px; border-radius: 5px 5px 0 0; height: ${individualBar}px; max-height: 100%; background-color: #FF8C00;"></div>
        <span style="font-size: 10px; margin-top: 10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Individuals: ${individualCount}</span>
    </div>
<div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 50px; border-radius: 5px 5px 0 0; height: ${corporationBar}px; max-height: 100%; background-color: #00758D;"></div>
        <span style="font-size: 10px;margin-top: 10px;  padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Corporations: ${corporationCount}</span>
    </div>
    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 50px; border-radius: 5px 5px 0 0; height: ${foundationBar}px; max-height: 100%; background-color: #77C4D5;"></div>
        <span style="font-size: 10px;margin-top: 10px;  padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Foundations: ${foundationCount}</span>
    </div>
   
    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 50px; border-radius: 5px 5px 0 0; height: ${boardBar}px; max-height: 100%; background-color: #7866A1"></div>
        <span style="font-size: 10px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Board: ${boardCount}</span>
    </div>
<div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 50px; border-radius: 5px 5px 0 0; height: ${publicBar}px; max-height: 100%; background-color: #CBCBCB;"></div>
        <span style="font-size: 10px;margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Public: ${publicCount}</span>
    </div>
	<div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);width: 50px; border-radius: 5px 5px 0 0; height: ${otherBar}px; max-height: 100%; background-color: #000000;"></div>
        <span style="font-size: 10px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Other: ${otherCount}</span>
    </div>
</div>

</div>
<div style="width: 100%; height: 160px; background-color: #FFFFFF; border: 5px solid #00758D; border-radius: 10px; margin-bottom: 15px; padding: 0 20px;">
    <h2 style="color: #00758D; font-size: 37px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); text-align: center; padding: 0 20px;">Top 5 Donors</h2>
    <div id="donorContainer" style="display: flex; justify-content: center; align-items: center; width: 100%; height: 50%;">
        <!-- Donors will be populated here by the script -->
    </div>
</div>



        </div>
    `;

		// Sort donorArray in descending order based on donation amount
    donors.sort((a, b) => b.amount - a.amount);
	console.log(donors);
    // Get the donorContainer element
    const donorContainer = document.getElementById('donorContainer');

    // Populate the top 5 donors into the donorContainer
    for (let i = 0; i < 5 && i < donors.length; i++) {
        const donor = donors[i];
        const donorElement = document.createElement('div');
donorElement.innerHTML = `
    <div style="text-align: center; margin: 0 35px;">
        <div style="color: #00758D; font-weight: bold; font-size: 20px;">${donor.name}</div>
        <div style="font-size: 20px;"><span style="color: #00758D; font-weight: bold;">$${numberWithCommas(Math.round(donor.amount))}</span></div>
    </div>`;
        donorContainer.appendChild(donorElement);
    }
		
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
            <div style="width: 100%; height: 70px; background-color: #F0F0F0; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
                <!-- Campaign goal box -->
                <h2 style="color: #00758D; font-size: 41px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); padding: 0 20px;">$0 Pledged (0% to Goal)</h2>
            </div>
            <div class="dashboard-meter" style="width: 100%; height: 90px; background-color: #FFFFFF; border: 5px solid #00758D; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center; padding: 10px;">
                    ${meterFillContent}
            </div>
            <div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 15px; align-items: center;">
        		<div style="width: 49%; background-color: #FFFFFF; border: 5px solid #00758D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            		<!-- Amount pledged box -->
            		<div>
               	 		<h3 style="color: #00758D; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">$0 Pending</h3>
            		</div>
        		</div>
        		<div style="width: 49%; background-color: #FFFFFF; border: 5px solid #00758D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
            		<!-- Amount pending box -->
            		<div>
                		<h3 style="color: #00758D; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">$0 Pledged & Pending</h3>
            		</div>
        		</div>
    		</div>
     
 <div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 15px; align-items: center;">
       
<div style="width: 37%; height: 250px; background-color: rgb(255, 255, 255); border: 5px solid #00758D; border-radius: 8px; display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; padding: 10px;">
<div style="width: 100%; display: flex; align-items: center; flex-direction: column;">
    <h3 style="color: #00758D; font-size: 20px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">
        ${formattedGoal} Campaign Goal
    </h3>
<div id="pieChartPlaceholder" style="width: 65%; height: 65%;">
    <!-- Pie Chart Using SVG -->
    <svg width="100%" height="100%" viewBox="0 0 42 42">
        <!-- Endowment slice -->
        <path id="endowmentSlice" d="" fill="#00758D"></path>
        <text id="endowmentText" x="12" y="10" font-size="2" fill="white"></text>

        <!-- Capital slice -->
        <path id="capitalSlice" d="" fill="#7866A1"></path>
        <text id="capitalText" x="5" y="30" font-size="2" fill="white"></text>

        <!-- Operating slice -->
        <path id="operatingSlice" d="" fill="#FF8C00"></path>
        <text id="operatingText" x="27" y="30" font-size="2" fill="white"></text>
    </svg>
</div>

   </div>
   </div>


<div style="width: 61%; height: 250px; background-color: rgb(255, 255, 255); border: 5px solid #00758D; border-radius: 8px; display: flex; align-items: flex-end; justify-content: space-between; padding: 10px;">
    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="width: 50px; border-radius: 5px 5px 0 0; height: 180px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 10px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Individuals: 0</span>
    </div>

  <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="width: 50px; border-radius: 5px 5px 0 0; height: 180px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 10px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Corporations: 0</span>
    </div>  
<div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="width: 50px; border-radius: 5px 5px 0 0; height: 180px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 10px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Foundations: 0</span>
    </div>
    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="width: 50px; border-radius: 5px 5px 0 0; height: 180px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 10px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Board: 0</span>
    </div>
    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="width: 50px; border-radius: 5px 5px 0 0; height: 180px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 10px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Public: 0</span>
    </div>
    
	<div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
        <div style="width: 50px; border-radius: 5px 5px 0 0; height: 180px; max-height: 100%; background-color: #EFEFEFE5"></div>
        <span style="font-size: 10px; margin-top:10px; padding: 2px; padding-right: 10px; padding-left: 10px; background-color: #EAEAEA ; border-radius: 20px; ">Other: 0</span>
    </div>

</div>

        </div>
<div style="width: 100%; height: 100px; background-color: #FFFFFF; border: 5px solid #00758D; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
                <!-- Campaign goal box -->
                <h2 style="color: #00758D; font-size: 37px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); padding: 0 20px;">Top 5 Donors</h2>
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


function addDonationBox(row, rowIndex, donationName, donationAmount, donationColor) {

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
  boxFront.setAttribute('data-row', rowIndex);
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
	
	const remainingAmount = removeDonationBoxes(amount);
if (remainingAmount > 0) {
  return;
}

  // Update the total donations amount
  const totalDonationsLabel = document.querySelector('.donation-row-label b');
  const totalDonations = calculateTotalDonations(); // Function to calculate the total donations
  totalDonationsLabel.innerText = 'Total: $' + numberWithCommas(totalDonations); // Helper function to add commas to the number
}
	
		
let globalRowLabel; // To hold the reference to the element being edited

function editNumBoxes(element) {
    globalRowLabel = element;
    const numBoxes = parseInt(globalRowLabel.innerText.split(' ')[0]);

    // Set input value and show the modal
    document.getElementById('numBoxesInput').value = numBoxes;
    document.getElementById('customModal').style.display = "block";
}

function saveChanges() {
    const newNumBoxes = parseInt(document.getElementById('numBoxesInput').value);
    if (isNaN(newNumBoxes) || newNumBoxes < 0) {
        document.getElementById('customModal').style.display = "none";
		const mod = document.getElementById('errorModule2');
    	mod.style.display = "block";
        return;
    }

    const row = globalRowLabel.parentElement;
    const currentNumBoxes = row.querySelectorAll('.donation-box').length;
    const filledBoxes = row.querySelectorAll('.donation-box-front:not(:empty)');

    if (filledBoxes.length > 0 && newNumBoxes < currentNumBoxes) {
		document.getElementById('customModal').style.display = "none";
		const modalerror = document.getElementById('errorModule1');
    	modalerror.style.display = "block";
        return;
    }

    if (newNumBoxes === 0) {
		document.getElementById('customModal').style.display = "none";
		const modalDelete = document.getElementById('errorModule4');
    	modalDelete.style.display = "block";
        return;
    }

    globalRowLabel.innerText = newNumBoxes + ' x ' + globalRowLabel.innerText.split(' ')[2];
    const existingBox = row.querySelector('.donation-box-front');
    const rowId = existingBox ? existingBox.getAttribute('data-row') : '';

    const amount = parseInt(globalRowLabel.innerText.split('$')[1].replace(/[^0-9.-]+/g, ''));
    let boxesToAdd = newNumBoxes - currentNumBoxes;
    let currentRow = row;

    while (boxesToAdd > 0) {
        const boxesInThisRow = currentRow.querySelectorAll('.donation-box').length;
        const boxesSpaceInThisRow = 8 - boxesInThisRow; // calculate the space left in the current row
        const boxesToAppend = Math.min(boxesSpaceInThisRow, boxesToAdd); // Decide how many boxes we can append to the current row

        for (let i = 0; i < boxesToAppend; i++) {
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
            currentRow.appendChild(box);
        }

        boxesToAdd -= boxesToAppend; // Reduce the number of boxes left to append

        if (boxesToAdd > 0) {
            // Create a new row without a label and append it below the current row
            const newRow = document.createElement('div');
            newRow.className = row.className; // Assuming the row has a specific class to copy
            currentRow.parentNode.insertBefore(newRow, currentRow.nextSibling);
            currentRow = newRow;
			closeModal();
        }
    }
    // Logic for removing boxes, unchanged from the previous version
    if (newNumBoxes < currentNumBoxes) {
        const boxesToRemove = currentNumBoxes - newNumBoxes;
        const boxes = row.querySelectorAll('.donation-box');

        for (let i = 0; i < boxesToRemove; i++) {
            row.removeChild(boxes[boxes.length - 1 - i]);
        }
    }

    updateTotalDonations();
    closeModal();
}

function updateTotalDonations() {
    const totalDonationsLabel = document.querySelector('.donation-row-label b');
    const totalDonations = calculateTotalDonations();
    totalDonationsLabel.innerText = 'Total: $' + numberWithCommas(totalDonations);
}


function addNewRow(amount, numBoxes, position, referenceElement) {
    if (numBoxes > 8) {
		document.getElementById('addNewModuleModal').style.display = "none";
		const modal = document.getElementById('errorModule');
    	modal.style.display = "block";
        return;
    }
	if (numBoxes < 1) {
		document.getElementById('addNewModuleModal').style.display = "none";
		const modal = document.getElementById('errorModule3');
    	modal.style.display = "block";
        return;
    }

	const row = globalRowLabel.parentElement;
    const newRow = document.createElement('div');
    newRow.className = 'donation-row';
	
const newRowLabel = document.createElement('div');
newRowLabel.className = 'donation-row-label';
newRowLabel.innerText = numBoxes + ' x $' + amount;

// Assign an anonymous function to onclick
newRowLabel.onclick = function() {
  editNumBoxes(this); // Pass the 'this' context to editNumBoxes
};

newRow.appendChild(newRowLabel);



    for (let i = 0; i < numBoxes; i++) {
        const box = document.createElement('div');
        box.className = 'donation-box';
        box.setAttribute('data-amount', amount);

        const boxInner = document.createElement('div');
        boxInner.className = 'donation-box-inner';

        const boxFront = document.createElement('div');
        boxFront.className = 'donation-box-front';
		
        const boxBack = document.createElement('div');
        boxBack.className = 'donation-box-back';

        boxInner.appendChild(boxFront);
        boxInner.appendChild(boxBack);
        box.appendChild(boxInner);
        newRow.appendChild(box);
    }

    if (position === 'above') {
        referenceElement.parentNode.insertBefore(newRow, referenceElement);
    } else {
        if (referenceElement.nextSibling) {
            referenceElement.parentNode.insertBefore(newRow, referenceElement.nextSibling);
        } else {
            referenceElement.parentNode.appendChild(newRow);
        }
    }
	// Update the total donations amount
  const totalDonationsLabel = document.querySelector('.donation-row-label b');
  const totalDonations = calculateTotalDonations(); // Function to calculate the total donations
  totalDonationsLabel.innerText = 'Total: $' + numberWithCommas(totalDonations); // Helper function to add commas to the number
}
		
function openAddNewModule() {
	closeModal();
    const modal = document.getElementById('addNewModuleModal');
    modal.style.display = "block";

    const aboveBtn = modal.querySelector('#aboveBtn');
    const belowBtn = modal.querySelector('#belowBtn');
    const saveModuleBtn = modal.querySelector('#saveModuleBtn');

    aboveBtn.onclick = function () {
        setButtonHighlighted(aboveBtn);
        setButtonUnhighlighted(belowBtn);
        modal.setAttribute('data-position', 'above');
    };

    belowBtn.onclick = function () {
        setButtonHighlighted(belowBtn);
        setButtonUnhighlighted(aboveBtn);
        modal.setAttribute('data-position', 'below');
    };

    saveModuleBtn.onclick = function () {
        const newRowNumBoxes = document.getElementById('numberBoxesInput').value;
        const newRowDonationAmount = document.getElementById('donationAmountInput').value;

        if (newRowDonationAmount && newRowNumBoxes) {
            const donationAmount = parseFloat(newRowDonationAmount.replace(/[^0-9.]/g, ''));
            const positionChoice = modal.getAttribute('data-position');
            addNewRow(numberWithCommas(donationAmount), parseInt(newRowNumBoxes), positionChoice, globalRowLabel.parentElement);
        }
        
        document.getElementById('addNewModuleModal').style.display = "none";
    };
}

function setButtonHighlighted(button) {
    button.style.backgroundColor = "#F78D2D";
    button.style.color = "black";
}

function setButtonUnhighlighted(button) {
    button.style.backgroundColor = "";
    button.style.color = "";
}

function closeModal() {
    document.getElementById('customModal').style.display = "none";
}
		
function closeNewModal() {
    document.getElementById('addNewModuleModal').style.display = "none";
}
		
function closeErrorOpenModal() {
	document.getElementById('errorModule').style.display = "none";
    document.getElementById('addNewModuleModal').style.display = "block";
}
		
function closeErrorOpenModal1() {
	document.getElementById('errorModule1').style.display = "none";
    document.getElementById('customModal').style.display = "block";
}
	
function closeErrorOpenModal2() {
	document.getElementById('errorModule2').style.display = "none";
    document.getElementById('customModal').style.display = "block";
}
		
function closeErrorOpenModal3() {
	document.getElementById('errorModule3').style.display = "none";
    document.getElementById('addNewModuleModal').style.display = "block";
}

function cancelDeleteRow() {
	document.getElementById('errorModule4').style.display = "none";
    document.getElementById('customModal').style.display = "block";
}
		
function closeNewMod(){
	const row = globalRowLabel.parentElement;
	row.remove();
	document.getElementById('errorModule4').style.display = "none";
    updateTotalDonations();
    closeModal();
}

function closeAlert() {
	document.getElementById('alertModule').style.display = "none";
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
		
// JavaScript function to open the settings menu
function openSettingsMenu() {
  const popupContainer = document.getElementById("settingsPopup");
  popupContainer.style.display = "block";
  updateSettings(); // Update the checkboxes based on the current settings
}

// JavaScript function to close the settings menu
function closeSettingsMenu() {
  const popupContainer = document.getElementById("settingsPopup");
  popupContainer.style.display = "none"; // Simply set the display to "none"
}

// JavaScript function to toggle the settings menu
function toggleSettingsMenu() {
  const popupContainer = document.getElementById("settingsPopup");
  if (popupContainer.style.display === "none" || popupContainer.style.display === "") {
    openSettingsMenu();
  } else {
    closeSettingsMenu();
  }
}

// Function to update the checkboxes based on the current settings
function updateSettings() {
  const generalSettings = {
    setting1: document.getElementById('setting1').checked,
    setting2: document.getElementById('setting2').checked,
    // Add more General settings here if needed
  };

  // Update the checkboxes based on the saved settings
  document.getElementById('setting1').checked = generalSettings.setting1;
  document.getElementById('setting2').checked = generalSettings.setting2;
  // Update other checkboxes as needed
}

function saveSettings() {
  const generalSettings = {
    setting1: document.getElementById('setting1').checked,
    setting2: document.getElementById('setting2').checked,
    // Add more General settings here if needed
  };

  const campaignSettings = {
    setting3: document.getElementById('setting3').checked,
    setting4: document.getElementById('setting4').value,
    // Add more Campaign settings here if needed
  };

  const appearanceSettings = {
    setting5: document.getElementById('setting5').checked,
    setting6: document.getElementById('setting6').checked,
    // Add more Appearance settings here if needed
  };

  // Implement the setting1 (Read-Only Mode)
  if (generalSettings.setting1) {
    // Disable all buttons except for the tabbed menu tab buttons and the logout button
    const allButtons = document.querySelectorAll('button');
    allButtons.forEach(button => {
      // Check if the button is not part of the tabbed menu and not the logout button
      if (!button.closest('.tabbed-menu') && !button.closest('.logout-button')) {
        button.disabled = true;
      }
    });
  } else {
    // Enable all buttons if setting1 is not checked
    const allButtons = document.querySelectorAll('button');
    allButtons.forEach(button => {
      button.disabled = false;
    });
  }
	
  // Mock implementation: Save the settings to a database or apply them to the application
  console.log('Settings saved.');

  // Close the settings menu after saving
  closeSettingsMenu();
}


// You may also want to close the settings menu if the user clicks outside of it
document.addEventListener("click", function (event) {
  const popupContainer = document.getElementById("settingsPopup");
  const settingsButton = document.querySelector(".settings-button");
  
  // Check if the clicked element is not within the settings button or settings popup
  if (!event.target.closest(".settings-button") && !event.target.closest(".settings-popup")) {
    closeSettingsMenu(); // Close the settings menu
  }
});

// Attach the event listeners for the close and save buttons
document.addEventListener("DOMContentLoaded", function () {
  const closeButton = document.getElementById("closeButton");
  if (closeButton) {
    closeButton.addEventListener("click", closeSettingsMenu);
  }

  const saveButton = document.getElementById("saveButton");
  if (saveButton) {
    saveButton.addEventListener("click", saveSettings);
  }
});
		
// Function to remove donation boxes
function removeDonationBoxes(donationAmount) {
  let remainingAmount = donationAmount;

  // Select all donation rows
  let rows = Array.from(document.querySelectorAll('.donation-row'));

  // Sort rows based on donation amount in descending order
  rows.sort((a, b) => {
    const aAmount = parseInt(a.querySelector('.donation-box').getAttribute('data-amount'));
    const bAmount = parseInt(b.querySelector('.donation-box').getAttribute('data-amount'));
    return bAmount - aAmount;
  });

  // Start from the highest donation amount row and move downwards
  for (let i = 0; i < rows.length && remainingAmount > 0; i++) {
    const row = rows[i];
    let rowBoxes = Array.from(row.querySelectorAll('.donation-box'));

    // Filter boxes to consider only empty boxes
    rowBoxes = rowBoxes.filter(box => box.querySelector('.donation-box-front').innerText.trim() === '');

    // If there are no empty boxes in this row, continue to the next row
    if (rowBoxes.length === 0) {
      continue;
    }

    const rowBoxAmount = parseInt(rowBoxes[0].getAttribute('data-amount')); // Assuming all boxes in a row have same donation amount

    // Remove box if there's enough remaining amount
    if (remainingAmount >= rowBoxAmount) {
      rowBoxes[rowBoxes.length - 1].remove(); // Remove the last box
      remainingAmount -= rowBoxAmount;

      // Update row label
      const rowLabel = row.querySelector('.donation-row-label');
      const numBoxes = parseInt(rowLabel.innerText.split(' ')[0]);
      rowLabel.innerText = (numBoxes - 1) + ' x ' + rowLabel.innerText.split(' ')[2];
    }
  }

  return remainingAmount; // If it's not zero, there wasn't enough boxes to remove
}
	
    const slice1Value = <?php echo $field1name; ?>;
    const slice2Value = <?php echo $field2name; ?>;
    const slice3Value = <?php echo $field3name; ?>;
    var totalBudget = <?php echo $goal; ?>;

    var slice1Amount = <?php echo $field1amount; ?>;
    var slice2Amount = <?php echo $field2amount; ?>;
    var slice3Amount = <?php echo $field3amount; ?>;

    var slice1Proportion = (slice1Amount / totalBudget);
    var slice2Proportion = (slice2Amount / totalBudget);
    var slice3Proportion = (slice3Amount / totalBudget);


    // Update pie chart slices
    createSlice("endowmentSlice", "endowmentText", "#00758D", 0, slice1Proportion, slice1Value);
    createSlice("capitalSlice", "capitalText", "#7866A1", slice1Proportion, slice1Proportion + slice2Proportion, slice2Value);
    createSlice("operatingSlice", "operatingText", "#FF8C00", slice1Proportion + slice2Proportion, slice1Proportion + slice2Proportion + slice3Proportion, slice3Value);

    // Function to create a path description for a pie chart slice
    function createSlice(sliceId, textId, fillColor, startProportion, endProportion, sliceName, sliceValue) {
        var radius = 18;
        var centerX = 21;
        var centerY = 21;

        var startAngle = startProportion * 360;
        var endAngle = endProportion * 360;

        var startRad = (startAngle - 90) * Math.PI / 180;
        var endRad = (endAngle - 90) * Math.PI / 180;

        var largeArcFlag = endAngle - startAngle <= 180 ? 0 : 1;

        var startX = centerX + radius * Math.cos(startRad);
        var startY = centerY + radius * Math.sin(startRad);

        var endX = centerX + radius * Math.cos(endRad);
        var endY = centerY + radius * Math.sin(endRad);

        var pathData = [
            "M", centerX, centerY,
            "L", startX, startY,
            "A", radius, radius, 0, largeArcFlag, 1, endX, endY,
            "Z"
        ].join(" ");

        // Update slice path and text
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById(sliceId).setAttribute("d", pathData);
            document.getElementById(sliceId).setAttribute("fill", fillColor);
        });
    }
		
	</script>
	<?php
	
// Define the HTML and CSS output
$output = '
<style>

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    padding-top: 10%;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border-radius: 8px; /* Added border-radius for rounded corners */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Subtle shadow for a popup effect */
    border: 1px solid #e5e5e5; /* Lighter border color */
    width: 300px;
    text-align: center;
}

/* New button styles */
.modal-content button {
    display: inline-block;
    padding: 7px 12px; /* Smaller padding for smaller buttons */
    margin: 7px; /* Space between buttons */
	margin-top: 7px; /* Space between buttons */
    background-color: #00758D; /* Example color: Adjust as per theme */
    border: none;
    border-radius: 7px; /* Rounded corners */
    color: #fff;
    cursor: pointer;
    font-size: 14px; /* Font size adjust if needed */
    transition: background-color 0.3s ease; /* Smooth color transition */
}

.modal-content button:hover {
    background-color: #F78D2D; /* Darker shade on hover */
}

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
  background-color: #f1f1f1; /* Light gray background */
  width: 350px; /* Adjust the width as needed */
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15); /* Slightly stronger shadow */
  z-index: 10000; /* Make sure the popup is on top of other elements */
}

.settings-popup h2 {
  margin: 0 0 16px; /* Add some bottom margin to the heading */
  font-size: 24px; /* Larger heading font size */
  text-align: center; /* Center the heading text */
}

.settings-popup h3 {
  margin: 20px 5px 20px; /* Add some margin around the section headings */
  padding-bottom: 10px; /* Add some space below the h3 element */
  font-size: 18px; /* Slightly larger section headings */
  font-weight: bold;
  border-bottom: 1px solid #000; /* Thin black line at the bottom of each h3 */
}

.settings-popup .setting {
  display: flex;
  align-items: center;
  margin-bottom: 17px; /* Adjust spacing between settings */
}

.settings-popup label {
  margin-left: 8px; /* Add some space between the label and the toggle */
}

.settings-popup button {
  margin-top: 15px; /* Increase top margin for better spacing */
  display: block; /* Make the button a block element */
  width: 100%; /* Make the button full-width */
  padding: 10px 16px; /* Add padding to the button */
  font-size: 16px; /* Larger button font size */
  background-color: #F78D2D;
  color: #fff; /* White button text color */
  border: none;
  border-radius: 12px;
  cursor: pointer;
}

.settings-popup button:hover {
  background-color: #00758D;
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

 .settings-popup input[type="number"] {
 background-color: #F8F8F8; /* Added background color to match the original style */
    width: 100px; /* Set the desired width for the input number fields */
	height: 25px;
    padding: 8px; /* Add some padding for better appearance */
    border: 1px solid #ccc; /* Add a border for a clear outline */
    border-radius: 5px; /* Add a slight border-radius for a rounded look */
    box-sizing: border-box; /* Include the padding and border within the specified width */
    font-size: 12px; /* Adjust the font size as needed */
	position: absolute;
	 right: 45px;
	margin-left: 10px;
  }
  
tfoot tr {
    background-color: #A7A7A7; /* Dark grey background */
    color: #fff; /* White text */
    font-size: 0.9em; /* Slightly smaller font size */
    font-style: italic; /* Italicize text */
    height: 17px; /* Set row height */
    overflow: hidden; /* This ensures that the background color respects the border radius */
}

tfoot td {
    padding: 2px 10px; /* Adjusted padding for better appearance within the 20px height */
    line-height: 16px; /* Adjusted line height to fit better within the given height */
}



</style>

<div class="settings-button" onclick="toggleSettingsMenu()">
  <img src="https://icon-library.com/images/white-gear-icon/white-gear-icon-6.jpg" alt="Settings" style="width: 20px; height: 20px; align-items: center;  justify-content: center; align-items: center;">
</div>

<div class="popup-container" id="settingsPopup">
  <div class="settings-popup">
    <h2>Settings</h2>
    
    <!-- General Section -->
    <h3>General</h3>
    <div class="setting">
      <input type="checkbox" id="setting1">
      <label for="setting1">Read-Only Mode</label>
    </div>
    <div class="setting">
      <input type="checkbox" id="setting2">
      <label for="setting2">Anonymous Mode</label>
    </div>
    <!-- Add more General settings here if needed -->
    
    <!-- Campaign Section -->
    <h3>Campaign</h3>
    <div class="setting">
      <label for="setting3">Edit Campaign Goal:</label>
      <input type="number" id="setting3" min="0">
    </div>
    <div class="setting">
      <label for="setting4">Edit Lead Gift:</label>
      <input type="number" id="setting4" min="0">
    </div>
    <!-- Add more Campaign settings here if needed -->
    
    <!-- Appearance Section -->
    <h3>Appearance</h3>
    <div class="setting">
      <input type="checkbox" id="setting5">
      <label for="setting5">High Contrast</label>
    </div>
    <div class="setting">
      <input type="checkbox" id="setting6">
      <label for="setting6">Large Text</label>
    </div>
    <!-- Add more Appearance settings here if needed -->

    <button id="closeButton">Close</button>
	<button id="saveButton">Save</button>
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
                    <tfoot>
                        <tr>
                            <td>Total:</td>
                            <td class="pledges-total"></td>
                            <td></td>
                        </tr>
                    </tfoot>
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
                    <tfoot>
                        <tr>
                            <td>Total:</td>
                            <td class="pending-total"></td>
                            <td></td>
                        </tr>
                    </tfoot>
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
                    <tfoot>
                        <tr>
                            <td>Total:</td>
                            <td class="pipeline-total"></td>
                            <td></td>
                        </tr>
                    </tfoot>
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
            <th>Amount</th>
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
  
<div id="customModal" class="modal">
  <div class="modal-content">
    <label>Enter the new number of boxes:</label>
    <input type="number" id="numBoxesInput" value="">
    <button onclick="saveChanges()">Save Changes</button>
    <button onclick="openAddNewModule()">Add New Row</button>
    <button onclick="closeModal()">Cancel</button>
  </div>
</div> 

<div id="addNewModuleModal" class="modal">
  <div class="modal-content">
    <label>Where would you like to add the new row?</label>
    <button id="aboveBtn">Above</button>
    <button id="belowBtn">Below</button>
    <label>New number of donation boxes: </label>
    <input type="number" id="numberBoxesInput" value="">
    <label>Donation amount: </label>
    <input type="text" id="donationAmountInput" value="">
    <button id="saveModuleBtn">Save New Row</button>
    <button onclick="closeNewModal()">Cancel</button>
  </div>
</div>


<div id="errorModule" class="modal">
  <div class="modal-content">
  <label>Error: Maximum number of boxes in a new row is 8.</label>
    <button onclick="closeErrorOpenModal()">Ok</button>
</div>  
</div>
  
<div id="errorModule1" class="modal">
  <div class="modal-content">
  <label>Error: Cannot reduce the number of boxes to less than the amount of boxes already filled.</label>
    <button onclick="closeErrorOpenModal1()">Ok</button>
  </div>
 </div>
 
<div id="errorModule2" class="modal">
  <div class="modal-content">
  <label>Error: Number of boxes must be greater than 0.</label>
    <button onclick="closeErrorOpenModal2()">Ok</button>
 </div>
 </div>
 
 <div id="errorModule3" class="modal">
  <div class="modal-content">
  <label>Error: Number of boxes must be greater than 0.</label>
    <button onclick="closeErrorOpenModal3()">Ok</button>
 </div>
 </div>
  
  
<div id="errorModule4" class="modal">
  <div class="modal-content">
  <label>Are you sure you would like to delete this row?</label>
    <button onclick="cancelDeleteRow()">Cancel</button>
	<button onclick="closeNewMod()">Ok</button>
  </div>
</div>

<div id="alertModule" class="modal">
  <div class="modal-content">
  <label>Donator is not saved. Please manually add a new box to the row and re-save the donator.</label>
	<button onclick="closeAlert()">Ok</button>
  </div>
</div>

<div id="alertModule1" class="modal">
  <div class="modal-content">
    <button data-action="cancel">Cancel</button>
    <button data-action="ok">Ok</button>
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
