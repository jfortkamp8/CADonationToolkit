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
	ob_start();
	createDonationPyramid($goal);
	$donation_pyramid = ob_get_clean();

	ob_start();
	createDonationDashboard($goal);
	$donation_dashboard = ob_get_clean();

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
  		const remainingSpaceMM = donationContainer.getBoundingClientRect().bottom - movesManagementTable.getBoundingClientRect().bottom - 125;
  		const remainingSpacePP = donationContainer.getBoundingClientRect().bottom - movesManagementTable.getBoundingClientRect().bottom - 127;
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
  		const displayNameSelect = document.createElement("select");
  		displayNameSelect.innerHTML = `
  			<option value="org">Org</option>
  			<option value="first-last">Name</option>
  		`;
  		displayNameSelect.style.fontSize = "10px";
		cells[0].style.textAlign = "center";
		cells[0].style.verticalAlign = "middle";
		cells[0].appendChild(pledgePendingSelect);
		cells[1].style.textAlign = "center";
		cells[1].style.verticalAlign = "middle";
		cells[1].appendChild(displayNameSelect);
  		cells[0].width = '13%';
  		cells[1].width = '10.5%';
  		cells[2].width = '15%';
  		cells[3].width = '10%';
  		cells[4].width = '12%';
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

  		// Download button click event listener
  		downloadButton.addEventListener("click", function handleDownloadButtonClick() {
    		console.log("Download button clicked");
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
				pledgePendingSelect.disabled = false;
    			displayNameSelect.disabled = false;
      			inputs.forEach((input, index) => {
  					if (index !== 6) {
   						const value = cells[index + 2].innerHTML;
    					cells[index + 2].innerHTML = "";
    					cells[index + 2].appendChild(input);
    					input.value = value;
  					}
				});

      			const saveButton = document.createElement("button");
      			saveButton.className = "save-button";
      			saveButton.innerText = "Save";
      			cells[9].innerHTML = "";
      			cells[9].appendChild(saveButton);
      			cells[9].style.textAlign = "center";
      			cells[9].style.verticalAlign = "middle";

      			saveButton.addEventListener("click", handleSaveButtonClick);
    		});

    		pledgePendingSelect.disabled = true;
    		displayNameSelect.disabled = true;
			
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
		let displayName = "";
		if (displayNameSelect.value === "first-last") {
  			displayName = inputs[0].value;
		} else {
  			displayName = inputs[1].value;
		}
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


		//add box to pyramid
		const donationName = values[1];
		const donationAmount = parseInt(values[2].replace(/[^0-9.-]+/g,""));
		let donationColor = '';
		switch (pledgePendingValue) {
  			case 'pledge':
    			donationColor = '#7866A1';
    			break;
  			case 'pending':
    			donationColor = '#77C4D5';
    			break;
  			case 'engaged':
    			donationColor = '#00758D';
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

		// Create an array that maps donation amounts to row indexes
		const amountsToRows = {
  			5000: 0,
  			10000: 1,
  			25000: 2,
  			50000: 3,
  			75000: 4,
  			100000: 5,
  			250000: 6,
  			500000: 7,
  			1250000: 8,
  			2500000: 9,
  			5000000: 10,
		};

		// Find the row index for the donation
		const rowIndex = 'row' + amountsToRows[donationAmount];

		// Get the boxes in the correct row
		console.log("rowIndex:", rowIndex);
		const boxes = document.querySelectorAll('.donation-box[data-row="' + rowIndex + '"]');
		console.log(boxes);

		// Keep track of which boxes have already been filled in this row
		const filledBoxes = [];
		boxes.forEach((box, index) => {
  			if (box.innerHTML.trim() !== "") {
    			filledBoxes.push(index);
  			}
		});

		// Find the first empty box in the row
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

  			// Check if the display name has two words
  			const words = displayName.split(" ");
  			if (words.length === 2) {
    			displayName = words.join("<br>");
  			}

  			// Adjust font size if display name is too big for the box
  			const boxWidth = 80;
  			const boxHeight = 38; 

  			// Create a temporary span element to measure the text size
  			const span = document.createElement("span");
  			span.innerHTML = displayName;

  			// Append the temporary span to the document body
  			document.body.appendChild(span);

  			// Start with the initial font size and gradually reduce until it fits vertically
  			let fontSize = 18;

  			// Check if the text overflows the box vertically
  			while (span.offsetHeight > boxHeight) {
    			fontSize--; // Reduce the font size
    			span.style.fontSize = fontSize + "px";
  			}

  			// Adjust font size if the text overflows the box horizontally
	  		while (span.offsetWidth > boxWidth) {
    			fontSize--; // Reduce the font size
    			span.style.fontSize = fontSize + "px";
  			}

  			// Apply the final font size and padding
  			box.style.fontSize = fontSize + "px";
  			box.style.padding = "10px";

  			// Set the display name as the innerHTML of the box
  			box.innerHTML = displayName;

  			// Remove the temporary span from the document body
 			document.body.removeChild(span);
		}


    	if (pledgePendingValue === "pending") {
  			const tableBody = document.querySelector(".pending-table tbody");
  			tableBody.insertBefore(targetRow, tableBody.firstChild);
		}
		if (pledgePendingValue === "engaged") {
  			const tableBody = document.querySelector(".pipeline-table tbody");
  			tableBody.insertBefore(targetRow, tableBody.firstChild);tableBody
		} else if (pledgePendingValue === "pledge") {
  			const tableBody = document.querySelector(".pledges-table tbody");
  			tableBody.insertBefore(targetRow, tableBody.firstChild);
		}
		const donationCells = Array.from(document.querySelectorAll(".pledges-table tbody td:nth-child(2)"));
		const totalDonations = donationCells.reduce((acc, curr) => {
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
		meterText.innerHTML = `$${totalDonations.toLocaleString()} Raised of $${goal.toLocaleString()} Goal <span class="percent">◄ ${percent.toFixed()}% OF GOAL ►</span>`;
		}, 0);
	}
		
	document.addEventListener("DOMContentLoaded", function() {
  		const goal = <?php echo $goal; ?>;
  		const meterText = document.getElementById("donation-meter-text");
  		meterText.innerHTML = `$0 Raised of $${goal.toLocaleString()} Goal <span class="percent">◄ 0% OF GOAL ►</span>`;
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
	
	</script>
	<?php
	
// Define the HTML and CSS output
$output = '
<style>

	
.logout-button {
  margin-top: 0;
  position: absolute;
  top: 165px;
  right: 40px;
  display: flex;
  align-items: center;
  width: 100%;
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

#moves-management table {
  margin-left: 10px; /* Spacing from donation meter */
  border-collapse: collapse;
  margin-bottom: 150px; //this value should be changing
}

#moves-management table th {
  background-color: #00758D;
  font-size: 12px;
  color: white;
  padding: 7px;
  text-align: left;
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
  border-collapse: collapse;
   margin-bottom: 77px; //this value should be changing
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

.donation-box {
  width: 90px; /* adjust the width as needed */
  height: 38px; /* adjust the height as needed */
  border-radius: 25px;
  display: flex;
  box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.1);
  justify-content: center;
  align-items: center;
  background-color: #d4d4d4; /* adjust the background color as needed */
  margin: 7px; /* adjust the margin as needed */
}

.donation-row-label {
  position: absolute;
  left: 0;
  margin-top: 17px;
  margin-left: 75px;
  font-size: 13px;
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

</style>

<div class="logout-button">
	' . add_logout_button() . '
</div>

<div class="donation-container">
  <div class="donation-meter">
    <h2>Donation Meter</h2>
    <p id="donation-meter-text"></p>
    <div class="meter">
      <div class="fill" id="donation-meter-fill"></div>
    </div>
  </div>
  <div class="donation-buttons" style="text-align:center">
    <button id="add-donation-button" style="width:100%" onclick="addRow()">ADD NEW DONATION</button>
    <button id="pdf-button" style="width:100%" onclick="generatePDF()">SAVE AS PDF FILE</button>
  </div>
</div>


<div class="tabbed-container">
  <div class="tabbed-content">
	<div class="tab active" id="donation-pyramid">
      <h2>Donation Pyramid</h2>
      ' . $donation_pyramid . '
    </div>
    <div class="tab" id="pledges-pending">
      <h2>Pledges and Pending</h2>
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
		  	<th>Donation Status</th>
            <th>Display Name</th>
            <th>Full Name</th>
            <th>Organization</th>
            <th>Gift Request</th>
            <th>Next Step</th>
            <th>Recent Involvement</th>
            <th>Extra Notes</th>
			<th>Documents</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
<div class="tab" id="dashboard">
      <h2>Dashboard</h2>
	  ' . $donation_dashboard . '
    </div>

<div class="tabbed-menu">
  <ul>
    <li class="tab-link active" data-tab="donation-pyramid" onclick="activateTab()">Donation Pyramid</li>
    <li class="tab-link" data-tab="pledges-pending" onclick="activateTab()">Pledges, Pending, Pipeline</li>
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

