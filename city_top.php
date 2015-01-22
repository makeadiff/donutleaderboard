<?php
require 'common.php';
$ig_users = $sql->getCol('SELECT user_id FROM users_leaderboard_ignore');


$cities = $sql->getAll("SELECT name from cities WHERE name NOT LIKE '%FOM%' AND name NOT LIKE '%Sparta%' ORDER BY name");

foreach($cities as $city){

	${$city['name']} = $sql->getAll("SELECT users.first_name AS first_name, users.last_name AS last_name, users.phone_no as phone_no, users.email as email_id,
							SUM(donations.donation_amount) AS amount, COUNT(donations.donation_amount) AS count,
							cities.name as city_name
							FROM donations 
							INNER JOIN users
							ON donations.fundraiser_id = users.id
							INNER JOIN cities
							ON cities.id = users.city_id AND cities.name = '$city[name]'
							AND donations.fundraiser_id NOT IN (" . implode(',',$ig_users) . ")
							GROUP BY users.id HAVING SUM(donations.donation_amount) >= 2500
							ORDER BY SUM(donations.donation_amount) DESC
							");

}





?>


<!DOCTYPE HTML>
<html>
<head>
<title>City Top</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="css/style.css" />

</head>

<body>

<div class="board">

<div class="pin_center">

<?php
foreach($cities as $city){


	echo "<h2>$city[name]</h2>";

	echo "<table>";
	echo "<th>Name</th><th>Phone No</th><th>Email</th><th>Amount</th><th>Donors</th>";
	

	foreach(${$city['name']} as $c){

		echo "<tr><td>$c[first_name] $c[last_name]</td><td>$c[phone_no]</td><td>$c[email_id]</td><td>$c[amount]</td><td>$c[count]</td></tr>";


	}

	echo "</table>";
	echo "<br><br><br>";



}

?>

</div>
</div>	
</body>

</html>