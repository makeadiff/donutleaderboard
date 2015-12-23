<?php
require './common.php';

// Users to Ignore
$checks = array();
$ig_users = $sql->getCol('SELECT user_id FROM users_leaderboard_ignore');
if($ig_users) $checks[] = "D.fundraiser_id NOT IN (" . implode(',',$ig_users) . ")";

include("_city_filter.php");
$filter = "WHERE 1 AND " . implode(" AND ", $checks);

$last_amount = 0;
$amount_count = array('100' => 0, '500' => 0, '1000' => 0, '2000' => 0, '5000' => 0, '10000' => 0, '50000' => 0);

foreach ($amount_count as $amount => $count) {
	$amount_count[$amount] = $sql->getOne("SELECT COUNT(*) as count FROM donations D
		INNER JOIN users ON D.fundraiser_id=users.id
		$filter AND donation_amount > $last_amount AND donation_amount <= $amount");
	$amount_count[$amount] += $sql->getOne("SELECT COUNT(*) as count FROM external_donations D
		INNER JOIN users ON D.fundraiser_id=users.id
		$filter AND  amount > $last_amount AND amount <= $amount");
	$last_amount = $amount;
}

$cities = $sql->getById("SELECT cities.id, cities.name AS name, SUM(D.donation_amount) AS amount, COUNT(D.donation_amount) AS count
		FROM donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.city_id
		ORDER BY amount DESC");
$cities_external = $sql->getById("SELECT cities.id, cities.name AS name, SUM(D.amount) AS amount, COUNT(D.amount) AS count
		FROM external_donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.city_id
		ORDER BY amount DESC");
$total_amount = array('amount' => 0, 'count' => 0);
foreach ($cities as $city_id => $value) {
	if(!isset($cities_external[$city_id])) continue;

	$cities[$city_id]['amount'] += $cities_external[$city_id]['amount'];
	$cities[$city_id]['count'] += $cities_external[$city_id]['count'];
}
foreach ($cities as $city_data) {
	$total_amount['amount'] += $city_data['amount'];
	$total_amount['count'] += $city_data['count'];
}


$total_donors = $sql->getOne("SELECT COUNT(D.donation_amount) AS count
		FROM donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter");
$total_donors += $sql->getOne("SELECT COUNT(D.amount) AS count
		FROM external_donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter");


$fundraisers_amount = $sql->getById("SELECT users.id, users.first_name AS first_name, users.last_name AS last_name,
		SUM(D.donation_amount) AS amount, COUNT(D.donation_amount) AS count,
		cities.name as city_name
		FROM donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.id
		ORDER BY SUM(D.donation_amount) DESC
		LIMIT 25");
$fundraisers_amount_external = $sql->getById("SELECT users.id, users.first_name AS first_name, users.last_name AS last_name,
		SUM(D.amount) AS amount, COUNT(D.amount) AS count,
		cities.name as city_name
		FROM external_donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.id
		ORDER BY SUM(D.amount) DESC
		LIMIT 25");
	
$fundraisers_count = $sql->getById("SELECT users.id, users.first_name AS first_name, users.last_name AS last_name,
		SUM(D.donation_amount) AS amount, COUNT(D.donation_amount) AS count,
		cities.name as city_name
		FROM donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.id
		ORDER BY COUNT(D.donation_amount) DESC
		LIMIT 25");
$fundraisers_count_external = $sql->getById("SELECT users.id, users.first_name AS first_name, users.last_name AS last_name,
		SUM(D.amount) AS amount, COUNT(D.amount) AS count,
		cities.name as city_name
		FROM external_donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.id
		ORDER BY COUNT(D.amount) DESC
		LIMIT 25");

foreach ($fundraisers_amount as $user_id => $value) {
	if(isset($fundraisers_amount_external[$user_id])) {
		$fundraisers_amount[$user_id]['amount'] += $fundraisers_amount_external[$user_id]['amount'];
		$fundraisers_amount[$user_id]['count'] += $fundraisers_amount_external[$user_id]['count'];
	}

	if(isset($fundraisers_count_external[$user_id])) {
		$fundraisers_count[$user_id]['amount'] += $fundraisers_count_external[$user_id]['amount'];
		$fundraisers_count[$user_id]['count'] += $fundraisers_count_external[$user_id]['count'];
	}
} 

		
$regions = $sql->getById("SELECT states.id, states.name AS name, SUM(D.donation_amount) AS amount, COUNT(D.donation_amount) AS count
		FROM donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		INNER JOIN states ON cities.state_id = states.id 
		$filter
		GROUP BY states.id
		ORDER BY amount DESC");
$regions_external = $sql->getById("SELECT states.id, states.name AS name, SUM(D.amount) AS amount, COUNT(D.amount) AS count
		FROM external_donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		INNER JOIN states ON cities.state_id = states.id 
		$filter
		GROUP BY states.id
		ORDER BY amount DESC");
foreach ($regions as $state_id => $value) {
	if(!isset($regions_external[$state_id])) continue;
	$regions[$state_id]['amount'] += $regions_external[$state_id]['amount'];
	$regions[$state_id]['count'] += $regions_external[$state_id]['count'];
}


$donut_users = $sql->getAssoc("SELECT COUNT(DISTINCT users.id) AS count
		FROM donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		$filter");


$user_amount_count = array('100' => 0, '12000' => 0, '100000' => 0);
$last_user_amount = 0;
foreach ($user_amount_count as $amount => $count) {
	$user_amount_count[$amount] = $sql->getOne("SELECT COUNT(users.id) AS count
		FROM donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		$filter
		GROUP BY users.id
		HAVING SUM(D.donation_amount) > $last_user_amount AND SUM(D.donation_amount) <= $amount");
	$user_amount_count[$amount] += $sql->getOne("SELECT COUNT(D.id) AS count
		FROM external_donations D
		INNER JOIN users ON D.fundraiser_id = users.id
		$filter
		GROUP BY users.id
		HAVING SUM(D.amount) > $last_user_amount AND SUM(D.amount) <= $amount");

	$last_user_amount = $amount;
}
$user_with_more_than_10_donations = $sql->getOne("SELECT COUNT(users.id) AS count
		FROM donations D
		INNER JOIN users ON users.id=D.fundraiser_id
		$filter
		GROUP BY D.fundraiser_id
		HAVING COUNT(D.id) >= 10");
$user_with_more_than_10_ext_donations = $sql->getOne("SELECT COUNT(users.id) AS count
		FROM external_donations D
		INNER JOIN users ON users.id=D.fundraiser_id
		$filter
		GROUP BY D.fundraiser_id
		HAVING COUNT(D.id) >= 10");
$user_with_more_than_10_donations += $user_with_more_than_10_ext_donations;

$amount_per_day = $sql->getById("SELECT DATE_FORMAT(D.created_at, '%Y-%m-%d') AS day, SUM(donation_amount) as sum 
		FROM donations D
		INNER JOIN users ON users.id=D.fundraiser_id
		$filter AND D.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) 
		GROUP BY day");
$amount_per_day_external = $sql->getById("SELECT DATE_FORMAT(D.created_at, '%Y-%m-%d') AS day, SUM(amount) as sum 
		FROM external_donations D
		INNER JOIN users ON users.id=D.fundraiser_id
		$filter AND D.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY) 
		GROUP BY day");
foreach ($amount_per_day as $day => $sum) {
	if(!isset($amount_per_day_external[$day])) continue;

	$amount_per_day[$day] += $amount_per_day_external[$day];
}

?><!DOCTYPE HTML>
<html>
<head>
<title>Donut Leaderboard</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="css/style.css" />

 <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data_pie = new google.visualization.DataTable();
        data_pie.addColumn('string', 'Bracket');
        data_pie.addColumn('number', 'Number');
        data_pie.addRows([
			<?php
			echo "['0-100 Rs', {$amount_count['100']}],";
			echo "['101-500 Rs', {$amount_count['500']}],";
			echo "['501-1000 Rs', {$amount_count['1000']}],";
			echo "['1001-2000 Rs', {$amount_count['2000']}],";
			echo "['2001-5000 Rs', {$amount_count['5000']}],";
			echo "['5001-10000 Rs', {$amount_count['10000']}],";
			echo "['10001-50000 Rs', {$amount_count['50000']}]";
			?>
        ]);

        // Set chart options
        var options_pie = {'title':'Number of donations per range',
                       'width':400,
                       'height':300,
					   'backgroundColor':'#ffe800',
					   'titleTextStyle' : {fontSize:16},
					   'is3D' : true,
					   'legend.alignment' : 'center'
					   };

        // Instantiate and draw our chart, passing in some options.
        var chart_pie = new google.visualization.PieChart(document.getElementById('chart_pie_div'));
        chart_pie.draw(data_pie, options_pie);
		
		//Line Chart
		var data_line = google.visualization.arrayToDataTable([
			['Date', 'Amount Raised'],
			<?php
			for($d=30; $d>=0; $d--){
				$date_compare = new DateTime("now - $d days");
				$date_compare = $date_compare->format('Y-m-d');

				if(isset($amount_per_day[$date_compare]))
					echo "['$date_compare', ".$amount_per_day[$date_compare]."],";
				else
					echo "['$date_compare',0],";
			}
			?>
        ]);

        var options_line = {
          title: 'Amount raised per day',
		  'backgroundColor':'#ffe800',
		  'titleTextStyle' : {fontSize:16},
		  'animation.duration' : 100,
		  'exlorer' : {}
        };

        var chart_line = new google.visualization.LineChart(document.getElementById('chart_line_div'));
        chart_line.draw(data_line, options_line);

      }
    </script>

</head>

<body>
<div style="overflow:auto" class="board">
<div style="float:left" class="pin_left">
	<h2>Top Cities</h2>
	<?php

	setlocale(LC_MONETARY, 'en_IN');
	echo "<table>";
	echo "<tr><th>City</th><th>Amount</th><th>Donors</th></tr>";
	
	foreach($cities as $city){
		echo "<tr><td>$city[name]</td><td>" . number_format($city["amount"]) . "</td><td>$city[count]</td></tr>";
	}

	echo "<tr><td> </td><td> </td><td> </td></tr>";
	echo "<tr><td> </td><td> </td><td> </td></tr>";

	echo "<tr><td>GRAND TOTAL</td><td>" . number_format($total_amount['amount']) . "</td><td>$total_amount[count]</td></tr>";
	echo "</table>";
	?>
</div>

<div style = "float:right" class="pin_right">
	<h2>Top Fund Raisers by Amount</h2>
	<?php
	echo "<table>";
	echo "<tr><th>Rank</th><th>Name</th><th>Amount</th><th>Donors</th><th>City</th></tr>";
	
	$count = 0;
	
	foreach($fundraisers_amount as $fr){
		$count++;
		list($first_name) = explode(" ",$fr['first_name']);
		echo "<tr><td>$count</td><td>$first_name</td><td>" . number_format($fr['amount']) . "</td><td>$fr[count]</td><td>$fr[city_name]</td></tr>";
	}
	
	echo "</table>";
	
	?>

</div>

<div style="float:left" class="pin_left">

	<h2>Top Regions</h2>
	
	<?php
	
	echo "<table>";
	echo "<tr><th>Region</th><th>Amount</th><th>Donors</th></tr>";
	
	foreach($regions as $region){
		echo "<tr><td>$region[name]</td><td>" . number_format($region['amount']) . "</td><td>$region[count]</td></tr>";
	}
	
	echo "</table>";
	
	?>

</div>

<div style = "float:right" class="pin_right">
	<h2>Top FundRaisers by Donors</h2>
	
	<?php
	
	echo "<table>";
	echo "<tr><th>Rank</th><th>Name</th><th>Donors</th><th>Amount</th><th>City</th></tr>";
	
	$count = 0;
	
	foreach($fundraisers_count as $fr){
	
		$count++;
	
		list($first_name) = explode(" ",$fr['first_name']);
		echo "<tr><td>$count</td><td>$first_name</td><td>$fr[count]</td><td>" . number_format($fr['amount']) . "</td><td>$fr[city_name]</td></tr>";
	}
	
	echo "</table>";
	
	?>

</div>

<div style = "float:left" class = "pin_center">
	<h2>Other Stats</h2>
	
	<p>Total number of volunteers who used Donut : <?php echo $donut_users['count'];?></p>
	<p>Number of volunteers who raised at least 100 : <?php echo $user_amount_count['100']; ?></p>
	<p>Number of volunteers who raised at least 12,000 : <?php echo $user_amount_count['12000']; ?></p>
	<p>Number of volunteers who raised at least 1,00,000 : <?php echo $user_amount_count['100000']; ?></p>
	<p>Number of volunteers with more than 10 donations : <?php echo $user_with_more_than_10_donations; ?></p>
</div>

<div style = "float:left" class="pin_center">
	<h2>Charts</h2>
	
	<div style = "margin-left:200px;" id="chart_pie_div"></div>
	<div style = "margin-left:auto;margin-right:auto;" id="chart_line_div"></div>
</div>

</div>
</body>
</html>