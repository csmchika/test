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
    $db = dbconnect();
    if(createBarcode($db)) {
        if (checkSecondAPI($data)) {
            saveOrder($data, $db);
        }
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
    if (checkFirstAPI($number)) {
        $sth = $db->prepare("INSERT INTO `test`.`barcodes` SET `barcode` = ?");
        $sth->execute(array($number));
        return true;
    }

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
    $json = '{"message": "order successfully booked"}';
    $res = json_decode($json, true);
//    $res = json_decode($response, true);
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
    return ($res['message'] == "order successfully aproved");
}
//(:data['event_id'], :data['ticket_adult_price'], :data['ticket_kid_price'], :data['event_date'])");
//function saveOrder($data, $db)
//{
//    $sth = $db->prepare("INSERT INTO `test`.`events`
//        (`event_id`, `ticket_adult_price`, `ticket_kid_price`, `event_date`)
//    VALUES
//        (:event_id, :ticket_adult_price, :ticket_kid_price, :event_date)");
//    $sth->bindParam(':event_id', $data['event_id']);
//    $sth->bindParam(':ticket_adult_price', $data['ticket_adult_price']);
//    $sth->bindParam(':ticket_kid_price', $data['ticket_kid_price']);
//    $sth->bindParam(':event_date', $data['event_date']);
//    $sth->execute();
//
//}
/**
 * @throws Exception
 */
function saveOrder($data, $db) {
    $data['category'] = $_POST['category'];
    print_r($data['category']);
    $sth = $db->prepare("INSERT INTO `test`.`orders` 
        (`event_id`, `event_date`, `ticket_adult_price`, `ticket_adult_quantity`, `ticket_kid_price`, `ticket_kid_quantity`, `barcode`, `user_id`, `equal_price`, `category`) 
    VALUES 
        (:event_id, :event_date, :ticket_adult_price, :ticket_adult_quantity, :ticket_kid_price, :ticket_kid_quantity, :barcode, :user_id, :equal_price, :category)");
    $equal = ($data['ticket_adult_price'] * $data['ticket_adult_quantity'] + $data['ticket_kid_price'] * $data['ticket_kid_quantity']);
    $number = $data['ticket_adult_quantity'] + $data['ticket_kid_quantity'];
    if ($data['category'] == "exemption") {
        $equal *= 0.9;
    } elseif ($data['category'] == "group") {
        $equal *= 0.8;
    }
    $sth->execute(array(
        ':event_id' => $data['event_id'],
        ':event_date' => $data['event_date'],
        ':ticket_adult_price' => $data['ticket_adult_price'],
        ':ticket_adult_quantity' => $data['ticket_adult_quantity'],
        ':ticket_kid_price' => $data['ticket_kid_price'],
        ':ticket_kid_quantity' => $data['ticket_kid_quantity'],
        ':barcode' => $data['barcode'],
        ':user_id' => '1123',
        ':equal_price' => $equal,
        ':category' => $data['category']

    ));
    for ($i=0; $i < $number; $i++) {
        $stH = $db->prepare("INSERT INTO `barcodes_group` 
        (`barcode_main`, `barcode_group`) 
    VALUES 
        (:barcode_main, :barcode_generate)");
        $stH->execute(array(
            ':barcode_main' => $data['barcode'],
            ':barcode_generate' => strval(random_int(100, 99999999))));
    }
}
try {
    task1();
} catch (Exception $e) {
    echo $e;
}