#! /usr/local/bin/php
<?php
/**
 * @file request_mono_price.php
 *
 * @brief 회원 결제내역에 입금내역 추가하면서 선입금액 수정
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');
include_once('/home/sitemgr/inc/define/nimda/order_mng_define.inc');

$amt_arr = [
    0.5, 1, 2, 3, 4, 5, 6, 7, 8, 9,
    10, 11, 12, 13, 14, 15, 16, 17, 18, 19,
    20, 21, 22, 23, 24, 25, 26, 27, 28, 29,
    30, 31, 32, 33, 34, 35, 36, 37, 38, 39,
    40, 41, 42, 43, 44, 45, 46, 47, 48, 49,
    50, 51, 52, 53, 54, 55, 56, 57, 58, 59,
    60, 61, 62, 63, 64, 65, 66, 67, 68, 69,
    70, 71, 72, 73, 74, 75, 76, 77, 78, 79,
    80, 81, 82, 83, 84, 85, 86, 87, 88, 89,
    90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100
];
$count = count($amt_arr);

$url = "http://172.16.33.207/ajax/product/load_calc_price.php";

$post_data = [
    "dvs" => "bl"
    ,"cate_sortcode" => "005002001"
    ,"stan_mpcode" => "401"
    ,"affil" => "국"
    ,"paper_mpcode" => "250"
    ,"bef_print_mpcode" => "116"
    ,"bef_add_print_mpcode" => "" 
    ,"aft_add_print_mpcode" => ""
    ,"bef_print_name" => "칼라4도"
    ,"bef_add_print_name" => ""
    ,"aft_add_print_name" => ""
    ,"print_purp" => "일반옵셋"
    ,"page_info" => "2"
    ,"flattyp_yn" => "Y"
    ,"amt_unit" => "R"
    ,"pos_num" => "8"
];

$fd = fopen("./ret.csv", "w");

$ch  = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
//curl_setopt($ch, CURLOPT_VERBOSE, true);

for ($i = 0; $i < $count; $i++) {
    $post_data["amt"] = $amt_arr[$i];

    echo $i . " / " . $count . " : " . $amt_arr[$i] . "\r";

    for ($j = 0; $j < 2; $j++) {
        if (!($j % 2)) {
            // 4
            $post_data["aft_print_mpcode"] = "120";
            $post_data["aft_print_name"] = "없음";
        } else {
            // 8
            $post_data["aft_print_mpcode"] = "122";
            $post_data["aft_print_name"] = "칼라4도";
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // { "bl"  : { "paper"  : "48340", "print"  : "57200", "output" : "35200", "sell_price" : "140740"}}"
        /*
           array(1) {
               ["bl"]=>
                   array(4) {
                       ["paper"]=> string(5) "82650"
                       ["print"]=> string(5) "35200"
                       ["output"]=> string(5) "17600"
                       ["sell_price"]=> string(6) "135450"
                   }
           }
        */
        $json = json_decode(curl_exec($ch), true);
        $json = $json["bl"];

        $flds = [
             round(($json["paper"] / 1.1) * 0.1) * 10.0
            ,round(($json["output"] / 1.1) * 0.1) * 10.0
            ,round(($json["print"] / 1.1) * 0.1) * 10.0
            ,round(($json["sell_price"] / 1.1) * 0.1) * 10.0
        ];
        fputcsv($fd, $flds);
    }

    sleep(0.5);
}

fclose($fd);
curl_close($ch);
