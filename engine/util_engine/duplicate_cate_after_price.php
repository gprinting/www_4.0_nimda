#! /usr/local/bin/php -f
<?
/**
 * @file duplicate_cate_after_price.php
 *
 * @brief 카테고리 후공정간 가격 복사
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

if (count($argv) < 3) {
    echo "Useage : ./duplicate_cate_after_price.php [from_sortcode] [to_sortcode]\n";
    exit;
}

$from_sortcode = $argv[1];
$to_sortcode   = $argv[2];
$name = $argv[3];

if (!isset($from_sortcode[8]) || !isset($to_sortcode[8])) {
    echo "카테고리 소분류코드 입력\n";
    exit;
}

$query  = "\n SELECT A.mpcode AS from_mpcode, B.mpcode AS to_mpcode";
$query .= "\n FROM cate_after AS A, cate_after AS B";
$query .= "\n WHERE A.prdt_after_seqno = B.prdt_after_seqno";
$query .= "\n AND A.cate_sortcode = '%s'";
$query .= "\n AND B.cate_sortcode = '%s'";
$query .= "\n AND A.affil = B.affil";
$query .= "\n AND A.subpaper = B.subpaper";
$query .= "\n AND A.basic_yn = B.basic_yn";
$query .= "\n AND A.crtr_unit = B.crtr_unit ";
if (!empty($name)) {
    $query .= "\n AND A.name = '" . $name . "'";
}
$query  = sprintf($query, $from_sortcode, $to_sortcode);

//echo $query;exit;

$rs = $conn->Execute($query);

$query  = "\n SELECT A.*";
$query .= "\n FROM cate_after_price AS A";
$query .= "\n WHERE A.cate_after_mpcode = '%s'";

$price_query  = "\n INSERT INTO cate_after_price (";
$price_query .= "\n      cate_after_mpcode";
$price_query .= "\n     ,amt";
$price_query .= "\n     ,basic_price";
$price_query .= "\n     ,sell_rate";
$price_query .= "\n     ,sell_aplc_price";
$price_query .= "\n     ,sell_price";
$price_query .= "\n     ,cpn_admin_seqno";
$price_query .= "\n ) VALUES ";
$values  = "\n ('%s', '%s', '%s', '%s', '%s', '%s', '%s'),";

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $from_mpcode = $fields["from_mpcode"];
    $to_mpcode   = $fields["to_mpcode"];

    $price_rs = $conn->Execute(sprintf($query, $from_mpcode));

    $q_str = $price_query;
    $str_tmp = '';

    if ($price_rs->EOF) {
        $rs->MoveNext();
        continue;
    }

    while ($price_rs && !$price_rs->EOF) {
        $fld = $price_rs->fields;
        $str_tmp .= sprintf($values, $to_mpcode
                                   , $fld["amt"]
                                   , $fld["basic_price"]
                                   , $fld["sell_rate"]
                                   , $fld["sell_aplc_price"]
                                   , $fld["sell_price"]
                                   , $fld["cpn_admin_seqno"]);
        $price_rs->MoveNext();
    }

    $q_str .= substr($str_tmp, 0, -1);

    echo "\n# from : $from_mpcode -> to : $to_mpcode";
    echo $q_str;

    $conn->debug = 1;
    $conn->StartTrans();
    $conn->Execute($q_str);
    $conn->CompleteTrans();
    $conn->debug = 0;

    //sleep(1);

    $rs->MoveNext();
}
unset($rs);
?>
