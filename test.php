<?php
$data['event_id'] = $_POST['event_id'];
$data['event_date'] = $_POST['event_date'];
$data['ticket_adult_price'] = $_POST['ticket_adult_price'];
$data['ticket_adult_quantity'] = $_POST['ticket_adult_quantity'];
$data['ticket_kid_price'] = $_POST['ticket_kid_price'];
$data['ticket_kid_quantity'] = $_POST['ticket_kid_quantity'];
/**
 * @throws Exception
 */
function task1()
{
    global $data;
    createBarcode(dbconnect());
    if (checkSecondAPI($data)) {
        print_r($data);
//        saveOrder();
    }



}
function dbconnect($dbname="test", $host="localhost", $username="root", $pass="root"): PDO
{
    return new PDO("mysql:dbname=$dbname;host=$host", "$username", "$pass");
}

/**
 * @throws Exception
 */
function createBarcode($db)
{
    do {
        // Генерируем код
        $number = random_int(100, 99999999);
        if (checkFirstAPI($number)) {
            return 0;
        }
        echo $number;
        // Проверяем в БД
        $sth = $db->prepare("SELECT * FROM `test`.`barcodes` WHERE `barcode` = ?");
        $sth->execute(array($number));
        $is_db = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($is_db)){
            $cont = true;
        } else {
            $cont = false;
        }
    } while ($cont === true);
    // Сохранение уникального кода
    $sth = $db->prepare("INSERT INTO `test`.`barcodes` SET `barcode` = ?");
    $sth->execute(array($number));
}

function checkFirstAPI($barcode): bool
{
    global $data;
    $data['barcode'] = $barcode;

    $ch = curl_init('https://api.site.com/book');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response= curl_exec($ch);
    curl_close($ch);
    $json = '{message": "order successfully booked"}';
    $res = json_decode($json, true);
//    $res = json_decode($response, true);
    echo 1;
    return ($res['message'] == "order successfully booked");
}

function checkSecondAPI($barcode): bool
{
    $ch = curl_init('https://api.site.com/approve');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $barcode);

    $response= curl_exec($ch);
    curl_close($ch);
    $json = '{"message": "order successfully aproved"}';
    $res = json_decode($json, true);
//    $res = json_decode($response, true);
    echo 2;
    return ($res['message'] == "order successfully aproved");
}

function saveOrder($event_id, $event_date, $ticket_adult_price, $ticket_adult_quantity, $ticket_kid_price, $ticket_kid_quantity, $barcode, $db)
{

}
try {
    task1();
} catch (Exception $e) {
    echo $e;
}