


<!DOCTYPE html>
<html>
  <head>
    <title>Eurovision 2024 Voting</title>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="stylesheet" type="text/css" href="style.css?v=2">

    <script>
    var isFormConfirmed = false;

    function validateForm(event) {
    event.preventDefault(); // Always prevent default submission initially

    if (isFormConfirmed) {
        return true; // This allows the form to submit after confirmation
    }

    const form = event.currentTarget;
    const nameInput = form.querySelector('input[name="name"]').value; // Retrieve the value directly
    const countryDropdowns = form.querySelectorAll('select[name^="country"]');
    const usedCountryCodes = new Set();

    // Validate name input
    if (nameInput.trim() === '') {
        showMessage('Please enter your name.');
        return false;
    }

    // Validate unique country selection
    for (const select of countryDropdowns) {
        const countryCode = select.value;
        if (countryCode !== "") {
            if (usedCountryCodes.has(countryCode)) {
                showMessage('Each country can only be selected once. You have selected a country more than once.');
                return false;
            }
            usedCountryCodes.add(countryCode);
        }
    }

    // Validate that all countries have been selected
    if (usedCountryCodes.size !== countryDropdowns.length) {
        showMessage('Please vote for all countries.');
        return false;
    }

    // Call showMessage with nameInput, countryDropdowns, and messageContainer as parameters
    showMessage('Confirm your vote?', document.getElementById('message'), true, nameInput, countryDropdowns);

    // Log the values for troubleshooting
    console.log('Name:', nameInput);
    console.log('Selected countries:', countryDropdowns);
    console.log('Used CCs:', usedCountryCodes);

    return false; // Prevent default form submission
}


function showMessage(message, messageContainer, isConfirmation = false, nameInput, countryDropdowns) {
    // Log the values for troubleshooting
    console.log('Confirmation message:', message);
    console.log('Is confirmation:', isConfirmation);
    console.log('Name:', nameInput);
    console.log('Selected countries:', countryDropdowns);

    messageContainer.innerHTML = message; // Reset the initial message

    if (isConfirmation) {
        // Construct the votes summary
        let votesSummary = '<h3>Your Votes:</h3>';
        votesSummary += `<p><strong>Voter Name:</strong> ${nameInput}</p>`; // Use nameInput.value instead of nameInput
        votesSummary += '<ul>';

        Array.from(countryDropdowns).forEach(dropdown => {
            if (dropdown.value) {
                const points = dropdown.getAttribute('data-point-value');
                const selectedOption = dropdown.options[dropdown.selectedIndex];
                const artist = selectedOption.getAttribute('data-artist');
                const song = selectedOption.getAttribute('data-song');
                const countryName = selectedOption.text.split(' - ')[0]; // Make sure this split logic matches the actual text format

                votesSummary += `<li>${points} points to ${countryName} (Artist: ${artist}, Song: ${song})</li>`;
            }
        });

        votesSummary += '</ul>';

        // Append the votes summary to the message container
        messageContainer.innerHTML += votesSummary;

        // Log the constructed vote summary
        console.log('Vote summary:', votesSummary);
    }

    // Display the overlay and conditional confirmation button
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('confirmButton').style.display = isConfirmation ? 'inline-block' : 'none';
}

function hideOverlay() {
    document.getElementById('overlay').style.display = 'none';
}

function submitForm() {
    isFormConfirmed = true;
    document.querySelector('form').submit();
}

function displaySongInfo(selectElement) {
    const selectedIndex = selectElement.selectedIndex;
    const countryCode = selectElement.value;
    const pointValue = selectElement.getAttribute('data-point-value');
    const artist = selectElement.options[selectedIndex].getAttribute('data-artist');
    const song = selectElement.options[selectedIndex].getAttribute('data-song');
    const songInfoElement = document.getElementById('song-info-' + pointValue);
    const allCountryDropdowns = document.getElementsByName('country[]');

    // Check if the country has already been voted for in another point value
    for (let i = 0; i < allCountryDropdowns.length; i++) {
        const otherPointValue = allCountryDropdowns[i].getAttribute('data-point-value');
        if (otherPointValue != pointValue && allCountryDropdowns[i].value === countryCode) {
            alert('You have already voted for this country.');
            selectElement.selectedIndex = 0;
            songInfoElement.innerHTML = '';
            return;
        }
    }

    // Update the song info display
    if (artist && song) {
        songInfoElement.innerHTML = '<strong>Artist:</strong> ' + artist + '<br><strong>Song:</strong> ' + song;
    } else {
        songInfoElement.innerHTML = '';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    console.log('DOM fully loaded and parsed');

    const countryDropdowns = document.querySelectorAll('select[name^="country"]');
    for (const select of countryDropdowns) {
        select.addEventListener('change', function() {
            displaySongInfo(this, this.name.replace('country[','').replace(']',''));
        });
    }
});


    </script>
  <!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//analytics.ptslondon.co.uk/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '5']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->
  </head>
  <body>
    <div class="center">
    <img src="img/esc_sweden_malmo_rgb_white.png" class="eurovision-logo"><br>
    <a href="view_results.php">Show me the results!</a>
      <form method="POST" action="submit_vote.php" onsubmit="return validateForm(event);">
        <p>Enter your name: <input type="text" name="name"></p>
        <p>Please enter your votes and hit submit!</p>
        <p>You must select a country for every row before submitting</p>
        <p>Note the top row is 12 points, ie your winner!</p>
    </div>
        <table class="voting-table">
          <tr>
          </tr>
          <?php
            // Load the list of countries and songs from a CSV file
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

            // Load the config file
            $config = json_decode(file_get_contents('config.json'), true);
            $pointValues = array_filter($config['pointValues'], function($value) {
              return $value !== '0';
            });
            echo '<tr>';
            echo '<th class="col-points-header">Points</th>';            
            echo '<th class="col-country-header">Country</th>';
            echo '<th class="col-song-header">Song</th>';
            echo '</tr>';
            // Display point values and dropdown menus for countries
            foreach ($pointValues as $value) {

                echo '<tr>';
                echo '<td class="col-points">' . $value . '</td>';
                echo '<td class="col-countries"><select name="country[]" onchange="displaySongInfo(this, ' . $value . ')" data-point-value="' . $value . '">';
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
    <div class="center">
      <input type="submit" value="Vote!">
    </div>
  </form>
  <div id="overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
    <div style="background:#6e2cb0; margin:10% auto; padding:20px; width:80%; max-width:400px;">
        <div id="message"></div>
        <button id="confirmButton" onclick="submitForm()">Confirm</button>
        <button onclick="hideOverlay()">Cancel</button>
    </div>
</div>


</body>
</html>
