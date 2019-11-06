<?php 

include 'vendor/autoload.php';
include 'connect.php';

/*Get Data From POST Http Request*/
$datas = file_get_contents('php://input');
/*Decode Json From LINE Data Body*/
$deCode = json_decode($datas,true);

file_put_contents('log.txt', $datas . PHP_EOL, FILE_APPEND);

$mysqli_smdb = mysqli_connect(HOST1, USERNAME1, PASSWORD1, DB_NAME1);
$mysqli_line = mysqli_connect(HOST2, USERNAME2, PASSWORD2, DB_NAME2);

$channel_access_token = CHANNEL_ACCESS_TOKEN;
$channel_secret = CHANNEL_SECRET;

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($channel_access_token);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$userId = $deCode['events'][0]['source']['userId'];

if( $deCode['events'][0]['type'] == 'message' ){

    $message_from_user = $deCode['events'][0]['message']['text'];
    $replyToken = $deCode['events'][0]['replyToken'];
    $text = 'ระบบยังไม่รองรับการตอบกลับอัตโนมัติ รอแป๊บเด้อ';

    if( $message_from_user === 'อับดุล' ){
        $text = 'เอ้ย!';

    }elseif( $message_from_user === 'ถามอะไรตอบได้' ){
        $text = 'ตอบได้';

    }elseif( $message_from_user === 'ตอนนี้กี่โมง' ){
        $text = date('H:i:s');

    }elseif( $message_from_user === 'ขอดาต้า' ){
        $text = $datas;

    }elseif( preg_match('/ลงทะเบียน\s?(\d+)/', $message_from_user, $matchs) > 0 ){ 

        $idcard = $matchs['1'];

        
        $sql = "SELECT `hn`,`idcard`,CONCAT(`name`,' ',`surname`) AS `ptname` FROM `opcard` WHERE `idcard` = '$idcard' ";
        $result = $mysqli_smdb->query($sql);
        if ($result->num_rows > 0) {

            $actor = $result->fetch_assoc();

            $idcard = $actor['idcard'];
            $ptname = $actor['ptname'];
            $timestamp = $deCode['events'][0]['timestamp'];

            $test_res = $mysqli_line->query("SELECT * FROM `line_user` WHERE `idcard` = '$idcard' ");
            if ($test_res->num_rows > 0) {
                $text = 'คุณเคยลงทะเบียนไปแล้ว';

            }else{
                
                $sql_insert = "INSERT INTO `line_user` (`id`, `idcard`, `userId`, `fullname`, `timestamp`) 
                VALUES (NULL, '$idcard', '$userId', '$ptname', '$timestamp');";
                $mysqli_line->query($sql_insert);
                $text = 'ขณะนี้คุณ'.$actor['ptname'].' HN: '.$actor['hn'].' ได้ลงทะเบียนเรียบร้อยแล้ว ขอบคุณครับ';

            }

        }else{
            $text = 'ขออภัยครับ ไม่สามารถลงทะเบียนได้ กรุณาโทร.054-839305 ต่อ 8500 ขอบคุณครับ';
        }

        

    }elseif( $message_from_user === 'ขอดูใบนัด' ){

        $line_res = $mysqli_line->query("SELECT `idcard` FROM `line_user` WHERE `userId` = '$userId' ");
        if ($line_res->num_rows > 0) {
            
            $text = 'asdfasdfasdfasdfsdf';

            $item = $line_res->fetch_assoc();
            $idcard = $item['idcard'];
            $text = $item;
            $sql_app = "SELECT b.`appdate`,b.`apptime`,b.`room`,b.`advice`  
            FROM ( 
                SELECT `hn` FROM `opcard` WHERE `idcard` = '$idcard' 
            ) AS a 
            LEFT JOIN `appoint` AS b ON b.`hn` = a.`hn` 
            ORDER BY b.`row_id` DESC";
            $res_app = $mysqli_smdb->query($sql_app);
            if ($res_app->num_rows > 0) {
                $app = $res_app->fetch_assoc();
                $text = 'คุณมีนัดวันที่ '.$app['appdate'].' เวลา'.$app['apptime'].' ยื่นจุดนัดที่:'.$app['room'].' คำแนะนำ:'.$app['advice'];

            }else{
                $text = 'คุณยังไม่มีวันนัด ขอบคุณครับ';
            }

        }
        
        else{
            $text = 'กรุณาลงทะเบียนด้วยครับ';
        }

    }elseif( $message_from_user === 'howto' ){
        $text = 'มีอะไรที่ bot สามารถทำได้ตอนนี้'."\n";
        $text .= '1. เล่น อับดุล ถามอะไรตอบได้'."\n";
        $text .= '2. ตอนนี้กี่โมง'."\n";
        $text .= '3. ขอดาต้า'."\n";
        $text .= '4. ลงทะเบียน <เลขบัตรประชาชน>'."\n";
        $text .= '5. ขอดูใบนัด'."\n";
        
    }elseif( $message_from_user === 'version' ){
        $text = 'Version: pre-alpha, Develop by Kritsanasak Kuntaros รักนะจ๊ะ จุ๊บุ จุ๊บุ';
    }
    

    $textMessageBuilder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text);
    $response = $bot->replyMessage($replyToken, $textMessageBuilder);
    return;

    echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
}


$mysqli_smdb->close();

return;