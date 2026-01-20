<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

if (!file_exists("../../settings/connect_datebase.php")) {
    echo json_encode(['error' => 'Файл подключения не найден']);
    exit;
}

require_once("../../settings/connect_datebase.php");

if (!$mysqli) {
    echo json_encode(['error' => 'Ошибка подключения к БД']);
    exit;
}

$check_table = $mysqli->query("SHOW TABLES LIKE 'logs'");
if (!$check_table || $check_table->num_rows == 0) {
    echo json_encode(['error' => 'Таблица logs не существует']);
    exit;
}

$Sql = "SELECT * FROM `logs` ORDER BY `Date` DESC";
$Query = $mysqli->query($Sql);

$Events = array();

if ($Query) {
    if ($Query->num_rows > 0) {
        while($Read = $Query->fetch_assoc()) {
            $Status = "offline";
            $SqlSession = "SELECT * FROM `session` WHERE `IdUser` = " . intval($Read["IdUser"]) . " ORDER BY `DateStart` DESC";
            $QuerySession = $mysqli->query($SqlSession);
            
            if($QuerySession && $QuerySession->num_rows > 0) {
                $ReadSession = $QuerySession->fetch_assoc();
                $TimeEnd = strtotime($ReadSession["DateNow"]) + 5*60;
                $TimeNow = time();
                
                if($TimeEnd > $TimeNow) {
                    $Status = "online";
                } else {
                    $TimeEnd = strtotime($ReadSession["DateNow"]);
                    $TimeDelta = round(($TimeNow - $TimeEnd)/60);
                    $Status = "Был в сети: {$TimeDelta} минут назад";
                }
            }

            $Event = array(
                "Id" => $Read["Id"] ?? 0,
                "Ip" => $Read["Ip"] ?? '',
                "Date" => $Read["Date"] ?? date("Y-m-d H:i:s"),
                "TimeOnline" => $Read["TimeOnline"] ?? '00:00:00',
                "Status" => $Read["Status"] ?? 'INFO',
                "Event" => $Read["Event"] ?? ''
            );
            array_push($Events, $Event);
        }
    } else {
        $Events[] = array(
            "Id" => 1,
            "Ip" => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            "Date" => date("Y-m-d H:i:s"),
            "TimeOnline" => '00:00:00',
            "Status" => 'INFO',
            "Event" => 'Таблица logs пуста. События появятся после авторизации пользователей.'
        );
    }
} else {
    $Events[] = array(
        "Id" => 0,
        "Ip" => '127.0.0.1',
        "Date" => date("Y-m-d H:i:s"),
        "TimeOnline" => '00:00:00',
        "Status" => 'ERROR',
        "Event" => 'Ошибка выполнения запроса к базе данных: ' . $mysqli->error
    );
}

echo json_encode($Events, JSON_UNESCAPED_UNICODE);
?>