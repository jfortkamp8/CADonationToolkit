function donation_toolkit_shortcode($atts) {
	wp_enqueue_script( 'js-pdf', 'https://cdn.jsdelivr.net/npm/jspdf@latest/dist/jspdf.min.js', array(), 'latest', true );
	wp_enqueue_script( 'html2pdf', 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js', array(), 'latest', true );	
	if( !is_admin() ) {
    	echo '<script type="text/javascript">
            var ajaxurl = "' . admin_url('admin-ajax.php') . '";
            var pyramidNonce = "' . wp_create_nonce('pyramid_nonce') . '";
        </script>';
	}
	wp_enqueue_script('jquery');

	$campaign_slug = isset($atts['id']) ? sanitize_text_field($atts['id']) : '';
	$campaign = get_page_by_path($campaign_slug, OBJECT, 'campaigns');
	if (!$campaign) {
		return 'Campaign not found.';
	} 
	$campaign_id = $campaign->ID;
	$goal = floatval(str_replace(array('$', ','), '', get_field('donation_goal', $campaign_id)));
	
	$imgurl = get_field('client_logo', $campaign_id);

	$campaign_name = get_field('campaign_name', $campaign_id);
	
	$slice1 = get_field('slice_1_name', $campaign_id);
	$slice2 = get_field('slice_2_name', $campaign_id);
	$slice3 = get_field('slice_3_name', $campaign_id);
	
    $field1name = "'" . $slice1 . "'";
	$field2name = "'" . $slice2 . "'";
	$field3name = "'" . $slice3 . "'";

	$field1amount = preg_replace("/[^0-9]/", "", get_field('slice_1_amount', $campaign_id));
	$field2amount = preg_replace("/[^0-9]/", "", get_field('slice_2_amount', $campaign_id));
	$field3amount = preg_replace("/[^0-9]/", "", get_field('slice_3_amount', $campaign_id));

    if (filter_var($imgurl, FILTER_VALIDATE_URL) === FALSE)
    {
      $imgurl = wp_get_attachment_url($imgurl);
    }

	ob_start();
	createDonationPyramid($goal);
	$donation_pyramid = ob_get_clean();

	?>
	<script>


	// Global 
	let donors = [];
	let globalRowLabel;
	let globalDisplayName = '';
	let isEditing = false;
	let pollingEnabled = true;
	const rowsType = document.querySelectorAll('#moves-management table tbody tr');
		

document.addEventListener('DOMContentLoaded', function() {
    activateTab();
    
    // Function to update active tab and content
    function updateActiveTab(tabContentId) {
        // Remove active class from all tab links
        const tabLinks = document.querySelectorAll('.tabbed-menu .tab-link');
        tabLinks.forEach(link => link.classList.remove('active'));

        // Add active class to the current tab link
        const activeTabLink = document.querySelector(`.tabbed-menu .tab-link[data-tab="${tabContentId}"]`);
        if (activeTabLink) {
            activeTabLink.classList.add('active');
        }

        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tabbed-content .tab');
        tabContents.forEach(content => content.classList.remove('active'));

        // Show the current tab content
        const activeTabContent = document.getElementById(tabContentId);
        if (activeTabContent) {
            activeTabContent.classList.add('active');
        }
    }

    // Event listeners for navigation buttons
    const navButtons = document.querySelectorAll('.navigation-button');
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Hide the overlay
            const overlay = document.getElementById('navigation-overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }

            // Update the active tab based on the clicked button
            updateActiveTab(this.dataset.tab);
        });
    });
});
		
function flipBox(){
document.body.addEventListener('click', function(event) {
    let clickedBox = event.target.closest('.donation-box');
    
    if (clickedBox) {
        let boxFront = clickedBox.querySelector('.donation-box-front');
        let boxBack = clickedBox.querySelector('.donation-box-back');

        if (boxFront && boxBack) {
            // Check which side (front or back) is currently displayed
            if (getComputedStyle(boxFront).display !== 'none') {
                boxFront.style.display = 'none';
                boxBack.style.display = 'flex';
            } else {
                boxFront.style.display = 'flex';
                boxBack.style.display = 'none';
            }
        }
    }
	});	
}
		
flipBox();
		


		
		// This dictionary will store the removal dates indexed by displayName
let removalDates = {};
	var clientLogoURL = '<?php echo $imgurl; ?>';
		
const cleanAmount = (amount) => {
  return parseFloat(amount.replace(/[$,]/g, ""));
}	
	

		
	function makeDarker(color, factor) {
  const r = Math.max(0, parseInt(color.substring(1, 3), 16) - factor);
  const g = Math.max(0, parseInt(color.substring(3, 5), 16) - factor);
  const b = Math.max(0, parseInt(color.substring(5, 7), 16) - factor);

  return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}
	function numberWithCommas(number) {
  const formattedNumber = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  if (!formattedNumber.startsWith('$')) {
    return '$' + formattedNumber;
  }
  return formattedNumber;
}

	

function activateTab(){
    const tabLinks = document.querySelectorAll('.tabbed-menu .tab-link');
    const tabContents = document.querySelectorAll('.tabbed-content .tab');

    tabLinks.forEach(tabLink => {
        tabLink.addEventListener('click', function() {
            // Check if editing is in progress and the clicked tab isn't Moves Management
            if (isEditing && this.dataset.tab !== 'moves-management') {
                showAlertTab(); // Display the custom modal
                return; // Do not proceed with changing tabs
            }
            
            // Remove active class from all tab links
            tabLinks.forEach(link => {
                link.classList.remove('active');
            });

            // Add active class to clicked tab link
            this.classList.add('active');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
            });

            // Show the tab content corresponding to the clicked tab link
            const tabContentId = this.dataset.tab;
            const tabContent = document.getElementById(tabContentId);
            tabContent.classList.add('active');
        });
    });
}

function showAlertTab() {
    const alertModule = document.getElementById('alertModuleTab');
    alertModule.style.display = 'block'; // Display the modal
}

function closeAlertTab() {
    const alertModule = document.getElementById('alertModuleTab');
    alertModule.style.display = 'none'; // Hide the modal
}

document.addEventListener('DOMContentLoaded', activateTab);

		
	//ADD CHECKBOX
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
		
function resetRows() {
    let donationRows = document.querySelectorAll('.donation-row');
    
    donationRows.forEach((row) => {
        // If the row has no child elements
        if (!row.hasChildNodes()) {
            row.style.marginTop = '0px';
        } else {
            row.style.marginTop = '7px';
        }
    });
}

		
	function rowSpace(){
let donationRows = document.querySelectorAll('.donation-row');
    donationRows.forEach((row, index) => {
        if (index < donationRows.length - 1) {
            let currentBox = row.querySelector('.donation-box');
            let nextBox = donationRows[index + 1].querySelector('.donation-box');

            // Check if both boxes exist
            if (currentBox && nextBox) {
                let currentAmount = currentBox.getAttribute('data-amount');
                let nextAmount = nextBox.getAttribute('data-amount');

                if (currentAmount && nextAmount && currentAmount === nextAmount) {
                    donationRows[index + 1].style.marginTop = '-5px';
                }
            } 
        }
    });
	}

			function addRow() {
		pollingEnabled = false; // Disable polling temporarily

		isEditing = true;
		 // Generate a unique identifier using timestamp and random number
    	const uniqueId = Math.floor(Date.now() / 10000).toString() + Math.floor(Math.random() * 10).toString();


  		const tabLinks = document.querySelectorAll('.tabbed-menu .tab-link');
  		const tabContents = document.querySelectorAll('.tabbed-content .tab');
  		tabLinks.forEach(link => {
      		link.classList.remove('active');
    	});
		
    	  // Add 'active' class to "moves-management" tab link
    const movesManagementTabLink = document.querySelector('li.tab-link[data-tab="moves-management"]');
    if (movesManagementTabLink) {
        movesManagementTabLink.classList.add('active');
    } else {
        console.error('Moves Management tab link not found');
    }


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
		// Set the UUID as a data attribute on the row
    	newRow.setAttribute('donor-id', uniqueId);
		
  		const cells = Array.from({ length: 11 }, () => document.createElement("td"));
  		// Add dropdown for pledge or pending column
  		const pledgePendingSelect = document.createElement("select");
		pledgePendingSelect.className = "donation-status-select";
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
  		cells[0].width = '10%';
  		cells[1].width = '10%';

		cells[2].width = '10%';
    cells[3].width = '8%';
    cells[4].width = '8%';
    cells[5].width = '18%';
    cells[6].width = '15%';
    cells[7].width = '15%';
	cells[8].width = '9%';
    cells[1].style.verticalAlign = 'middle';
    cells[2].style.verticalAlign = 'middle';
    cells[3].style.verticalAlign = 'middle';
    cells[4].style.verticalAlign = 'middle';
    cells[5].style.verticalAlign = 'middle';
    cells[6].style.verticalAlign = 'middle';
    cells[7].style.verticalAlign = 'middle';
	cells[8].style.verticalAlign = 'middle';
		
		const docIndex = Array.from(cells).indexOf(cells[9]);
const inputs = cells.slice(2, 10).map(cell => {
    const textarea = document.createElement("textarea");
    textarea.style.fontSize = "11px";
    textarea.style.width = "100%";
    textarea.style.boxSizing = "border-box";
    textarea.style.overflowY = "hidden";
    textarea.style.resize = "none";
    textarea.style.verticalAlign = "middle"; // Align textareas vertically
    textarea.style.padding = "5px"; // Minimal padding for better readability
    textarea.style.margin = "0"; // No margin to align with the table cell
    textarea.style.border = "1px solid #000000"; // Add a border for better visibility
    textarea.style.borderRadius = "4px"; // Rounded corners for a modern look
    textarea.style.lineHeight = "1.1"; // Adjust line height for better readability
	textarea.style.minHeight = "40px"; // Set a minimum height for the text area

    // Function to resize textarea based on content
    const resizeTextarea = (el) => {
        el.style.height = 'auto'; // Reset height
        el.style.height = el.scrollHeight + 'px'; // Set new height based on scrollHeight
    };

    // Resize textarea when the content changes
    textarea.addEventListener('input', (e) => {
        resizeTextarea(e.target);
    });

    // Initial resize to fit content if any
    resizeTextarea(textarea);
	cell.style.padding = "3px"; // Remove padding from the table cell
    cell.appendChild(textarea);
    return textarea;
});

 
		
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
	 
		
		let displayName = '';
		
// Function to show a custom alert
function showAlertMM(message) {
  const alertModule = document.getElementById("alertModuleMM");
  const alertMessage = document.getElementById("alertMessageMM");
  alertMessage.innerText = message;
  alertModule.style.display = "block";
}
		
		

// Function to validate the form
function validateForm() {
	if (inputs[0].value.trim() === "" && inputs[2].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Full Name, Amount field, and a Checkbox selection are required.");
    return false;
	} else if (inputs[0].value.trim() === "" && inputs[2].value.trim() === "") {
    showAlertMM("Full Name and Amount fields are required.");
    return false;
	} else if (inputs[0].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Full Name and a Checkbox selection are required.");
    return false;
  } else if (inputs[0].value.trim() === "" && inputs[2].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Full Name and Amount field with a Checkbox selection is required.");
    return false;
  } else if ( inputs[2].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Amount field and a Checkbox selection is required.");
    return false;
  } else if (inputs[0].value.trim() === "") {
    showAlertMM("Full Name field is required.");
    return false;
  } else if (inputs[0].value.trim() === "" && inputs[2].value.trim() === "") {
    showAlertMM("Full Name and Amount fields are required.");
    return false;
  } else if ( inputs[2].value.trim() === "") {
    showAlertMM("Amount field is required.");
    return false;
	  } else if (inputs[0].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Full Name field and a Checkbox selection is required.");
    return false;
  } else if ((!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("A Checkbox selection is required.");
    return false;
  } else if (inputs[2].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Amount field and a Checkbox selection is required.");
    return false;
  } else if (inputs[0].value.trim() === "") {
    showAlertMM("Full Name field is required.");
    return false;
  } else if (inputs[2].value.trim() === "") {
    showAlertMM("Amount field is required.");
    return false;
  } else if (!fullNameCheckbox.checked && !orgNameCheckbox.checked) {
    showAlertMM("Please select at least one checkbox.");
    return false;
  }
  return true;
}

// Add column to attach files to donation
const attachFiles = document.createElement("td");
attachFiles.style.textAlign = 'center';
attachFiles.style.verticalAlign = 'middle';
attachFiles.width = '10%';

// Create a container for the attach and download buttons
const buttonContainer = document.createElement("div");
buttonContainer.className = 'attach-download-container';

// Attach button
const attachButton = document.createElement("button");
attachButton.className = "attach-button";
attachButton.style.backgroundColor = "lightgrey";
attachButton.style.border = "none";
attachButton.innerHTML = '<img src="https://cdn-icons-png.flaticon.com/512/6583/6583130.png" alt="Attach files">';
buttonContainer.appendChild(attachButton);

// Download button
const downloadButton = document.createElement("button");
downloadButton.className = "download-button";
downloadButton.style.backgroundColor = "lightgrey";
downloadButton.style.border = "none";
downloadButton.innerHTML = '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/OOjs_UI_icon_download.svg/2048px-OOjs_UI_icon_download.svg.png" alt="Download files">';
buttonContainer.appendChild(downloadButton);

// Append the container to the table cell
attachFiles.appendChild(buttonContainer);
cells[9] = attachFiles;

// Create an array to store selected files
const selectedFiles = [];

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
      selectedFiles.push(...files);
    }
  });
  fileInput.click();
});

downloadButton.addEventListener("click", function handleDownloadButtonClick() {
  if (selectedFiles.length > 0) {
    // Create a popup/modal to display the list of files
    const popup = document.createElement("div");
    popup.className = "popup";
    popup.style.position = "fixed";
    popup.style.top = "50%";
    popup.style.left = "50%";
    popup.style.transform = "translate(-50%, -50%)";
    popup.style.background = "white";
    popup.style.padding = "10px";
    popup.style.borderRadius = "10px";
    popup.style.boxShadow = "0 4px 8px 0 rgba(0, 0, 0, 0.2)";
    popup.style.zIndex = "9999";
    popup.style.textAlign = "center";

    const fileListContainer = document.createElement("div");
    fileListContainer.className = "file-list-container";

    selectedFiles.forEach((file, index) => {
      const fileItem = document.createElement("div");
fileItem.innerText = file.name;
fileItem.style.marginBottom = "8px"; // Vertical spacing between files
fileItem.style.backgroundColor = index % 2 === 0 ? "#f5f5f5" : "white"; // Alternating shades of grey and white

const fileIcon = document.createElement("img");

const downloadLink = document.createElement("a");
downloadLink.href = URL.createObjectURL(file);
downloadLink.download = file.name;

const downloadIcon = document.createElement("img");
downloadIcon.src = "https://imgur.com/Eh4o70Y";
downloadIcon.alt = "Download";
downloadIcon.style.width = "20px";
downloadIcon.style.height = "20px";
downloadIcon.style.marginLeft = "5px"; // Spacing between download icon and file name

downloadIcon.addEventListener("click", () => {
  // Change the color of the file name to #7866A1
  fileItem.style.color = "#7866A1";
  fileItem.style.textDecoration = "underline";
});

downloadLink.appendChild(downloadIcon);

fileItem.appendChild(downloadLink);
fileListContainer.appendChild(fileItem);

    });

    const closeButton = document.createElement("button");
    closeButton.innerText = "Close";
    closeButton.style.padding = "7.5px 12.5px"; // Smaller close button
    closeButton.style.marginTop = "10px"; // Space between files and close button
    closeButton.addEventListener("click", function handleCloseButtonClick() {
      document.body.removeChild(popup);
    });

    fileListContainer.appendChild(closeButton);
    popup.appendChild(fileListContainer);
    document.body.appendChild(popup);
  }
});

const saveButton = document.createElement("button");
saveButton.className = "save-button";
saveButton.innerText = "Save";
cells[10].appendChild(saveButton);
cells[10].style.textAlign = "center";
cells[10].style.verticalAlign = "middle";

cells.forEach(cell => newRow.appendChild(cell));
 
		
  	// Utility function to parse the donation amount from the cell text
function parseDonationAmount(text) {
    return parseFloat(text.replace(/\$|,/g, '').trim() || "0");
}

const tableBody = document.querySelector("#moves-management table tbody");

// Insert the new row at the top
tableBody.insertBefore(newRow, tableBody.firstChild);

		// Create a delete button and add it to each row
const deleteButton = document.createElement("div");
    deleteButton.className = "delete-button";

    const deleteImage = document.createElement("img");
    deleteImage.src = "https://freepngtransparent.com/wp-content/uploads/2023/03/X-Png-87.png";
    deleteButton.appendChild(deleteImage);

// Attach an event listener to the delete button
deleteButton.addEventListener("click", function handleDeleteButtonClick() {
    // Check the response from the confirm dialog
    if (confirm("Are you sure you would like to delete this donor?")) {
        isEditing = false;
		function getDonorIdFromRowDelete(row) {
    return row.getAttribute('donor-id');
}
        const donorId = getDonorIdFromRowDelete(newRow);

        // Assuming 'ajaxurl' is defined and points to the admin-ajax.php file
        var donorData = {
            action: 'delete_donor_info', // Adjusted to target the deletion action hook
            donor_id: donorId // Sending only the donor_id for deletion
        };

        fetch(ajaxurl, { 
            method: 'POST',
            credentials: 'same-origin', 
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            },
            body: new URLSearchParams(donorData).toString() 
        })
        .then(response => response.text()) // Assuming the server responds with text
        .then(responseText => {
            // Handle the server response here
        
            // If successful, remove the row from the table
            if (responseText.includes('successfully')) { // Adjust based on actual success message
                newRow.remove();
                this.remove(); // Remove the delete button/icon
            } else {
                // Handle error or unsuccessful deletion
                alert('Failed to delete the donor. Please try again.');
            }
        })
        .catch(error => console.error('Error:', error));
    
		
					
// Populate the totals for Pledges
const pledgesCells = Array.from(document.querySelectorAll(".pledges-table tbody td:nth-child(2)"));
const totalDonations = pledgesCells.reduce((acc, curr) => {
    const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
   return isNaN(amount) ? acc : acc + amount;
}, 0);

// Populate the totals for Pending
const pendingCells = Array.from(document.querySelectorAll(".pending-table tbody td:nth-child(2)"));
const pendingDonations = pendingCells.reduce((acc, curr) => {
    const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
return isNaN(amount) ? acc : acc + amount;
}, 0);

// Populate the totals for Pipeline
const pipelineCells = Array.from(document.querySelectorAll(".pipeline-table tbody td:nth-child(2)"));
const pipelineDonations = pipelineCells.reduce((acc, curr) => {
    const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
  return isNaN(amount) ? acc : acc + amount;
}, 0);

// Calculate and populate the combined total
const combinedTotal = totalDonations + pendingDonations + pipelineDonations;
const combinedTotalElement = document.querySelector(".combined-total-amount");
combinedTotalElement.innerText = formatCurrency(combinedTotal);
		
		//repop dashboard
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
	
const rowsType = document.querySelectorAll('#moves-management table tbody tr');
		
		// Initialize count variables for each donation type
let individualCount = 0;
let foundationCount = 0;
let corporationCount = 0;
let publicCount = 0;
let boardCount = 0;
let otherCount = 0;

// Iterate over each row and update the count variables
rowsType.forEach(row => {
  const donationTypeSelect = row.querySelector('.donation-type-select');
  const donationStatusSelect = row.querySelector('.donation-status-select'); // Select the donation status
  const donationTypeValue = donationTypeSelect.value;
  const donationStatusValue = donationStatusSelect.value; // Get the donation status value

  // Proceed only if the donation status is "pledged"
  if (donationStatusValue === "pledge") {
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
  }
});

			
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
<div id="pieChartPlaceholder" style="width: 80%; height: 80%;">
    <!-- Pie Chart Using SVG -->
    <svg width="150%" height="100%" viewBox="-30 -30 72 72">
        <path id="endowmentSlice1" d="" fill="#00758D"></path>
        <text id="endowmentTextName1" font-size="2" fill="#005D70"></text>
        <text id="endowmentTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
	

        <!-- Capital slice -->
        <path id="capitalSlice1" d="" fill="#7866A1"></path>
        <text id="capitalTextName1" font-size="2" fill="#635387"></text>
        <text id="capitalTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>


        <!-- Operating slice -->
        <path id="operatingSlice1" d="" fill="#FF8C00"></path>
        <text id="operatingTextName1" font-size="2" fill="#D17607"></text>
        <text id="operatingTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		
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
    
	// Update pie chart slices
			
createSlice1("endowmentSlice1", "endowmentTextName1", "endowmentTextAmount1", "#00758D", 0, slice1Proportion, slice1Value, slice1Amount);
	createSlice1("capitalSlice1", "capitalTextName1", "capitalTextAmount1", "#7866A1", slice1Proportion, slice1Proportion + slice2Proportion, 		slice2Value, slice2Amount);
	createSlice1("operatingSlice1", "operatingTextName1", "operatingTextAmount1", "#FF8C00", slice1Proportion + slice2Proportion, 1, slice3Value, slice3Amount);

		// Sort donorArray in descending order based on donation amount
    donors.sort((a, b) => b.amount - a.amount);

    // Get the donorContainer element
    const donorContainer = document.getElementById('donorContainer');

    // Populate the top 5 donors into the donorContainer
    for (let i = 0; i < 5 && i < donors.length; i++) {
        const donor = donors[i];
        const donorElement = document.createElement('div');
donorElement.innerHTML = `
    <div style="text-align: center; margin: 0 35px;">
        <div style="color: #00758D; font-weight: bold; font-size: 20px;">${donor.name}</div>
        <div style="font-size: 20px;"><span style="color: #00758D; font-weight: bold;">${numberWithCommas(Math.round(donor.amount))}</span></div>
    </div>`;
        donorContainer.appendChild(donorElement);
    }
    }
    // If the user clicked "No" or "Cancel", nothing will happen.
});
		
newRow.appendChild(deleteButton);

 

	/*	 need to fix when changing confirm to a prompt
const deleteConfirmButton = document.getElementById("deleteConfirmButton");
const deleteCancelButton = document.getElementById("deleteCancelButton");



deleteCancelButton.addEventListener("click", function handledeleteCancelClick() {
    const modal = document.getElementById('deleteModule');
    modal.style.display = "none"; // Hide the modal
}); 

*/

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

		
  		saveButton.addEventListener("click", function handleSaveButtonClick() {
				isEditing = false;
			if (validateForm()) { 
			
				function getDonorIdFromRow(row) {
    return row.getAttribute('donor-id');
}
				const donorId = getDonorIdFromRow(newRow);
				const campaignId = <?php echo $campaign_id; ?>;
				
				
var donorData = {
    action: 'insert_donor_info', // The WP action hook to target
    donor_id: donorId,
    campaign_id: campaignId, // Use the campaign ID set by the server-side script
    status: pledgePendingSelect.value, 
    type: donationTypeSelect.value, 
    full_name: inputs[0].value, 
    organization: inputs[1].value, 
    amount: inputs[2].value, 
    next_step: inputs[3].value, 
    recent_involvement: inputs[4].value,
    notes: inputs[5].value,
	lead: inputs[6].value,
    // TBD 'documents' need file hosting
};

// Convert donorData to a URL-encoded string
var encodedData = Object.keys(donorData).map(function(key) {
    return encodeURIComponent(key) + '=' + encodeURIComponent(donorData[key]);
}).join('&');

fetch(ajaxurl, { 
    method: 'POST',
    credentials: 'same-origin', 
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    },
    body: encodedData 
})
.then(response => response.text()) // Assuming the server responds with text
.then(responseText => {
    // Handle the server response here
    // Possibly update UI or alert the user of success
})
.catch(error => console.error('Error:', error));
				
		// Iterate over each row and update the count variables
		rowsType.forEach(row => {
  			const donationTypeSelect = row.querySelector('.donation-type-select');
			const donationStatusSelect = row.querySelector('.donation-status-select');
			
 			const donationTypeValue = donationTypeSelect.value;	
			const donationStatusValue = donationStatusSelect.value;
			
	
 
});				 
			
		deleteButton.remove();
     
    		const values = inputs.map(input => input.value);

    		cells.slice(2, 9).forEach((cell, index) => {
      			cell.innerHTML = values[index];
   			});
				
			
  
    		const editButton = document.createElement("button");
    		editButton.className = "edit-button";
    		editButton.innerText = "Edit";
    		cells[10].innerHTML = "";
    		cells[10].appendChild(editButton);
    		cells[10].style.textAlign = "center";
    		cells[10].style.verticalAlign = "middle";
		
					 // Sort rows based on the donation amount
Array.from(tableBody.rows)
    .sort((rowA, rowB) => {
        const donationA = parseDonationAmount(rowA.cells[4].innerText);
        const donationB = parseDonationAmount(rowB.cells[4].innerText);
        
        // Sorting in descending order (largest donation first)
        return donationB - donationA;
    })
    .forEach(row => tableBody.appendChild(row));
 
		
				
    		editButton.addEventListener("click", function handleEditButtonClick() { 
				isEditing = true;
		// Re-add the delete button after updating the row's content
        newRow.appendChild(deleteButton);
				
// Remove the donation from the donation pyramid
const pyramidRows = document.querySelectorAll('.donation-row');
pyramidRows.forEach(row => {
    const boxInner = row.querySelector('.donation-box-inner');
    const frontElement = row.querySelector('.donation-box-front');
    const backElement = row.querySelector('.donation-box-back');
 
    if (boxInner) {
      const boxDisplayName = frontElement.innerText.replace(/ /g, '');
const parseName = displayName.replace(/<br\s*\/?>/g, '').replace(/\s+/g, '');
        if (boxDisplayName === parseName) {
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
                backElement.style.backgroundColor = "#d4d4d4";
                backElement.style.color = '';
                backElement.style.fontWeight = '';
                backElement.style.textAlign = '';
                backElement.style.display = '';
                backElement.style.justifyContent = '';
            }
        }
    }
});
const parseName = displayName.replace(/<br\s*\/?>/g, '').replace(/\s+/g, '');
const formatCurrency = (amount) => {
    // You can format this as per your requirements
    return numberWithCommas(amount);
};
				 
const pledgesTotalElement = document.querySelector(".pledges-total");
const pendingTotalElement = document.querySelector(".pending-total");
const pipelineTotalElement = document.querySelector(".pipeline-total");
				
    			// Remove the donation from the pledges table
const pledgesTable = document.querySelector(".pledges-table tbody");
const pledgesRows = pledgesTable.querySelectorAll("tr");
pledgesRows.forEach(row => {
    const donationCell = row.querySelector("td:nth-child(1)");
    if (donationCell.innerHTML.replace(/ /g, '') === parseName) {
        const amountCell = row.querySelector("td:nth-child(2)");
        const amount = parseFloat(amountCell.innerText.replace(/[^\d.-]/g, ''));
        if (!isNaN(amount)) {
            const currentTotal = parseFloat(pledgesTotalElement.innerText.replace(/[^\d.-]/g, '')) || 0;
            pledgesTotalElement.innerText = formatCurrency(currentTotal - amount);
        }
		// Save the removal date for this displayName
        const dateCell = row.querySelector("td:nth-child(3)");
        removalDates[parseName] = dateCell.innerHTML;
        row.remove(); // Remove the row from the table
		
		 // Remove the donation from the array
        removeDonationFromArray(parseName);
    }
});			

// Remove the donation from the pending table
const pendingTable = document.querySelector(".pending-table tbody");
const pendingRows = pendingTable.querySelectorAll("tr");
pendingRows.forEach(row => {
    const donationCell = row.querySelector("td:nth-child(1)");
    if (donationCell.innerHTML.replace(/ /g, '') === parseName) {
        const amountCell = row.querySelector("td:nth-child(2)");
        const amount = parseFloat(amountCell.innerText.replace(/[^\d.-]/g, ''));
        if (!isNaN(amount)) {
            const currentTotal = parseFloat(pendingTotalElement.innerText.replace(/[^\d.-]/g, '')) || 0;
            pendingTotalElement.innerText = formatCurrency(currentTotal - amount);
        }
		const dateCell = row.querySelector("td:nth-child(3)");
        removalDates[parseName] = dateCell.innerHTML;
        row.remove(); // Remove the row from the table
    }
});

// Remove the donation from the pipeline table
const pipelineTable = document.querySelector(".pipeline-table tbody");
const pipelineRows = pipelineTable.querySelectorAll("tr");
pipelineRows.forEach(row => {
    const donationCell = row.querySelector("td:nth-child(1)");
    if (donationCell.innerHTML.replace(/ /g, '') === parseName) {
        const amountCell = row.querySelector("td:nth-child(2)");
        const amount = parseFloat(amountCell.innerText.replace(/[^\d.-]/g, ''));
        if (!isNaN(amount)) {
            const currentTotal = parseFloat(pipelineTotalElement.innerText.replace(/[^\d.-]/g, '')) || 0;
            pipelineTotalElement.innerText = formatCurrency(currentTotal - amount);
        }
		const dateCell = row.querySelector("td:nth-child(3)");
        removalDates[parseName] = dateCell.innerHTML;
        row.remove(); // Remove the row from the table
    }
});



				pledgePendingSelect.disabled = false;
				donationTypeSelect.disabled = false;

      			inputs.forEach((input, index) => {
  					if (index !== 7) {
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
		
// Set the initial state of the checkboxes based on current displayName
function setCheckboxState() {
    if (displayName === inputs[0].value) {
        fullNameCheckbox.checked = true;
        orgNameCheckbox.checked = false;
    } else if (displayName === inputs[1].value) {
        fullNameCheckbox.checked = false;
        orgNameCheckbox.checked = true;
    }
}

setCheckboxState();

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

// When the input values change, update the displayName accordingly
inputs[0].addEventListener("input", function() {
    if (fullNameCheckbox.checked) {
        displayName = inputs[0].value;
    }
});

inputs[1].addEventListener("input", function() {
    if (orgNameCheckbox.checked) {
        displayName = inputs[1].value;
    }
});
				
				
      			const saveButton = document.createElement("button");
      			saveButton.className = "save-button";
      			saveButton.innerText = "Save";
      			cells[10].innerHTML = "";
      			cells[10].appendChild(saveButton);
      			cells[10].style.textAlign = "center";
      			cells[10].style.verticalAlign = "middle";

      			saveButton.addEventListener("click", handleSaveButtonClick);
	
    		});	
		
	
				
    		pledgePendingSelect.disabled = true;
			donationTypeSelect.disabled = true;
			
  		// Set font size for saved values
  		cells.slice(2, 9).forEach(cell => {
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

		targetCells[0].innerHTML = displayName;
		targetCells[1].innerHTML = numberWithCommas(values[2]);
		// Check if there's a stored removal date for this displayName
const dateForDisplay = removalDates[displayName] || new Date().toLocaleDateString();
targetCells[2].innerHTML = dateForDisplay;

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
		
				// Utility function to parse and return the donation amount
function getDonationAmount(row) {
  return parseFloat(row.cells[1].innerText.replace(/[$,]/g, ''));
}

function insertRowInOrder(tableBody, newRow) {
  const newDonation = getDonationAmount(newRow);
  
  for (let i = 0; i < tableBody.rows.length; i++) {
    const row = tableBody.rows[i];
    const currentDonation = getDonationAmount(row);

    if (newDonation > currentDonation) {
      tableBody.insertBefore(newRow, row);
      return;
    }
  }

  // If loop completes without inserting, append at the end
  tableBody.appendChild(newRow);
}
							
				globalDisplayName = displayName;

				
const donationName = globalDisplayName;
const donationAmount = parseInt(values[2].replace(/[^0-9.-]+/g, ""));

// Create a new donor object and add to donors array
// Function to remove a donation from the array
function removeDonationFromArray(name) {
  donors = donors.filter(donor => donor.name !== name);
}
			
let donationColor = '';
switch (pledgePendingValue) {
  case 'pledge':
	let newDonor = {
  name: displayName,
  amount: donationAmount
};
donors.push(newDonor);
    donationColor = '#F78D2D';
    break;
  case 'identified':
    donationColor = '#5DABBC';
    break;
  case 'engaged':
    donationColor = '#00728A';
    break;
  case 'pending':
    donationColor = '#7866A1';
    break;
  default:
    return;
}

// Get the rows and their amounts from the DOM
const rows = document.querySelectorAll('.donation-row');
let donationRowAmounts = [];

document.querySelectorAll('.donation-row').forEach(row => {
    let box = row.querySelector('.donation-box');
    if (box) {
        let amount = parseInt(box.getAttribute('data-amount'));
        if (!isNaN(amount)) {
            donationRowAmounts.push(amount);
        }
    }
});


// Find the closest donation amount
const closestAmount = donationRowAmounts.reduce((prev, curr) => Math.abs(curr - donationAmount) < Math.abs(prev - donationAmount) ? curr : prev);

// Use closestAmount to get the closest row
const rowIndex = 'row' + closestAmount / 1000;

const boxes = document.querySelectorAll('.donation-box-front[data-row="' + rowIndex + '"]');

const boxesToFill = 1;


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
const box = boxes[emptyIndex];  // Assuming boxes[emptyIndex] is your .donation-box-front

box.style.backgroundColor = donationColor;
box.style.color = "#fff";
box.style.fontWeight = "500";
box.style.textAlign = "center";
box.style.display = "flex";
box.style.justifyContent = "center";
box.style.alignItems = "center";

const backOfBox = box.parentElement.querySelector('.donation-box-back');
backOfBox.innerHTML = numberWithCommas(donationAmount);
const darkerDonationColor = makeDarker(donationColor, 30); // The factor 30 can be adjusted
backOfBox.style.backgroundColor = darkerDonationColor;

backOfBox.style.color = "#fff";
backOfBox.style.fontWeight = "400";
backOfBox.style.fontSize = "17px";
backOfBox.style.textAlign = "center";
backOfBox.style.display = "none";  // Hide the back initially

const donationBox = box.closest('.donation-box');

// Assuming 'box' is the DOM element reference of your box
const computedStyle = window.getComputedStyle(box);

const boxWidth = parseFloat(computedStyle.width);
const boxHeight = parseFloat(computedStyle.height);

const span = document.createElement("span");
span.style.display = 'inline-block';
document.body.appendChild(span);

let fontSize = 20; // Start from a larger font size for better readability

const adjustFontSize = () => {
    while ((span.offsetHeight > boxHeight || span.offsetWidth > boxWidth) && fontSize > 10) { // Ensuring font size doesn't go below 10px for readability
        fontSize--;
        span.style.fontSize = fontSize + "px";
    }
}

// Initially set the displayName without breaks
span.innerHTML = displayName;
span.style.fontSize = fontSize + "px";
adjustFontSize();

// If the text still doesn't fit, start adding breaks between words to stack them.
const words = displayName.split(" ");
if (fontSize < 30) {  // Adjust this threshold as per your design
    for (let i = 1; i < words.length && (span.offsetHeight > boxHeight || span.offsetWidth > boxWidth); i++) {
        span.innerHTML = words.slice(0, i).join(" ") + "<br>" + words.slice(i).join(" ");
        adjustFontSize();
    }
    span.style.lineHeight = "0.9"; // Setting line-height to 0.8 (without units)
}

box.style.fontSize = fontSize + "px";
box.style.padding = "10px";
box.innerHTML = span.innerHTML;
box.style.lineHeight = span.style.lineHeight;

document.body.removeChild(span);


  const donationBoxAmount = parseInt(donationBox.getAttribute('data-amount'));
  const donationValue = donationAmount < donationBoxAmount ? donationAmount : donationBoxAmount;
  const donationLabel = document.createElement('div');
  donationLabel.className = 'donation-label';
  box.appendChild(donationLabel);
	
// Reset previously red rows if needed
    Array.from(tableBody.rows).forEach(function(row) {
        row.classList.remove("red-row");  // Remove the red-row class from all rows
    });
	// Remove the block on leaving the page after saving
    window.removeEventListener('beforeunload', blockPageUnload);

} else {
    const modal = document.getElementById('alertModule1');
    modal.style.display = "block";
	
    const confirmButton = document.getElementById('confirmButton');
    const cancelButton = document.getElementById('cancelButton');
	

	cancelButton.onclick = function() {
		modal.style.display = "none";
        const modal1 = document.getElementById('alertModule');
    	modal1.style.display = "block";
		// Block the user from leaving the page
    	window.addEventListener('beforeunload', blockPageUnload);
		newRow.classList.add("red-row"); 
    }
	
    confirmButton.onclick = function() {
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
        
        addDonationBox(row, rowIndex, donationName, donationAmount, donationColor);
		  savePyramidHTML();
      }
    }
        modal.style.display = "none";
		    	
		if (pledgePendingValue === "pending") {
  const tableBody = document.querySelector(".pending-table tbody");
  insertRowInOrder(tableBody, targetRow);
} else if (pledgePendingValue === "engaged" || pledgePendingValue === "identified") {
  const tableBody = document.querySelector(".pipeline-table tbody");
  insertRowInOrder(tableBody, targetRow);
} else if (pledgePendingValue === "pledge") {
  const tableBody = document.querySelector(".pledges-table tbody");
  insertRowInOrder(tableBody, targetRow);
}

const pledgesTotalElement = document.querySelector(".pledges-total");
const pendingTotalElement = document.querySelector(".pending-total");
const pipelineTotalElement = document.querySelector(".pipeline-total");

		const formatCurrency = (amount) => {
    // You can format this as per your requirements
    return numberWithCommas(amount);
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

		// Calculate and populate the combined total
const combinedTotal = totalDonations + pendingDonations + pipelineDonations;
const combinedTotalElement = document.querySelector(".combined-total-amount");
combinedTotalElement.innerText = formatCurrency(combinedTotal);
		
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
			
		localStorage.setItem('totalDonations', totalDonations);
localStorage.setItem('percent', percent);
	
const rowsType1 = document.querySelectorAll('#moves-management table tbody tr');
		// Initialize count variables for each donation type
let individualCount = 0;
let foundationCount = 0;
let corporationCount = 0;
let publicCount = 0;
let boardCount = 0;
let otherCount = 0;

// Iterate over each row and update the count variables
rowsType1.forEach(row => {
  const donationTypeSelect = row.querySelector('.donation-type-select');
  const donationStatusSelect = row.querySelector('.donation-status-select'); // Select the donation status
  const donationTypeValue = donationTypeSelect.value;
  const donationStatusValue = donationStatusSelect.value; // Get the donation status value

  // Proceed only if the donation status is "pledged"
  if (donationStatusValue === "pledge") {
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
  }
});

	
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
<div id="pieChartPlaceholder" style="width: 80%; height: 80%;">
    <!-- Pie Chart Using SVG -->
    <svg width="150%" height="100%" viewBox="-30 -30 72 72">
        <path id="endowmentSlice1" d="" fill="#00758D"></path>
        <text id="endowmentTextName1" font-size="2" fill="#005D70"></text>
        <text id="endowmentTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		
        <!-- Capital slice -->
        <path id="capitalSlice1" d="" fill="#7866A1"></path>
        <text id="capitalTextName1" font-size="2" fill="#635387"></text>
        <text id="capitalTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>

        <!-- Operating slice -->
        <path id="operatingSlice1" d="" fill="#FF8C00"></path>
        <text id="operatingTextName1" font-size="2" fill="#D17607"></text>
        <text id="operatingTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
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
			
			createSlice1("endowmentSlice1", "endowmentTextName1", "endowmentTextAmount1", "#00758D", 0, slice1Proportion, slice1Value, slice1Amount);
	createSlice1("capitalSlice1", "capitalTextName1", "capitalTextAmount1", "#7866A1", slice1Proportion, slice1Proportion + slice2Proportion, 		slice2Value, slice2Amount);
	createSlice1("operatingSlice1", "operatingTextName1", "operatingTextAmount1", "#FF8C00", slice1Proportion + slice2Proportion, 1, slice3Value, slice3Amount);

		// Sort donorArray in descending order based on donation amount
    donors.sort((a, b) => b.amount - a.amount);

    // Get the donorContainer element
    const donorContainer = document.getElementById('donorContainer');

    // Populate the top 5 donors into the donorContainer
    for (let i = 0; i < 5 && i < donors.length; i++) {
        const donor = donors[i];
        const donorElement = document.createElement('div');
donorElement.innerHTML = `
    <div style="text-align: center; margin: 0 35px;">
        <div style="color: #00758D; font-weight: bold; font-size: 20px;">${donor.name}</div>
        <div style="font-size: 20px;"><span style="color: #00758D; font-weight: bold;">${numberWithCommas(Math.round(donor.amount))}</span></div>
    </div>`;
        donorContainer.appendChild(donorElement);
    }
		
		}

	return;
}

    	if (pledgePendingValue === "pending") {
  const tableBody = document.querySelector(".pending-table tbody");
  insertRowInOrder(tableBody, targetRow);
} else if (pledgePendingValue === "engaged" || pledgePendingValue === "identified") {
  const tableBody = document.querySelector(".pipeline-table tbody");
  insertRowInOrder(tableBody, targetRow);
} else if (pledgePendingValue === "pledge") {
  const tableBody = document.querySelector(".pledges-table tbody");
  insertRowInOrder(tableBody, targetRow);
}
		// ... previous code ...

const pledgesTotalElement = document.querySelector(".pledges-total");
const pendingTotalElement = document.querySelector(".pending-total");
const pipelineTotalElement = document.querySelector(".pipeline-total");

const formatCurrency = (amount) => {
    // You can format this as per your requirements
    return numberWithCommas(amount);
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

// Calculate and populate the combined total
const combinedTotal = totalDonations + pendingDonations + pipelineDonations;
const combinedTotalElement = document.querySelector(".combined-total-amount");
combinedTotalElement.innerText = formatCurrency(combinedTotal);
		
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
	
const rowsType2 = document.querySelectorAll('#moves-management table tbody tr');
				
		// Initialize count variables for each donation type
let individualCount = 0;
let foundationCount = 0;
let corporationCount = 0;
let publicCount = 0;
let boardCount = 0;
let otherCount = 0;

// Iterate over each row and update the count variables
rowsType2.forEach(row => {
  const donationTypeSelect = row.querySelector('.donation-type-select');
  const donationStatusSelect = row.querySelector('.donation-status-select'); // Select the donation status
  const donationTypeValue = donationTypeSelect.value;
  const donationStatusValue = donationStatusSelect.value; // Get the donation status value

  // Proceed only if the donation status is "pledged"
  if (donationStatusValue === "pledge") {
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
  }
});

			
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
<div id="pieChartPlaceholder" style="width: 80%; height: 80%;">
    <!-- Pie Chart Using SVG -->
    <svg width="150%" height="100%" viewBox="-30 -30 72 72">
        <path id="endowmentSlice1" d="" fill="#00758D"></path>
        <text id="endowmentTextName1" font-size="2" fill="#005D70"></text>
        <text id="endowmentTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
	
        <!-- Capital slice -->
        <path id="capitalSlice1" d="" fill="#7866A1"></path>
        <text id="capitalTextName1" font-size="2" fill="#635387"></text>
        <text id="capitalTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		

        <!-- Operating slice -->
        <path id="operatingSlice1" d="" fill="#FF8C00"></path>
        <text id="operatingTextName1" font-size="2" fill="#D17607"></text>
        <text id="operatingTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
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
    
		createSlice1("endowmentSlice1", "endowmentTextName1", "endowmentTextAmount1", "#00758D", 0, slice1Proportion, slice1Value, slice1Amount);
	createSlice1("capitalSlice1", "capitalTextName1", "capitalTextAmount1", "#7866A1", slice1Proportion, slice1Proportion + slice2Proportion, 		slice2Value, slice2Amount);
	createSlice1("operatingSlice1", "operatingTextName1", "operatingTextAmount1", "#FF8C00", slice1Proportion + slice2Proportion, 1, slice3Value, slice3Amount);

		// Sort donorArray in descending order based on donation amount
    donors.sort((a, b) => b.amount - a.amount);

    // Get the donorContainer element
    const donorContainer = document.getElementById('donorContainer');

    // Populate the top 5 donors into the donorContainer
    for (let i = 0; i < 5 && i < donors.length; i++) {
        const donor = donors[i];
        const donorElement = document.createElement('div');
donorElement.innerHTML = `
    <div style="text-align: center; margin: 0 35px;">
        <div style="color: #00758D; font-weight: bold; font-size: 20px;">${donor.name}</div>
        <div style="font-size: 20px;"><span style="color: #00758D; font-weight: bold;">${numberWithCommas(Math.round(donor.amount))}</span></div>
    </div>`;
        donorContainer.appendChild(donorElement);
    }
		}
		}, 0);
		setTimeout(() => {
        pollingEnabled = true;
			
    }, 5000); // Adjust delay as needed
	}
		
document.addEventListener("DOMContentLoaded", function() {
	loadPyramidHTML().then(() => {
    const campaignId = <?php echo $campaign_id; ?>;
    let donors = [];

	
    // Select all donation rows
    let rows = document.querySelectorAll('.donation-row');

    // Loop through the first three rows (if they exist)
    for (let i = 0; i < 3 && i < rows.length; i++) {
        let boxes = rows[i].querySelectorAll('.donation-box');

        // Add the 'top-donor-box' class to each box in the row
        boxes.forEach(box => {
            box.classList.add('top-donor-box');
        });
	}

		
    // Fetch donors for the campaign and add them to the table
    fetchDonorsByCampaign(campaignId);

let displayedDonors = new Set(); // Track displayed donors by donor_id
let lastFetchedDonorIds = new Set(); // Store last fetched donor IDs

function fetchDonorsByCampaign(campaignId) {
    if (!pollingEnabled) return; // Skip fetching if polling is disabled

    var donorData = {
        action: 'fetch_donors_by_campaign',
        campaign_id: campaignId
    };

    fetch(ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
        },
        body: new URLSearchParams(donorData).toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const newDonorIds = new Set(data.data.map(donor => donor.donor_id));

            // Add new donors
            data.data.forEach(donor => {
                if (!displayedDonors.has(donor.donor_id)) {
                    displayedDonors.add(donor.donor_id);
                    addDonorToTable(donor);
                    updateDashboard(donor);
                    updateDonationPyramid(donor);
                }
            });

            // Remove donors if they are missing from the database after the last poll
            displayedDonors.forEach(donorId => {
                if (!newDonorIds.has(donorId)) {
                    const donor = { donor_id: donorId }; // Create a donor object with the missing donorId
                    removeDonorFromTabs(donor);
                    updateDashboardTotals();
                    displayedDonors.delete(donorId); // Remove the donor from the displayed donors set
                }
            });

            updateTopDonors(); // Ensure top donors are updated after all donors are processed
        } else {
            console.error(data.data);
        }
    })
    .catch(error => console.error('Error:', error));
}


// Function to start polling
function startPolling() {
	
    fetchDonorsByCampaign(campaignId);
    setTimeout(() => {
        if (pollingEnabled) {
            startPolling(); // Poll every 5 seconds if polling is enabled
        }
    }, 1000);
}

startPolling(); // Start polling on page load
function addDonorToTable(donor) {
    const newRow = document.createElement("tr");
    newRow.setAttribute('donor-id', donor.donor_id);

    const cells = Array.from({ length: 11 }, () => document.createElement("td"));

    const pledgePendingSelect = document.createElement("select");
    pledgePendingSelect.className = "donation-status-select";
    pledgePendingSelect.innerHTML = `
        <option value="pledge">Pledged</option>
        <option value="pending">Pending</option>
        <option value="engaged">Engaged</option>
        <option value="identified">Identified</option>
        <option value="denied">Declined</option>
    `;
    pledgePendingSelect.style.fontSize = "10px";
    pledgePendingSelect.value = donor.status;
    pledgePendingSelect.disabled = true;

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
    donationTypeSelect.value = donor.type;
    donationTypeSelect.disabled = true;

    cells[0].style.textAlign = "center";
    cells[0].style.verticalAlign = "middle";
    cells[0].appendChild(pledgePendingSelect);
    cells[1].style.textAlign = "center";
    cells[1].style.verticalAlign = "middle";
    cells[1].appendChild(donationTypeSelect);
    cells[0].width = '10%';
    cells[1].width = '10%';

    cells[2].width = '10%';
    cells[3].width = '8%';
    cells[4].width = '8%';
    cells[5].width = '18%';
    cells[6].width = '15%';
    cells[7].width = '15%';
    cells[8].width = '9%';
    cells[1].style.verticalAlign = 'middle';
    cells[2].style.verticalAlign = 'middle';
    cells[3].style.verticalAlign = 'middle';
    cells[4].style.verticalAlign = 'middle';
    cells[5].style.verticalAlign = 'middle';
    cells[6].style.verticalAlign = 'middle';
    cells[7].style.verticalAlign = 'middle';
    cells[8].style.verticalAlign = 'middle';

    const inputs = cells.slice(2, 10).map(cell => {
        const textarea = document.createElement("textarea");
        textarea.style.fontSize = "11px";
        textarea.style.width = "100%";
        textarea.style.boxSizing = "border-box";
        textarea.style.overflowY = "hidden";
        textarea.style.resize = "none";
        textarea.style.verticalAlign = "middle";
        textarea.style.padding = "5px";
        textarea.style.margin = "0";
        textarea.style.border = "1px solid #000000";
        textarea.style.borderRadius = "4px";
        textarea.style.lineHeight = "1.1";
        textarea.style.minHeight = "40px";

        const resizeTextarea = (el) => {
            el.style.height = 'auto';
            el.style.height = el.scrollHeight + 'px';
        };

        textarea.addEventListener('input', (e) => {
            resizeTextarea(e.target);
        });

        resizeTextarea(textarea);
        cell.style.padding = "3px";
        cell.appendChild(textarea);
        return textarea;
    });

    cells[2].textContent = donor.full_name;
    cells[3].textContent = donor.organization;
    cells[4].textContent = numberWithCommas(donor.amount);
    cells[5].textContent = donor.next_step;
    cells[6].textContent = donor.recent_involvement;
    cells[7].textContent = donor.notes;
    cells[8].textContent = donor.lead;
    cells[2].style.fontSize = "12px";
    cells[3].style.fontSize = "12px";
    cells[4].style.fontSize = "12px";
    cells[5].style.fontSize = "12px";
    cells[6].style.fontSize = "12px";
    cells[7].style.fontSize = "12px";
    cells[8].style.fontSize = "12px";

    const attachFiles = document.createElement("td");
    attachFiles.style.textAlign = 'center';
    attachFiles.style.verticalAlign = 'middle';
    attachFiles.width = '10%';

    const buttonContainer = document.createElement("div");
    buttonContainer.className = 'attach-download-container';

    const attachButton = document.createElement("button");
    attachButton.className = "attach-button";
    attachButton.style.backgroundColor = "lightgrey";
    attachButton.style.border = "none";
    attachButton.innerHTML = '<img src="https://cdn-icons-png.flaticon.com/512/6583/6583130.png" alt="Attach files">';
    buttonContainer.appendChild(attachButton);

    const downloadButton = document.createElement("button");
    downloadButton.className = "download-button";
    downloadButton.style.backgroundColor = "lightgrey";
    downloadButton.style.border = "none";
    downloadButton.innerHTML = '<img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/OOjs_UI_icon_download.svg/2048px-OOjs_UI_icon_download.svg.png" alt="Download files">';
    buttonContainer.appendChild(downloadButton);

    attachFiles.appendChild(buttonContainer);
    cells[9] = attachFiles;

    const selectedFiles = [];

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
                selectedFiles.push(...files);
            }
        });
        fileInput.click();
    });

    downloadButton.addEventListener("click", function handleDownloadButtonClick() {
        if (selectedFiles.length > 0) {
            const popup = document.createElement("div");
            popup.className = "popup";
            popup.style.position = "fixed";
            popup.style.top = "50%";
            popup.style.left = "50%";
            popup.style.transform = "translate(-50%, -50%)";
            popup.style.background = "white";
            popup.style.padding = "10px";
            popup.style.borderRadius = "10px";
            popup.style.boxShadow = "0 4px 8px 0 rgba(0, 0, 0, 0.2)";
            popup.style.zIndex = "9999";
            popup.style.textAlign = "center";

            const fileListContainer = document.createElement("div");
            fileListContainer.className = "file-list-container";

            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement("div");
                fileItem.innerText = file.name;
                fileItem.style.marginBottom = "8px";
                fileItem.style.backgroundColor = index % 2 === 0 ? "#f5f5f5" : "white";

                const fileIcon = document.createElement("img");

                const downloadLink = document.createElement("a");
                downloadLink.href = URL.createObjectURL(file);
                downloadLink.download = file.name;

                const downloadIcon = document.createElement("img");
                downloadIcon.src = "https://imgur.com/Eh4o70Y";
                downloadIcon.alt = "Download";
                downloadIcon.style.width = "20px";
                downloadIcon.style.height = "20px";
                downloadIcon.style.marginLeft = "5px";

                downloadIcon.addEventListener("click", () => {
                    fileItem.style.color = "#7866A1";
                    fileItem.style.textDecoration = "underline";
                });

                downloadLink.appendChild(downloadIcon);

                fileItem.appendChild(downloadLink);
                fileListContainer.appendChild(fileItem);
            });

            const closeButton = document.createElement("button");
            closeButton.innerText = "Close";
            closeButton.style.padding = "7.5px 12.5px";
            closeButton.style.marginTop = "10px";
            closeButton.addEventListener("click", function handleCloseButtonClick() {
                document.body.removeChild(popup);
            });

            fileListContainer.appendChild(closeButton);
            popup.appendChild(fileListContainer);
            document.body.appendChild(popup);
        }
    });

    const editButton = document.createElement("button");
    editButton.className = "edit-button";
    editButton.innerText = "Edit";
    cells[10].appendChild(editButton);
    cells[10].style.textAlign = "center";
    cells[10].style.verticalAlign = "middle";

    cells.forEach(cell => newRow.appendChild(cell));

    const tableBody = document.querySelector("#moves-management table tbody");

    const amount = parseFloat(donor.amount.replace(/[^\d.-]/g, ''));
    let inserted = false;

    for (let i = 0; i < tableBody.rows.length; i++) {
        const rowAmount = parseFloat(tableBody.rows[i].cells[4].textContent.replace(/[^\d.-]/g, ''));
        if (amount > rowAmount) {
            tableBody.insertBefore(newRow, tableBody.rows[i]);
            inserted = true;
            break;
        }
    }

    if (!inserted) {
        tableBody.appendChild(newRow);
    }

    const deleteButton = document.createElement("div");
    deleteButton.className = "delete-button";
    const deleteImage = document.createElement("img");
    deleteImage.src = "https://freepngtransparent.com/wp-content/uploads/2023/03/X-Png-87.png";
    deleteButton.appendChild(deleteImage);

    deleteButton.style.display = 'none';

    deleteButton.addEventListener("click", function handleDeleteButtonClick() {
        if (confirm("Are you sure you would like to delete this donor?")) {
            isEditing = false;
			const donorId = donor.donor_id;

            var donorData = {
                action: 'delete_donor_info',
                donor_id: donorId
            };

            fetch(ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                },
                body: new URLSearchParams(donorData).toString()
            })
            .then(response => response.text())
            .then(responseText => {
                if (responseText.includes('successfully')) {
                    newRow.remove();
                    removeDonorFromTabs(donor);
                    updateDashboardTotals();
                } else {
                    alert('Failed to delete the donor. Please try again.');
                }
            })
            .catch(error => console.error('Error:', error));
        }
    });

    newRow.appendChild(deleteButton);

    editButton.addEventListener("click", function handleEditButtonClick() {
		  isEditing = true;
		pollingEnabled = false; // Disable polling temporarily
        removeDonorFromTabs(donor);
		updateTopDonors();
        deleteButton.style.display = 'flex';
        pledgePendingSelect.disabled = false;
        donationTypeSelect.disabled = false;

        const fullNameCheckbox = addCircleCheckbox();
        const orgNameCheckbox = addCircleCheckbox();

        const fullNameContainer = document.createElement("div");
        fullNameContainer.style.display = "flex";
        fullNameContainer.style.alignItems = "center";

        const orgNameContainer = document.createElement("div");
        orgNameContainer.style.display = "flex";
        orgNameContainer.style.alignItems = "center";

		inputs.forEach((input, index) => {
            if (index !== 7) {
                const value = cells[index + 2].textContent;
                cells[index + 2].textContent = "";
                cells[index + 2].appendChild(input);
                input.value = value;
            }
        });

        inputs[0].value = donor.full_name;
		console.log()
        inputs[1].value = donor.organization;

        cells[2].innerHTML = "";
        cells[2].appendChild(fullNameContainer);
        fullNameContainer.appendChild(fullNameCheckbox);
        fullNameContainer.appendChild(inputs[0]);

        cells[3].innerHTML = "";
        cells[3].appendChild(orgNameContainer);
        orgNameContainer.appendChild(orgNameCheckbox);
        orgNameContainer.appendChild(inputs[1]);

        if (donor.display_name === donor.full_name) {
            fullNameCheckbox.checked = true;
        } else if (donor.display_name === donor.organization) {
            orgNameCheckbox.checked = true;
        } else {
            fullNameCheckbox.checked = true;
            donor.display_name = donor.full_name;
        }

        fullNameCheckbox.addEventListener("change", function () {
            if (fullNameCheckbox.checked) {
                orgNameCheckbox.checked = false;
                donor.display_name = inputs[0].value;
            }
        });

        orgNameCheckbox.addEventListener("change", function () {
            if (orgNameCheckbox.checked) {
                fullNameCheckbox.checked = false;
                donor.display_name = inputs[1].value;
            }
        });

        
        const saveButton = document.createElement("button");
        saveButton.className = "save-button";
        saveButton.innerText = "Save";
        cells[10].innerHTML = "";
        cells[10].appendChild(saveButton);
        cells[10].style.textAlign = "center";
        cells[10].style.verticalAlign = "middle";

		pollingEnabled = true;
		
        saveButton.addEventListener("click", function handleSaveButtonClick() {
			isEditing = false;
            const updatedDonor = {
                donor_id: donor.donor_id,
                status: pledgePendingSelect.value,
                type: donationTypeSelect.value,
                full_name: inputs[0].value,
                organization: inputs[1].value,
                amount: inputs[2].value,
                next_step: inputs[3].value,
                recent_involvement: inputs[4].value,
                notes: inputs[5].value,
                lead: inputs[6].value,
                date_added: donor.date_added,
                display_name: donor.display_name
            };

            var donorData = {
                action: 'insert_donor_info',
                donor_id: updatedDonor.donor_id,
                campaign_id: campaignId,
                status: updatedDonor.status,
                type: updatedDonor.type,
                full_name: updatedDonor.full_name,
                organization: updatedDonor.organization,
                amount: updatedDonor.amount,
                next_step: updatedDonor.next_step,
                recent_involvement: updatedDonor.recent_involvement,
                notes: updatedDonor.notes,
                lead: updatedDonor.lead,
                date_added: updatedDonor.date_added,
                display_name: updatedDonor.display_name
            };

            fetch(ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                },
                body: new URLSearchParams(donorData).toString()
            })
            .then(response => response.text())
            .then(responseText => {
                if (responseText.includes('successfully')) {
                    updateDashboard(updatedDonor);
                    updateDonationPyramid(updatedDonor);
                    updateTopDonors();
                    updateDashboardTotals();
                } else {
                    console.error('Failed to save the donor. Please try again.');
                }
            })
            .catch(error => console.error('Error:', error));

            pledgePendingSelect.disabled = true;
            donationTypeSelect.disabled = true;

            deleteButton.remove();

            const values = inputs.map(input => input.value);

            cells.slice(2, 9).forEach((cell, index) => {
                if (index === 2) {
                    cell.innerHTML = values[index];
                } else {
                    cell.innerHTML = values[index];
                }
            });

            const newEditButton = document.createElement("button");
            newEditButton.className = "edit-button";
            newEditButton.innerText = "Edit";
            cells[10].innerHTML = "";
            cells[10].appendChild(newEditButton);
            cells[10].style.textAlign = "center";
            cells[10].style.verticalAlign = "middle";

            newEditButton.addEventListener("click", handleEditButtonClick);

            deleteButton.style.display = 'none';
        });
    });
	  
}
		
function removeDonorFromTabs(donor) {
	
    const parseName = donor.display_name.replace(/<br\s*\/?>/g, '').replace(/\s+/g, '');

  const pyramidRows = document.querySelectorAll('.donation-row');
let donorFound = false;  // Track if the donor was found and removed

pyramidRows.forEach(row => {
    const boxInners = row.querySelectorAll('.donation-box-inner');
    const frontElements = row.querySelectorAll('.donation-box-front');
    const backElements = row.querySelectorAll('.donation-box-back');

    boxInners.forEach((boxInner, index) => {
        if (boxInner) {
            const frontElement = frontElements[index];
            const backElement = backElements[index];
            const boxDisplayName = frontElement.innerText.replace(/<br\s*\/?>/g, '').replace(/\s+/g, '');

            console.log(boxDisplayName);
            console.log(parseName);

            // Compare display name and parseName
            if (boxDisplayName === parseName) {
                donorFound = true;  // Donor found
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
                    backElement.style.backgroundColor = "#d4d4d4";
                    backElement.style.color = '';
                    backElement.style.fontWeight = '';
                    backElement.style.textAlign = '';
                    backElement.style.display = '';
                    backElement.style.justifyContent = '';
                }
            }
        }
    });
});

// Logic to remove the donor from the top 5 list if they are already in it
if (donorFound) {
    // Find the donor in the donors array (top 5 donors) and remove them
    donors = donors.filter(donor => donor.name !== parseName);
}

// Now populate the top 5 donors into the donorContainer
const donorContainer = document.getElementById('donorContainer');
donorContainer.innerHTML = '';  // Clear previous donors

for (let i = 0; i < 5 && i < donors.length; i++) {
    const donor = donors[i];
    const donorElement = document.createElement('div');
    donorElement.innerHTML = `
        <div style="text-align: center; margin: 0 35px;">
            <div style="color: #00758D; font-weight: bold; font-size: 20px;">${donor.name}</div>
            <div style="font-size: 20px;"><span style="color: #00758D; font-weight: bold;">${numberWithCommas(Math.round(donor.amount))}</span></div>
        </div>`;
    donorContainer.appendChild(donorElement);
}
	
    // Remove the donor from the pledges table
    const pledgesTable = document.querySelector(".pledges-table tbody");
    const pledgesRows = pledgesTable.querySelectorAll("tr");
    pledgesRows.forEach(row => {
        const donationCell = row.querySelector("td:nth-child(1)");
        if (donationCell.innerHTML.replace(/ /g, '') === parseName) {
            row.remove();
        }
    });

    // Remove the donor from the pending table
    const pendingTable = document.querySelector(".pending-table tbody");
    const pendingRows = pendingTable.querySelectorAll("tr");
    pendingRows.forEach(row => {
        const donationCell = row.querySelector("td:nth-child(1)");
        if (donationCell.innerHTML.replace(/ /g, '') === parseName) {
            row.remove();
        }
    });

    // Remove the donor from the pipeline table
    const pipelineTable = document.querySelector(".pipeline-table tbody");
    const pipelineRows = pipelineTable.querySelectorAll("tr");
    pipelineRows.forEach(row => {
        const donationCell = row.querySelector("td:nth-child(1)");
        if (donationCell.innerHTML.replace(/ /g, '') === parseName) {
            row.remove();
        }
    });

    updateDashboardTotals();
}
 

    function updateDashboard(donor) {
        const pledgePendingValue = donor.status;
        const displayName = donor.display_name;
		const dateForDisplay = donor.date_added;
        const donationAmount = parseInt(donor.amount.replace(/[^0-9.-]+/g, ""));

        const targetTable = document.querySelector(`.${pledgePendingValue}-table tbody`);
        const targetRow = document.createElement("tr");
        const targetCells = [
            document.createElement("td"),
            document.createElement("td"),
            document.createElement("td"),
        ];

        targetCells[0].innerHTML = displayName;
        targetCells[1].innerHTML = numberWithCommas(donationAmount);

        targetCells[2].innerHTML = dateForDisplay;

        targetCells[2].addEventListener('click', (event) => {
        event.target.contentEditable = true;
        event.target.focus();
        targetCells[2].style.color = '#00758D';
    });

    targetCells[2].addEventListener('blur', (event) => {
        event.target.contentEditable = false;
        targetCells[2].style.color = '#333';

        // Capture the edited date and send it to the server
        const editedDate = event.target.innerText.trim(); // Trim any extra spaces or newline characters

        const donorData = {
            action: 'update_donor_date',
            donor_id: donor.donor_id,
            campaign_id: campaignId,
            date_added: editedDate
        };


        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            },
            body: new URLSearchParams(donorData).toString()
        })
        .then(response => response.text())
        .then(responseText => {
            if (!responseText.includes('successfully')) {
                console.error('Failed to update the donor date. Please try again.');
            }
        })
        .catch(error => console.error('Error:', error));
    });

        targetRow.appendChild(targetCells[0]);
        targetRow.appendChild(targetCells[1]);
        targetRow.appendChild(targetCells[2]);

        function insertRowInOrder(tableBody, newRow) {
            const newDonation = parseFloat(newRow.cells[1].innerText.replace(/[$,]/g, ''));
            for (let i = 0; i < tableBody.rows.length; i++) {
                const row = tableBody.rows[i];
                const currentDonation = parseFloat(row.cells[1].innerText.replace(/[$,]/g, ''));
                if (newDonation > currentDonation) {
                    tableBody.insertBefore(newRow, row);
                    return;
                }
            }
            tableBody.appendChild(newRow);
        }

        if (pledgePendingValue === "pending") {
            const tableBody = document.querySelector(".pending-table tbody");
            insertRowInOrder(tableBody, targetRow);
        } else if (pledgePendingValue === "engaged" || pledgePendingValue === "identified") {
            const tableBody = document.querySelector(".pipeline-table tbody");
            insertRowInOrder(tableBody, targetRow);
        } else if (pledgePendingValue === "pledge") {
            const tableBody = document.querySelector(".pledges-table tbody");
            insertRowInOrder(tableBody, targetRow);
        }

 
        const pledgesTotalElement = document.querySelector(".pledges-total");
        const pendingTotalElement = document.querySelector(".pending-total");
        const pipelineTotalElement = document.querySelector(".pipeline-total");

        const formatCurrency = (amount) => {
            return numberWithCommas(amount);
        };

        const pledgesCells = Array.from(document.querySelectorAll(".pledges-table tbody td:nth-child(2)"));
        const totalDonations = pledgesCells.reduce((acc, curr) => {
            const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
            return isNaN(amount) ? acc : acc + amount;
        }, 0);
        pledgesTotalElement.innerText = formatCurrency(totalDonations);

        const pendingCells = Array.from(document.querySelectorAll(".pending-table tbody td:nth-child(2)"));
        const pendingDonations = pendingCells.reduce((acc, curr) => {
            const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
            return isNaN(amount) ? acc : acc + amount;
        }, 0);
        pendingTotalElement.innerText = formatCurrency(pendingDonations);

        const pipelineCells = Array.from(document.querySelectorAll(".pipeline-table tbody td:nth-child(2)"));
        const pipelineDonations = pipelineCells.reduce((acc, curr) => {
            const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
            return isNaN(amount) ? acc : acc + amount;
        }, 0);
        pipelineTotalElement.innerText = formatCurrency(pipelineDonations);

        const combinedTotal = totalDonations + pendingDonations + pipelineDonations;
        const combinedTotalElement = document.querySelector(".combined-total-amount");
        combinedTotalElement.innerText = formatCurrency(combinedTotal);

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
			
        const rowsType = document.querySelectorAll('#moves-management table tbody tr');
		
		// Initialize count variables for each donation type
let individualCount = 0;
let foundationCount = 0;
let corporationCount = 0;
let publicCount = 0;
let boardCount = 0;
let otherCount = 0;

// Iterate over each row and update the count variables
rowsType.forEach(row => {
  const donationTypeSelect = row.querySelector('.donation-type-select');
  const donationStatusSelect = row.querySelector('.donation-status-select'); // Select the donation status
  const donationTypeValue = donationTypeSelect.value;
  const donationStatusValue = donationStatusSelect.value; // Get the donation status value

  // Proceed only if the donation status is "pledged"
  if (donationStatusValue === "pledge") {
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
  }
});

			
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
<div id="pieChartPlaceholder" style="width: 80%; height: 80%;">
    <!-- Pie Chart Using SVG -->
    <svg width="150%" height="100%" viewBox="-30 -30 72 72">
        <path id="endowmentSlice1" d="" fill="#00758D"></path>
        <text id="endowmentTextName1" font-size="2" fill="#005D70"></text>
        <text id="endowmentTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		
        <!-- Capital slice -->
        <path id="capitalSlice1" d="" fill="#7866A1"></path>
        <text id="capitalTextName1" font-size="2" fill="#635387"></text>
        <text id="capitalTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		
        <!-- Operating slice -->
        <path id="operatingSlice1" d="" fill="#FF8C00"></path>
        <text id="operatingTextName1" font-size="2" fill="#D17607"></text>
        <text id="operatingTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
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
    
		createSlice1("endowmentSlice1", "endowmentTextName1", "endowmentTextAmount1", "#00758D", 0, slice1Proportion, slice1Value, slice1Amount);
	createSlice1("capitalSlice1", "capitalTextName1", "capitalTextAmount1", "#7866A1", slice1Proportion, slice1Proportion + slice2Proportion, 		slice2Value, slice2Amount);
	createSlice1("operatingSlice1", "operatingTextName1", "operatingTextAmount1", "#FF8C00", slice1Proportion + slice2Proportion, 1, slice3Value, slice3Amount);

		// Sort donorArray in descending order based on donation amount
    updateTopDonors();
     
    }

    function updateDashboardTotals() {
        const pledgesTotalElement = document.querySelector(".pledges-total");
        const pendingTotalElement = document.querySelector(".pending-total");
        const pipelineTotalElement = document.querySelector(".pipeline-total");

        const formatCurrency = (amount) => {
            return numberWithCommas(amount);
        };

        const pledgesCells = Array.from(document.querySelectorAll(".pledges-table tbody td:nth-child(2)"));
        const totalDonations = pledgesCells.reduce((acc, curr) => {
            const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
            return isNaN(amount) ? acc : acc + amount;
        }, 0);
        pledgesTotalElement.innerText = formatCurrency(totalDonations);

        const pendingCells = Array.from(document.querySelectorAll(".pending-table tbody td:nth-child(2)"));
        const pendingDonations = pendingCells.reduce((acc, curr) => {
            const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
            return isNaN(amount) ? acc : acc + amount;
        }, 0);
        pendingTotalElement.innerText = formatCurrency(pendingDonations);

        const pipelineCells = Array.from(document.querySelectorAll(".pipeline-table tbody td:nth-child(2)"));
        const pipelineDonations = pipelineCells.reduce((acc, curr) => {
            const amount = parseFloat(curr.innerText.replace(/[^\d.-]/g, ''));
            return isNaN(amount) ? acc : acc + amount;
        }, 0);
        pipelineTotalElement.innerText = formatCurrency(pipelineDonations);

        const combinedTotal = totalDonations + pendingDonations + pipelineDonations;
        const combinedTotalElement = document.querySelector(".combined-total-amount");
        combinedTotalElement.innerText = formatCurrency(combinedTotal);

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

        localStorage.setItem('totalDonations', totalDonations);
        localStorage.setItem('percent', percent);

        updateDonationDashboard(goal, totalDonations, pendingDonations, pipelineDonations, percent);
        updateTopDonors();
    }

	function updateDonationDashboard(goal, totalDonations, pendingDonations, pipelineDonations, percent) {
        const rowsType1 = document.querySelectorAll('#moves-management table tbody tr');
		
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

        let individualCount = 0;
        let foundationCount = 0;
        let corporationCount = 0;
        let publicCount = 0;
        let boardCount = 0;
        let otherCount = 0;

        rowsType1.forEach(row => {
            const donationTypeSelect = row.querySelector('.donation-type-select');
            const donationStatusSelect = row.querySelector('.donation-status-select');
            const donationTypeValue = donationTypeSelect.value;
            const donationStatusValue = donationStatusSelect.value;

            if (donationStatusValue === "pledge") {
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
            }
        });

        const highestCount = Math.max(individualCount, foundationCount, corporationCount, publicCount, boardCount, otherCount);

        const individualBar = ((individualCount * 180) / highestCount);
        const foundationBar = ((foundationCount * 180) / highestCount);
        const corporationBar = ((corporationCount * 180) / highestCount);
        const publicBar = ((publicCount * 180) / highestCount);
        const boardBar = ((boardCount * 180) / highestCount);
        const otherBar = ((otherCount * 180) / highestCount);
		const formattedGoal = '$' + goal.toLocaleString();

        const dashboard = document.getElementById("dashboard-html");
        dashboard.innerHTML = `
            <div style="display: flex; flex-direction: column; background-color: #F0F0F0; border-radius: 10px; padding: 20px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3); margin-bottom: 20px;">
                <div style="width: 100%; height: 70px; background-color: #F0F0F0; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
                    <h2 style="color: #00758D; font-size: 41px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); padding: 0 20px;">$${totalDonations.toLocaleString()} Pledged (${percent.toFixed()}% to Goal)</h2>
                </div>
                <div class="dashboard-meter" style="width: 100%; height: 90px; background-color: #FFFFFF; border: 5px solid #00758D; border-radius: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center;">
                    <div class="fill" style="width: ${percent}%">
                        ${percent > 100 ? `<p>${percent.toFixed()}%</p>` : ''}
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 15px; align-items: center;">
                    <div style="width: 49%; background-color: #FFFFFF; border: 5px solid #00758D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
                        <div>
                            <h3 style="color: #00758D; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">${numberWithCommas(pendingDonations)} Pending</h3>
                        </div>
                    </div>
                    <div style="width: 49%; background-color: #FFFFFF; border: 5px solid #00758D; height: 65px; border-radius: 10px; display: flex; justify-content: center; align-items: center; padding: 10px; box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);">
                        <div>
                            <h3 style="color: #00758D; font-size: 25px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">${numberWithCommas(totalDonations + pendingDonations + pipelineDonations)} Pledged & Pending</h3>
                        </div>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; border-radius: 10px; margin-bottom: 15px; align-items: center;">
                    <div style="width: 37%; height: 250px; background-color: rgb(255, 255, 255); border: 5px solid #00758D; border-radius: 8px; display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; padding: 10px;">
                        <div style="width: 100%; display: flex; align-items: center; flex-direction: column;">
                            <h3 style="color: #00758D; font-size: 20px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">
        ${formattedGoal} Campaign Goal
    </h3>
<div id="pieChartPlaceholder" style="width: 80%; height: 80%;">
    <!-- Pie Chart Using SVG -->
    <svg width="150%" height="100%" viewBox="-30 -30 72 72">
        <path id="endowmentSlice1" d="" fill="#00758D"></path>
        <text id="endowmentTextName1" font-size="2" fill="#005D70"></text>
        <text id="endowmentTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
	

        <!-- Capital slice -->
        <path id="capitalSlice1" d="" fill="#7866A1"></path>
        <text id="capitalTextName1" font-size="2" fill="#635387"></text>
        <text id="capitalTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
	

        <!-- Operating slice -->
        <path id="operatingSlice1" d="" fill="#FF8C00"></path>
        <text id="operatingTextName1" font-size="2" fill="#D17607"></text>
        <text id="operatingTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		
    </svg>
</div>
                        </div>
                    </div>
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
                    </div>
                </div>
            </div>
        `;
		
		
	createSlice1("endowmentSlice1", "endowmentTextName1", "endowmentTextAmount1", "#00758D", 0, slice1Proportion, slice1Value, slice1Amount);
	createSlice1("capitalSlice1", "capitalTextName1", "capitalTextAmount1", "#7866A1", slice1Proportion, slice1Proportion + slice2Proportion, 		slice2Value, slice2Amount);
	createSlice1("operatingSlice1", "operatingTextName1", "operatingTextAmount1", "#FF8C00", slice1Proportion + slice2Proportion, 1, slice3Value, slice3Amount);
		
updateTopDonors();
		
    }

    function updateDonationPyramid(donor) {
        const pledgePendingValue = donor.status;
        const displayName = donor.display_name;
        const donationAmount = parseInt(donor.amount.replace(/[^0-9.-]+/g, ""));

        let donationColor = '';
        switch (pledgePendingValue) {
            case 'pledge':
                let newDonor = {
                    name: displayName,
                    amount: donationAmount
                };
                donors.push(newDonor);
                donationColor = '#F78D2D';
                break;
            case 'identified':
                donationColor = '#5DABBC';
                break;
            case 'engaged':
                donationColor = '#00728A';
                break;
            case 'pending':
                donationColor = '#7866A1';
                break;
            default:
             
                return;
        }

        const rows = document.querySelectorAll('.donation-row');
        let donationRowAmounts = [];

        document.querySelectorAll('.donation-row').forEach(row => {
            let box = row.querySelector('.donation-box');
            if (box) {
                let amount = parseInt(box.getAttribute('data-amount'));
                if (!isNaN(amount)) {
                    donationRowAmounts.push(amount);
                }
            }
        });

        const closestAmount = donationRowAmounts.reduce((prev, curr) => Math.abs(curr - donationAmount) < Math.abs(prev - donationAmount) ? curr : prev);
        const rowIndex = 'row' + closestAmount / 1000;
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

            const backOfBox = box.parentElement.querySelector('.donation-box-back');
            backOfBox.innerHTML = numberWithCommas(donationAmount);
            const darkerDonationColor = makeDarker(donationColor, 30);
            backOfBox.style.backgroundColor = darkerDonationColor;

            backOfBox.style.color = "#fff";
            backOfBox.style.fontWeight = "400";
            backOfBox.style.fontSize = "17px";
            backOfBox.style.textAlign = "center";
            backOfBox.style.display = "none";

            const donationBox = box.closest('.donation-box');
            const computedStyle = window.getComputedStyle(box);

            const boxWidth = parseFloat(computedStyle.width);
            const boxHeight = parseFloat(computedStyle.height);

            const span = document.createElement("span");
            span.style.display = 'inline-block';
            document.body.appendChild(span);

            let fontSize = 20;

            const adjustFontSize = () => {
                while ((span.offsetHeight > boxHeight || span.offsetWidth > boxWidth) && fontSize > 10) {
                    fontSize--;
                    span.style.fontSize = fontSize + "px";
                }
            }

            span.innerHTML = displayName;
            span.style.fontSize = fontSize + "px";
            adjustFontSize();

            const words = displayName.split(" ");
            if (fontSize < 30) {
                for (let i = 1; i < words.length && (span.offsetHeight > boxHeight || span.offsetWidth > boxWidth); i++) {
                    span.innerHTML = words.slice(0, i).join(" ") + "<br>" + words.slice(i).join(" ");
                    adjustFontSize();
                }
                span.style.lineHeight = "0.9";
            }

            box.style.fontSize = fontSize + "px";
            box.style.padding = "10px";
            box.innerHTML = span.innerHTML;
            box.style.lineHeight = span.style.lineHeight;

            document.body.removeChild(span);
        } else { 
            const modal = document.getElementById('alertModule1');
            modal.style.display = "block";
			 
            const confirmButton = document.getElementById('confirmButton');
            const cancelButton = document.getElementById('cancelButton');
			

            cancelButton.onclick = function() {
                modal.style.display = "none";
		
                const modal1 = document.getElementById('alertModule');
                modal1.style.display = "block";
				
            }

            confirmButton.onclick = function() {
                const rowBoxes = document.querySelectorAll('.donation-box-front[data-row="' + rowIndex + '"]');
                let filledRowBoxes = 0;
                rowBoxes.forEach((box) => {
                    if (box.innerHTML.trim() !== "") {
                        filledRowBoxes++;
                    }
                });

                if (filledRowBoxes === rowBoxes.length) {
                    const row = rowBoxes[0] ? rowBoxes[0].closest('.donation-row') : null;
                    if (row) {
                        addDonationBox(row, rowIndex, displayName, donationAmount, donationColor);
						savePyramidHTML();
                    }
                }
                modal.style.display = "none";
            }
        }
    }

    function updateTopDonors() {
        const donorContainer = document.getElementById('donorContainer');
        if (!donorContainer) {
            console.error("Donor container not found!");
            return;
        }
        donorContainer.innerHTML = '';
        donors.sort((a, b) => b.amount - a.amount);
        for (let i = 0; i < 5 && i < donors.length; i++) {
            const donor = donors[i];
            const donorElement = document.createElement('div');
            donorElement.innerHTML = `
                <div style="text-align: center; margin: 0 35px;">
                    <div style="color: #00758D; font-weight: bold; font-size: 20px;">${donor.name}</div>
                    <div style="font-size: 20px;"><span style="color: #00758D; font-weight: bold;">${numberWithCommas(Math.round(donor.amount))}</span></div>
                </div>`;
            donorContainer.appendChild(donorElement);
        }
    }
	
	// Function to validate the form
function validateForm() {
	if (inputs[0].value.trim() === "" && inputs[2].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Full Name, Amount field, and a Checkbox selection are required.");
    return false;
	} else if (inputs[0].value.trim() === "" && inputs[2].value.trim() === "") {
    showAlertMM("Full Name and Amount fields are required.");
    return false;
	} else if (inputs[0].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Full Name and a Checkbox selection are required.");
    return false;
  } else if (inputs[0].value.trim() === "" && inputs[2].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Full Name and Amount field with a Checkbox selection is required.");
    return false;
  } else if ( inputs[2].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Amount field and a Checkbox selection is required.");
    return false;
  } else if (inputs[0].value.trim() === "") {
    showAlertMM("Full Name field is required.");
    return false;
  } else if (inputs[0].value.trim() === "" && inputs[2].value.trim() === "") {
    showAlertMM("Full Name and Amount fields are required.");
    return false;
  } else if ( inputs[2].value.trim() === "") {
    showAlertMM("Amount field is required.");
    return false;
	  } else if (inputs[0].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Full Name field and a Checkbox selection is required.");
    return false;
  } else if ((!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("A Checkbox selection is required.");
    return false;
  } else if (inputs[2].value.trim() === "" && (!fullNameCheckbox.checked && !orgNameCheckbox.checked)) {
    showAlertMM("Amount field and a Checkbox selection is required.");
    return false;
  } else if (inputs[0].value.trim() === "") {
    showAlertMM("Full Name field is required.");
    return false;
  } else if (inputs[2].value.trim() === "") {
    showAlertMM("Amount field is required.");
    return false;
  } else if (!fullNameCheckbox.checked && !orgNameCheckbox.checked) {
    showAlertMM("Please select at least one checkbox.");
    return false;
  }
  return true;
}

	function numberWithCommas(number) {
  const formattedNumber = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  if (!formattedNumber.startsWith('$')) {
    return '$' + formattedNumber;
  }
  return formattedNumber;
}

	function makeDarker(color, factor) {
  const r = Math.max(0, parseInt(color.substring(1, 3), 16) - factor);
  const g = Math.max(0, parseInt(color.substring(3, 5), 16) - factor);
  const b = Math.max(0, parseInt(color.substring(5, 7), 16) - factor);

  return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}
	});
});

	//ADD DASHBOARD ON PAGE LOAD 
	
	document.addEventListener("DOMContentLoaded", function() {
  		const goal = <?php echo $goal; ?>;
  		
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
<div id="pieChartPlaceholder" style="width: 80%; height: 80%;">
    <!-- Pie Chart Using SVG -->
    <svg width="150%" height="100%" viewBox="-30 -30 72 72">
        <path id="endowmentSlice1" d="" fill="#00758D"></path>
        <text id="endowmentTextName1" font-size="2" fill="#005D70"></text>
        <text id="endowmentTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		

        <!-- Capital slice -->
        <path id="capitalSlice1" d="" fill="#7866A1"></path>
        <text id="capitalTextName1" font-size="2" fill="#635387"></text>
        <text id="capitalTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		

        <!-- Operating slice -->
        <path id="operatingSlice1" d="" fill="#FF8C00"></path>
        <text id="operatingTextName1" font-size="2" fill="#D17607"></text>
        <text id="operatingTextAmount1" font-size="1.7" fill="rgb(0,0,0)"></text>
		
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
		
		// Update pie chart slices
		createSlice1("endowmentSlice1", "endowmentTextName1", "endowmentTextAmount1", "#00758D", 0, slice1Proportion, slice1Value, slice1Amount);
	createSlice1("capitalSlice1", "capitalTextName1", "capitalTextAmount1", "#7866A1", slice1Proportion, slice1Proportion + slice2Proportion, 		slice2Value, slice2Amount);
	createSlice1("operatingSlice1", "operatingTextName1", "operatingTextAmount1", "#FF8C00", slice1Proportion + slice2Proportion, 1, slice3Value, slice3Amount);

	});
function generatePDF() {
    const tab = document.querySelector('.tab.active');
    const tabTitleElement = tab.querySelector('h2');
    const tabTitle = tabTitleElement ? tabTitleElement.innerText : '';
    const campaignName = "<?php echo $campaign_name; ?>"; // Replace this with the appropriate variable or function to get the campaign name

    // Clone the tab content deeply to avoid modifying the original DOM
    const clonedTab = tab.cloneNode(true);

    // Create a wrapper div to hold the cloned content and the additional elements
    const clonedTabWrapper = document.createElement('div');
    clonedTabWrapper.style.position = 'relative';
    clonedTabWrapper.style.width = '100%';
    clonedTabWrapper.style.height = '100%';
    clonedTabWrapper.appendChild(clonedTab);

    // Create a header div
    const headerDiv = document.createElement('div');
    headerDiv.style.textAlign = 'center';
    headerDiv.style.fontFamily = 'Noto Sans';
    headerDiv.style.marginBottom = '20px';

    const title = document.createElement('h1');
    title.innerText = campaignName;
    title.style.fontSize = '30px';
    title.style.fontWeight = 'bold';
    headerDiv.appendChild(title);

    const date = document.createElement('div');
    date.innerText = new Date().toDateString();
    date.style.fontSize = '20px';
    date.style.fontStyle = 'italic';
    headerDiv.appendChild(date);

    // Insert the header at the top of the cloned tab
    clonedTab.prepend(headerDiv);

    // Hide the key element in the cloned content
    const clonedKeyElement = clonedTab.querySelector('.donation-key');
    if (clonedKeyElement) {
        clonedKeyElement.style.display = 'none'; // Hide the key element in the cloned content
    }

    // Ensure key element is correctly positioned in the cloned content
    const originalKeyElement = tab.querySelector('.donation-key');
    if (originalKeyElement) {
        const clonedKeyElement = originalKeyElement.cloneNode(true); // Clone the key element
        clonedKeyElement.style.position = 'absolute';
        clonedKeyElement.style.top = '35%';
        clonedKeyElement.style.right = '5%';
        clonedKeyElement.style.zIndex = '1000';
        clonedTabWrapper.appendChild(clonedKeyElement); // Append to cloned content
    }

    // Add watermark diagonally
    const watermarkDiv = document.createElement('div');
    watermarkDiv.innerText = 'CONFIDENTIAL';
    watermarkDiv.style.position = 'fixed';
    watermarkDiv.style.top = '50%';
    watermarkDiv.style.left = '50%';
    watermarkDiv.style.transform = 'translate(-50%, -50%) rotate(-45deg)';
    watermarkDiv.style.fontSize = '100px';
    watermarkDiv.style.fontFamily = 'Noto Serif';
    watermarkDiv.style.color = 'red';
    watermarkDiv.style.opacity = '0.1'; // Make it more opaque
    watermarkDiv.style.zIndex = '9999';
    clonedTabWrapper.appendChild(watermarkDiv);

    // Add the footer
    const footerDiv = document.createElement('div');
    footerDiv.style.textAlign = 'center';
    footerDiv.style.fontFamily = 'Noto Sans';
    footerDiv.style.fontSize = '14px';
    footerDiv.style.marginTop = '20px';
    footerDiv.style.borderTop = '2px solid #007C92';
    footerDiv.style.paddingTop = '10px';
    footerDiv.style.color = '#A3A3A3';
    footerDiv.innerHTML = `
        <span>(614) 766-4483</span>
        <span style="padding: 0 10px;">|</span>
        <span>18 S High St</span>
        <span style="padding: 0 10px;">|</span>
        <span>Dublin, OH 43017</span>
        <span style="padding: 0 10px;">|</span>
        <a href="http://CramerPhilanthropy.com" target="_blank" style="color: #007C92; text-decoration: none;">CramerPhilanthropy.com</a>
    `;
    clonedTabWrapper.appendChild(footerDiv);

    // Convert the cloned tab to PDF
    const options = {
        margin: 10,
        filename: `${campaignName}_${tabTitle}_${new Date().toISOString().slice(0, 10)}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().from(clonedTabWrapper).set(options).save().then(() => {
        // Original key element remains unchanged
    });
}

// Add new donation box 
function addDonationBox(row, rowIndex, donationName, donationAmount, donationColor) {
  const rowLabel = row.querySelector('.donation-row-label');
  let numBoxes = parseInt(rowLabel.innerText.split(' ')[0]);

  // Increment the number of boxes
  numBoxes += 1;
  rowLabel.innerText = numBoxes + ' x ' + rowLabel.innerText.split(' ')[2];
  rowLabel.style.display = numBoxes === 0 ? "none" : "";  // Show or hide the label

  // Create the box container
  const box = document.createElement('div');
  box.className = 'donation-box';
  box.setAttribute('data-amount', donationAmount);

  // Front side of the box
  const boxFront = document.createElement('div');
  boxFront.className = 'donation-box-front';
  boxFront.innerHTML = donationName;
  boxFront.style.backgroundColor = donationColor;
  boxFront.style.color = "#fff"; // Set text color to white
  boxFront.style.fontWeight = "500";
  boxFront.style.textAlign = "center";
  boxFront.style.display = "flex";
  boxFront.style.justifyContent = "center";
  boxFront.style.alignItems = "center";
  boxFront.style.padding = "10px";
  boxFront.style.borderRadius = "7px"; // Match the border-radius from your image
boxFront.setAttribute('data-row', rowIndex);

  // Back side of the box
  const boxBack = document.createElement('div');
  boxBack.className = 'donation-box-back';
  boxBack.innerHTML = numberWithCommas(donationAmount);
  boxBack.style.backgroundColor = makeDarker(donationColor, 30); // Darker color for the back
  boxBack.style.color = "#fff"; // Set text color to white
  boxBack.style.fontWeight = "400";
  boxBack.style.fontSize = "17px";
  boxBack.style.textAlign = "center";
  boxBack.style.display = "none";  // Initially hide the back

  // Create the inner container and append front and back sides
  const boxInner = document.createElement('div');
  boxInner.className = 'donation-box-inner';
  boxInner.appendChild(boxFront);
  boxInner.appendChild(boxBack);

  // Append the inner container to the box
  box.appendChild(boxInner);
  
  // Add the new box to the row
  row.appendChild(box);

  // Automatically adjust font size if necessary
  adjustFontSizeToFit(boxFront, donationName);
  
  // Update the total donations amount (assuming you have a function for this)
  const totalDonationsLabel = document.querySelector('.donation-row-label b');
  const totalDonations = calculateTotalDonations();
  totalDonationsLabel.innerText = 'Total: ' + numberWithCommas(totalDonations);
}

// Function to adjust the font size of the donation name to fit the box
function adjustFontSizeToFit(box, donationName) {
  const span = document.createElement("span");
  span.style.display = 'inline-block';
  document.body.appendChild(span);

  const boxWidth = parseFloat(window.getComputedStyle(box).width);
  const boxHeight = parseFloat(window.getComputedStyle(box).height);
  
  let fontSize = 20; // Start from a larger font size for better readability
  
  span.innerHTML = donationName;
  span.style.fontSize = fontSize + "px";
  
  const adjustFontSize = () => {
    while ((span.offsetHeight > boxHeight || span.offsetWidth > boxWidth) && fontSize > 10) {
      fontSize--;
      span.style.fontSize = fontSize + "px";
    }
  };

  // Adjust the font size to fit within the box
  adjustFontSize();

  const words = donationName.split(" ");
  if (fontSize < 30) {  // Adjust this threshold as per your design
    for (let i = 1; i < words.length && (span.offsetHeight > boxHeight || span.offsetWidth > boxWidth); i++) {
      span.innerHTML = words.slice(0, i).join(" ") + "<br>" + words.slice(i).join(" ");
      adjustFontSize();
    }
    span.style.lineHeight = "0.9"; // Set a reasonable line-height
  }

  box.style.fontSize = fontSize + "px";
  box.style.lineHeight = span.style.lineHeight;
  box.innerHTML = span.innerHTML;
  
  document.body.removeChild(span);
}

	
	//DONATION PYRAMID EDIT ROWS
	function editNumBoxes(element) {
    	globalRowLabel = element;
    	const numBoxes = parseInt(globalRowLabel.innerText.split(' ')[0]);

    	// Set input value and show the modal
    	document.getElementById('numBoxesInput').value = numBoxes;
    	document.getElementById('customModal').style.display = "block";
	}
		
function updateRowMargin(){
	const donationRows = document.querySelectorAll('.donation-row');

donationRows.forEach((row, index) => {
    if (index > 0) {
        const currentAmount = row.querySelector('.donation-box').dataset.amount;
        const previousAmount = donationRows[index - 1].querySelector('.donation-box').dataset.amount;
        
        if (currentAmount && previousAmount && currentAmount === previousAmount) {
            row.style.marginTop = '-5px'; // Adjust as needed
        }
    }
});

}

		//SAVE
function saveChanges() {

	
    const newNumBoxes = parseInt(document.getElementById('numBoxesInput').value);

    if (isNaN(newNumBoxes) || newNumBoxes < 0) {
        document.getElementById('customModal').style.display = "none";
        const mod = document.getElementById('errorModule2');
        mod.style.display = "block";
        return;
    }
   const row = globalRowLabel.parentElement;
    const amount = parseInt(globalRowLabel.innerText.split('$')[1].replace(/[^0-9.-]+/g, ''));
	

    // Fetch all boxes of the same data-amount across all rows
    const allBoxesOfSameAmount = document.querySelectorAll(`.donation-box[data-amount="${amount}"]`);
    const currentNumBoxes = allBoxesOfSameAmount.length;

    // Get all filled boxes with the same data-amount across all rows
    const filledBoxes = document.querySelectorAll(`.donation-box[data-amount="${amount}"] .donation-box-front:not(:empty)`);

    // Check if we're trying to reduce the number of boxes to a value less than the number of filled boxes
    if (filledBoxes.length > newNumBoxes) {
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

    let boxesToAdd = newNumBoxes - currentNumBoxes;
     const existingBox = row.querySelector('.donation-box-front');
    const rowId = existingBox ? existingBox.getAttribute('data-row') : '';
	
    // Get the parent row of the last box with the same data-amount
    let currentRow = allBoxesOfSameAmount[allBoxesOfSameAmount.length - 1].parentNode;

    while (boxesToAdd > 0) {
        const boxesInThisRow = currentRow.querySelectorAll(`.donation-box[data-amount="${amount}"]`).length;
        const boxesSpaceInThisRow = 8 - boxesInThisRow;
        const boxesToAppend = Math.min(boxesSpaceInThisRow, boxesToAdd);
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

        boxesToAdd -= boxesToAppend;

        if (boxesToAdd > 0) {
            const newRow = document.createElement('div');
            newRow.className = currentRow.className;
            currentRow.parentNode.insertBefore(newRow, currentRow.nextSibling);
            currentRow = newRow;
        }
    }

    if (newNumBoxes < currentNumBoxes) {
        const boxesToRemove = currentNumBoxes - newNumBoxes;

        // Removing boxes from the last, irrespective of the row they are in
        for (let i = 0; i < boxesToRemove; i++) {
            allBoxesOfSameAmount[allBoxesOfSameAmount.length - 1 - i].remove();
        }
    }
	resetRows();
	rowSpace();
    updateTotalDonations();
	savePyramidHTML();
    closeModal();
}


	//UPDATE DONATION RUNNING TOTAL
	function updateTotalDonations() {
    	const totalDonationsLabel = document.querySelector('.donation-row-label b');
    	const totalDonations = calculateTotalDonations();
    	totalDonationsLabel.innerText = 'Total:' + numberWithCommas(totalDonations);
	}

	function addNewRow(amount, numBoxes, position, referenceElement) {
    	if (numBoxes > 32) {
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

		   const baseRow = globalRowLabel.parentElement;
    let currentRow;
    let boxesToAdd = numBoxes;
    let firstRow = null; // Keep track of the first row created in the loop

    while (boxesToAdd > 0) {
        const newRow = document.createElement('div');
        newRow.className = 'donation-row';
        amount = parseInt(amount.toString().replace(/[$,]/g, ''));
       const matchingBoxesData = findClosestMatchingBoxesData(amount, boxesToAdd);

    matchingBoxesData.forEach(matchingBoxData => {
        const newBox = createDonationBox(amount, matchingBoxData);
        newRow.appendChild(newBox);

        let originalRowAmount;
        const originalBox = matchingBoxData.originalRow.querySelector('.donation-box');
        if (originalBox) {
            originalRowAmount = parseInt(originalBox.getAttribute('data-amount'));
        }

        if (originalRowAmount !== undefined) {
            const additionalBox = createNewEmptyBox(originalRowAmount);
            matchingBoxData.originalRow.appendChild(additionalBox);
        } else {
            console.error('Failed to get the original row amount.');
        }

        if (!matchingBoxData.originalRow.hasChildNodes()) {
            matchingBoxData.originalRow.parentNode.removeChild(matchingBoxData.originalRow);
        }
    });

    boxesToAdd -= matchingBoxesData.length;

        const boxesInThisRow = Math.min(8, boxesToAdd);
        for (let i = 0; i < boxesInThisRow; i++) {
		 
   
        const box = document.createElement('div');
        box.className = 'donation-box';
        box.setAttribute('data-amount', amount);

        const boxInner = document.createElement('div');
        boxInner.className = 'donation-box-inner';
// Get the rows and their amounts from the DOM
const rows = document.querySelectorAll('.donation-row');
let donationRowAmounts = [];

document.querySelectorAll('.donation-row').forEach(row => {
    let box = row.querySelector('.donation-box');
    if (box) {
        let amount = parseInt(box.getAttribute('data-amount'));
        if (!isNaN(amount)) {
            donationRowAmounts.push(amount);
        } 
    }
});
    

    // Add the current row's amount to the list of row amounts.
    donationRowAmounts.push(amount);

    // Sort the array in ascending order
    donationRowAmounts.sort((a, b) => a - b);

    // Find the closest donation amount
    const closestAmount = donationRowAmounts.reduce((prev, curr) => Math.abs(curr - amount) < Math.abs(prev - amount) ? curr : prev);

    // Use closestAmount to get the closest row
    const rowIndex = 'row' + closestAmount / 1000;

       const boxFront = document.createElement('div');
		boxFront.className = 'donation-box-front';
		boxFront.setAttribute('data-row', rowIndex); 
		
        const boxBack = document.createElement('div');
        boxBack.className = 'donation-box-back';

        boxInner.appendChild(boxFront);
        boxInner.appendChild(boxBack);
        box.appendChild(boxInner);
        newRow.appendChild(box);
    }

    // Decide the position of this new row 
        if (position === 'above') {
            referenceElement.parentNode.insertBefore(newRow, referenceElement);
            referenceElement = newRow.nextSibling;
        } else {
            if (referenceElement.nextSibling) {
                referenceElement.parentNode.insertBefore(newRow, referenceElement.nextSibling);
                referenceElement = newRow;
            } else {
                referenceElement.parentNode.appendChild(newRow);
                referenceElement = newRow;
            }
        }

        if (!firstRow) {
            firstRow = newRow;
        }

        boxesToAdd -= boxesInThisRow;
    }

    if (firstRow) {  // If there's a first row, add the label to it
        const newRowLabel = document.createElement('div');
        newRowLabel.className = 'donation-row-label';
        newRowLabel.innerText = numBoxes + ' x ' + numberWithCommas(amount);
        newRowLabel.onclick = function() {
            editNumBoxes(this);
        };
        firstRow.insertBefore(newRowLabel, firstRow.firstChild);
    }
		resetRows();
		rowSpace();
  		updateTotalDonations();
	 	savePyramidHTML();
	}
		
function findClosestMatchingBoxesData(targetAmount, maxMatches = Infinity) {
    const donationBoxes = Array.from(document.querySelectorAll('.donation-box'));
    const rankedMatches = [];

    donationBoxes.forEach(box => {
        const boxFront = box.querySelector('.donation-box-front');
        const boxBack = box.querySelector('.donation-box-back');

        if (boxBack) {
            const boxAmount = parseInt(boxBack.innerText.replace(/[$,]/g, ''));
            const difference = Math.abs(boxAmount - targetAmount);

            rankedMatches.push({
                difference,
                box,
                boxFront,
                boxBack,
                boxAmount,
                originalRow: box.parentElement
            });
        }
    });

    // Sort the matches based on the difference to target amount
    rankedMatches.sort((a, b) => a.difference - b.difference);

    const matches = [];

    for (let i = 0; i < rankedMatches.length && matches.length < maxMatches; i++) {
        const match = rankedMatches[i];
        
        if (shouldMoveBox(match, targetAmount)) {
            const { box, boxFront, boxBack } = match;
            
            const clonedFront = boxFront.cloneNode(true);
            const clonedBack = boxBack.cloneNode(true);

            // ... The styling and cloning process ...

            clonedFront.style.color = "#fff";
            clonedFront.style.fontWeight = "500";
            clonedFront.style.textAlign = "center";
            clonedFront.style.display = "flex";
            clonedFront.style.justifyContent = "center";
            clonedFront.style.alignItems = "center";

            clonedBack.style.color = "#fff";
            clonedBack.style.fontWeight = "400";
            clonedBack.style.fontSize = "17px";
            clonedBack.style.textAlign = "center";
            clonedBack.style.display = "none"; // Hide the back initially

            const clonedDonationBoxInner = document.createElement('div');
            clonedDonationBoxInner.className = 'donation-box-inner';

            // Appending the cloned elements
            clonedDonationBoxInner.appendChild(clonedFront);
            clonedDonationBoxInner.appendChild(clonedBack);

            const clonedDonationBox = document.createElement('div');
            clonedDonationBox.className = 'donation-box';
            clonedDonationBox.appendChild(clonedDonationBoxInner); // Append inner box

            matches.push({
                box: clonedDonationBox,
                front: clonedFront,
                back: clonedBack,
                originalRow: match.originalRow
            });

            // Remove the original box after cloning its data
            box.parentElement.removeChild(box);
        }
    }

    return matches;
}

// Helper function to determine if the box should be moved or not
function shouldMoveBox(match, targetAmount) {
    const currentRowAmount = parseInt(match.box.getAttribute('data-amount'));
    const diffForCurrentRow = Math.abs(match.boxAmount - currentRowAmount);
    const diffForTargetRow = match.difference; // already calculated for the target row

    return diffForTargetRow < diffForCurrentRow;
}


 
		
function createDonationBox(amount, data) {
    const box = document.createElement('div');
    box.className = 'donation-box';
    box.setAttribute('data-amount', amount);

    const boxInner = document.createElement('div');
    boxInner.className = 'donation-box-inner';

    // Find the first unfilled donation-box in the newRow (assuming it's the last row in the DOM)
    const unfilledBox = Array.from(document.querySelectorAll('.donation-row:last-child .donation-box')).find(b => !b.hasChildNodes());

    if (unfilledBox) {
        const rowValue = unfilledBox.querySelector('.donation-box-front').getAttribute('data-row');
        data.front.setAttribute('data-row', rowValue);
    } else {
        // If no unfilled box is found, fall back to some default behavior
        data.front.setAttribute('data-row', 'row' + (amount / 1000));
    }

    boxInner.appendChild(data.front);
    boxInner.appendChild(data.back);
    box.appendChild(boxInner); 

    // Any other code for box creation...

    return box;
}



function createNewEmptyBox(amount) {
     const box = document.createElement('div');
        box.className = 'donation-box';
        box.setAttribute('data-amount', amount);

        const boxInner = document.createElement('div');
        boxInner.className = 'donation-box-inner';
// Get the rows and their amounts from the DOM
const rows = document.querySelectorAll('.donation-row');
let donationRowAmounts = [];

document.querySelectorAll('.donation-row').forEach(row => {
    let box = row.querySelector('.donation-box');
    if (box) {
        let amount = parseInt(box.getAttribute('data-amount'));
        if (!isNaN(amount)) {
            donationRowAmounts.push(amount);
        } 
    }
});
    

    // Add the current row's amount to the list of row amounts.
    donationRowAmounts.push(amount);

    // Sort the array in ascending order
    donationRowAmounts.sort((a, b) => a - b);


    // Find the closest donation amount
    const closestAmount = donationRowAmounts.reduce((prev, curr) => Math.abs(curr - amount) < Math.abs(prev - amount) ? curr : prev);

    // Use closestAmount to get the closest row
    const rowIndex = 'row' + closestAmount / 1000;

       const boxFront = document.createElement('div');
		boxFront.className = 'donation-box-front';
		boxFront.setAttribute('data-row', rowIndex); 
		
        const boxBack = document.createElement('div');
        boxBack.className = 'donation-box-back';

        boxInner.appendChild(boxFront);
        boxInner.appendChild(boxBack);
        box.appendChild(boxInner);

    return box;
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
	
		function base64Encode(str) {
    return btoa(unescape(encodeURIComponent(str)));
} 

function cleanPyramidHTML(htmlContent) {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = htmlContent;

    const boxes = tempDiv.querySelectorAll('.donation-box');
    boxes.forEach(box => {
        const frontElement = box.querySelector('.donation-box-front');
        const backElement = box.querySelector('.donation-box-back');

        // Apply the styling to clear the inner text and set the background color
        if (frontElement) {
            frontElement.innerText = '';
            frontElement.style.backgroundColor = "#d4d4d4";
        }
        if (backElement) {
            backElement.innerText = '';
            backElement.style.backgroundColor = "#d4d4d4";
        }
    });

    return tempDiv.innerHTML;
}
		
		
	
function savePyramidHTML() {
		
    const campaignId = <?php echo $campaign_id; ?>;


    const pyramidContainer = document.querySelector('.donation-pyramid');
if (pyramidContainer) {
        let htmlContent = pyramidContainer.innerHTML;
        htmlContent = cleanPyramidHTML(htmlContent); // Clean up the HTML content
        const encodedHtmlContent = base64Encode(htmlContent);

        // Prepare data for AJAX request
        const pyramidData = {
            action: 'save_pyramid_html',
            campaign_id: campaignId,
            html_content: encodedHtmlContent,
            nonce: pyramidNonce
        };

        // Send AJAX request
        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            },
            body: new URLSearchParams(pyramidData).toString()
        })
        .then(response => {
           
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            
            if (data.success) {
               
            } else {
                console.error('Error saving pyramid HTML:', data);
            }
        })
        .catch(error => {
            console.error('Error saving pyramid HTML:', error);
        });
    } else {
        console.error('Pyramid container element not found.');
    }
}

function base64Decode(str) {
    return decodeURIComponent(escape(atob(str)));
}
	
function loadPyramidHTML() {
    attachEventListeners();

    const campaignId = <?php echo $campaign_id; ?>;
    // Prepare data for AJAX request
    const pyramidData = {
        action: 'load_pyramid_html',
        campaign_id: campaignId,
        nonce: pyramidNonce
    };

    return new Promise((resolve) => {
        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            },
            body: new URLSearchParams(pyramidData).toString()
        })
        .then(response => {
   
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(response => {
  
            if (response.success && response.data) {
                const pyramidContainer = document.querySelector('.donation-pyramid'); // Adjust selector as needed
                if (pyramidContainer) {
                    pyramidContainer.innerHTML = base64Decode(response.data);
                    resolve('Pyramid HTML loaded successfully.');
                } else {
                    console.warn('Pyramid container element not found.');
                    resolve('Pyramid container element not found.');
                }
            } else {
                console.warn('Failed to load pyramid HTML:', response.data);
                resolve('Failed to load pyramid HTML.');
            }
        })
        .catch(error => {
            console.warn('Error loading pyramid HTML:', error);
            resolve('Error loading pyramid HTML.');
        });
    });
}
		
function attachEventListeners() {
    // Attach event delegation for the side buttons and donation-row-labels
    const pyramidContainer = document.querySelector('.donation-pyramid');

    if (pyramidContainer) {
        pyramidContainer.addEventListener('click', function(event) {
             if (event.target.classList.contains('donation-row-label') && !event.target.classList.contains('donation-row-label b')) {
                editNumBoxes(event.target); 
			}
        });
    }
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
		const amount = parseInt(globalRowLabel.innerText.split('$')[1].replace(/[^0-9.-]+/g, ''));
	let nextRow = row;
        while (nextRow && nextRow.querySelector(`.donation-box[data-amount="${amount}"]`)) {
            const tempRow = nextRow;
            nextRow = nextRow.nextElementSibling;
            tempRow.remove();
			savePyramidHTML();
        }
	document.getElementById('errorModule4').style.display = "none";
    updateTotalDonations();
    closeModal();
}

	function closeAlert() {
		document.getElementById('alertModule').style.display = "none";
	}
		
function calculateTotalDonations() {
    let totalDonations = 0;

    // Get all donation-box elements
    const donationBoxes = document.querySelectorAll('.donation-box');

    donationBoxes.forEach(box => {
        // Retrieve the data-amount value and add it to the total
        const amount = parseInt(box.getAttribute('data-amount'));
        if (!isNaN(amount)) {
            totalDonations += amount;
        }
    });

    return totalDonations;
}


		
	let savedSettings = {
  		setting1: false,  // Default value
  		//setting2: false,  // Default value
  		setting5: false,  // Default value
  		setting6: false,  // Default value
  		// ...add other settings here...
	};
		
	// open the settings menu
	function openSettingsMenu() {
	
		document.getElementById('setting1').checked = savedSettings.setting1;
  		//document.getElementById('setting2').checked = savedSettings.setting2;
  		document.getElementById('setting5').checked = savedSettings.setting5;
  		document.getElementById('setting6').checked = savedSettings.setting6;
	
  		const popupContainer = document.getElementById("settingsPopup");
  		popupContainer.style.display = "block";
  		updateSettings(); // Update the checkboxes based on the current settings
	}

	// close the settings menu
	function closeSettingsMenu() {
  		const popupContainer = document.getElementById("settingsPopup");
  		popupContainer.style.display = "none"; // Simply set the display to "none"
	}

	// toggle the settings menu
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
    		//setting2: document.getElementById('setting2').checked,
    		// Add more General settings here if needed
  		};

  		// Update the checkboxes based on the saved settings
  		document.getElementById('setting1').checked = generalSettings.setting1;
  		//document.getElementById('setting2').checked = generalSettings.setting2;
  		// Update other checkboxes as needed
	}
		
	/*function applyAnonymousSetting() {
    const generalSettings = {
        setting2: document.getElementById('setting2').checked,
    };
    
    if (generalSettings.setting2) {
        for (const input in lockedInputs) {
            if (lockedInputs.hasOwnProperty(input)) {
                input.value = ' ';
            }
        }
    } else {
        for (const input in lockedInputs) {
            if (lockedInputs.hasOwnProperty(input)) {
                input.value = lockedInputs[input]; // Restore the original displayName
            }
        }
    }
}*/

	function applyAppearanceSettings() {
    const appearanceSettings = {
      setting5: document.getElementById('setting5').checked,
      setting6: document.getElementById('setting6').checked,
      // Add more Appearance settings here if needed
    };
    // Setting5 (High Contrast Mode)
    if (appearanceSettings.setting5) {
        document.body.classList.add('high-contrast'); 
    } else {
        document.body.classList.remove('high-contrast');
    }

    // Setting6 (Large Text Mode)
    if (appearanceSettings.setting6) {
        document.body.classList.add('large-text');
    } else {
        document.body.classList.remove('large-text');
    }
}

	function saveSettings() {
		savedSettings.setting1 = document.getElementById('setting1').checked;
  		//savedSettings.setting2 = document.getElementById('setting2').checked;
  		savedSettings.setting5 = document.getElementById('setting5').checked;
  		savedSettings.setting6 = document.getElementById('setting6').checked;
	
  	const generalSettings = {
    	setting1: document.getElementById('setting1').checked,
    	//setting2: document.getElementById('setting2').checked,
    	// Add more General settings here if needed
  	};

  	const appearanceSettings = {
    	setting5: document.getElementById('setting5').checked,
    	setting6: document.getElementById('setting6').checked,
    	// Add more Appearance settings here if needed
  	};

  	// Implement the setting1 (Read-Only Mode)
	if (generalSettings.setting1) {
  	// Disable all buttons except for the tabbed menu tab buttons, the logout button, and buttons in settingsPopup
  		const allButtons = document.querySelectorAll('button');
  		allButtons.forEach(button => {
    	// Check if the button is not part of the tabbed menu, not the logout button, and not inside the settingsPopup
   		if (!button.closest('.tabbed-menu') && !button.closest('.logout-button') && !button.closest('#settingsPopup')) {
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

  	//nas/content/live/cramerassoclyAnonymousSetting();
  	applyAppearanceSettings();

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
		const saveButton = document.getElementById("settingSaveButton");
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
      	
			let numBoxes = parseInt(rowLabel.innerText.split(' ')[0]);

  		numBoxes -= 1;  
if (numBoxes == 0) {
    rowLabel.style.display = " none";  // Hide the label
} else {
    rowLabel.style.display = "";  // Ensure the label is visible
    rowLabel.innerText = numBoxes + ' x ' + rowLabel.innerText.split(' ')[2];
}

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
	createSlice("endowmentSlice", "endowmentTextName", "endowmentTextAmount", "#00758D", 0, slice1Proportion, slice1Value, slice1Amount);
	createSlice("capitalSlice", "capitalTextName", "capitalTextAmount", "#7866A1", slice1Proportion, slice1Proportion + slice2Proportion, 		slice2Value, slice2Amount);
	createSlice("operatingSlice", "operatingTextName", "operatingTextAmount", "#FF8C00", slice1Proportion + slice2Proportion, 1, slice3Value, slice3Amount);

// Function to create a path description for a pie chart slice
function createSlice(sliceId, textIdName, textIdAmount, fillColor, startProportion, endProportion, sliceName, sliceValue) {
    var radius = 11;
    var centerX = -8;  // was 21
    var centerY = -15;  // was 21

    // Handle the case where the slice is 100%
    if (startProportion === 0 && endProportion === 1) {
        var pathData = [
            "M", centerX, centerY,
            "m", -radius, 0,
            "a", radius, radius, 0, 1, 0, radius * 2, 0,
            "a", radius, radius, 0, 1, 0, -(radius * 2), 0
        ].join(" ");
        
        // Position text at the center of the full circle
        var textX = centerX - 15;
        var textY = centerY - 10;
        var percentX = centerX - 1;
        var percentY = centerY + 1;
        var slicePercentage = "100";
    } else {
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

        // Calculate text position outside the pie chart (near the slice)
        var midAngle = (startAngle + endAngle) / 2;
        var midRad = (midAngle - 75) * Math.PI / 180;
        var textRadius = radius + 6;  // Positioning text outside the pie chart
        var textX = centerX + textRadius * Math.cos(midRad);
        var textY = centerY + textRadius * Math.sin(midRad);
        // Calculate the position for the percentage inside the pie chart slice
        var percentRadius = radius / 2;  // Midway inside the slice
        var percentX = centerX + percentRadius * Math.cos(midRad);
        var percentY = centerY + percentRadius * Math.sin(midRad);
        var slicePercentage = ((endProportion - startProportion) * 100).toFixed(0);  // Convert proportion to percentage
    }

    // Update slice path and text
    document.getElementById(sliceId).setAttribute("d", pathData);
    document.getElementById(sliceId).setAttribute("fill", fillColor);
    document.getElementById(textIdName).textContent = sliceName;
    document.getElementById(textIdName).setAttribute("x", textX - 5);
    document.getElementById(textIdName).setAttribute("y", textY - 1);  // Adjusted Y position for name to be above amount
    document.getElementById(textIdAmount).textContent = numberWithCommas(sliceValue);
    document.getElementById(textIdAmount).setAttribute("x", textX - 5);
    document.getElementById(textIdAmount).setAttribute("y", textY + 1);  // Adjusted Y position for amount to be below name
    
    
}

// Function to create a path description for a pie chart slice
function createSlice1(sliceId, textIdName, textIdAmount, fillColor, startProportion, endProportion, sliceName, sliceValue) {
    var radius = 11;
    var centerX = -8;  // was 21
    var centerY = -15;  // was 21

    // Handle the case where the slice is 100%
    if (startProportion === 0 && endProportion === 1) {
        var pathData = [
            "M", centerX, centerY,
            "m", -radius, 0,
            "a", radius, radius, 0, 1, 0, radius * 2, 0,
            "a", radius, radius, 0, 1, 0, -(radius * 2), 0
        ].join(" ");
        
        // Position text at the center of the full circle
        var textX = centerX - 15;
        var textY = centerY - 10;
        var percentX = centerX - 1;
        var percentY = centerY + 1;
        var slicePercentage = "100";
    } else {
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

        // Calculate text position outside the pie chart (near the slice)
        var midAngle = (startAngle + endAngle) / 2;
        var midRad = (midAngle - 75) * Math.PI / 180;
        var textRadius = radius + 6;  // Positioning text outside the pie chart
        var textX = centerX + textRadius * Math.cos(midRad);
        var textY = centerY + textRadius * Math.sin(midRad);
        // Calculate the position for the percentage inside the pie chart slice
        var percentRadius = radius / 2;  // Midway inside the slice
        var percentX = centerX + percentRadius * Math.cos(midRad);
        var percentY = centerY + percentRadius * Math.sin(midRad);
        var slicePercentage = ((endProportion - startProportion) * 100).toFixed(0);  // Convert proportion to percentage
    }

    // Update slice path and text
    document.getElementById(sliceId).setAttribute("d", pathData);
    document.getElementById(sliceId).setAttribute("fill", fillColor);
    document.getElementById(textIdName).textContent = sliceName;
    document.getElementById(textIdName).setAttribute("x", textX - 5);
    document.getElementById(textIdName).setAttribute("y", textY - 1);  // Adjusted Y position for name to be above amount
    document.getElementById(textIdAmount).textContent = numberWithCommas(sliceValue);
    document.getElementById(textIdAmount).setAttribute("x", textX - 5);
    document.getElementById(textIdAmount).setAttribute("y", textY + 1);  // Adjusted Y position for amount to be below name
    
}

		
	function redirectToToolkitHome() {
    window.location.href = "https://www.cramerphilanthropy.com/campaign-toolkit-home/";
}
		// Function to close the custom alert
function closeAlert4() {
  const alertModule = document.getElementById("alertModuleMM");
  alertModule.style.display = "none";
}

function formatCurrency(amount) {
  return amount.toLocaleString('en-US', {
    style: 'currency',
    currency: 'USD',
  });
}
		
		function sortTable(header, columnIndex) {
    var table = document.getElementById("donation-table");
    var rows = Array.from(table.querySelectorAll("tbody tr"));
    var ascending = header.getAttribute("data-sort-asc") === "true"; // Check current sort direction

    // Sort rows based on the clicked column
    rows.sort(function(rowA, rowB) {
      var cellA = rowA.cells[columnIndex].textContent.trim();
      var cellB = rowB.cells[columnIndex].textContent.trim();

      // Handle currency sorting by removing dollar signs and commas
      if (columnIndex === 4) { // Assuming column index 4 is the "Amount" column
        cellA = parseFloat(cellA.replace(/[$,]/g, '')) || 0;  // Remove $ and , and convert to number
        cellB = parseFloat(cellB.replace(/[$,]/g, '')) || 0;
      } else {
        // Handle text sorting (lowercase for case-insensitive comparison)
        cellA = cellA.toLowerCase();
        cellB = cellB.toLowerCase();
      }

      if (ascending) {
        return cellA > cellB ? 1 : -1;
      } else {
        return cellA < cellB ? 1 : -1;
      }
    });

    // Re-append sorted rows in the correct order
    rows.forEach(function(row) {
      table.querySelector("tbody").appendChild(row);
    });

    // Reset all sort indicators, column highlights, and data highlights
    var allHeaders = table.querySelectorAll("th");
    allHeaders.forEach(function(th) {
      var sortIndicator = th.querySelector(".sort-indicator");
      if (sortIndicator) {
        sortIndicator.textContent = ''; // Clear the indicator
      }
      th.classList.remove("active-sort"); // Remove highlight from all headers
    });

    // Clear column highlighting from all table data cells
    var allCells = table.querySelectorAll("td");
    allCells.forEach(function(cell) {
      cell.classList.remove("active-column");
    });

    // Add sort indicator to the clicked header
    var sortIndicator = header.querySelector(".sort-indicator");
    if (ascending) {
      sortIndicator.textContent = ' ▲'; // Ascending indicator
    } else {
      sortIndicator.textContent = ' ▼'; // Descending indicator
    }

    // Highlight the clicked header
    header.classList.add("active-sort");

    // Highlight the entire sorted column
    rows.forEach(function(row) {
      row.cells[columnIndex].classList.add("active-column");
    });

    // Toggle sort direction for the next click
    header.setAttribute("data-sort-asc", !ascending);
  }

		
		// Function to handle the beforeunload event
function blockPageUnload(event) {
    event.preventDefault(); // Some browsers require this
    event.returnValue = ''; // Required for Chrome to show the confirmation dialog
}

		
	</script>
	<?php
	
// Define the HTML and CSS output
$output = '
<style>

/* Style to turn a specific row red */
tr.red-row td {
    background-color: #ffcccc !important; /* Light red background for the row */
    color: #b30000 !important; /* Darker red text for contrast */
}


/* High Contrast Mode */
.high-contrast {
    background-color: #000; /* Black background */
    color: #F78D2D; /* Orange text color */
    border-color: #F78D2D; /* Orange borders */
}

.high-contrast a {
    color: #77C4D5; /* Light Blue links for visibility */
    text-decoration: underline; /* Underline links to make them stand out more */
}

.high-contrast button,
.high-contrast input,
.high-contrast select,
.high-contrast textarea {
    background-color: #00758D; /* Teal background for form elements */
    color: #F78D2D; /* Orange text */
    border: 2px solid #F78D2D; /* Orange border for form elements */
}

.high-contrast button:hover,
.high-contrast input[type="button"]:hover,
.high-contrast input[type="submit"]:hover {
    background-color: #7866A1; /* Purple for hover states */
    color: #F78D2D;
}

.high-contrast button:focus,
.high-contrast input:focus,
.high-contrast select:focus,
.high-contrast textarea:focus {
    outline: 2px solid #77C4D5; /* Light Blue outline on focus for better visibility */
}

.high-contrast header,
.high-contrast footer,
.high-contrast nav {
    background-color: #00758D; /* Teal backgrounds for larger site sections */
}

.high-contrast h1, 
.high-contrast h2, 
.high-contrast h3,
.high-contrast h4, 
.high-contrast h5, 
.high-contrast h6 {
    color: #7866A1; /* Purple headings */
}


/* Large Text Mode */
.large-text {
    font-size: 20px; /* Base font size for larger text */
}

.large-text h1 {
    font-size: 2.5em; /* Adjust heading sizes based on new base size */
}

.large-text h2 {
    font-size: 2em;
}

.large-text h3 {
    font-size: 1.75em;
}

.large-text h4 {
    font-size: 1.5em;
}

.large-text h5 {
    font-size: 1.25em;
}

.large-text h6 {
    font-size: 1em;
}

.large-text button,
.large-text input,
.large-text select,
.large-text textarea {
    font-size: 20px; /* Keep form elements consistent with text size */
}

.modal {
    display: none;
    position: fixed;
    z-index: 10000000;
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
  position: flex;
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
  top: 180px;
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
  border-radius: 5px;
  margin-top: 8px;
  display: flex;
  justify-content: space-between;
}

.donation-meter .fill {
   transition: width 0.5s ease-out;
   border-radius: 5px;
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
  position: sticky;
  bottom: 10px;
  width: 100%;
  z-index: 100; /* Ensure it stays above other content */
}

.tabbed-menu ul {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center; /* Center the tabs horizontally */
}

.tabbed-menu li {
  margin-right: 5px;
  padding: 10px 15px;
  cursor: pointer;
  box-shadow: 0 2px 7px rgba(0, 0, 0, 0.3);
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

#moves-management table tr{
  position: relative;
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
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 30px; /* Adjust width as needed */
  height: 30px; /* Adjust height as needed */
  margin-right: 5px; /* Space between buttons */
}

.attach-download-container {
  display: flex;
  justify-content: center;
  align-items: center;
}


  .table-container {
    display: flex;
    justify-content: center;
   margin-top: 20px;
	
  }

.delete-button {  
    position: absolute;   /* Position the button absolutely within the row */
    left: -10px;            /* Adjust this to position it correctly */
    top: -5px;             /* Adjust this to position it correctly */
    background: #CACACA;
    border-radius: 50%;
    border: none;
    width: 18px;
    height: 18px;
    cursor: pointer;
    outline: none;
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.delete-button:hover {
    background: #B3B3B3;
    transition: background-color 0.3s;
    box-shadow: none;
}

.delete-button img {
    width: 50%;          /* Adjusted width for better appearance */
    height: auto;
    display: block;      /* Ensure the image is block level for proper centering */
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
   margin-bottom: 10px; //this value should be changing
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
  width: 50%;
  height: 50%;
  margin-left: 32%;
}

.donation-row {
  margin-top: 8px;
  display: flex;
  justify-content: space-between; /* This will distribute the spacing evenly */
}

.donation-row-label {
  position: absolute;
  left: -30px;
  margin-top: 17px;
  margin-left: 75px;
  font-size: 15px;
}

.donation-row:first-child .donation-box {
  margin-bottom: auto;
}

.donation-row:last-child .donation-box {
  display: flex; /* This line is crucial for enabling flexbox properties */
  justify-content: center; /* Center content horizontally */
  align-items: center; /* Center content vertically */
  font-size: 13px;
  margin-top: 7px;
  margin-bottom: 15px;
  width: 250px;
  height: 38px; /* Adjust as needed */
  border: none;
  background-color: #F3F3F3DB;
}

.donation-row:last-child .donation-label {

 top: 50px;
  
}

.donation-key {
  position: absolute;
  top: 35%;
  right: 35%;
}

  .donation-key-item {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
  }

  .donation-key-item .status-circle {
    display: inline-block;
    width: 20px;
    height: 20px;
    margin-right: 12px;
    border-radius: 10px;
	box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.1);
  }

  .donation-key-item .status-label {
    font-weight: normal;
	font-size: 75%;
  }

  .donation-key-item .pending {
    background-color: #7866A1;
  }

  .donation-key-item .identified {
    background-color: #77C4D5;
  }

  .donation-key-item .engaged {
    background-color: #00758D;
  }

  .donation-key-item .pledged {
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
  width: 6.5vw;  /* Adjusted to viewport width */
  height: 5.3vh;  /* Adjusted to viewport height */
  border-radius: 7px;
  margin: 7px 7px; /* Ensure consistent margin on all sides */
  overflow: hidden;
  cursor: pointer;
  justify-content: center;
  align-items: center;
  text-align: center;
}


/* If the screen size is below 600px, adjust the box size */
@media (max-width: 600px) {
  .donation-box {
    width: 15vw;  /* adjusted for smaller screens */
    height: 7vh;  /* adjusted for smaller screens */
   
  }
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

}

.donation-box-front {
  background-color: #d4d4d4;
  display: flex;
  border-radius: 7px;
  justify-content: center;
  align-items: center;
  font-weight: 500;
  text-align: center; 
  font-size: 18px;
  padding: 10px;
  color: #000;
}

.donation-box-back {
  background-color: #d4d4d4;
  border-radius: 7px;
  justify-content: center;
  align-items: center;
  font-weight: 500;
  text-align: center;
  font-size: 18px;
  padding: 10px;
  color: #fff;

  
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

.settings-popup .small-button {
  width: 100%; /* Remove full-width */
  padding: 5px 10px; /* Adjust padding for smaller size */
  font-size: 14px; /* Smaller font size */
  background-color:  #3D8898;
}

.settings-popup .small-button:hover {
  background-color: #FEA758;
}

/* Additional style for the settings button */
.settings-button {
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px;
  position: absolute;
  top: 180px;
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

.home-button {
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 10px;
  position: absolute;
  top: 180px;
  right: 210px;
  width: 41px; 
  height: 41px;
  background-color: #B2B2B2; /* Added background color to match the original style */
  color: white;
  cursor: pointer;
}

.home-button:hover {
	background-color: #707070;
	transition: background-color 0.3s;
}
 
  
tfoot tr {
    background-color: #DC9657; /* Dark grey background */
    color: #fff; /* White text */
    font-size: 0.9em; /* Slightly smaller font size */
    font-style: italic; /* Italicize text */
    height: 17px; /* Set row height */
    overflow: hidden; /* This ensures that the background color respects the border radius */
}

tfoot td {
    padding: 2px 10px; /* Adjusted padding for better appearance within the 20px height */
    line-height: 16px; /*justed line height to fit better within the given height */
}
/* Style for the required field asterisks */
.required-field {
  font-size: 11px; /* Adjust the font size as needed */
  vertical-align: super; /* Move the asterisk to a superscript position */
  margin-left: 1px; 
  color: rgb(226,118,118); 
}

.copyright-box{
  background-color: #F78D2D;
  border-radius: 10px;
  padding: 10px;
  width: 280px;
  position: absolute; 
  top: 175px;
  left: 30px;
  text-align: left;
  box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.1);
}

.copyright-box h2 {
  color: white;
  margin: 0;
  font-size: 14px;
  font-weight: bold;
}

.copyright-box p {
  color: white;
  margin: 0;
  font-size: 12px;
}
.button-container {
  margin-top: -200px;
  display: flex;
  flex-wrap: wrap; /* Allows buttons to wrap to the next line on smaller screens */
  justify-content: center; /* Centers buttons horizontally */
  gap: 20px; /* Adds space between the buttons */
  align-items: center; /* Centers buttons vertically */
}

.button-background {
  margin: 20px;
  padding: 20px;
  border-radius: 20px;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  width: 250px;
  background-color: white;
  height: 180px;
  position: relative; /* Allows absolute positioning inside */
  display: flex;
  flex-direction: column; /* Stack the image and button vertically */
  align-items: center; /* Center the image and button horizontally */
  justify-content: center; /* Center the image and button vertically */
}

.background-image {
  position: absolute;
  border-radius: 20px; /* Match the border-radius of the parent */
  width: 80%;
  height: 80%;
  object-fit: contain; /* Change to contain to fit the image inside without cropping */
  z-index: -1; /* Positions the image behind the button */
}

.navigation-button {
  background-color: transparent;
  border: none;
  padding: 15px 30px;
  font-size: 1.2em;
  cursor: pointer;
  position: absolute; /* Position the button absolutely within the parent */
  bottom: 0; /* Position the button at the bottom of the container */
  z-index: 0;
  margin-bottom: -20px; /* Overlap the button slightly on top of the image */
}


  /* Full page overlay styles */
  .full-page-overlay {
    position: absolute;
    width: 100%;
	top: 160px;
	left: 0;
    height: 100%; 
    background-color:#F1FCFE;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000; /* High z-index to cover everything else */
} 

.combined-total-bar {
  position: relative; /* Stick to the bottom of the viewport */
  bottom: 0; /* Align the bar at the bottom of the tab */
  width: 100%;
  z-index: 10; /* Make sure it sits above other content */
  box-shadow: 0 -2px 5px rgba(0,0,0,0.05); /* Optional: adds a slight shadow to the top of the totals bar */
}

.combined-total-table {
  width: 100%; /* Full width */
  margin: 0 auto; /* Center the table */
  border-collapse: collapse;
}

.combined-total-table tfoot {
  background-color: #77C4D5; /* Same color as other totals */
}

.combined-total-table td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: center;
  font-weight: bold; /* If you want the combined total to stand out */
}

/* Highlight column header when clicked */
  th.active-sort {
    background-color: #77C4D5 !important; /* Lighter blue for column header */
  }

  /* Highlight the entire sorted column */
  td.active-column {
    background-color: #f2f7ff !important; /* Darker blue for data cells */
  }

  /* Pointer cursor for clickable column headers */
  th {
    cursor: pointer;
  }

/* Styling for the sort indicators */
.sort-indicator {
  margin-left: 5px;
}

 
/* Light blue styling for top donor boxes */
.top-donor-box {
	box-shadow: 0 0 15px 4px #77C4D5;    
}
 

</style>

<div class="toolkit-container">

<!-- Full page overlay navigation menu -->
<div class="full-page-overlay" id="navigation-overlay">
  <div class="button-container">
    <div class="button-background" data-tab="donation-pyramid">
      <img src="https://i.imgur.com/10CJZqj.png" alt="Gift Pyramid" class="button-img">
      <button class="navigation-button tab-link" data-tab="donation-pyramid">Gift Pyramid</button>
    </div>
    <div class="button-background" data-tab="pledges-pending">
      <img src="https://i.imgur.com/wjgi1nu.png" alt="Pledges Pending" class="button-img">
      <button class="navigation-button tab-link" data-tab="pledges-pending">Pledges, Pending, Pipeline</button>
    </div>
    <div class="button-background" data-tab="moves-management">
      <img src="https://i.imgur.com/knEDbjo.png" alt="Relationship Action Plans" class="button-img">
      <button class="navigation-button tab-link" data-tab="moves-management">Relationship Action Plans</button>
    </div>
    <div class="button-background" data-tab="dashboard">
      <img src="https://i.imgur.com/Voj9MeM.png" alt="Dashboard" class="button-img">
      <button class="navigation-button tab-link" data-tab="dashboard">Dashboard</button>
    </div> 
  </div>
</div>

<div class="home-button" onclick="redirectToToolkitHome()">
<img src="https://icon-library.com/images/white-home-icon-png/white-home-icon-png-21.jpg" alt="Home" style="width: 20px; height: 20px; align-items: center;  justify-content: center; align-items: center;">
</div>

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
    
    <!-- Add more General settings here if needed -->
    
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

    <button class="small-button" id="closeButton">Close</button>
	<button id="settingSaveButton">Save</button>
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
	<p style="font-size: 14px;">Produced and Powered by:</p>
    <img src="https://i.imgur.com/lXDtYC1.png" alt="Cramer Logo" style="width: 100%; display: block; margin-top: 0px;">
  </div>
  
  
</div>
<div class="copyright-box">
<h2>Campaign Toolkit - PATENT PENDING</h2>
<p>Cramer & Associates, Inc., 2024</p>
</div> 

<div class="tabbed-container">

  <div class="tabbed-content">
	<div class="tab" id="donation-pyramid">
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
                            <th span class="display-name">Name</th>
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
		<!-- Combined Total Bar -->
    <div class="combined-total-bar">
        <table class="combined-total-table">
            <tfoot>
                <tr>
                    <td>Combined Total:</td>
                    <td class="combined-total-amount"></td>
                    
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="tab" id="moves-management">
  <h2>Relationship Action Plans</h2>
  <table id="donation-table">
    <thead>
      <tr>
        <th onclick="sortTable(this, 0)">Status <span class="sort-indicator"></span></th>
        <th onclick="sortTable(this, 1)">Type <span class="sort-indicator"></span></th>
        <th onclick="sortTable(this, 2)">
          <div>Full Name <span class="required-field">*</span><span class="sort-indicator"></span></div>
        </th>
        <th onclick="sortTable(this, 3)">Organization <span class="sort-indicator"></span></th>
        <th onclick="sortTable(this, 4)">
          <div>Amount <span class="required-field">*</span><span class="sort-indicator"></span></div>
        </th>
        <th onclick="sortTable(this, 5)">Next Step <span class="sort-indicator"></span></th>
        <th onclick="sortTable(this, 6)">Recent Involvement <span class="sort-indicator"></span></th>
        <th onclick="sortTable(this, 7)">Notes <span class="sort-indicator"></span></th>
        <th onclick="sortTable(this, 8)">Lead <span class="sort-indicator"></span></th>
        <th>Documents</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <!-- Add your table rows here -->
    </tbody>
  </table>
</div>

<div class="tab" id="dashboard">
	<h2>Campaign Dashboard</h2>
	<div class="fill" id="dashboard-html"></div>
</div>

<div class="tabbed-menu">
  <ul>
    <li class="tab-link" data-tab="donation-pyramid" onclick="activateTab()">Gift Pyramid</li>
    <li class="tab-link" data-tab="pledges-pending" onclick="activateTab()">Pledges, Pending, & Pipeline</li>
    <li class="tab-link" data-tab="moves-management" onclick="activateTab()">Relationship Action Plans</li>
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
  <label>Error: Maximum number of boxes in a new row is 32.</label>
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

<!-- Custom modal -->
<!--
<div id="alertModule1Retired" class="modalretired">
    <div class="modal-contentretired">
        <p>This row is already full. Would you like to add an additional donator box and automatically repopulate the Gift Pyramid?</p>
        <button id="confirmButtonRetired">Yes</button>
        <button id="cancelButtonRetired">No</button>
    </div>
</div>
-->

<!-- Custom modal -->
<div id="alertModule1" class="modal">
    <div class="modal-content">
        <p>This row is already full. Would you like to add an additional donator box and automatically repopulate the Gift Pyramid?</p>
        <button id="confirmButton">Yes</button>
        <button id="cancelButton">No</button>
    </div>
</div>

<div id="alertModuleMM" class="modal">
  <div class="modal-content">
    <label id="alertMessageMM"></label>
    <button onclick="closeAlert4()">Ok</button> 
  </div>
</div>

<div id="deleteModule" class="modal">
    <div class="modal-content">
        <p>Are you sure you would like to delete this row?</p>
        <button id="deleteConfirmButton">Yes</button>
        <button id="deleteCancelButton">Cancel</button>
  </div>
</div>
 

<div id="alertModuleTab" class="modal">
  <div class="modal-content">
  <label>Please finish editing in the Relationship Action Plans before navigating to another tab!</label>
	<button onclick="closeAlertTab()">Ok</button>
  </div>
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
