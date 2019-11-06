<?php 

include 'vendor/autoload.php';
include 'connect.php';

$channel_access_token = CHANNEL_ACCESS_TOKEN;
$channel_secret = CHANNEL_SECRET;

$link = mysqli_connect(HOST1, USERNAME1, PASSWORD1, DB_NAME1);
$res = $mysqli->query("SELECT `hn` FROM `opcard` WHERE `row_id` = '174803' ");
$row = $res->fetch_assoc();

// $httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
// $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);
// $response = $bot->getGroupMemberIds('1622305778', <continuationToken>);

// echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
