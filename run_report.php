<?php
try {
    if($argc < 5) {
        throw new Exception("Usage: php ".$argv[0] . " url username password report_id [start_date] [stop_date]");
    }

    ini_set('default_socket_timeout', 180);
    $client = new SoapClient(trim($argv[1], ' /') . "/api/soap/?wsdl", array("connection_timeout" => 180));
    $apiuser = trim($argv[2]);
    $apikey = trim($argv[3]);
    $reportId = intval($argv[4]);
    $startDate = (isset($argv[5]) ? trim($argv[5]) : '');
    $stopDate = (isset($argv[6]) ? trim($argv[6]) : '');

    $sessionId = $client->login($apiuser, $apikey);
    $data = $client->call($sessionId, "cleansql.runReport", array($reportId, $startDate, $stopDate));
    $client->endSession($sessionId);

    if (empty($data)) {
        exit;
    }

    $f = fopen('php://stdout', 'w');

    $header = reset($data);
    $header = array_keys($header);
    fputcsv($f, $header);

    foreach ($data as $row) {
        fputcsv($f, $row);
    }

    fclose($f);
} catch (Exception $e) {
    $f = fopen('php://stderr', 'w');
    fputs($f, $e->getMessage() . "\n");
    fclose($f);
}
