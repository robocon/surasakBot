<?php 
include 'vendor/autoload.php';
include 'connect.php';

use LINE\LINEBot;
use LINE\LINEBot\Constant\MessageType;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;


/*Get Data From POST Http Request*/ 
// $datas = file_get_contents('php://input');
/*Decode Json From LINE Data Body*/
// $deCode = json_decode($datas,true);
// file_put_contents('log_med.txt', $datas . PHP_EOL, FILE_APPEND);

$mysqli_smdb = mysqli_connect(HOST1, USERNAME1, PASSWORD1, DB_NAME1);
$mysqlLine = mysqli_connect(HOST2, USERNAME2, PASSWORD2, DB_NAME2);

// กลุ่มห้องยาตั้งเป็น y ตัวอื่นยังไม่รู้
$sql = "SELECT `userId` FROM `user` WHERE `med_group` = 'y' ORDER BY `id` ASC ";
$qMedGrouup = $mysqlLine->query($sql);
$medList = array();
if ($qMedGrouup->num_rows > 0) {
    
    while ($item = $qMedGrouup->fetch_assoc()) {
        $medList[] = $item['userId'];
    }
    
}

$httpClient = new CurlHTTPClient(CHANNEL_ACCESS_TOKEN);
$bot = new LINEBot($httpClient, ['channelSecret' => CHANNEL_SECRET]);

foreach ($_POST as $key => $id) {
    
    $sql = "SELECT * FROM `med_scan` WHERE `id` = '$id' ";
    $result = $mysqli_smdb->query($sql);
    
    if ($result->num_rows > 0) {

        $item = $result->fetch_assoc();

        // copy รูปจาก .2 มา .31 
        $copy_url = 'http://'.IP1.IP_PORT1.'/sm3/surasak3/'.$item['path'];
        copy($copy_url,$item['path']);

        // รูปที่ใช้ส่งใน Line
        $newImg = NGROK.'/surasakbot/'.$item['path'];
        file_put_contents('log_med.txt', $newImg . PHP_EOL, FILE_APPEND);

        // ดึง user กลุ่ม admin กับ phar
        foreach ($medList as $keyMed => $medUserId) {

            $textMessageBuilder = new ImageMessageBuilder($newImg,$newImg);
            $response = $bot->pushMessage($medUserId, $textMessageBuilder);
            file_put_contents('log_med.txt', $response . PHP_EOL, FILE_APPEND);
        }
    }
    

}


// if ($response->isSucceeded()) {
//     echo 'Succeeded!';
//     return;
// }

// echo $response->getHTTPStatus() . ' ' . $response->getRawBody();