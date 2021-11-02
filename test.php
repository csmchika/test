<?php
//event_id, event_date, ticket_adult_price, ticket_adult_quantity, ticket_kid_price, ticket_kid_quantity
/**
 * @throws Exception
 */
function task1()
{
    $barcode = createBarcode(dbconnect());



}
function dbconnect($dbname="test", $host="localhost", $username="root", $pass="root"): PDO
{
    return new PDO("mysql:dbname=$dbname;host=$host", "$username", "$pass");
}

/**
 * @throws Exception
 */
function createBarcode($db): int
{
    do {
        // Генерируем код
        $number = random_int(100, 99999999);
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
    return $number;
}

try {
    task1();
} catch (Exception $e) {
    echo $e;
}