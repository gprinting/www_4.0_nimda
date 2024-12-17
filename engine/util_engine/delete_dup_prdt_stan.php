#! /usr/local/bin/php -f
<?
/**
 * @file delete_dup_prdt_stan.php
 *
 * @brief 중복된 상품 규격 삭제하면서 카테고리 규격 일련번호 수정
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$query  = "\n   SELECT  prdt_stan_seqno AS seqno";
$query .= "\n          ,sort";
$query .= "\n          ,typ";
$query .= "\n          ,name";
$query .= "\n          ,cut_wid_size AS cut_wid";
$query .= "\n          ,cut_vert_size AS cut_vert";
$query .= "\n          ,work_wid_size AS work_wid";
$query .= "\n          ,work_vert_size AS work_vert";
$query .= "\n     FROM  prdt_stan";
$query .= "\n ORDER BY seqno";

$chk_arr = array();

$rs = $conn->Execute($query);
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $seqno = $fields["seqno"];

    $key = sprintf("%s|%s|%s|%s|%s|%s|%s", $fields["sort"]
                                         , $fields["typ"]
                                         , $fields["name"]
                                         , $fields["cut_wid"]
                                         , $fields["cut_vert"]
                                         , $fields["work_wid"]
                                         , $fields["work_vert"]);
    if (empty($chk_arr[$key])) {
        $chk_arr[$key] = array();
    }

    $chk_arr[$key][] = $seqno;

    $rs->MoveNext();
}

$dup_arr = array();
foreach ($chk_arr as $seq_arr) {
    if (count($seq_arr) > 1) {
        $dup_arr[] = $seq_arr;
    }
}

$update = "\n UPDATE cate_stan SET prdt_stan_seqno = '%s' WHERE prdt_stan_seqno = '%s'";
$delete = "\n DELETE FROM prdt_stan WHERE prdt_stan_seqno = '%s'";

//$conn->debug = 1;
foreach ($dup_arr as $seq_arr) {
    $is_fst = true;
    $fst_seq = $seq_arr[0];
    unset($seq_arr[0]);

    foreach ($seq_arr as $seq) {
        $update_q = sprintf($update, $fst_seq, $seq);
        $delete_q = sprintf($delete, $seq);
        echo $update_q;
        echo $delete_q;
        echo "\n---";

        //$conn->Execute($update_q);
        //$conn->Execute($delete_q);
    }
}

$conn->Close();
?>
