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
 * 2017/06/27 이청산 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/DateUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

//$conn->debug = 1;

//TODO 검색조건 관련 파라미터 추가 필요
$member_seqno = $fb["seqno"];
$target_date  = $fb["target_date"];
$cate_mid     = $fb["cate_mid"];
$cate_bot     = $fb["cate_bot"];
$searchTxt    = $fb["searchTxt"];
$term_dvs     = $fb["term_dvs"];
$sig          = $fb["sig"];
$cate_sortcode = null;

if (!empty($cate_bot)) {
    $cate_sortcode = $cate_bot;
} else if (!empty($cate_mid)) {
    $cate_sortcode = $cate_mid;
}

//이미지 파일 경로(이곳에는 불필요)
//$file_path = FILE_ARROW_DOWN;

//TODO 검색조건 관련 파라미터 추가 필요
$param = array();
$param["member_seqno"]  = $member_seqno;

// 일별, 주별, 월별, 분기별, 연별에 따라 검색조건이 달라짐
$target_year = substr($target_date, 0, 4);
$target_mont = substr($target_date, 8, 2);
if ($term_dvs == 'd') {
    $target_date = substr($target_date, 0, 10);
    $param["from"] = $target_date;
    $param["to"]   = $target_date;

} else if ($term_dvs == 'w') {
    $t_week = substr($target_date, 14, 1);
    $weekCal = makeWeekSearch($target_year, $target_mont, $t_week);
    $resDiv = explode('/', $weekCal);
    $param["from"] = $resDiv[0];
    $param["to"]   = $resDiv[1];

} else if ($term_dvs == 'm') { 
    switch ($target_mont) {
        case '01':
        case '03':
        case '05':
        case '07':
        case '08':
        case '10':
        case '12':
           $param["from"] = $target_year . '-' . $target_mont .'-01';
           $param["to"]   = $target_year . '-' . $target_mont .'-31';
           break;

        case '04':
        case '06':
        case '09':
        case '11':
           $param["from"] = $target_year . '-' . $target_mont .'-01';
           $param["to"]   = $target_year . '-' . $target_mont .'-30';
           break;

        case '02':
           $param["from"] = $target_year . '-' . $target_mont .'-01';
           $param["to"]   = $target_year . '-' . $target_mont .'-29';
           break;

    }

} else if ($term_dvs == 'q') {
    $target_quta = substr($target_date, 8, 1);
    switch ($target_quta) {
        case '1':
           $param["from"] = $target_year . "-01-01";
           $param["to"]   = $target_year . "-03-31";
           break;

        case '2':
           $param["from"] = $target_year . "-04-01";
           $param["to"]   = $target_year . "-06-30";
           break;

        case '3':
           $param["from"] = $target_year . "-07-01";
           $param["to"]   = $target_year . "-09-30";
           break;

        case '4':
           $param["from"] = $target_year . "-10-01";
           $param["to"]   = $target_year . "-12-31";
           break;
    }

} else if ($term_dvs == 'y') {
    $param["from"]          = $target_year . "-01-01";
    $param["to"]            = $target_year . "-12-31";
}

$param["cate_sortcode"] = $cate_sortcode;
$param["term_dvs"]      = $term_dvs;
$param["searchTxt"]     = $searchTxt;

if($sig != null) {
    $sheet_dvs = substr($searchTxt, 0, 3);

    switch($sheet_dvs) {
        case "NGC" : // 코팅명함
        case "NGN" : // 무코팅명함  
        case "SPC" : // 코팅스티커
        case "SPN" : // 무코팅스티커
        case "SSP" : // 특수지스티커
        case "JGA" : // 아트지합판전단 
        case "JGM" : // 모조지합판전단 
        case "JSG" : // 일반지독판전단 
        case "JSH" : // 고급지독판전단 
        case "JSS" : // 특수지독판전단 
        case "JSN" : // 수입지독판전단 
        case "APP" : // 포스터 
        case "APL" : // 리플렛, 팜플렛 
        case "APF" : // 문어발 
        case "APD" : // 문고리 
        case "APM" : // 메모지 
        case "EGC" : // 봉투 
        case "MJG" : // 초소량인쇄 
        case "DNC" : // 디지털명함인쇄 
        case "ETC" : // 기타 
            $res = $dao->selectSalesSheetTypset($conn, $param);
            $fields = $res->fields;

            $dvs_num = $fields["order_num"];

            $param["dvs_num"] = $dvs_num;
            break;

        default : 
            $res = $dao->selectSalesBrochureTypset($conn, $param);
            $fields = $res->fields;

            $dvs_num_rare = $fields["order_detail_dvs_num"];
            $dvs_num = substr($dvs_num_rare, 1, 16);
            $param["dvs_num"] = $dvs_num;  

            break;

    }

    $rs = $dao->selectSalesOrderDetail($conn, $param);

} else {
    $rs = $dao->selectSalesDetail($conn, $param);
}

if ($rs->EOF) {
    $html_arr = array();
    $html_arr["thead"] = '';
    $html_arr["tbody"] = "<td style=\"text-align:center;\" colspan=\"10\">검색결과없음</td>";
    goto FIN;
}

$sort_arr = sortSalesDataRs($rs);
unset($rs);

$cate_top_arr = $dao->selectCateInfoArr($conn, '1');
$state_rs     = $dao->selectStateAdmin($conn);

$state_arr = array();
while ($state_rs && !$state_rs->EOF) {
    $fields = $state_rs->fields;

    $state_arr[$fields["state_code"]] = $fields["erp_state_name"];

    $state_rs->MoveNext();

}

unset($param);
$param["cate_top_arr"] = $cate_top_arr;
$param["sort_arr"]     = $sort_arr;
$param["state_arr"]    = $state_arr;
$html_arr = makeSalesDetailHtml($param, $file_path);

FIN:
    $json  = '{';
    $json .= "\"thead\" : \"%s\", \"tbody\" : \"%s\"";
    $json .= '}';

    echo sprintf($json, $util->convJsonStr($html_arr["thead"])
                      , $util->convJsonStr($html_arr["tbody"]));
    
    $conn->Close();
    exit;


/*************************************************************************************함수영역
*************************************************************************/


/**
 * @brief 매출정보 검색결과 구분
 * 
 * @param $rs = 검색결과
 * 
 * @return array(
 *      "총 건수(sum_count)" => x
 *      "총 결제금액(sum_pay_price)" => x
 *      "명함(001) => [데이터1, 데이터2...]
 *      "스티커(002) => [데이터1, 데이터2...] ...
 */
function sortSalesDataRs($rs) {
    $ret = array(
        "sum_count" => $rs->RecordCount()
    );

    $sum_pay_price = 0;

    $dup_chk = array();

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $sum_pay_price += intval($fields["pay_price"]);
        $cate_top = substr($fields["cate_sortcode"], 0, 3);

        if (empty($ret[$cate_top])) {
            $ret[$cate_top]["sum_count"] = 1;
            $ret[$cate_top]["sum_pay_price"] = intval($fields["pay_price"]);
        } else {
            $ret[$cate_top]["sum_count"] += 1;
            $ret[$cate_top]["sum_pay_price"] += intval($fields["pay_price"]);
        } 
        
        $ret[$cate_top]["data_arr"][] = $fields;

        $rs->MoveNext();
    }

    $ret["sum_pay_price"] = $sum_pay_price;

    return $ret;
}

/**
 * @brief 데이터 검색결과로 리스트 html 생성
 * @TODO 수정필요(하드코딩, 데이터를 받을 수 있도록 수정필요)
 * @param $param = html 생성용 데이터
 * 
 * @return thead, tbody html array
 */
function makeSalesDetailHtml($param, $file_path) {
    $cate_top_arr = $param["cate_top_arr"];
    $sort_arr     = $param["sort_arr"];
    $state_arr    = $param["state_arr"];

    $sum_count = $sort_arr["sum_count"];
    $sum_pay_price = $sort_arr["sum_pay_price"];
    unset($sort_arr["sum_count"]);
    unset($sort_arr["sum_pay_price"]);

    
    $thead_form  = "<th class=\"th_table_accent\"></th>";
    $thead_form .= "<th class=\"th_table_accent\">총합계</th>";
    $thead_form .= "<th class=\"th_table_accent\" colspan=\"4\">총 %s건</th>";
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">%s</th>";
    $thead_form .= "<th class=\"th_table_accent\" colspan=\"3\"></th>";

    $tr_top_form  = "<tr class=\"tr_table_accent\"onclick=\"toggleSalesRow('%s', 'mid');\">";
    $tr_top_form .=     "<td></td>";
    $tr_top_form .=     "<td>%s</td>";
    $tr_top_form .=     "<td>총 %s건</td>";
    $tr_top_form .=     "<td colspan=\"3\">합계</td>";
    $tr_top_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tr_top_form .=     "<td colspan=\"3\"></td>";
    $tr_top_form .= "</tr>";

    $tr_mid_form  = "<tr class=\"toggleSales_mid_%s %s row_bg hidden_row\">";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td class=\"cursor\" style=\"text-align:left;overflow: initial;\"><span class=\"tooltip\">%s";
    $tr_mid_form .=     "<span class=\"tooltiptext\">%s</span>";
    //$tr_mid_form .=     "<img class=\"img_view_depth\" src=". $file_path .">";
    $tr_mid_form .=     "</span></td>";
    $tr_mid_form .=     "<td style=\"text-align:left;\">%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td style=\"text-align:right;\">%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "%s";
    $tr_mid_form .= "</tr>";

    $memo_form .=     "<input type=\"hidden\" value=\"%s\" />";
    $memo_form .=     "<td onclick=\"showOrderCustMemo('%s');\" class=\"memo_active\" style=\"cursor:pointer;\">메모</td>";
   
    $thead_html = sprintf($thead_form, number_format($sum_count)
                                     , number_format($sum_pay_price));

    $tbody_html = '';
    $prev_cate_top = null;
    $i = 1; 
    foreach ($sort_arr as $cate_top => $info_arr) {
        $tr_top = sprintf($tr_top_form, $i 
                                      , $cate_top_arr[$cate_top]
                                      , number_format($info_arr["sum_count"])
                                      , number_format($info_arr["sum_pay_price"]));

        $tbody_html .= $tr_top;
        
        $data_arr = $info_arr["data_arr"];
        $data_arr_count = count($data_arr);
        for ($j = 0; $j < $data_arr_count; $j++) {
            $data = $data_arr[$j];

            if ($j % 2 == 0) {
                $class = ""; 
            } else if ($j % 2 == 1) {
                $class = "cellbg";
            }
            
            $order_detail = $data["order_detail"];
            $order_prdt = explode('/', $order_detail);

            if (!empty($data["cust_memo"])) {
                $memo = sprintf($memo_form, $data["cust_memo"]
                                          , $data["order_num"]);
            } else {
                $memo = "<td>메모</td>";
            }

            $tr_mid = sprintf($tr_mid_form, $i
                                          , $class
                                          , $j + 1
                                          , $data["order_num"]
                                          , mb_substr($data["title"], 0, 12)
                                          , $data["title"]
                                          , $order_prdt[0]
                                          , $data["amt"]
                                          , $data["count"] 
                                          , number_format($data["pay_price"])
                                          , $state_arr[$data["order_state"]]
                                          , DLVR_TYP[$data["dlvr_way"]]
                                          , $memo);

            $tbody_html .= $tr_mid;
        }

        $i++;
    }

    return array(
        "thead" => $thead_html,
        "tbody" => $tbody_html
    );
}

/**
 * @brief 주차로 해당 주 시작 날짜 구하기
 * @param $target_year = 목표 년
 *        $target_mont = 목표 월
 *        $t_week      = 목표 주차
 * @return $start      = 검색 시작일
 *         $last       = 검색 종료일
 */
function makeWeekSearch($target_year, $target_mont, $t_week) {

    $util = new DateUtil();

    $y = $target_year;
    $m = $target_mont;
    $w = $t_week;

    $start_day = 1;
    $last_day = $util->getLastDay($y, $m);

    $day_arr = [];

    for ($i = $start_day; $i <= $last_day; $i++) {
        $d = str_pad(strval($i), 2, '0', STR_PAD_LEFT);

        $day = sprintf("%s-%s-%s", $y, $m, $d);

        $week = $util->getWeekNum($day);

        $day_arr[$week][] = $d;
    }

    $start = sprintf("%s-%s-%s", $y, $m, $day_arr[$w][0]);
    $last  = sprintf("%s-%s-%s", $y, $m, $day_arr[$w][count($day_arr[$w]) -1]);

    return "$start / $last"; 

}


/**
 * @brief 주차로 해당 주 시작 날짜 구하기
 * @param $target_year = 목표 년
 *        $target_mont = 목표 월
 *        $t_week      = 목표 주차
 * @return from
 */ /*
function makeWeekSearchFrom($target_year, $target_mont, $t_week) {
    // 선택한 전 달의 마지막 날짜
    $endDay = mktime(0,0,0, $target_mont-1, date('t', mktime(0,0,0, $target_mont-1, 1, $target_year)), $target_year); 
    // 그 날짜의 요일
    $endDay_weekDay  = date('N', $endDay);
    $sunday_interval = abs(0-$endDay_weekDay);
    // 마지막 날짜가 속한 일요일
    $week_start      = $endDay - (60*60*24)*$sunday_interval;

    if ($endDay_weekDay > 3) {
        $searchDay = date('Y-m-d H:i:s', $week_start + (60*60*24)*7*$t_week);
    } else {
        $searchDay = date('Y-m-d H:i:s', $week_start + (60*60*24)*7*($t_week-1));
    }

    $searchFrom = substr($searchDay, 0, 10);

    if ($t_week == '1') {
        $searchFrom = explode('-', $searchFrom);
    }
    echo "..1......";
    echo date('d/m/Y', $endDay);
    echo "..2-1......";
    echo $endDay_weekDay;
    echo "..3-1......";
    echo $sunday_interval;
    echo "..4-1......";
    echo date('d/m/Y', $week_start);
    echo "..5-1......";

    return $searchFrom;
} */ 

/**
 * @brief 주차로 해당 주 끝나는 날짜 구하기
 * @param $target_year = 목표 년
 *        $target_mont = 목표 월
 *        $t_week      = 목표 주차
 * @return to
 *
function makeWeekSearchTo($target_year, $target_mont, $t_week) {
    // 선택한 전 달의 마지막 날짜
    $endDay = mktime(0,0,0, $target_mont-1, date('t', mktime(0,0,0, $target_mont-1, 1, $target_year)), $target_year); 
    $endDay_weekDay  = date('N', $endDay);
    $saturday_interval = 6-$endDay_weekDay; 
    // 마지막 날짜가 속한 토요일
    $week_end        = $endDay + (60*60*24)*$saturday_interval;


    if ($endDay_weekDay > 3) {
        $searchDay = date('Y-m-d H:i:s', $week_end + (60*60*24)*7*($t_week)+(60+60+23)+(60*59));
    } else {
        $searchDay = date('Y-m-d H:i:s', $week_end + (60*60*24)*7*($t_week-1)+(60+60+23)+(60*59));
    }
    $searchTo = substr($searchDay, 0, 10);

    echo "--1......";
    echo date('d/m/Y', $endDay);
    echo "--2-1......";
    echo $endDay_weekDay;
    echo "--3-1......";
    echo $saturday_interval;
    echo "--4-1......";
    echo date('d/m/Y', $week_end);
    echo "--5-1......";


    return $searchTo;
} */ 
?>
