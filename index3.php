<!DOCTYPE html>
<html>
  <head>
    <title>Eurovision 2023 Voting</title>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link rel="stylesheet" type="text/css" href="style.css?v=2">

    <script>
    function validateForm(event) {
        const form = event.currentTarget;
        const nameInput = form.querySelector('input[name="name"]');
        const countryDropdowns = form.querySelectorAll('select[name^="country"]');
        const usedCountryCodes = new Set();

        // Validate name input
        if (nameInput.value.trim() === '') {
        alert('Please enter your name.');
        event.preventDefault();
        return false;
        }

        // Validate unique country selection
        for (const select of countryDropdowns) {
        const countryCode = select.value;
        if (countryCode !== "") {
            if (usedCountryCodes.has(countryCode)) {
            alert('Each country can only be selected once. You have selected a country more than once.');
            event.preventDefault();
            return false;
            }
            usedCountryCodes.add(countryCode);
        }
        }

        // Validate that all countries have been selected
        if (usedCountryCodes.size !== countryDropdowns.length) {
        alert('Please vote for all countries.');
        event.preventDefault();
        return false;
        }

        return true;
    }


    function displaySongInfo(selectElement, pointValue) {
        const selectedIndex = selectElement.selectedIndex;
        const artist = selectElement.options[selectedIndex].getAttribute('data-artist');
        const song = selectElement.options[selectedIndex].getAttribute('data-song');
        const songInfoElement = document.getElementById('song-info-' + pointValue);

        const selectedCountry = selectElement.value;
        const allCountryDropdowns = document.getElementsByName('country[]');

        for (let i = 0; i < allCountryDropdowns.length; i++) {
            if (allCountryDropdowns[i] !== selectElement && allCountryDropdowns[i].value === selectedCountry) {
            alert('You have already voted for this country.');
            selectElement.selectedIndex = 0;
            songInfoElement.innerHTML = '';
            return;
            }
        }

        if (artist && song) {
            songInfoElement.innerHTML = '<strong>Artist:</strong> ' + artist + '<br><strong>Song:</strong> ' + song;
        } else {
            songInfoElement.innerHTML = '';
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        const countryDropdowns = document.querySelectorAll('select[name^="country"]');
        for (const select of countryDropdowns) {
            select.addEventListener('change', function() {
                displaySongInfo(this, this.name.replace('country[','').replace(']',''));
            });
        }
    });

    </script>

  </head>
  <body>
    <div class="center">
    <img src="img/ESC2023_Ukraine_LIVERPOOL_RGB_White_600px.png" class="eurovision-logo">
    
      <form method="POST" action="submit_vote.php" onsubmit="return validateForm(event);">
        <p>Enter your name: <input type="text" name="name"></p>
        <p>Please enter your votes and hit submit!</p>
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

            // Display point values and dropdown menus for countries
            foreach ($pointValues as $value) {
                echo '<tr>';
                echo '<td class="col-points">' . $value . '</td>';
                echo '<td class="col-countries"><select name="country[' . $value . ']" onchange="displaySongInfo(this, ' . $value . ')">';
                echo '<option value="">Select a country</option>';
                foreach ($countries as $code => $data) {
                    echo '<option value="' . $code . '" data-artist="' . $data['artist'] . '" data-song="' . $data['song'] . '">' . $data['name'] . '</option>';
                }
                echo '</select></td>';
                echo '<td class="col-song-info" id="song-info-' . $value . '"></td>';
                echo '</tr>';            
            }
            

      ?>
    </table>
    <div class="center">
      <input type="submit" value="Submit your votes">
    </div>
  </form>
</body>
</html>
