#!/usr/bin/php
<?php
include_once 'Fitbit.php';
include_once 'config.php';
$date = date("Y-m-d");
$period = new DatePeriod(
    new DateTime(Config::$start_date),
    new DateInterval('P1D'),
    new DateTime(date("Y-m-d"))
);
$p2 = array();
foreach ($period as $key => $value) {
    array_push($p2, $value->format('Y-m-d'));
}

$m = new PHPFitBit(Config::$client_id, Config::$client_secret, Config::$redirect_url);
$t = json_decode(file_get_contents("token.json"), true);
$m->set_tokens($t['access_token'], $t['refresh_token']);

function logs($msg)
{
    $log = fopen(Config::$log, "ab");
    $ts = gmdate("Y-m-d\TH:i:s\Z");
    $ent = $ts . ": " . $msg . "\n";
    fwrite($log, $ent);
    fclose($log);
    echo $ent;
}

function get_data($df)
{
    $m = new PHPFitBit(Config::$client_id, Config::$client_secret, Config::$redirect_url);
    $t = json_decode(file_get_contents("token.json"), true);
    $m->set_tokens($t['access_token'], $t['refresh_token']);
    $ep = array("activities/calories", "activities/caloriesBMR", "activities/steps", "activities/distance", "activities/floors", "activities/elevation", "activities/minutesSedentary", "activities/minutesLightlyActive", "activities/minutesFairlyActive", "activities/minutesVeryActive", "activities/activityCalories");

    foreach ($ep as $i) {
        $done = false;
        $p = '/user/-/' . $i . '/date/' . $df . '/1d.json';
        $aa = str_replace('activities/', "", $i);
        $fn = $aa . "_" . $df . ".json";

        while (!$done) {
            $d2 = $m->get($p);
            $done = ratecheck($m->headers, $m->last_http);
            file_put_contents("./backup/" . $fn, json_encode($d2, JSON_PRETTY_PRINT));
            logs("Writing file " . $fn);
        }
    }
}

function ratecheck($h, $r)
{
    $resert = (int) $h['Fitbit-Rate-Limit-Reset'];
    $cur = (int) $h["Fitbit-Rate-Limit-Remaining"];

    if ($cur <= 10 || $r != 200) {
        if ($r == 401) {
            $m = new PHPFitBit(Config::$client_id, Config::$client_secret, Config::$redirect_url);
            $t = json_decode(file_get_contents("token.json"), true);
            $m->set_tokens($t['access_token'], $t['refresh_token']);
            $t2 = $m->refresh();
            file_put_contents("token.json", json_encode($t2));
            logs("Token Expired refreshing auth token. Wating 10 sec.");
            return false;
            sleep(10);
        }else{
            logs("Sleeping for " . ($resert + 60) . " Sec at rate limit. Responce Code:" . $r . " Requests remaining:" . $cur);
            sleep($resert + 60);
            return false;
        }

    } else {
        logs("Rate limit left " . $cur . " Keeping going");
        return true;
    }
}

foreach ($p2 as $p) {
    get_data($p);
}
