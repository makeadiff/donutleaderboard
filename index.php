<?php
require './common.php';

// Users to Ignore
$ignore_users = '';
$ig_users = $sql->getCol('SELECT user_id FROM users_leaderboard_ignore');
if($ig_users) $ignore_users = " AND donations.fundraiser_id NOT IN (" . implode(',',$ig_users) . ")";

// Get data only from this time from this city...
$city_date_filter = array(
		'44'	=> array('from' => '2015-11-22'),	// Bangalore
		'6'		=> array('from' => '2015-11-22'),	// Vellore
		'7'		=> array('from' => '2015-11-22'),	// Vizag
		'12'	=> array('from' => '2015-12-01'),	// Bhopal
		'17'	=> array('from' => '2015-12-01'),	// Hyd
		'19'	=> array('from' => '2015-12-10'),	// Coimbatore
		'9'		=> array('from' => '2015-12-06'),	// Mumbai
		'3'		=> array('from' => '2015-12-06'),	// Cochin
		'8'		=> array('from' => '2015-12-06'),	// Nagpur
		'20'	=> array('from' => '2015-12-13'),	// Delhi
		'11'	=> array('from' => '2015-12-13'),	// Kolkatta
		'13'	=> array('from' => '2015-12-13'),	// Ahmd
		'18'	=> array('from' => '2015-12-20'),	// Guntur
		'16'	=> array('from' => '2015-12-20'),	// Wada
		'15'	=> array('from' => '2016-01-01'),	// Tvm
		'22'	=> array('from' => '2016-01-01'),	// Lucknow
		'23'	=> array('from' => '2016-01-10'),	// Gwalior
		'14'	=> array('from' => '2016-01-10'),	// Chennai
		'5'		=> array('from' => '2016-01-10'),	// Mysore
		'10'	=> array('from' => '2016-01-10'),	// Pune
		'4'		=> array('from' => '2016-01-17'),	// Mlore
		'21'	=> array('from' => '2016-01-17'),	// Chandigarh
		'24'	=> array('from' => '2016-01-17'),	// Dun
	);
$filter_array = array();
foreach ($city_date_filter as $city_id => $dates) {
	$filter_array[] = "(users.city_id=$city_id AND donations.created_at >= '$dates[from] 00:00:00')";
}
$filter = " AND (" . implode(" OR ", $filter_array) . ") " . $ignore_users;

// $from = '2015-11-25 00:00:00';
// $date_filter = " AND donations.created_at >= '$from'";


$lessthan100 = $sql->getAssoc("SELECT COUNT(*) as count FROM donations
		INNER JOIN users ON donations.fundraiser_id=users.id
		WHERE donation_amount <= 100 $filter");

$lessthan500 = $sql->getAssoc("SELECT COUNT(*) as count FROM donations
		INNER JOIN users ON donations.fundraiser_id=users.id
		WHERE donation_amount >= 101 AND donation_amount <=500 $filter");
							
$lessthan1000 = $sql->getAssoc("SELECT COUNT(*) as count FROM donations
		INNER JOIN users ON donations.fundraiser_id=users.id
		WHERE donation_amount >= 501 AND donation_amount <=1000 $filter");

$lessthan2000 = $sql->getAssoc("SELECT COUNT(*) as count FROM donations
		INNER JOIN users ON donations.fundraiser_id=users.id
		WHERE donation_amount >= 1001 AND donation_amount <=2000 $filter");

$lessthan5000 = $sql->getAssoc("SELECT COUNT(*) as count FROM donations
		INNER JOIN users ON donations.fundraiser_id=users.id
		WHERE donation_amount >= 2001 AND donation_amount <=5000 $filter");

$lessthan10000 = $sql->getAssoc("SELECT COUNT(*) as count FROM donations
		INNER JOIN users ON donations.fundraiser_id=users.id
		WHERE donation_amount >= 5001 AND donation_amount <=10000 $filter");
							
$lessthan50000 = $sql->getAssoc("SELECT COUNT(*) as count FROM donations
		INNER JOIN users ON donations.fundraiser_id=users.id
		WHERE donation_amount >= 10001 AND donation_amount <=50000 $filter");

$cities = $sql->getAll("SELECT cities.name AS name, SUM(donations.donation_amount) AS amount, COUNT(donations.donation_amount) AS count
		FROM donations 
		INNER JOIN users ON donations.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.city_id
		ORDER BY SUM(donations.donation_amount) DESC");

$total_amount = $sql->getAssoc("SELECT SUM(donations.donation_amount) AS amount
		FROM donations
		INNER JOIN users ON donations.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter");
							
$total_donors = $sql->getAssoc("SELECT COUNT(donations.donation_amount) AS count
		FROM donations
		INNER JOIN users ON donations.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter");
						
$fundraisers_amount = $sql->getAll("SELECT users.first_name AS first_name, users.last_name AS last_name,
		SUM(donations.donation_amount) AS amount, COUNT(donations.donation_amount) AS count,
		cities.name as city_name
		FROM donations 
		INNER JOIN users ON donations.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.id
		ORDER BY SUM(donations.donation_amount) DESC
		LIMIT 25");
		
$fundraisers_count = $sql->getAll("SELECT users.first_name AS first_name, users.last_name AS last_name,
		SUM(donations.donation_amount) AS amount, COUNT(donations.donation_amount) AS count,
		cities.name as city_name
		FROM donations 
		INNER JOIN users ON donations.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		GROUP BY users.id
		ORDER BY COUNT(donations.donation_amount) DESC
		LIMIT 25");					
		
$regions = $sql->getAll("SELECT states.name AS name, SUM(donations.donation_amount) AS amount,
		COUNT(donations.donation_amount) AS count
		FROM donations
		INNER JOIN users ON donations.fundraiser_id = users.id
		INNER JOIN cities ON cities.id = users.city_id
		$filter
		INNER JOIN states ON cities.state_id = states.id 
		GROUP BY states.id
		ORDER BY SUM(donations.donation_amount) DESC
		");

		
$donut_users = $sql->getAssoc("SELECT COUNT(DISTINCT users.id) AS count
		FROM donations
		INNER JOIN users ON donations.fundraiser_id = users.id
		$filter");

$users_10k = $sql->getAll("SELECT COUNT(*)
		from donations
		INNER JOIN users ON donations.fundraiser_id = users.id
		$filter
		GROUP BY users.id
		HAVING SUM(donations.donation_amount) >= 10000");
		
$users_5k = $sql->getAll("SELECT COUNT(*)
		from donations
		INNER JOIN users ON donations.fundraiser_id = users.id
		$filter
		GROUP BY users.id
		HAVING SUM(donations.donation_amount) >= 5000");
		
$users_10 = $sql->getAll("SELECT COUNT(*)
		from donations
		INNER JOIN users ON donations.fundraiser_id = users.id
		$filter
		GROUP BY users.id
		HAVING COUNT(donations.donation_amount) >= 10");
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
			echo "['0-100 Rs', $lessthan100[count]],";
			echo "['101-500 Rs', $lessthan500[count]],";
			echo "['501-1000 Rs', $lessthan1000[count]],";
			echo "['1001-2000 Rs', $lessthan2000[count]],";
			echo "['2001-5000 Rs', $lessthan5000[count]],";
			echo "['5001-10000 Rs', $lessthan10000[count]],";
			echo "['10001-50000 Rs', $lessthan50000[count]]";
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
				
					
				$amount_per_day = $sql->getAssoc("SELECT SUM(donation_amount) as sum FROM donations
													INNER JOIN users ON users.id=donations.fundraiser_id
													WHERE DATE(donations.created_at) = '$date_compare'
													$filter");
												
				if($amount_per_day['sum'] != NULL)
					echo "['$date_compare',$amount_per_day[sum]],";
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

	echo "<tr><td>GRAND TOTAL</td><td>" . number_format($total_amount['amount']) . "</td><td>$total_donors[count]</td></tr>";
	echo "</table>";
	
	?>

</div>

<div style = "float:right" class="pin_right">
	<h2>Top FundRaisers by Amount</h2>
	
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
	<p>Number of volunteers who have raised more than 10k : <?php echo count($users_10k);?></p>
	<p>Number of volunteers who have raised more than 5k : <?php echo count($users_5k);?></p>
	<p>Number of volunteers with more than 10 donations : <?php echo count($users_10);?></p>
</div>

<div style = "float:left" class="pin_center">
	<h2>Charts</h2>
	
	<div style = "margin-left:200px;" id="chart_pie_div"></div>
	<div style = "margin-left:auto;margin-right:auto;" id="chart_line_div"></div>
</div>

</div>
</body>
</html>