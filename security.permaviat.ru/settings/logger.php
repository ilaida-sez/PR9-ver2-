<?php
function logEvent($mysqli, $userId, $event, $status = 'INFO', $ip = null) {
    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    $date = date("Y-m-d H:i:s");
    $timeOnline = '00:00:00';
    
    $sql = "INSERT INTO `logs` (`Ip`, `IdUser`, `Date`, `TimeOnline`, `Status`, `Event`) 
            VALUES ('$ip', $userId, '$date', '$timeOnline', '$status', '$event')";
    $mysqli->query($sql);
    
    $logFile = dirname(__DIR__) . '/log.txt';
    $logMessage = "[$date] [$status] IP: $ip | UserID: $userId | Event: $event\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    return true;
}
?>