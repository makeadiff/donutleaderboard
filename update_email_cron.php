<?php
require 'common.php';

$rows = $sql->execQuery("UPDATE donours
				SET donours.email_id='noreply@makeadiff.in'
				WHERE donours.email_id='' ");
echo "Success : $rows rows updated";

?>

