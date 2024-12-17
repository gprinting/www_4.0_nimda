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

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

$conn->debug = 1;

$member_seqno = $fb["seqno"];
$term_dvs     = $fb["term_dvs"];
$from         = $fb["from"];
$to           = $fb["to"];

$param = array();
$param["member_seqno"] = $member_seqno;
$param["from"]         = $from;
$param["to"]           = $to;

$rs = $dao->selectCrmSalesStatsList($conn, $param);

while ($rs && !$rs->EOF) {
$fields      = $rs->fields;
$target_date = $fields["input_date"];
$date_html   = getLastWeekDay($target_date);

    echo "-----------";
    echo $target_date;
    echo "===========";
    echo $date_html;

    $rs->MoveNext();
} 
exit;


if ($rs->EOF) {
    $html_arr = array();
    $html_arr["thead"] = '';
    $html_arr["tbody"] = "<td style=\"text-align:center;\" colspan=\"5\">검색결과없음</td>"; 
    
    goto FIN;
}

$html_arr = makeCrmSalesInfoHtml($rs, $term_dvs);

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
function makeCrmSalesInfoHtml($rs, $term_dvs) {
    $period_end_oa   = $rs->fields["period_end_oa"];
    $carryforward_oa = $rs->fields["carryforward_oa"];
    $sum_oa = intval($period_end_oa) + intval($carryforward_oa);

    $tr_form  = "<tr class=\"sales_detail_tr\" id=\"sales_detail_tr_%s\" seq=\"%s\">";
    $tr_form .=     "<td style=\"text-align:center\">%s</td>";
    $tr_form .=     "<td class=\"sales_date\" style=\"text-align:center\">%s</td>";
    $tr_form .=     "<td>%s</td>";
    $tr_form .=     "<td>%s</td>";
    $tr_form .=     "<td>%s</td>";
    $tr_form .= "</tr>";

    $tbody_data = makeCrmTbodyHtmlOther($rs, $tr_form, $term_dvs);

    $thead_html = "";

    return array(
        "thead" => $thead_html,
        "tbody" => $tbody_data["html"]
    );
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
 * @brief 해당 날에 해당하는 마지막 일자 반환
 *
 * @param $date = 반환할 일자(yyyy-mm-dd)
 *
 * @return 마지막 일자
 */
function getLastWeekDay($date) {
    $ts         = strtotime($date);
    $start      = (date('w', $ts) == 0) ? $ts : strtotime('last monday', $ts);
    $start_date = date('Y-m-d', $start);
    $end_date   = date('Y-m-d', strtotime('next sunday', $start));

    return $start_date . "!!!GOD DAMN!!!" . $end_date;
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
function makeCrmTbodyHtmlOther($rs, $tr_form, $term_dvs) {
    $sum_net   = 0;
    $sum_depo  = 0;

    $html = '';

    $merge_arr = array();

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $sum_net   += intval($fields["net_sales_price"]);
        $sum_depo  += intval($fields["depo_price"]);

        $date = getDateType($fields["input_date"], $term_dvs);

        if ($merge_arr[$date] === null) {
            $temp = array(
                 "net_sales_price" => intval($fields["net_sales_price"])
                ,"depo_price"      => intval($fields["depo_price"])
            );

            $merge_arr[$date] = $temp;
        } else {
            $temp = $merge_arr[$date];
            $temp["net_sales_price"] += intval($fields["net_sales_price"]);
            $temp["depo_price"]      += intval($fields["depo_price"]);

            $merge_arr[$date] = $temp;
        }

        $rs->MoveNext();
    }
    unset($rs);

    $i = 1;
    foreach ($merge_arr as $date => $fields) {

        $html .= sprintf($tr_form, $i
                                 , $i
                                 , $i++
                                 , $date
                                 , number_format($fields["net_sales_price"])
                                 , number_format($fields["depo_price"])
                                 , "진행중"
                                 );
    }

    return array(
         "sum_net"   => $sum_net
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
        case 'y':
            $date = $date_arr[0] . '년';
            break;
    }

    return $date;
}
?>
