<?php
require 'common.php';

$ig_users = $sql->getAll("SELECT * FROM users_leaderboard_ignore");

foreach($ig_users as $ig_user) {

    if($ig_user["user_id"] == 0){

        $user = $sql->getAssoc("SELECT id FROM users WHERE phone_no = $ig_user[phone_no]");
        if(!empty($user["id"])) {
            $sql->execQuery("UPDATE users_leaderboard_ignore SET user_id = $user[id] WHERE id = $ig_user[id]");

        }
    }


}