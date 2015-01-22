<?php
require 'common.php';

$ig_users = $sql->getCol('SELECT user_id FROM users_leaderboard_ignore');
$ig_users = implode(',',$ig_users);

$pocs = $sql->getAll("SELECT poc.id as id, poc.first_name as poc_fn, poc.last_name as poc_ln, cities.name as city_name
						FROM users AS poc
						INNER JOIN cities ON poc.city_id = cities.id
						INNER JOIN user_role_maps ON poc.id = user_role_maps.user_id
						WHERE user_role_maps.role_id =9
						AND poc.is_deleted =0
						AND cities.name <> 'Beyond Bangalore'

						GROUP BY poc.id
						ORDER BY cities.name");

foreach($pocs as &$poc) {

    $query = $sql->getOne("SELECT COUNT(vol.id) as vol_count FROM users as vol
                            INNER JOIN reports_tos
                            ON reports_tos.user_id = vol.id
                            WHERE reports_tos.manager_id = $poc[id]");



    if(empty($query)) {
        $poc['volunteers_assigned'] = 0;
    } else {
        $poc['volunteers_assigned'] = $query;
    }

    $query = $sql->getOne("SELECT COUNT(DISTINCT vol.id) as active_users
								FROM donations
								INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
								INNER JOIN reports_tos ON reports_tos.user_id = vol.id
								WHERE reports_tos.manager_id = $poc[id]");

    if(empty($query)) {
        $poc['volunteers_active'] = 0;
    } else {
        $poc['volunteers_active'] = $query;
    }


    $query = $sql->getOne("SELECT SUM( poc_donations.donation_amount ) as own_sum
							FROM users AS poc
							INNER JOIN donations AS poc_donations
							ON poc.id = poc_donations.fundraiser_id
							WHERE poc.id = $poc[id]
							AND poc.id NOT IN ($ig_users)");


    if(empty($query)) {
        $poc['raised_by_poc'] = 0;
    } else {
        $poc['raised_by_poc'] = $query;
    }

    $query = $sql->getOne("SELECT SUM( donations.donation_amount ) as group_sum
                            FROM donations
                            INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
                            INNER JOIN reports_tos ON reports_tos.user_id = vol.id
                            WHERE reports_tos.manager_id = $poc[id]
                            AND reports_tos.user_id <> $poc[id]
                            AND vol.id NOT IN ($ig_users)");

    if(empty($query)) {
        $poc['raised_by_group'] = 0;
    } else {
        $poc['raised_by_group'] = $query;
    }

    $query1 = $sql->getOne("SELECT COALESCE(SUM( poc_donations.donation_amount ),0) as own_sum
							FROM users AS poc
							INNER JOIN donations AS poc_donations
							ON poc.id = poc_donations.fundraiser_id
							WHERE poc.id = $poc[id]
							AND poc.id NOT IN ($ig_users)
							AND poc_donations.donation_status = 'TO_BE_APPROVED_BY_POC'");

    $query2 = $sql->getOne("SELECT COALESCE(SUM( donations.donation_amount ),0) as group_sum
                            FROM donations
                            INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
                            INNER JOIN reports_tos ON reports_tos.user_id = vol.id
                            WHERE reports_tos.manager_id = $poc[id]
                            AND reports_tos.user_id <> $poc[id]
                            AND vol.id NOT IN ($ig_users)
                            AND donations.donation_status = 'TO_BE_APPROVED_BY_POC'");


    $poc['with_vol'] = $query1 + $query2;

    $query1 = $sql->getOne("SELECT COALESCE(SUM( poc_donations.donation_amount ),0) as own_sum
							FROM users AS poc
							INNER JOIN donations AS poc_donations
							ON poc.id = poc_donations.fundraiser_id
							WHERE poc.id = $poc[id]
							AND poc.id NOT IN ($ig_users)
							AND poc_donations.donation_status = 'HAND_OVER_TO_FC_PENDING'");

    $query2 = $sql->getOne("SELECT COALESCE(SUM( donations.donation_amount ),0) as group_sum
                            FROM donations
                            INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
                            INNER JOIN reports_tos ON reports_tos.user_id = vol.id
                            WHERE reports_tos.manager_id = $poc[id]
                            AND reports_tos.user_id <> $poc[id]
                            AND vol.id NOT IN ($ig_users)
                            AND donations.donation_status = 'HAND_OVER_TO_FC_PENDING'");


    $poc['with_poc'] = $query1 + $query2;

    $query1 = $sql->getOne("SELECT COALESCE(SUM( poc_donations.donation_amount ),0) as own_sum
							FROM users AS poc
							INNER JOIN donations AS poc_donations
							ON poc.id = poc_donations.fundraiser_id
							WHERE poc.id = $poc[id]
							AND poc.id NOT IN ($ig_users)
							AND poc_donations.donation_status = 'DEPOSIT_PENDING'");

    $query2 = $sql->getOne("SELECT COALESCE(SUM( donations.donation_amount ),0) as group_sum
                            FROM donations
                            INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
                            INNER JOIN reports_tos ON reports_tos.user_id = vol.id
                            WHERE reports_tos.manager_id = $poc[id]
                            AND reports_tos.user_id <> $poc[id]
                            AND vol.id NOT IN ($ig_users)
                            AND donations.donation_status = 'DEPOSIT_PENDING'");


    $poc['with_fc'] = $query1 + $query2;

    $query1 = $sql->getOne("SELECT COALESCE(SUM( poc_donations.donation_amount ),0) as own_sum
							FROM users AS poc
							INNER JOIN donations AS poc_donations
							ON poc.id = poc_donations.fundraiser_id
							WHERE poc.id = $poc[id]
							AND poc.id NOT IN ($ig_users)
							AND (poc_donations.donation_status = 'DEPOSIT COMPLETE'
							OR poc_donations.donation_status = 'RECEIPT PENDING'
							OR poc_donations.donation_status = 'RECEIPT SENT')");

    $query2 = $sql->getOne("SELECT COALESCE(SUM( donations.donation_amount ),0) as group_sum
                            FROM donations
                            INNER JOIN users AS vol ON vol.id = donations.fundraiser_id
                            INNER JOIN reports_tos ON reports_tos.user_id = vol.id
                            WHERE reports_tos.manager_id = $poc[id]
                            AND reports_tos.user_id <> $poc[id]
                            AND vol.id NOT IN ($ig_users)
                            AND (donations.donation_status = 'DEPOSIT COMPLETE'
							OR donations.donation_status = 'RECEIPT PENDING'
							OR donations.donation_status = 'RECEIPT SENT')");


    $poc['complete'] = $query1 + $query2;

    $poc['total'] = $poc['with_vol'] + $poc['with_poc'] + $poc['with_fc'] + $poc['complete'];


}


?>

<!DOCTYPE HTML>
<html>
<head>
    <title>POC</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link type="text/css" rel="stylesheet" href="css/style.css" />

</head>

<body>

<div class="board_poc">

    <div class="pin_center_poc">

        <table>
            <tr>
                <th>Name</th>
                <th>Volunteer Count</th>
                <th>Volunteers Active</th>
                <th>Raised by POC</th>
                <th>Raised by Group(Exc POC)</th>
                <th>With Vol</th>
                <th>With POC</th>
                <th>With FC</th>
                <th>Deposited</th>
                <th>Total</th>
                <th>City</th>
            </tr>

            <?php
                foreach($pocs as $poc) {
                    echo "<tr>
                                <td>
                                   $poc[poc_fn] $poc[poc_ln]
                                </td>
                                <td>
                                    $poc[volunteers_assigned]
                                </td>
                                <td>
                                    $poc[volunteers_active]
                                </td>
                                <td>
                                    $poc[raised_by_poc]
                                </td>
                                <td>
                                    $poc[raised_by_group]
                                </td>
                                <td>
                                    $poc[with_vol]
                                </td>
                                <td>
                                    $poc[with_poc]
                                </td>
                                <td>
                                    $poc[with_fc]
                                </td>
                                <td>
                                    $poc[complete]
                                </td>
                                <td>
                                    $poc[total]
                                </td>
                                <td>
                                    $poc[city_name]
                                </td>
                            </tr>";
                }

            ?>

        </table>


    </div>
</div>