<?php
require 'common.php';

$ig_users = $sql->getCol('SELECT user_id FROM users_leaderboard_ignore');


$poc_count = $sql->getAll("SELECT poc.id as id, poc.first_name as poc_fn, poc.last_name as poc_ln, cities.name as city_name, COUNT( vol.id ) as count
						FROM users AS poc
						INNER JOIN cities ON poc.city_id = cities.id
						INNER JOIN user_role_maps ON poc.id = user_role_maps.user_id
						INNER JOIN reports_tos ON poc.id = reports_tos.manager_id
						RIGHT OUTER JOIN users AS vol ON vol.id = reports_tos.user_id
						WHERE user_role_maps.role_id =9
						AND poc.is_deleted =0
						AND cities.name <> 'Beyond Bangalore'
						GROUP BY poc.id
						ORDER BY poc.id, cities.name");

$poc_own_money = $sql->getAll("SELECT poc.first_name, poc.last_name, cities.name, SUM( poc_donations.donation_amount ) as own_sum
							FROM users AS poc
							INNER JOIN cities ON poc.city_id = cities.id
							INNER JOIN user_role_maps ON poc.id = user_role_maps.user_id
							LEFT OUTER JOIN donations AS poc_donations ON poc.id = poc_donations.fundraiser_id
							WHERE user_role_maps.role_id =9
							AND poc.is_deleted =0
							AND cities.name <> 'Beyond Bangalore'

							GROUP BY poc.id
							ORDER BY poc.id, cities.name");

$poc_group_money = $sql->getAll("SELECT poc.id as poc_id, poc.first_name, poc.last_name, cities.name, SUM( donations.donation_amount ) as group_sum
								FROM donations
								INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
								INNER JOIN reports_tos ON reports_tos.user_id = vol.id
								RIGHT OUTER JOIN users AS poc ON reports_tos.manager_id = poc.id
								INNER JOIN user_role_maps ON poc.id = user_role_maps.user_id
								INNER JOIN cities ON poc.city_id = cities.id
								WHERE user_role_maps.role_id =9
								AND poc.is_deleted =0
								AND cities.name <> 'Beyond Bangalore'

								GROUP BY poc.id
								ORDER BY poc.id, cities.name");


$poc_group_money_with_vol = $sql->getAll("SELECT poc.id as poc_id, poc.first_name, poc.last_name, cities.name, SUM( donations.donation_amount ) as group_sum
								FROM donations
								INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
								INNER JOIN reports_tos ON reports_tos.user_id = vol.id
								RIGHT OUTER JOIN users AS poc ON reports_tos.manager_id = poc.id
								INNER JOIN user_role_maps ON poc.id = user_role_maps.user_id
								INNER JOIN cities ON poc.city_id = cities.id
								WHERE user_role_maps.role_id =9
								AND poc.is_deleted =0
								AND cities.name <> 'Beyond Bangalore'
								AND donations.donation_status = 'TO_BE_APPROVED_BY_POC'

								GROUP BY poc.id
								ORDER BY poc.id, cities.name");


$poc_group_money_with_poc = $sql->getAll("SELECT poc.id as poc_id, poc.first_name, poc.last_name, cities.name, SUM( donations.donation_amount ) as group_sum
								FROM donations
								INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
								INNER JOIN reports_tos ON reports_tos.user_id = vol.id
								RIGHT OUTER JOIN users AS poc ON reports_tos.manager_id = poc.id
								INNER JOIN user_role_maps ON poc.id = user_role_maps.user_id
								INNER JOIN cities ON poc.city_id = cities.id
								WHERE user_role_maps.role_id =9
								AND poc.is_deleted =0
								AND cities.name <> 'Beyond Bangalore'
								AND donations.donation_status = 'HAND_OVER_TO_FC_PENDING'

								GROUP BY poc.id
								ORDER BY poc.id, cities.name");


$poc_active_users = $sql->getAll("SELECT poc.first_name, poc.last_name, cities.name, COUNT(DISTINCT vol.id) as active_users
								FROM donations
								INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
								INNER JOIN reports_tos ON reports_tos.user_id = vol.id
								RIGHT OUTER JOIN users AS poc ON reports_tos.manager_id = poc.id
								INNER JOIN user_role_maps ON poc.id = user_role_maps.user_id
								INNER JOIN cities ON poc.city_id = cities.id
								WHERE user_role_maps.role_id =9
								AND poc.is_deleted =0
								AND cities.name <> 'Beyond Bangalore'

								GROUP BY poc.id
								ORDER BY poc.id, cities.name")


?>

<!DOCTYPE HTML>
<html>
<head>
<title>POC</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link type="text/css" rel="stylesheet" href="css/style.css" />

</head>

<body>

<div class="board">

<div class="pin_center">

	<?php

	

	echo "<table>";
	echo "<th>ID</th><th>Name of POC</th><th>Volunteers Assigned</th><th>Active Volunteers</th><th>Raised by POC</th><th>Raised by Group (Exc POC)</th><th>With Vols</th><th>With POC</th><th>City</th>";
	
	for($i=0, $count = count($poc_group_money);$i<$count;$i++){

		$with_vol[$i] = 0;
		$with_poc[$i] = 0;


	}



	for($i=0, $count = count($poc_group_money);$i<$count;$i++){

		for($j=0, $count1 = count($poc_group_money_with_vol);$j<$count1;$j++){

			if($poc_group_money[$i]['poc_id'] == $poc_group_money_with_vol[$j]['poc_id']){
				$with_vol[$i] = $poc_group_money_with_vol[$j]['group_sum'];
				//echo $i .'  ' . $j . '<br>';
				break;
			}else
				$with_vol[$i] = 0;




		}



	}

	for($i=0, $count = count($poc_group_money);$i<$count;$i++){

		for($j=0, $count1 = count($poc_group_money_with_poc);$j<$count1;$j++){

			if($poc_group_money[$i]['poc_id'] == $poc_group_money_with_poc[$j]['poc_id']){
				$with_poc[$i] = $poc_group_money_with_poc[$j]['group_sum'];
				break;
			}else
				$with_poc[$i] = 0;


		}


	}
	


	for($i=0, $count = count($poc_own_money);$i<$count;$i++){

		if($poc_own_money[$i]['own_sum'] == "")		
			$poc_own_money[$i]['own_sum'] = 0;
		if($poc_group_money[$i]['group_sum'] == "")
			$poc_group_money[$i]['group_sum'] = 0;


		/*echo "<pre>";
        print_r($poc_own_money);
        exit;*/
		echo "<tr><td>" . $poc_count[$i]['id'] . "</td><td>" . $poc_count[$i]['poc_fn'] . " " . $poc_count[$i]['poc_ln'] . "</td><td>" . $poc_count[$i]['count'] . "</td><td>"  . $poc_active_users[$i]['active_users'] . "</td><td>" . $poc_own_money[$i]['own_sum'] . "</td><td>" . $poc_group_money[$i]['group_sum'] . "</td><td>" . $with_vol[$i] . "</td><td>" . $with_poc[$i]. "</td><td>" . $poc_count[$i]['city_name'] . "</td></tr>";
		
	}
	

	
	echo "</table>";
	
	?>

</div>
</div>	
</body>

</html>
