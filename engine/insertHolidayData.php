#! /usr/local/php/bin/php
<?php
/**
 * @file insertHolidayData.php 
 *
 * @brief 공공데이터포털 공휴일 검색 api로 금년 공휴일 전체검색해서 입력
 * 1년마다 사용연장 필요
 */
include_once(dirname(__FILE__) . '/common/ConnectionPool.php');

// 
$url  = 'http://apis.data.go.kr/B090041/openapi/service/SpcdeInfoService/getRestDeInfo';
$url .= '?' . urlencode('ServiceKey') . '=yUi2svbb39CUcJloUVwy0x%2BDL1JBqo1EOz4uki5atTfQ0U%2FYSozHltpyz2MFxlHHWCyrFdscvhn6ciTnPONPSw%3D%3D';
$url .= '&' . urlencode('solYear') . '=' . urlencode(date('Y'));

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

$rest_arr = [];

$m = intval(date('n'));
for (; $m <= 12; $m++) {
    $temp_m = str_pad(strval($m), 2, '0', STR_PAD_LEFT);
    $temp_url = $url . '&' . urlencode('solMonth') . '=' . urlencode($temp_m);

    curl_setopt($ch, CURLOPT_URL, $temp_url);
    $res = curl_exec($ch);

    $xml_obj = simplexml_load_string($res);

    $items = $xml_obj->body->items;
    unset($xml_obj);

    foreach ($items->children() as $item) {
        $date = str_split($item->locdate);
        $rest_y = implode(array_slice($date, 0, 4));
        $rest_m = implode(array_slice($date, 4, 2));
        $rest_d = implode(array_slice($date, 6, 2));

        $rest_arr[] = [
            "date" => sprintf("%s-%s-%s", $rest_y, $rest_m, $rest_d)
            ,"name" => (string)$item->dateName
        ];
    }

}
curl_close($ch);

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$query_form  = "\n INSERT INTO holi_info(date, name, aplc_yn)";
$query_form .= "\n      VALUES ('%s', '%s', 'Y')";

$conn->debug = 1;

foreach ($rest_arr as $rest) {
    $q_str = sprintf($query_form, $rest["date"], $rest["name"]);

    $conn->Execute($q_str);
}

$conn->Close();
