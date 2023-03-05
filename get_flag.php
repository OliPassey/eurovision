<?php

$filetype = $_GET['filetype'];
$country_codes = array();

// Read the country codes from the CSV file
if (($handle = fopen("countries.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $country_codes[$data[0]] = $data[1];
    }
    fclose($handle);
}

// Download the flags for each country
foreach ($country_codes as $country_name => $country_code) {
    $url = "https://countryflagsapi.com/$filetype/$country_code";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $image = curl_exec($ch);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    // Save the flag to a file
    file_put_contents("$country_name.$filetype", $image);
}

?>
