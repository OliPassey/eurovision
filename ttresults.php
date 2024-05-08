<script>
function startTime() {
  offset = 0; //add an hour for GMT British SUmmertime
  var today = new Date();
  var h = today.getUTCHours();
  
  //United Kingdom British Summertime Adjustment 1 Hour Ahead
  if (h+offset == 24) h = -1;

  var m = today.getUTCMinutes();
  var s = today.getUTCSeconds();
  h = h + offset;
  if (h > 24) {
    h = h - 24;
  }
  if (h < 0) {
    h = h + 24;
  }
  h = checkTime(h);
  m = checkTime(m);
  s = checkTime(s);
  document.getElementById('clock').innerHTML = h + ":" + m + ":" + s;
  var t = setTimeout(function() {
    startTime()
  }, 500);
}

function checkTime(i) {
  if (i < 10) {
    i = "0" + i
  };
  return i;
}

function loadResults() {
  fetch('results.json')  // Adjust path to your results.json
    .then(response => response.json())
    .then(data => displayResults(data))
    .catch(error => console.error('Error loading the results:', error));
}

function displayResults(results) {
    const resultsPerPage = 10;
    let currentPage = 0;
    const totalPages = Math.ceil(results.length / resultsPerPage);

    function renderPage(page) {
        const leaderboard = document.getElementById('leaderboard');
        leaderboard.innerHTML = '<p class="result-header">Pos&nbsp;Pts&nbsp;Country</p>'; // Header for clarity
        const start = page * resultsPerPage;
        const end = start + resultsPerPage;
        const pageResults = results.slice(start, end);

        pageResults.forEach((result, index) => {
            leaderboard.innerHTML += `
            <div class="result-row">
                <span class="position">${start + index + 1}</span>
                <span class="points">&nbsp;&nbsp;&nbsp;${result.points}</span>
                <span class="country-name">&nbsp;&nbsp;&nbsp;&nbsp;${result.country}</span><br>
            </div>`;
        });
    }

    function nextPage() {
        currentPage = (currentPage + 1) % totalPages;
        renderPage(currentPage);
    }

    renderPage(0); // Initially render the first page
    setInterval(nextPage, 5000); // Change page every 5 seconds
}



document.addEventListener('DOMContentLoaded', function() {
  startTime();
  loadResults();
});
</script>
<head>
    <title>Eurovision 2024 Results</title>
    <link rel="stylesheet" type="text/css" href="ttstyles.css?v=2">
</head>

<div class="mycontainer">
  <p>&nbsp;&nbsp;CEEFAX 1 324 Sat 11 May 2024<div id="clock"></div></p>
  <img class="bbc" src="https://assets.codepen.io/439415/bbc2.png" alt="" />
  <p class="yellow"><span class="downabit">&nbsp;EUROVISION SONG CONTEST 2024 RESULTS</span></p><br><br>
  <div id="leaderboard"></div>
  <p>&nbsp;</p>
</div>
