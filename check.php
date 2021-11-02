<?php
$data['event_id'] = '003';
$data['event_date'] = '2021-08-21';
$data['ticket_adult_price'] = '700';
$data['ticket_adult_quantity'] ='1';
$data['ticket_kid_price'] = '450';
$data['ticket_kid_quantity'] = '0';
$data['barcode'] = '114239781';
$ch = curl_init('https://api.site.com/book');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$response= curl_exec($ch);
curl_close($ch);
$json = '{"error": "order successfully booked"}';
$res = json_decode($json, true);
print_r($res);
//$res = json_encode($response, JSON_UNESCAPED_UNICODE);
if ($res['message'] == "order successfully booked") {
    echo 1;
} else {
    echo 2;
}