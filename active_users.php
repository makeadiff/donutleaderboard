<?php
require 'common.php';


$ig_users = $sql->getCol('SELECT user_id FROM users_leaderboard_ignore');

$active_users = $sql->getAll("SELECT cities.name AS name, COUNT(DISTINCT users.id) AS count
							FROM donations
							INNER JOIN users ON donations.fundraiser_id = users.id
							INNER JOIN cities on users.city_id=cities.id AND cities.name <> 'Beyond Bangalore' AND cities.name NOT LIKE 'FOM%'
                            AND donations.fundraiser_id NOT IN (" . implode(',',$ig_users) . ")
							GROUP BY cities.name");

$poona_test = $sql->getAll("SELECT * FROM `donations` INNER JOIN users ON users.id = donations.fundraiser_id WHERE users.city_id = 10");

?>

<!DOCTYPE HTML>
<html>
<head>
<title>Active Users</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="css/style.css" />

</head>

<body>

<div class="board">

<div class="pin_center">

	<?php
	echo "<table>";
	echo "<th>City</th><th>Active Users</th>";
	
	foreach($active_users as $active){
		echo "<tr><td>$active[name]</td><td>$active[count]</td></tr>";
	}

    if(empty($poona_test))
        echo "<tr><td>Poona</td><td>0</td></tr>";
	echo "</table>";
	
	?>

</div>
</div>	
</body>

</html>
