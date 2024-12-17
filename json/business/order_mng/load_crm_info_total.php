<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * CRM정보 집계 검색 후
 * table html 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/24 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/common_lib/DateUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();
$dateUtil = new DateUtil();

$fb = $fb->getForm();

//$conn->debug = 1;

$member_seqno = $fb["seqno"];
$term_dvs     = $fb["term_dvs"];
$from         = $fb["from"];
$to           = $fb["to"];

$from_arr = explode('-', $fb["from"]);
$to_arr   = explode('-', $fb["to"]);

$tr_form  = "<tr class=\"crm_total_tr %s\" id=\"crm_total_tr_%s\" seq=\"%s\">";
$tr_form .=     "<td style=\"text-align:center\">%s</td>";
$tr_form .=     "<td class=\"crm_total_date\" style=\"text-align:center\">%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .=     "<td>%s</td>";
$tr_form .= "</tr>";

$param = array();
$param["member_seqno"] = $member_seqno;

$tbody .= '';
$i = 1;
if ($term_dvs === 'w') {
    // 주별집계
    // from 연/월에서 to 연/월까지 주차배열 생성
    $week_num_arr = $dateUtil->makeFromToWeekNumArr($from, $to);

    foreach ($week_num_arr as $ym => $w_arr) {
        $ym_arr = explode('-', $ym);

        foreach ($w_arr as $w => $d_arr) {
            $week_from = sprintf("%s-%s", $ym, $d_arr[0]);
            $week_to   = sprintf("%s-%s", $ym, $d_arr[count($d_arr) - 1]);

            $param["from"] = $week_from;
            $param["to"]   = $week_to;

            // 순매출, 입금액 검색
            $fields = $dao->selectDaySalesStats($conn, $param);
            // 선입금액 검색
            $prepay_bal = $dao->selectMemberPrepayBal($conn, $param);

            if ($i % 2 == 0) {
                $class = "";
            } else if ($i % 2 == 1) {
                $class = "cellbg";
            }

            $tbody .= sprintf($tr_form, $class
                                      , $i
                                      , $i
                                      , $i++
                                      , $ym_arr[0] . '년 ' . $ym_arr[1] . '월 ' . $w . "주차"
                                      , number_format($fields["sum_net_price"])
                                      , number_format($fields["sum_depo_price"])
                                      , number_format($prepay_bal));
        }
    }
} else if ($term_dvs === 'm') {
    $from_y = intval($from_arr[0]);
    $from_m = intval($from_arr[1]);
    $to_y   = intval($to_arr[0]);
    $to_m   = intval($to_arr[1]);

    for ($y = $from_y; $y <= $to_y; $y++) {
        for ($m = $from_m; $m <= $to_m; $m++) {
            $param["from"] = $y . '-' . $m . '-01';
            $param["to"]   = $y . '-' . $m . '-' . $dateUtil->getLastDay($y, $m);

            // 순매출, 입금액 검색
            $fields = $dao->selectDaySalesStats($conn, $param);
            // 선입금액 검색
            $prepay_bal = $dao->selectMemberPrepayBal($conn, $param);

            if ($i % 2 == 0) {
                $class = "";
            } else if ($i % 2 == 1) {
                $class = "cellbg";
            }

            $tbody .= sprintf($tr_form, $class
                                      , $i
                                      , $i
                                      , $i++
                                      , $y . '년 ' . $m . '월'
                                      , number_format($fields["sum_net_price"])
                                      , number_format($fields["sum_depo_price"])
                                      , number_format($prepay_bal));
        }
    }
} else if ($term_dvs === 'y') {
    $from_y = intval($from_arr[0]);
    $to_y   = intval($to_arr[0]);

    for ($y = $from_y; $y <= $to_y; $y++) {
        $param["from"] = $y . '-01-01';
        $param["to"]   = $y . '-12-' . $dateUtil->getLastDay($y, '12');

        // 순매출, 입금액 검색
        $fields = $dao->selectDaySalesStats($conn, $param);
        // 선입금액 검색
        $prepay_bal = $dao->selectMemberPrepayBal($conn, $param);

        if ($i % 2 == 0) {
            $class = "";
        } else if ($i % 2 == 1) {
            $class = "cellbg";
        }

        $tbody .= sprintf($tr_form, $class
                                  , $i
                                  , $i
                                  , $i++
                                  , $y . '년'
                                  , number_format($fields["sum_net_price"])
                                  , number_format($fields["sum_depo_price"])
                                  , number_format($prepay_bal));
    }
}


$thead = '';

FIN:
    $json  = '{';
    $json .= "\"thead\" : \"%s\", \"tbody\" : \"%s\"";
    $json .= '}';

    echo sprintf($json, $util->convJsonStr($thead)
                      , $util->convJsonStr($tbody));

    $conn->Close();
    exit;
?>
