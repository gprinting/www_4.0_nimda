#! /usr/local/bin/php -f
<?
/**
 * @file duplicate_order_data.php
 *
 * @brief 주문번호와 복제개수를 입력받아 주문번호 데이터 복제
 *
 * @detail 한 주문에 대해서 값을 복사해야 하는 테이블은 다음과 같다
 * 1. order_common
 * 2. order_detail            (FK : order_common_seqno)
 * 3. order_detail_count_file (FK : order_detail_seqno)
 * 4. order_after_history     (FK : order_common_seqno)
 * 5. order_opt_history       (FK : order_common_seqno)
 * 6. order_dlvr              (FK : order_common_seqno)
 * 7. amt_order_detail_sheet  (FK : order_detail_count_file_seqno)
 * 8. order_file              (FK : order_common_seqno)
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$dao = new CommonDAO();

if (count($argv) !== 3) {
    echo "Useage : ./duplicate_order_date.php [order_num] [duplicate_count]\n";
    exit;
}

$opt_arr = array(
    array(
        "opt_name" => "당일판",
        "depth1"   => "오전12시 마감",
        "depth2"   => "-",
        "depth3"   => "-",
        "price"    => "0",
        "basic_yn" => "N",
        "detail"   => ""
    ),
    array(
        "opt_name" => "사고",
        "depth1"   => "-",
        "depth2"   => "-",
        "depth3"   => "-",
        "price"    => "0",
        "basic_yn" => "N",
        "detail"   => ""
    ),
    array(
        "opt_name" => "빠른생산요청",
        "depth1"   => "무료",
        "depth2"   => "-",
        "depth3"   => "-",
        "price"    => "0",
        "basic_yn" => "N",
        "detail"   => ""
    ),
    array(
        "opt_name" => "재단주의",
        "depth1"   => "-",
        "depth2"   => "-",
        "depth3"   => "-",
        "price"    => "0",
        "basic_yn" => "N",
        "detail"   => ""
    ),
    array(
        "opt_name" => "색견본참고",
        "depth1"   => "인쇄물전달",
        "depth2"   => "-",
        "depth3"   => "-",
        "price"    => "0",
        "basic_yn" => "N",
        "detail"   => ""
    ),
    array(
        "opt_name" => "베다인쇄",
        "depth1"   => "100%베다",
        "depth2"   => "-",
        "depth3"   => "-",
        "price"    => "0",
        "basic_yn" => "N",
        "detail"   => ""
    ),
    array(
        "opt_name" => "감리요청",
        "depth1"   => "인쇄감리",
        "depth2"   => "-",
        "depth3"   => "-",
        "price"    => "0",
        "basic_yn" => "N",
        "detail"   => ""
    )
);

$order_num = $argv[1];
$dup_count = intval($argv[2]);

// 1. 복사할 주문공통정보 검색
$fields = $dao->selectOrderCommon($conn, $order_num);

$org_order_common_seqno = $fields["order_common_seqno"];

$last_num = $dao->selectOrderCommonLastNum($conn);

//$conn->debug = 1;

for ($i = 0; $i < $dup_count; $i++) {
    // 1. order_common
    $dao->insertOrderCommon($conn, $fields, $last_num);

    $order_common_seqno = $conn->Insert_ID();

    // 2.order_detail
    $detail_fields = $dao->selectOrderDetail($conn, $org_order_common_seqno);
    $detail_fields["order_common_seqno"] = $order_common_seqno;
    $ret = $dao->insertOrderDetail($conn, $detail_fields, $last_num++);

    $org_order_detail_seqno = $detail_fields["order_detail_seqno"];
    $order_detail_seqno = $conn->Insert_ID();
    $org_order_detail_dvs_num = $detail_fields["order_detail_dvs_num"];
    $order_detail_dvs_num = $ret["dvs_num"];

    // 4. order_after_history
    $after_rs = $dao->selectOrderAfterHistory($conn, $org_order_detail_dvs_num);
    while ($after_rs && !$after_rs->EOF) {
        $after_fields = $after_rs->fields;

        $after_fields["order_detail_dvs_num"] = $order_detail_dvs_num;
        $after_fields["order_common_seqno"] = $order_common_seqno;
        $dao->insertOrderAfterHistory($conn, $after_fields);

        $after_rs->MoveNext();
    }

    // 5. order_opt_history
    $opt_rs = $dao->selectOrderOptHistory($conn, $org_order_common_seqno);
    while ($opt_rs && !$opt_rs->EOF) {
        $opt_fields = $opt_rs->fields;

        $opt_fields["order_common_seqno"] = $order_common_seqno;
        $dao->insertOrderOptHistory($conn, $opt_fields);

        $temp_opt = $opt_arr[$i % 7];
        $temp_opt["order_common_seqno"] = $order_common_seqno;
        $dao->insertOrderOptHistory($conn, $temp_opt);

        $opt_rs->MoveNext();
    }

    // 6. order_dlvr
    $dlvr_rs = $dao->selectOrderDlvr($conn, $org_order_common_seqno);
    while ($dlvr_rs && !$dlvr_rs->EOF) {
        $dlvr_fields = $dlvr_rs->fields;

        $dlvr_fields["order_common_seqno"] = $order_common_seqno;
        $dao->insertOrderDlvr($conn, $dlvr_fields);

        $dlvr_rs->MoveNext();
    }

    // 7. amt_order_detail_sheet
    $amt_detail_sheet_fields = $dao->selectAmtOrderDetailSheet($conn,
                                                               $org_order_detail_count_file_seqno);
    $amt_detail_sheet_fields["order_detail_count_file_seqno"] = $order_detail_count_file_seqno;
    $dao->insertAmtOrderDetailSheet($conn, $amt_detail_sheet_fields);

    // 8.order_file
    $file_fields = $dao->selectOrderFile($conn, $org_order_common_seqno);
    $file_fields["order_common_seqno"] = $order_common_seqno;
    $dao->insertOrderFile($conn, $file_fields);

    echo "processing : " . ($i + 1) . "/ $dup_count\r";
}

echo "\n";
?>
