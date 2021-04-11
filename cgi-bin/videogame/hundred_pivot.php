<?php

if(!isset($_SESSION)) {
  session_start();
} ;
ob_start();

include '../connectToDB.php';

$g_id = $_GET['g_id'];
$hundred_percent = $_GET['hundred_percent'];
$user_id = $_SESSION['userid'];

$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_SESSION['userid']))
	{
		try {
			$stmt = $db->prepare("UPDATE `orion`.`g_user_videogames` SET `100_percent`=:100_percent WHERE `g_id`=:g_id AND `user_id`=:user");
			$stmt->bindParam(':user', $user_id);
			$stmt->bindParam(':g_id', $g_id);
      		$stmt->bindParam(':100_percent', $hundred_percent);
			$result = $stmt->execute();
			if ( false===$result ) {
				error_log( serialize ($stmt->errorInfo()));
			}
		} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
		}
	}
else
	{
	header("location: ../users/signin.php");
	}
?>