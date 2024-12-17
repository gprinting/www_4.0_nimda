#! /usr/local/bin/php -f
<?
/**
 * @file select_insert_manu_price.php
 *
 * @brief 2.0 자유형 도무송 데이터 3.0 테이블로 이동입력
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

// 출력
$query  = "\n SELECT output_seqno AS seqno,";
$query .= "\n        CONCAT(extnl_brand_seqno, '|', search_check) AS search_check,";
$query .= "\n        amt,";
$query .= "\n        basic_price,";
$query .= "\n        pur_rate,";
$query .= "\n        pur_aplc_price,";
$query .= "\n        pur_price";
$query .= "\n   FROM output";
$query .= "\n ORDER BY search_check, (amt + 0)";
$rs = $conn->Execute($query);

$output_arr = makeSeqArr($rs);
//print_r($output_arr);
insertPrice($conn, $output_arr, "output");

// 인쇄
$query  = "\n SELECT print_seqno AS seqno,";
$query .= "\n        CONCAT(extnl_brand_seqno, '|', top, '|', name, '|', wid_size, '|', vert_size) AS search_check,";
$query .= "\n        amt,";
$query .= "\n        basic_price,";
$query .= "\n        pur_rate,";
$query .= "\n        pur_aplc_price,";
$query .= "\n        pur_price";
$query .= "\n   FROM print";
$query .= "\n  WHERE extnl_brand_seqno IS NOT NULL";
$query .= "\n ORDER BY extnl_brand_seqno, top, name, affil, (amt + 0)";
$rs = $conn->Execute($query);

$print_arr = makeSeqArr($rs);
//print_r($print_arr);
insertPrice($conn, $print_arr, "print");

// 후공정
$query  = "\n SELECT after_seqno AS seqno,";
$query .= "\n        CONCAT(extnl_brand_seqno, '|', name, '|', depth1, '|', depth2, '|', depth3, '|', affil , '|', subpaper) AS search_check,";
$query .= "\n        amt,";
$query .= "\n        basic_price,";
$query .= "\n        pur_rate,";
$query .= "\n        pur_aplc_price,";
$query .= "\n        pur_price";
$query .= "\n   FROM after";
$query .= "\n  WHERE extnl_brand_seqno IS NOT NULL";
$query .= "\n ORDER BY extnl_brand_seqno, name, depth1, depth2, affil, subpaper, (amt + 0)";
$rs = $conn->Execute($query);

$after_arr = makeSeqArr($rs);
//print_r($after_arr);
insertPrice($conn, $after_arr, "after");

/*****************************************************************************
 *****************************************************************************/

function makeSeqArr($rs) {
    $dup_chr = array();
    $seq_arr = array();
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        if (empty($dup_chk[$fields["search_check"]])) {
            $dup_chk[$fields["search_check"]] = $fields["seqno"];

            $seq_arr[$fields["seqno"]] = array();
            $seq_arr[$fields["seqno"]][$fields["amt"]] =
                sprintf("%s!%s!%s!%s", $fields["basic_price"]
                                     , $fields["pur_rate"]
                                     , $fields["pur_aplc_price"]
                                     , $fields["pur_price"]);
        } else {
            $seq_arr[$dup_chk[$fields["search_check"]]][$fields["amt"]] =
                sprintf("%s!%s!%s!%s", $fields["basic_price"]
                                     , $fields["pur_rate"]
                                     , $fields["pur_aplc_price"]
                                     , $fields["pur_price"]);
        }

        $rs->MoveNext();
    }

    return $seq_arr;
}

function insertPrice($conn, $arr, $dvs) {
    $query  = "\n INSERT INTO {$dvs}_price (";
    $query .= "\n      {$dvs}_seqno";
    $query .= "\n     ,amt";
    $query .= "\n     ,basic_price";
    $query .= "\n     ,pur_rate";
    $query .= "\n     ,pur_aplc_price";
    $query .= "\n     ,pur_price";
    $query .= "\n ) VALUES (";
    $query .= "\n      %s";
    $query .= "\n     ,%s";
    $query .= "\n     ,%s";
    $query .= "\n     ,%s";
    $query .= "\n     ,%s";
    $query .= "\n     ,%s";
    $query .= "\n )";

    $conn->debug = 1;

    foreach ($arr as $seqno => $price_info_arr) {
        foreach($price_info_arr as $amt => $info) {
            $price = explode('!', $info);

            $q_str = sprintf($query, $seqno
                                   , $amt
                                   , $price[0]
                                   , $price[1]
                                   , $price[2]
                                   , $price[3]);
                                   

            //echo $q_str;
            $ret = $conn->Execute($q_str);

            if ($ret === false) exit;
        }
    }
};
?>
