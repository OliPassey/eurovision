<!DOCTYPE html>
<html>
  <head>
    <title>Eurovision 2023 Voting</title>
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
	<img src="img/ESC2023_Ukraine_LIVERPOOL_RGB_White_600px.png" width="300">
    <h1>Eurovision 2023 Voting</h1>
    <form method="POST" action="submit_vote.php">
      <p>Enter your name: <input type="text" name="name"></p>
      <p>Please enter your votes and hit submit!</p>
	  <p>The check-box will tick to show a recorded vote, untick if you wish to remvoe this vote</p>
    </div> 
	  <table>
        <tr>

        </tr>
        <?php
          // Load the list of countries from a CSV file
          $countries = array();
          $csv = array_map('str_getcsv', file('countries.csv'));
          array_shift($csv);
          foreach ($csv as $row) {
              $countries[$row[1]] = array(
                'name' => $row[0],
                'code' => $row[1],
              );
          }

          // Display checkboxes, flags, and drop-down menus for each country
          $pointValues = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 10, 12);
		  foreach ($countries as $code => $data) {
			echo '<tr>';
			echo '<td><input type="checkbox" name="votes[]" value="' . $code . '"></td>';
			echo '<td><img src="img/esc_' . strtolower(str_replace(' ', '_', $data['name'])) . '.png" width="175"></td>';
			echo '<td><label>' . $data['name'] . '</label></td>';
			echo '<td><select name="points[' . $code . ']" onchange="selectCheckbox(this)">';
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
