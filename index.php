<?php

$countries = array();
$csv = array_map('str_getcsv', file('songs.csv'));
array_shift($csv);
foreach ($csv as $row) {
    $countries[$row[1]] = array(
      'name' => $row[0],
      'code' => $row[1],
      'artist' => $row[2],
      'song' => $row[3],
    );
}

// Initialize $pointValues to ensure it has a default value
$pointValues = [];

// Attempt to load and decode the config file
$configFilePath = 'config.json'; // Ensure this path is correct
if (file_exists($configFilePath)) {
    $configContent = file_get_contents($configFilePath);
    $config = json_decode($configContent, true);

    // Check if 'pointValues' exists and is an array
    if (isset($config['pointValues']) && is_array($config['pointValues'])) {
        $pointValues = array_filter($config['pointValues'], function($value) {
            return $value !== '0';
        });
    } else {
        // Handle the case where 'pointValues' is not set or not an array
        echo "Warning: 'pointValues' is not set correctly in config.json.";
    }
} else {
    // Handle the case where config.json does not exist or cannot be read
    echo "Warning: config.json file not found or is not readable.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Eurovision 2024 Voting</title>
    <link rel="stylesheet" type="text/css" href="style.css?v=2">
</head>
<body>
    <div class="center">
        <img src="img/esc_sweden_malmo_rgb_white.png" class="eurovision-logo"><br>
        <a href="view_results.php">Show me the results!</a>
        <form id="votingForm" method="POST" action="submit_vote.php">
            <p>Enter your name: <input type="text" name="name" required></p>
            <p>Please enter your votes and hit submit!</p>
            <p>You must select a country for every row before submitting</p>
            <p>Note the top row is 12 points, i.e., your winner!</p>
            <table class="voting-table">
                <?php
                    echo '<tr>';
                    echo '<th class="col-points-header">Points</th>';            
                    echo '<th class="col-country-header">Country</th>';
                    echo '<th class="col-song-header">Song Info</th>';
                    echo '</tr>';
                    foreach ($pointValues as $value) {
                        echo '<tr>';
                        echo '<td class="col-points">' . $value . '</td>';
                        echo '<td class="col-countries"><select name="country[]" onchange="displaySongInfo(this)" data-point-value="' . $value . '">';
                        echo '<option value="">Select a country</option>';
                        foreach ($countries as $code => $data) {
                            echo '<option value="' . $code . '" data-artist="' . $data['artist'] . '" data-song="' . $data['song'] . '">' . $data['name'] . ' - ' . $data['song'] . '</option>';
                        }              
                        echo '</select></td>';
                        echo '<td class="col-song-info" id="song-info-' . $value . '"></td>';
                        echo '</tr>';            
                    }
                ?>
            </table>
            <button onclick="validateForm()">Vote!</button>
    </div>
    <div id="overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div style="background:#6e2cb0; margin:10% auto; padding:20px; width:80%; max-width:400px;">
            <div id="message"></div>
            <button id="confirmButton" onclick="submitForm()">Confirm</button>
            <button onclick="hideOverlay()">Cancel</button>
        </div>
    </div>
    <script>
        var isFormConfirmed = false; // Flag to check if form confirmation is done

        function validateForm() {
            const form = document.getElementById('votingForm');
            const nameInput = form.querySelector('input[name="name"]').value.trim();
            const countryDropdowns = form.querySelectorAll('select[name^="country"]');
            const usedCountryCodes = new Set();
            let hasEmpty = false;

            if (nameInput === '') {
                alert('Please enter your name.');
                return false; // Stop the form submission
            }

            // Check for unselected countries and duplicate selections
            for (let select of countryDropdowns) {
                const countryCode = select.value;
                if (countryCode === "") {
                    hasEmpty = true; // Set flag if any dropdown is unselected
                } else {
                    if (usedCountryCodes.has(countryCode)) {
                        alert('Each country can only be selected once. You have selected a country more than once.');
                        return false; // This stops the form submission early
                    }
                    usedCountryCodes.add(countryCode);
                }
            }

            if (hasEmpty) {
                alert('Please vote for all countries.');
                return false; // This stops the form submission early if any country is not selected
            }

            if (!isFormConfirmed) {
                // Only show the overlay if form is not yet confirmed
                showMessage('Confirm your vote?', true, nameInput, countryDropdowns);
                return false; // Prevent form submission if not confirmed
            }

            return true; // Proceed to submit if already confirmed
        }

        function showMessage(message, isConfirmation = false, nameInput, countryDropdowns) {
            const messageContainer = document.getElementById('message');
            messageContainer.innerHTML = message;

            if (isConfirmation) {
                let votesSummary = '<h3>Your Votes:</h3><ul>';
                countryDropdowns.forEach(dropdown => {
                    const points = dropdown.getAttribute('data-point-value');
                    const selectedOption = dropdown.options[dropdown.selectedIndex];
                    const artist = selectedOption.getAttribute('data-artist');
                    const song = selectedOption.getAttribute('data-song');
                    const countryName = selectedOption.text.split(' - ')[0];

                    votesSummary += `<li>${points} points to ${countryName}</li>`;
                });

                votesSummary += '</ul>';
                messageContainer.innerHTML += votesSummary;
            }

            document.getElementById('overlay').style.display = 'block';
            document.getElementById('confirmButton').style.display = isConfirmation ? 'inline-block' : 'none';
        }

        function hideOverlay() {
            document.getElementById('overlay').style.display = 'none';
            isFormConfirmed = false; // Reset confirmation flag
        }

        function submitForm() {
            isFormConfirmed = true; // Set flag to true indicating confirmation
            document.getElementById('votingForm').submit();
        }

        document.getElementById('votingForm').onsubmit = function(event) {
            if (!validateForm()) {
                event.preventDefault(); // Prevent form submission
            }
        };

        function displaySongInfo(selectElement) {
            const selectedIndex = selectElement.selectedIndex;
            const countryCode = selectElement.value;
            const pointValue = selectElement.getAttribute('data-point-value');
            const artist = selectElement.options[selectedIndex].getAttribute('data-artist');
            const song = selectElement.options[selectedIndex].getAttribute('data-song');
            const songInfoElement = document.getElementById('song-info-' + pointValue);
            const allCountryDropdowns = document.getElementsByName('country[]');

            // Clear previous selections if the same country is selected elsewhere
            for (let i = 0; i < allCountryDropdowns.length; i++) {
                if (allCountryDropdowns[i] !== selectElement && allCountryDropdowns[i].value === countryCode) {
                    alert('You have already voted for this country.');
                    selectElement.selectedIndex = 0;
                    songInfoElement.innerHTML = '';
                    return;
                }
            }

            // Update the song info display
            songInfoElement.innerHTML = artist && song ? '<strong>Artist:</strong> ' + artist + '<br><strong>Song:</strong> ' + song : '';
        }
    </script>



</body>
</html>
