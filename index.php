<?php

require_once  'PDO_DB_Backup.php';

//@param array(host,db username,db password,database)

$pdb = 	new PDO_DB_Backup(array('localhost','root','','barangay'));
$pdb->generateBackUp();


?>