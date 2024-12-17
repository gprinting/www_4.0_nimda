#! /usr/local/bin/php -f
<?
/**
 * @file insert_after_foil_press.php
 *
 * @brief 2.0 후공정 박, 형압 데이터 3.0으로 포팅하는 스크립트
 *
 * @detail
 * 2.0 AGroup_1 -> 06 = 박, 07 = 형압
 * 2.0 AGroup_2 -> 0n = 금박, 1n = 은박, 2n = 청박, 3n = 적박, 4n = 녹박, 5n = 먹박
 *              -> n = 1 : 단면, n = 2 : 양면같음, n = 3 : 양면다름
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CommonDAO();

$select_query  = "\n select case a.Ca_Code when 'N11' then '코팅명함'";
$select_query .= "\n                       when 'N12' then '무코팅명함'";
$select_query .= "\n                           when 'N13' then '고품격 코팅명함'";
$select_query .= "\n                           when 'N14' then '고품격 무코팅명함'";
$select_query .= "\n                           when 'N20' then '고급명함'";
$select_query .= "\n       end as cate_name,";
$select_query .= "\n       a.AGroup_1,";
$select_query .= "\n       a.AGroup_2,";
$select_query .= "\n       a.Qty,";
$select_query .= "\n       a.Price";
$select_query .= "\n from WEB_AfterProcessPrice as a";
$select_query .= "\n where a.AGroup_1 in ('06', '07')";

$rs = $conn->Execute($select_query);

$query  = "\n INSERT INTO after_foil_press_price (";
$query .= "\n     cate_sortcode ";
$query .= "\n    ,after_name";
$query .= "\n    ,dvs";
$query .= "\n    ,amt";
$query .= "\n    ,price";
$query .= "\n ) VALUES (";
$query .= "\n     '%s'";
$query .= "\n    ,'%s'";
$query .= "\n    ,'%s'";
$query .= "\n    ,'%s'";
$query .= "\n    ,'%s'";
$query .= "\n )";


$i = 0;
$rc = $rs->RecordCount();
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $agroup_1   = $fields["AGroup_1"];
    $agroup_2   = intval($fields["AGroup_2"]);
    $after_name = intval($agroup_2 / 10);
    $dvs        = intval($agroup_2 % 10);

    $cate_sortcode = $dao->selectCateName($conn, $fields["cate_name"]);
    $amt           = intval($fields["Qty"]);
    $price         = intval($fields["Price"]);

    if ($agroup_1 === "06") {
        switch ($after_name) {
        case '0' :
            $after_name = "금박";
            break;
        case '1' :
            $after_name = "은박";
            break;
        case '2' :
            $after_name = "청박";
            break;
        case '3' :
            $after_name = "적박";
            break;
        case '4' :
            $after_name = "녹박";
            break;
        case '5' :
            $after_name = "먹박";
            break;
        }
    } else {
            $after_name = "형압";
    }

    switch ($dvs) {
    case '1' :
        $dvs = "단면";
        break;
    case '2' :
        $dvs = "양면";
        break;
    case '3' :
        $dvs = "양면다름";
        break;
    }

    if (isset($cate_sortcode[6])) {
        $q_str = sprintf($query, $cate_sortcode
                               , $after_name
                               , $dvs
                               , $amt
                               , $price);

        $conn->Execute($q_str);
    } else {
        $J = 0;
        $cate_rs = $dao->selectCateBot($conn, $cate_sortcode);

        $cate_rc = $cate_rs->RecordCount();

        while ($cate_rs && !$cate_rs->EOF) {
            $cate_sortcode = $cate_rs->fields["sortcode"];

            $q_str = sprintf($query, $cate_sortcode
                                   , $after_name
                                   , $dvs
                                   , $amt
                                   , $price);

            $conn->Execute($q_str);

            $cate_rs->MoveNext();

            echo "j::::::::::::: " . $j++ . " / $cate_rc\r";
        }
    }

    echo "i::::::: " . $i++ . " / $cate_rc\r";

    $rs->MoveNext();
}
?>
