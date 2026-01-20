<?php
	session_start();
	session_destroy();
?><?php
session_start();
require_once("../settings/connect_datebase.php");

if (!isset($_SESSION["user"]) || !isset($_SESSION["IdSession"])) {
	session_destroy();
	exit;
}

$IdUser = $_SESSION["user"];
$IdSession = $_SESSION["IdSession"];

$Sql = "SELECT `session`.*, `users`.`login` ".
		"FROM `session` ".
		"JOIN `users` ON `users`.`id` = `session`.`IdUser` ".
		"WHERE `session`.`Id` = $IdSession";
$Query = $mysqli->query($Sql);

if ($Query && $Query->num_rows > 0) {
	$Read = $Query->fetch_array();

	$TimeStart = strtotime($Read["DateStart"]);
	$TimeNow = time();
	$Ip = $Read["Ip"];
	$TimeDelta = gmdate("H:i:s", ($TimeNow - $TimeStart));
	$Date = date("Y-m-d H:i:s");
	
	$Login = $Read["login"];
	$Sql = "INSERT INTO `logs` (`Ip`, `IdUser`, `Date`, `TimeOnline`, `Status`, `Event`) 
			VALUES ('$Ip', $IdUser, '$Date', '$TimeDelta', 'INFO', 'Пользователь $Login вышел из системы.')";
	$mysqli->query($Sql);
}

session_destroy();

echo "success";
?>