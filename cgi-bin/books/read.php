<?php

if(!isset($_SESSION)) {
  session_start();
} ;
ob_start();

include '../connectToDB.php';

$id = $_GET['id'];

if (isset($_SESSION['userid']))
	{
    $stmt = $db->prepare("UPDATE `orion`.`g_user_books` SET `completed`='1', `g_first`=now() WHERE `g_id`= :gid");
    $stmt->bindParam(':gid', $id);
    $result = $stmt->execute();
    if ( false===$result ) {
        error_log( serialize ($stmt->errorInfo()));
    }
    header("Location: book.php");
    exit;
	}
else
	{
	header("location: ../users/signin.php");
	}
?>
