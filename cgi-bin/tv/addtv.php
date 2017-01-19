<?php

session_start();
ob_start();

include '../connectToDB.php';

$id = $_GET['id'];
$title = urldecode($_GET['title']);
$poster = urldecode($_GET['poster']);
$status = urldecode($_GET['status']);
$tvmaze = $_GET['tvmaze'];
$user_id = $_SESSION['userid'];

if ($poster == 'N/A') {
	$poster = 'https://upload.wikimedia.org/wikipedia/en/f/f9/No-image-available.jpg';
}
$check = "SELECT * FROM orion.tv where imdb='".$id."';";
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $db->prepare($check);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( !$row){
	try {
		$stmt = $db->prepare("INSERT INTO orion.tv (`imdb`, `title`, `poster_url`, `tvmaze`, `status`) VALUES (:id, :title, :poster, :tvmaze, :status)");
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':title', $title);
		$stmt->bindParam(':poster', $poster);
		$stmt->bindParam(':tvmaze', $tvmaze);
		$stmt->bindParam(':status', $status);
		$stmt->execute();
		$row_id =  $db->lastInsertId();
	} catch (PDOException $e) {
	    echo 'Connection failed: ' . $e->getMessage();
	}
	$insertsql = "INSERT INTO `orion`.`tvepisodes` (`title`, `tvmaze`, `tv_id`, `airdate`, `season`, `season_number`) VALUES ";
	$api = "http://api.tvmaze.com/shows/$tvmaze/episodes";
	$apiresponse =  file_get_contents($api);
	$json = json_decode($apiresponse, true);
	foreach($json as $jsonitem){
			$insertsql .= "(\"".$jsonitem['name']."\", ".$jsonitem['id'].", 1, \"".$jsonitem['airdate']."\", ".$jsonitem['season'].", ".$jsonitem['number']."), ";
	}
	$insertsql = rtrim($insertsql,', ');
	try {
		$stmt = $db->prepare($insertsql);
		$stmt->execute();
	} catch (PDOException $e) {
	    echo 'Connection failed: ' . $e->getMessage();
	}
} else {
	$row_id = $row['id'];
}



if (isset($_SESSION['userid']))
	{
		try {
			$stmt = $db->prepare("INSERT INTO orion.g_user_tv (`user_id`, `tv_id`, `rank`, `status`) VALUES (:user, :row, '0', 'Behind')");
			$stmt->bindParam(':user', $user_id);
			$stmt->bindParam(':row', $row_id);
			$stmt->execute();
		} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
		}
		try {
			$gerundsql = "INSERT INTO g_user_tvepisodes(tvepisode_id, user_id, watched) SELECT id, ".$user_id.", 0 FROM tvepisodes WHERE tv_id = 1";
			$stmt = $db->prepare($gerundsql);
			$stmt->execute();
		} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
		}
  	header("Location: tv.php");
		exit;
	}
else
	{
	header("location: ../users/signin.php");
	}
?>
