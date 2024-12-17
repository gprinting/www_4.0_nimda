#! /usr/local/bin/php -f
<?
/**
 * @file delete_dup_prdt_paper.php
 *
 * @brief 중복된 상품종이 삭제
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

//  prdt_paper_seqno | name | dvs | color | sort | affil | basisweight | size | search_check | mpcode | basisweight_unit | crtr_unit
$query  = "\n SELECT a.*";
$query .= "\n FROM prdt_paper as a";
$query .= "\n LEFT OUTER JOIN prdt_paper_price as b";
$query .= "\n ON a.mpcode = b.prdt_paper_mpcode";

$rs = $conn->Execute($query);

$dup_arr = array();
$seqno_arr = array();

while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $key = sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s", $fields["name"]
                                               , $fields["dvs"]
                                               , $fields["color"]
                                               , $fields["sort"]
                                               , $fields["affil"]
                                               , $fields["basisweight"]
                                               , $fields["size"]
                                               , $fields["basisweight_unit"]
                                               , $fields["crtr_unit"]);

    if (empty($dup_arr[$key])) {
        $dup_arr[$key] = true;
    } else {
        echo $key . "\n";
        $seqno_arr[$key] = $fields["prdt_paper_seqno"];
    }

    $rs->MoveNext();
}
unset($rs);

$query  = "\n DELETE ";
$query .= "\n   FROM prdt_paper";
$query .= "\n  WHERE prdt_paper_seqno = '%s'";

$arr_count = count($arr);

foreach ($seqno_arr as $seqno) {
    $q_str = sprintf($query, $seqno);

    //$conn->Execute($q_str);
    echo $q_str;
}
?>
