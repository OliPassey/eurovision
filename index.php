<!DOCTYPE html>
<html>
  <head>
    <title>Eurovision 2023 Voting</title>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

	<link rel="stylesheet" href="style.css">
    <script>
      function selectCheckbox(select) {
        var checkbox = select.parentElement.parentElement.querySelector('input[type=checkbox]');
        checkbox.checked = true;
      }
    </script>
  </head>
  <body>
  	<div class="center">
	<img src="img/ESC2023_Ukraine_LIVERPOOL_RGB_White_600px.png" width="85%">
    
    <form method="POST" action="submit_vote.php">
      <p>Enter your name: <input type="text" name="name"></p>
      <p>Please enter your votes and hit submit!</p>
	  <p>The check-box will tick to show a recorded vote, untick if you wish to remove this vote</p>
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

          // Display checkboxes, flags, and drop-down menus for each country
          $pointValues = $config['pointValues'];
          foreach ($countries as $code => $data) {
            echo '<tr>';
            echo '<td class="col-checkbox"><input type="checkbox" name="votes[]" value="' . $code . '"></td>';
            echo '<td class="col-flag"><div class="flag-container"><img src="img/esc_' . strtolower(str_replace(' ', '_', $data['name'])) . '.png" class="flag"></div></td>';
            echo '<td class="col-country"><label>' . $data['name'] . '</label></td>';
            echo '<td class="col-song-info"><label>Artist: ' . $data['artist'] . '</label><br><label>Song: ' . $data['song'] . '</label></td>';
            echo '<td class="col-points"><select name="points[' . $code . ']" onchange="selectCheckbox(this)">';
            foreach ($pointValues as $value) {
              echo '<option value="' . $value . '">' . $value . '</option>';
            }
            echo '</select></td>';
            echo '</tr>';
          }          
        ?>
      </table>
      <p><input type="submit" value="Submit Vote"></p>
    </form>
  </body>
</html>