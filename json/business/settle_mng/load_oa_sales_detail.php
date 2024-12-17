<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/08/03 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/settle_mng/SettleMngDAO.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/common_lib/DateUtil.inc");
include_once(INC_PATH . "/define/nimda/order_mng_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SettleMngDAO();
$util = new CommonUtil();
$dateUtil = new DateUtil();

$fb = $fb->getForm();

$cpn_admin_seqno   = $fb["cpn_admin_seqno"];
$basic_from        = $fb["basic_from"];
$basic_to          = $fb["basic_to"];
$high_depar_code   = $fb["high_depar"];
$depar_code        = $fb["depar"];
$empl_seqno        = $fb["empl"];
//$depo_input_typ    = $fb["depo_input_typ"];
//$depo_input_detail = $fb["depo_input_detail"];
//$deal_yn           = $fb["deal_yn"];
$dlvr_dvs          = $fb["dlvr_dvs"];
$dlvr_code         = $fb["dlvr_code"];
$search_dvs        = $fb["search_dvs"];
$search_keyword    = $fb["search_keyword"];
$member_typ        = $fb["member_typ"];
$member_grade      = $fb["member_grade"];
$oper_sys          = $fb["oper_sys"];
$oa_yn             = $fb["oa_yn"];

$cate_top          = $fb["cate_top"];
$cate_mid          = $fb["cate_mid"];
$cate_bot          = $fb["cate_bot"];

$cate_sortcode     = $cate_top;
if (!empty($cate_mid)) {
    $cate_sortcode     = $cate_mid;
} else if (!empty($cate_bot)) {
    $cate_sortcode     = $cate_bot;
}

$order_arr = $fb["order"];

$page     = $fb["page"];
$page_dvs = $fb["page_dvs"];

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);
$page = ($page - 1) * 5;

$param = [];
$param["page_dvs"]        = $page_dvs;
$param["high_depar_code"] = $high_depar_code;
$param["depar_code"]      = $depar_code;
$param["empl_seqno"]      = $empl_seqno;
$param["dlvr_way"]        = $dlvr_dvs;
$param["info_cpn"]        = $dlvr_code;
$param[$search_dvs]       = $search_keyword;
$param["member_typ"]      = $member_typ;
$param["grade"]           = $member_grade;
$param["cate_sortcode"]   = $cate_sortcode;
$param["oper_sys"]        = $oper_sys;
$param["order"]           = $order_arr;

$param["cpn_admin_seqno"] = $cpn_admin_seqno;
$param["from"] = $basic_from;
$param["to"]   = $basic_to;
$param["input_date"] = $basic_to;

$date_arr = makeDateArr($dateUtil, $basic_to);

// thead 생성
$thead_html = makeTheadHtml($basic_to);

//$conn->debug = 1;
$sum_arr = [];
if (empty($page_dvs)) {
    // 검색기간의 순매출액, 입금액, 기말미수액 총계
    $sum = $dao->selectSettleInfoDetail($conn, $param, -1)->fields;
    unset($sum_rs);

    // 전월 매출액
    $param["from"]       = $date_arr["m1_from"];
    $param["to"]         = $date_arr["m1_to"];
    $param["input_date"] = $date_arr["m1_to"];
    $m1_sum = $dao->selectSettleInfoDetail($conn, $param, -1)->fields;

    // 평균 매출액(-1, -2, -3월 매출액 평균)
    $param["from"] = $date_arr["m3_from"];
    $avg_sum = $dao->selectSettleInfoDetail($conn, $param, -1)->fields;

    // 일별 매출액 총계
    $param["from"]       = $date_arr["m0_from"];
    $param["to"]         = $date_arr["m0_to"];
    $stats_rs = $dao->selectDaySalesStats($conn, $param);

    $sort_arr = [];
    while ($stats_rs && !$stats_rs->EOF) {
        $stats_fields = $stats_rs->fields;

        $input_date = $stats_fields["input_date"];
        $net_sales_price = intval($stats_fields["net_sales_price"]);

        $sort_arr[$input_date] = $net_sales_price;

        $stats_rs->MoveNext();
    }

    $sum_arr["net"]     = $sum["sum_net_price"];
    $sum_arr["depo"]    = $sum["sum_depo_price"];
    $sum_arr["oa"]      = $sum["sum_period_end_oa"];
    $sum_arr["m1_net"]  = $m1_sum["sum_net_price"];
    $sum_arr["avg_net"] = getAvg($m1_sum["sum_net_price"], 3);
    $sum_arr["days"] = $sort_arr;
    $sum_arr["to"]   = $basic_to;
}

$param["from"] = $basic_from;
$param["to"]   = $basic_to;
$param["input_date"] = $basic_to;
$list_rs = $dao->selectSettleInfoDetail($conn, $param, $page);

$result_cnt = 0;
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$sum = makeSumHtml($sum_arr);

$param["page"]     = $page;
$param["date_arr"] = $date_arr;
$list = makeListHtml($conn, $dao, $param, $list_rs);


$json = "{\"thead\" : \"%s\", \"sum\" : \"%s\", \"list\" : \"%s\", \"result_cnt\" : \"%s\"}";

echo sprintf($json, $util->convJsonStr($thead_html)
                  , $util->convJsonStr($sum)
                  , $util->convJsonStr($list)
                  , $result_cnt);

$conn->Close();

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/**
 * @brief 최상단 thead html 생성
 * @detail to에서 종료일
 *
 * @param $to = 종료일
 *
 * @return thead html
 */
function makeTheadHtml($to) {
    $to = intval(explode('-', $to)[2]);

    $html  = "<th style=\"width:50px;\">No.</th>";
    $html .= "<th class=\"order_th\" onclick=\"changeSort(this, 'oa_sales_detail', 'office_nick');\">회원명<span class=\"sort\"></span></th>";
    $html .= "<th class=\"order_th\" onclick=\"changeSort(this, 'oa_sales_detail', 'member_typ');\">여신초과<span class=\"sort\"></span></th>";
    $html .= "<th class=\"order_th\" onclick=\"changeSort(this, 'oa_sales_detail', 'sum_net_price');\">순 매출액<span class=\"sort\"></span></th>";
    $html .= "<th class=\"order_th\" onclick=\"changeSort(this, 'oa_sales_detail', 'sum_depo_price');\">입금액<span class=\"sort\"></span></th>";
    $html .= "<th class=\"order_th\" onclick=\"changeSort(this, 'oa_sales_detail', 'sum_period_end_oa');\">기말미수액<span class=\"sort\"></span></th>";
    //$html .= "<th class=\"order_th\" onclick=\"changeSort(this, 'oa_sales_detail', 'm1_sum_net');\">전월매출액<span class=\"sort\"></span></th>";
    //$html .= "<th class=\"order_th\" onclick=\"changeSort(this, 'oa_sales_detail', 'avg_sum_net');\">평균매출액<span class=\"sort\"></span></th>";
    $html .= "<th>전월매출액</th>";
    $html .= "<th>평균매출액</th>";

    for ($i = $to; $i > 0; $i--) {
        $html .= "<th>" . $i ."일</th>";
    }

    return $html;
}

/**
 * @brief tbody html 생성
 * @detail to에서 종료일
 *
 * @param $conn    = 
 * @param $dao     = 
 * @param $sum_arr = 
 * @param $param   = 
 *
 * @return thead html
 */
function makeListHtml($conn, $dao, $param, $rs) {
    $idx = $param["page"];
    $date_arr = $param["date_arr"];

    $date_form = "%s-%s-%s";
    $to_arr = explode('-', $date_arr["m0_to"]);
    $to_day = intval($to_arr[2]);

    $html  = "<tr>";
    $html .=     "<td>%s</td>"; //#1 no
    $html .=     "<td>%s</td>"; //#2 회원명
    $html .=     "<td>%s</td>"; //#3 여신초과
    $html .=     "<td style=\"text-align:right;\">%s</td>"; //#4 순매출액
    $html .=     "<td style=\"text-align:right;\">%s</td>"; //#5 입금액
    $html .=     "<td style=\"text-align:right;\">%s</td>"; //#6 기말미수액
    $html .=     "<td style=\"text-align:right;\">%s</td>"; //#7 전월매출액
    $html .=     "<td style=\"text-align:right;\">%s</td>"; //#8 평균매출액

    $ret = '';
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $param["member_seqno"] = $fields["member_seqno"];

        // 여신초과 구분
        switch ($fields["member_typ"]) {
            case "예외업체" :
                $member_fields = $dao->selectMemberInfo($conn, $param);
                $loan_dvs = $member_fields["loan_pay_promi_dvs"];
                break;
            default :
                $loan_dvs = "선입업체";
                break;
        }

        // 전월매출
        $param["from"] = $date_arr["m1_from"];
        $param["to"]   = $date_arr["m1_to"];
        $m1_sum = $dao->selectSettleInfoDetail($conn, $param, -1)->fields;
        // 평균매출
        $param["from"] = $date_arr["m3_from"];
        $param["to"]   = $date_arr["m1_to"];
        $avg_sum = $dao->selectSettleInfoDetail($conn, $param, -1)->fields;

        $ret .= sprintf($html, ++$idx //#1
                             , $fields["office_nick"] //#2
                             , $loan_dvs //#3
                             , number_format($fields["sum_net_price"]) //#4
                             , number_format($fields["sum_depo_price"]) //#5
                             , number_format($fields["sum_period_end_oa"]) //#6
                             , number_format($m1_num["sum_net_price"]) //#7
                             , number_format(getAvg($avg_sum["sum_net_price"], $avg_sum["div_count"])) //#8
                             );

        $param["from"] = $date_arr["m0_from"];
        $param["to"]   = $date_arr["m0_to"];
        $stats_rs = $dao->selectDaySalesStats($conn, $param);

        $sort_arr = [];
        while ($stats_rs && !$stats_rs->EOF) {
            $stats_fields = $stats_rs->fields;

            $input_date = $stats_fields["input_date"];
            $net_sales_price = intval($stats_fields["net_sales_price"]);

            $sort_arr[$input_date] = $net_sales_price;

            $stats_rs->MoveNext();
        }

        for ($i = $to_day; $i > 0; $i--) {
            $day = str_pad(strval($i), 2, '0', STR_PAD_LEFT);
            $key = sprintf($date_form, $to_arr[0], $to_arr[1], $day);

            if (empty($sort_arr[$key])) {
                $ret .=     "<td style=\"background:#ffe795;\"></td>";
            } else {
                $ret .=     "<td style=\"text-align:right;\">" . number_format($sort_arr[$key]) . "</td>";
            }
        }

        $ret .= "</tr>";

        $rs->MoveNext();
    }

    return $ret;
}

/**
 * @brief 총계 thead html 생성
 * @detail to에서 종료일
 *
 * @param $idx   = 순번
 * @param $to    = 종료일
 * @param $param = tr 생성용 데이터
 *
 * @return thead html
 */
function makeSumHtml($sum_arr) {
    $to_arr = explode('-', $sum_arr["to"]);
    $to_day = intval($to_arr[2]);

    $days_arr = $sum_arr["days"];

    $html .= "<td>총계</td>";
    $html .= "<td></td>";
    $html .= "<td></td>";
    $html .= "<td style=\"text-align:right;\">%s</td>"; //#1 순매출액
    $html .= "<td style=\"text-align:right;\">%s</td>"; //#2 입금액
    $html .= "<td style=\"text-align:right;\">%s</td>"; //#3 기말미수액
    $html .= "<td style=\"text-align:right;\">%s</td>"; //#4 전월매출액
    $html .= "<td style=\"text-align:right;\">%s</td>"; //#5 평균매출액

    $html  = sprintf($html, number_format($sum_arr["net"]) //#1
                          , number_format($sum_arr["depo"]) //#2
                          , number_format($sum_arr["oa"]) //#3
                          , number_format($sum_arr["m1_net"]) //#4
                          , number_format($sum_arr["avg_net"]) //#5
                          );

    $date_form = "%s-%s-%s";

    for ($i = $to_day; $i > 0; $i--) {
        $day = str_pad(strval($i), 2, '0', STR_PAD_LEFT);
        $key = sprintf($date_form, $to_arr[0], $to_arr[1], $day);

        if (empty($days_arr[$key])) {
            $html .= "<td style=\"background:#ffe795;\"></td>";
        } else {
            $html .= "<td style=\"text-align:right;\">" . number_format($days_arr[$key]) . "</td>";
        }
    }

    return $html;
}

/**
 * @brief 전월매출액, 평균매출액 구하기용 일자배열 생성
 *
 * @param $util  = DateUtil
 * @param $to    = 종료일
 *
 * @return [
 *     "m1" => 전월
 *     "m3" => 3개월전
 * ]
 */
function makeDateArr($dateUtil, $to) {
    $to_arr = explode('-', $to);
    $dateUtil->setData([
        'y' => $to_arr[0],
        'm' => $to_arr[1],
        'd' => 1
    ]);

    $dateUtil->calcDate('m', -1);
    $m1 = $dateUtil->getDateString();
    $m1_arr = explode('-', $m1);
    $m1_last_day = $dateUtil->getLastDay($m1_arr[0], $m1_arr[1]);


    $dateUtil->calcDate('m', -3);
    $m3 = $dateUtil->getDateString();
    $m3_arr = explode('-', $m3);
    $m3_last_day = $dateUtil->getLastDay($m3_arr[0], $m3_arr[1]);

    $date_form = "%s-%s-%s";

    return [
        "m0_from" => sprintf($date_form, $to_arr[0] ,$to_arr[1], "01"),
        "m0_to" => $to,
        "m1_from" => sprintf($date_form, $m1_arr[0] ,$m1_arr[1], "01"),
        "m1_to" => sprintf($date_form, $m1_arr[0] ,$m1_arr[1], $m1_last_day),
        "m3_from" => sprintf($date_form, $m3_arr[0] ,$m3_arr[1], "01"),
        "m3_to" => sprintf($date_form, $m3_arr[0] ,$m3_arr[1], $m3_last_day)
    ];
}

function getAvg($numerator, $denominator) {
    $numerator   = intval($numerator);
    $denominator = intval($denominator);

    if ($denominator === 0) {
        return 0;
    } else {
        return intval($numerator / $denominator);
    }
}
