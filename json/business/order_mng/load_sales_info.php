<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 매출거래현황정보 검색 및 집계 후
 * table html 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/04/27 엄준현 생성
 *=============================================================================
 */
define("UP"  , "red");
define("DOWN", "blue");

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

// 전년동기
$from_arr = explode('-', $fb["from"]);
$dateUtil->setData([
    'y' => $from_arr[0],
    'm' => $from_arr[1],
    'd' => $from_arr[2]
]);
$dateUtil->calcDate('y', -1);

$from_m1_y = $dateUtil->getDateString();

$to_arr   = explode('-', $fb["to"]);
$dateUtil->setData([
    'y' => $to_arr[0],
    'm' => $to_arr[1],
    'd' => $to_arr[2]
]);
$dateUtil->calcDate('y', -1);

$to_m1_y = $dateUtil->getDateString();

$param = array();
$param["member_seqno"] = $member_seqno;
$param["from"]         = $from;
$param["to"]           = $to;

$rs = $dao->selectDaySalesStatsList($conn, $param);

// 전년동기
$param["from"] = $from_m1_y;
$param["to"]   = $to_m1_y;
$rs_m1_y = $dao->selectDaySalesStatsList($conn, $param);

$m1_y_arr = [];
$m1_y_arr["period_end_oa"]   = intval($rs_m1_y->fields["period_end_oa"]);
$m1_y_arr["carryforward_oa"] = intval($rs_m1_y->fields["carryforward_oa"]);
$m1_y_arr["sum_sales"] = 0;
while ($rs_m1_y && !$rs_m1_y->EOF) {
    $fields = $rs_m1_y->fields;

    $sum_oa = intval($fields["period_end_oa"]) +
              intval($fields["carryforward_oa"]);

    $m1_y_arr["sum_sales"] += intval($fields["sales_price"]);

    $mi_y_arr[$fields["input_date"]]["sum_oa"] = $sum_oa;
    $mi_y_arr[$fields["input_date"]]["sales_price"] = $fields["sales_price"];

    $rs_m1_y->MoveNext();
}

if ($rs->EOF) {
    $html_arr = array();
    $html_arr["thead"] = '';
    $html_arr["tbody"] = "<td style=\"text-align:center;\" colspan=\"13\">검색결과없음</td>"; 
}

$html_arr = makeSalesInfoHtml($rs, $m1_y_arr, $term_dvs);

FIN:
    $json  = '{';
    $json .= "\"thead\" : \"%s\", \"tbody\" : \"%s\"";
    $json .= '}';

    echo sprintf($json, $util->convJsonStr($html_arr["thead"])
                      , $util->convJsonStr($html_arr["tbody"]));

    $conn->Close();
    exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/**
 * @brief 집계데이터 검색결과로 리스트 html 생성
 *
 * @param $rs       = 집계데이터 검색결과
 * @param $term_dvs = 기간별 구분값
 *
 * @return thead, tbody html array
 */
function makeSalesInfoHtml($rs, $m1_y_arr, $term_dvs) {
    $period_end_oa   = $rs->fields["period_end_oa"];
    $carryforward_oa = $rs->fields["carryforward_oa"];
    $sum_oa = intval($period_end_oa) + intval($carryforward_oa);

    // 전년동기 미수
    $period_end_oa_m1_y   = $m1_y_arr["period_end_oa"];
    $carryforward_oa_m1_y = $m1_y_arr["carryforward_oa"];
    $sum_oa_m1_y = $period_end_oa_m1_y + $carryforward_oa_m1_y;

    $tr_form  = "<tr class=\"sales_detail_tr %s\" id=\"sales_detail_tr_%s\" seq=\"%s\" onclick=\"loadSalesDetail.exec(this, '')\">";
    $tr_form .=     "<td>%s</td>";
    $tr_form .=     "<td class=\"sales_date\">%s</td>";
    $tr_form .=     "<td style=\"text-align:right;color:red\">%s</td>";
    $tr_form .=     "<td style=\"text-align:right;color:red\">%s</td>";
    $tr_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tr_form .=     "<td style=\"text-align:right;color:red\">%s</td>";
    $tr_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tr_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tr_form .=     "<td style=\"text-align:right;color:red\">%s</td>";
    $tr_form .=     "<td style=\"text-align:right;\"%s>%s</td>"; // 매출 증감액
    $tr_form .=     "<td style=\"text-align:right;\"%s>%s</td>"; // 매출 증감율
    $tr_form .=     "<td style=\"text-align:right;\"%s>%s</td>"; // 미수 증감액
    $tr_form .=     "<td style=\"text-align:right;\"%s>%s</td>"; // 미수 증감율
    $tr_form .= "</tr>";

    switch ($term_dvs) {
        case 'd':
            $tbody_data = makeTbodyHtmlDay($rs, $m1_y_arr, $tr_form);
            break;
        case 'w':
        case 'm':
        case 'q':
        case 'y':
            $tbody_data = makeTbodyHtmlOther($rs, $m1_y_arr, $tr_form, $term_dvs);
            break;
    }

$tr_form .=     "<td class=\"sales_date\" style=\"text-align:center\">%s</td>";

    $thead_form  = "<th class=\"th_table_accent\" colspan=\"2\">총계</th>";
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 총미수액
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 이월미수액
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 총매출액
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 에누리
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 순매출액
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 입금액
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 기말미수액
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 매출 증감액
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 매출 증감율
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 미수 증감액
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>"; // 미수 증감율

    $sum_sales = intval($tbody_data["sum_sales"]);
    $sum_sale  = intval($tbody_data["sum_sale"]);
    $sum_net   = intval($tbody_data["sum_net"]);
    $sum_depo  = intval($tbody_data["sum_depo"]);

    // 매출 증감액
    $vary_sales = intval($sum_sales) - $m1_y_arr["sum_sales"];
    // 매출 증감율
    $vary_rate_sales = empty($m1_y_arr["sum_sales"]) ? 0 : (($m1_y_arr["sum_sales"] - intval($sum_sales)) / $m1_y_arr["sum_sales"]) * 100;
    // 미수 증감액
    $vary_oa = $sum_oa - $sum_oa_m1_y;
    // 미수 증감율
    $vary_rate_oa = empty($sum_oa_m1_y) ? 0 : (($sum_oa_m1_y - $sum_oa) / $sum_oa_m1_y) * 100;

    $thead_html  = sprintf($thead_form, number_format($sum_oa)
                                      , number_format($carryforward_oa)
                                      , number_format($sum_sales)
                                      , number_format($sum_sale)
                                      , number_format($sum_net)
                                      , number_format($sum_depo)
                                      , number_format($period_end_oa)
                                      , number_format($vary_sales)
                                      , number_format($vary_rate_sales) . '%'
                                      , number_format($vary_oa)
                                      , number_format($vary_rate_oa) . '%'
                                      );

    return [
        "thead" => $thead_html,
        "tbody" => $tbody_data["html"]
    ];
}

/**
 * @brief 해당 일자에 해당하는 주차수 반환
 *
 * @param $date = 주차수를 반환할 일자(yyyy-mm-dd)
 *
 * @return 주차수
 */
function toWeekNum($date) {
    $timestamp = strtotime($date);
    $w = date('w', mktime(0,
                          0,
                          0,
                          date('n', $timestamp),
                          1,
                          date('Y', $timestamp)));

    return ceil(($w + date('j', $timestamp) - 1) / 7);
}

/**
 * @brief 일별 일 때 tbody html생성 및 총합계 반환
 *
 * @param $rs      = 검색결과
 * @param $tr_form = tr html 형식
 *
 * @return html, 총합 배열
 */
function makeTbodyHtmlDay($rs, $m1_y_arr, $tr_form) {
    $DAY_ARR = array(
        '1' => "월",
        '2' => "화",
        '3' => "수",
        '4' => "목",
        '5' => "금",
        '6' => "토",
        '7' => "일"
    );

    $sum_sales = 0;
    $sum_sale  = 0;
    $sum_net   = 0;
    $sum_depo  = 0;

    $color_form = "style=\"color:%s\"";

    $html = '';

    $i = 1;
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        if ($i % 2 == 0) {
            $class = "cellbg";
        } else if ($i % 2 == 1) {
            $class = ""; 
        }

        $input_date = $fields["input_date"];
        $sales = intval($fields["sales_price"]);
        $sale  = intval($fields["sale_price"]);
        $net   = intval($fields["net_sales_price"]);
        $depo  = intval($fields["depo_price"]);

        $sum_sales += intval($sales);
        $sum_sale  += intval($sale);
        $sum_net   += intval($net);
        $sum_depo  += intval($depo);

        $m1_y = $m1_y_arr[$input_date];

        $sum_oa = intval($fields["period_end_oa"]) +
                  intval($fields["carryforward_oa"]);
        $sum_oa_m1_y = intval($m1_y["sum_oa"]);

        // 매출 증감액
        $vary_sales = intval($sales) - intval($m1_y["sales_price"]);
        // 매출 증감율
        $vary_rate_sales = empty($m1_y["sales_price"]) ? 0 : (($m1_y["sales_price"] - intval($sales)) / $m1_y["sales_price"]) * 100;
        // 미수 증감액
        $vary_oa = $sum_oa - $sum_oa_m1_y;
        // 미수 증감율
        $vary_rate_oa = empty($sum_oa_m1_y) ? 0 : (($sum_oa_m1_y - $sum_oa) / $sum_oa_m1_y) * 100;

        $day = $DAY_ARR[date('N', strtotime($input_date))];

        // 색상
        $vary_sales_color = sprintf($color_form, UP);
        if ($vary_sales < 0 ) {
            $vary_sales_color = sprintf($color_form, DOWN);
        }
        $vary_oa_color = sprintf($color_form, UP);
        if ($vary_oa < 0 ) {
            $vary_oa_color = sprintf($color_form, DOWN);
        }

        $html .= sprintf($tr_form, $class
                                 , $i
                                 , $i
                                 , $i++
                                 , $fields["input_date"] . ' (' . $day . ')'
                                 , number_format($sum_oa)
                                 , number_format($fields["carryforward_oa"])
                                 , number_format($fields["sales_price"])
                                 , number_format($fields["sale_price"])
                                 , number_format($fields["net_sales_price"])
                                 , number_format($fields["depo_price"])
                                 , number_format($fields["period_end_oa"])
                                 , $vary_sales_color
                                 , number_format($vary_sales)
                                 , $vary_sales_color
                                 , number_format($vary_rate_sales) . '%'
                                 , $vary_oa_color
                                 , number_format($vary_oa)
                                 , $vary_oa_color
                                 , number_format($vary_rate_oa) . '%'
                                 );

        $rs->MoveNext();
    }

    return array(
         "sum_sales" => $sum_sales
        ,"sum_sale"  => $sum_sale
        ,"sum_net"   => $sum_net
        ,"sum_depo"  => $sum_depo
        ,"html"      => $html
    );
}

/**
 * @brief 일별 제외 나머지 때 tbody html생성 및 총합계 반환
 *
 * @param $rs       = 검색결과
 * @param $tr_form  = tr html 형식
 * @param $term_dvs = 기간별 구분값
 *
 * @return html, 총합 배열
 */
function makeTbodyHtmlOther($rs, $m1_y_arr, $tr_form, $term_dvs) {
    $sum_sales = 0;
    $sum_sale  = 0;
    $sum_net   = 0;
    $sum_depo  = 0;

    $html = '';

    $merge_arr = array();

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $sum_sales += intval($fields["sales_price"]);
        $sum_sale  += intval($fields["sale_price"]);
        $sum_net   += intval($fields["net_sales_price"]);
        $sum_depo  += intval($fields["depo_price"]);

        $date = getDateType($fields["input_date"], $term_dvs);

        if ($merge_arr[$date] === null) {
            $temp = array(
                 "carryforward_oa" => intval($fields["carryforward_oa"])
                ,"sales_price"     => intval($fields["sales_price"])
                ,"sale_price"      => intval($fields["sale_price"])
                ,"net_sales_price" => intval($fields["net_sales_price"])
                ,"depo_price"      => intval($fields["depo_price"])
                ,"period_end_oa"   => intval($fields["period_end_oa"])
            );

            $merge_arr[$date] = $temp;
        } else {
            $temp = $merge_arr[$date];
            $temp["carryforward_oa"] += intval($fields["carryforward_oa"]);
            $temp["sales_price"]     += intval($fields["sales_price"]);
            $temp["sale_price"]      += intval($fields["sale_price"]);
            $temp["net_sales_price"] += intval($fields["net_sales_price"]);
            $temp["depo_price"]      += intval($fields["depo_price"]);
            $temp["period_end_oa"]   += intval($fields["period_end_oa"]);

            $merge_arr[$date] = $temp;
        }

        $rs->MoveNext();
    }
    unset($rs);

    $i = 1;
    foreach ($merge_arr as $date => $fields) {
        $sum_oa = intval($fields["period_end_oa"]) +
                  intval($fields["carryforward_oa"]);

        $html .= sprintf($tr_form, $i
                                 , $i
                                 , $i++
                                 , $date
                                 , number_format($sum_oa)
                                 , number_format($fields["carryforward_oa"])
                                 , number_format($fields["sales_price"])
                                 , number_format($fields["sale_price"])
                                 , number_format($fields["net_sales_price"])
                                 , number_format($fields["depo_price"])
                                 , number_format($fields["period_end_oa"])
                                 , ''
                                 , "비교기준필요"
                                 , ''
                                 , "비교기준필요"
                                 , ''
                                 , "비교기준필요"
                                 , ''
                                 , "비교기준필요"
                                 );
    }

    return array(
         "sum_sales" => $sum_sales
        ,"sum_sale"  => $sum_sale
        ,"sum_net"   => $sum_net
        ,"sum_depo"  => $sum_depo
        ,"html"      => $html
    );
}

/**
 * @brief 기간구분별 집계날짜 형식 반환
 *
 * @param $date     = 일자
 * @param $term_dvs = 기간별 구분값
 *
 * @return 형식
 */
function getDateType($date, $term_dvs) {
    $date_arr = explode('-', $date);

    switch ($term_dvs) {
        case 'w':
            $w = toWeekNum($date);
            $date = $date_arr[0] . "년 " . $date_arr[1] . "월 " . $w . "주차";
            break;
        case 'm':
            $date = $date_arr[0] . "년 " . $date_arr[1] . '월';
            break;
        case 'q':
            $m = intval($date_arr[1]);
            if ($m < 4) {
                $date = $date_arr[0] . "년 1분기";
            } else if (3 < $m && $m < 7) {
                $date = $date_arr[0] . "년 2분기";
            } else if (6 < $m && $m < 10) {
                $date = $date_arr[0] . "년 3분기";
            } else if (9 < $m && $m <= 12) {
                $date = $date_arr[0] . "년 4분기";
            }
            break;
        case 'y':
            $date = $date_arr[0] . '년';
            break;
    }

    return $date;
}
?>
