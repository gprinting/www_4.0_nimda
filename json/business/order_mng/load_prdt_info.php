<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 주문정보 검색 및 집계 후
 * table html 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/29 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new ErpCommonUtil();

$fb = $fb->getForm();

$member_seqno = $fb["member_seqno"];
$member_name = $fb["member_name"];
$member_nick = $fb["member_nick"];
$from_arr = explode('-', $fb["from"]);
//$to   = $fb["to"];

$param = array();
$param["member_seqno"] = $member_seqno;
$param["from"] = $from;
$param["to"]   = $to;

//$conn->debug = 1;

// 카테고리 코드에 따른 한글명
$cate_arr = $dao->selectCateInfoArr($conn, '1');

// 품목별 현황정보
$rs = $dao->selectPrdtInfoSum($conn, $param);

$sort_arr = sortPrdtInfoListRs($rs);

// 품목별 현황정보 상세정보
$date_arr = $util->getDateRangeArr($from_arr[0], $from_arr[1]);
$date_arr_count = count($date_arr);

$ym_arr = array();
$detail_sort_arr = array();
for ($i = 0; $i < $date_arr_count; $i++) {
    $param["from"] = $date_arr[$i]["from"];
    $param["to"]   = $date_arr[$i]["to"];

    $ym = substr($date_arr[$i]["from"], 0, -3);
    $ym_arr[] = $ym;

    $rs = $dao->selectPrdtInfoSum($conn, $param);

    $temp_arr = sortPrdtInfoListRs($rs);

    foreach ($temp_arr as $cate_top => $data_arr) {
        $detail_sort_arr[$cate_top][$ym] = $data_arr;
    }

}

$prdt_vary_arr = getPrdtVaryArr($detail_sort_arr, $ym_arr);

$list_html = makePrdtInfoList($sort_arr, $cate_arr, $member_name, $member_nick);
$detail_rst = makePrdtDetailInfoList($detail_sort_arr,
                                     $cate_arr,
                                     $prdt_vary_arr,
                                     $ym_arr);
$chart_json = makeChartDataJson($detail_rst["sum_arr"],
                                $detail_sort_arr,
                                $cate_arr,
                                $ym_arr);

$json  = '{';
$json .=  "\"list\"   : \"%s\",";
$json .=  "\"detail\" : \"%s\",";
$json .=  "\"date\"   : %s,";
$json .=  "\"chart\"  : [%s]";
$json .= '}';

echo sprintf($json, $util->convJsonStr($list_html)
                  , $util->convJsonStr($detail_rst["html"])
                  , json_encode($ym_arr)
                  , $chart_json);

$conn->Close();

/******************************************************************************
 * 함수영역
 ******************************************************************************/

/**
 * @brief 품목별 현황정보 검색결과 정렬집계
 *
 * @param $rs = 검색결과
 *
 * @return 정렬집계된 배열
 */
function sortPrdtInfoListRs($rs) {
    // key => 카테고리 대분류, val => [건수 => x, 금액 => y]
    $ret = array();

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $sortcode_t = $fields["cate_top"];

        if (empty($ret[$sortcode_t])) {
            $ret[$sortcode_t] = array(
                "cnt"   => $fields["cnt"],
                "price" => $fields["sum_pay"]
            );
        } else {
            $ret[$sortcode_t]["cnt"] += $fields["cnt"];
            $ret[$sortcode_t]["price"] += $fields["price"];
        }

        $rs->MoveNext();
    }

    return $ret;
}

/**
 * @brief 집계용 날짜배열 생성
 *
 * @param $rs = 검색결과
 *
 * @return 정렬집계된 배열
 */
function sortPrdtInfoDetail() {
    $dateUtil = new DateUtil();

    // 품목별 상세 현황정보 검색
    $year  = date('Y');
    $month = date('m');

    $date_param = array(
        'y' => $year,
        'm' => $month
    );

    //------------------------------ 시작
    // 현재
    $date_param['d'] = "01";
    $dateUtil->setData($date_param);
    $cur_from = $dateUtil->getDateString();
    // 작년동기
    $dateUtil->calcDate('y', -1);
    $last_year_from = $dateUtil->getDateString();
    // -1월
    $dateUtil->calcDate('m', -1);
    $m1_from = $dateUtil->getDateString();
    // -2월
    $dateUtil->calcDate('m', -2);
    $m2_from = $dateUtil->getDateString();
    // -3월
    $dateUtil->calcDate('m', -3);
    $m3_from = $dateUtil->getDateString();

    //------------------------------ 종료
    // 현재
    $date_param['y'] = $year;
    $date_param['m'] = $month;
    $date_param['d'] = $dateUtil->getLastDay($year, $month);
    $dateUtil->setData($date_param);
    $cur_to = $dateUtil->getDateString();
    // 작년동기
    $dateUtil->calcDate('y', -1);
    $last_year_to = $dateUtil->getDateString();
    // -1월
    $dateUtil->calcDate('m', -1);
    $m1_to = $dateUtil->getDateString();
    // -2월
    $dateUtil->calcDate('m', -2);
    $m2_to = $dateUtil->getDateString();
    // -3월
    $dateUtil->calcDate('m', -3);
    $m3_to = $dateUtil->getDateString();

    $date_arr = array();
    $date_arr[] = array(
        "from" => $cur_from,
        "to"   => $cur_to
    );
    $date_arr[] = array(
        "from" => $last_year_from,
        "to"   => $last_year_to
    );
    $date_arr[] = array(
        "from" => $m1_from,
        "to"   => $m1_to
    );
    $date_arr[] = array(
        "from" => $m2_from,
        "to"   => $m2_to
    );
    $date_arr[] = array(
        "from" => $m3_from,
        "to"   => $m3_to
    );

    return $date_arr;
}

/**
 * @brief 회원 기간별 주문집계 tr html 생성
 *
 * @param $sort_arr = 정렬된 리스트 배열값
 * @param $cate_arr = 카테고리 한글명 배열
 * @param $member_name = 회원명
 * @param $member_nick = 회원사내닉네임
 *
 * @return 정렬집계된 배열
 */
function makePrdtInfoList($sort_arr, $cate_arr, $member_name, $member_nick) {
    $sort_arr_count = count($sort_arr);

    if ($sort_arr_count === 0) {
        return "<tr><td colspan=\"2\">검색된 결과가 없습니다.</td></tr>";
    }

    $tr_form  = "<tr>";
    $tr_form .=     "<td class=\"checked\">%s[%s]</td>";
    $tr_form .=     "<td>%s</td>";
    $tr_form .= "</tr>";

    $prdt_info = '';

    foreach ($sort_arr as $cate_top => $data_arr) {
        $prdt_info .= $cate_arr[$cate_top];
        $prdt_info .= $data_arr["cnt"] . "건 / ";
    }

    $prdt_info = substr($prdt_info, 0, -3);

    return sprintf($tr_form, $member_name, $member_nick, $prdt_info);
}

/**
 * @brief 회원 기간별 주문 상세집계 tr html 생성
 *
 * @param $detail_sort_arr = 정렬된 리스트 배열값
 * @param $prdt_vary_arr   = 상품 증감률 배열
 * @param $date_arr        = 날짜 배열
 *
 * @return array(
 *     "html" => tr html
 *     "sum_arr" => 총합계 데이터 배열
 * )
 */
function makePrdtDetailInfoList($detail_sort_arr,
                                $cate_arr,
                                $prdt_vary_arr,
                                $date_arr) {
    $ret = array(
        "html"    => '',
        "sum_arr" => array() 
    );
    $sort_arr_count = count($detail_sort_arr);

    if ($sort_arr_count === 0) {
        $ret["html"] = "<tr><td colspan=\"11\" style=\"text-align:center;\">검색된 결과가 없습니다.</td></tr>";
        goto END;
    }

    $date_arr_count = count($date_arr);

    $span_up = "<span style=\"color:#5daff5\">▲</span>";
    $span_down = "<span style=\"color:#ff0000\">▼</span>";

    $tr_form  = "<tr %s>";
    $tr_form .=     "<td rowspan=\"2\" style=\"text-align:center;\">%s</td>"; //#1 cate_name
    $tr_form .=     "%s";
    $tr_form .= "</tr>";
    $tr_form .= "<tr %s>";
    $tr_form .=     "%s";
    $tr_form .= "</tr>";

    $td_form .=     "<td>%s</td>";      //#2   당월 건수
    $td_form .=     "<td>%s%% %s</td>"; //#2-1 당월 건수 증감

    //$tr_func_form = "onclick=\"openPrdtDetailPop('%s');\" style=\"cursor:pointer;\"";
    $tr_func_form = '';

    // 총합계 생성
    $sum_arr = array("sum" => array());

    $bot_html = '';
    foreach ($detail_sort_arr as $cate_top => $sort_arr) {

        $cnt_td_html   = '';
        $price_td_html = '';
        for($i = 0; $i < $date_arr_count; $i++) {
            $date = cutDateStr($date_arr[$i]);
            $data_arr = $sort_arr[$date];

            $cnt_vary   = intval($prdt_vary_arr[$date]["cnt"]);
            $price_vary = intval($prdt_vary_arr[$date]["price"]);

            $cnt_arrow = $span_up;
            if ($cnt_vary < 0) {
                $cnt_arrow = $span_down;
            } else if (empty($cnt_vary)) {
                $cnt_arrow = '';
            }

            $price_arrow = $span_up;
            if ($price_vary < 0) {
                $price_arrow = $span_down;
            } else if (empty($price_vary)) {
                $price_arrow = '';
            }

            $cnt   = doubleval($data_arr["cnt"]);
            $price = doubleval($data_arr["price"]);

            $cnt_td_html .= sprintf($td_form, number_format($cnt) //#2
                                            , $cnt_vary //#2-1
                                            , $cnt_arrow);
            $price_td_html .= sprintf($td_form, number_format($price) //#2
                                              , $price_vary //#2-1
                                              , $price_arrow);
            // 총합계용 데이터
            if (empty($sum_arr["sum"][$date])) {
                $sum_arr["sum"][$date] = array(
                    "cnt"   => $cnt,
                    "price" => $price
                );
            } else {
                $sum_arr["sum"][$date]["cnt"]   += $cnt;
                $sum_arr["sum"][$date]["price"] += $price;
            }
        }

        $tr_func = sprintf($tr_func_form, $cate_top);

        $bot_html .= sprintf($tr_form, $tr_func
                                     , $cate_arr[$cate_top]
                                     , $cnt_td_html
                                     , $tr_func
                                     , $price_td_html);
    }

    // 총합계용 tr 생성
    $sum_vary_arr = getPrdtVaryArr($sum_arr, $date_arr);

    $sum_arr = $sum_arr["sum"];
    $sum_vary_arr = $sum_vary_arr["sum"];

    $sum_cnt_td_html .= '';
    $sum_price_td_html .= '';
    for($i = 0; $i < $date_arr_count; $i++) {
        $date = cutDateStr($date_arr[$i]);
        $data_arr = $sum_arr[$date];

        $cnt_vary   = intval($sum_vary_arr[$date]["cnt"]);
        $price_vary = intval($sum_vary_arr[$date]["price"]);

        $cnt_arrow = $span_up;
        if ($cnt_vary < 0) {
            $cnt_arrow = $span_down;
        } else if (empty($cnt_vary)) {
            $cnt_arrow = '';
        }

        $price_arrow = $span_up;
        if ($price_vary < 0) {
            $price_arrow = $span_down;
        } else if (empty($price_vary)) {
            $price_arrow = '';
        }

        $cnt   = doubleval($data_arr["cnt"]);
        $price = doubleval($data_arr["price"]);

        $sum_cnt_td_html .= sprintf($td_form, number_format($cnt) //#2
                                            , $cnt_vary //#2-1
                                            , $cnt_arrow);
        $sum_price_td_html .= sprintf($td_form, number_format($price) //#2
                                              , $price_vary //#2-1
                                              , $price_arrow);
    }

    $top_html .= sprintf($tr_form, ''
                                 , "총합계"
                                 , $sum_cnt_td_html
                                 , ''
                                 , $sum_price_td_html);

    $ret["html"] = $top_html . $bot_html;
    $ret["sum_arr"] = $sum_arr;

END:    
    return $ret;
}

/**
 * @brief 주문 상세집계 배열에서 증감값 배열 생성
 *
 * @param $detail_sort_arr = 정렬된 리스트 배열값
 * @param $date_arr        = 날짜 배열
 *
 * @return 기간별 증감값 배열
 */
function getPrdtVaryArr($detail_sort_arr, $date_arr) {
    $ret = array();

    $ret = calcPrdtVary($detail_sort_arr, "cnt", $date_arr, $ret);
    $ret = calcPrdtVary($detail_sort_arr, "price", $date_arr, $ret);

    return $ret;
}

/**
 * @brief 주문 상세집계 배열에서 증감 계산
 *
 * @param $arr      = 정렬된 리스트 배열값
 * @param $dvs      = 정렬 리스트 배열값에서 값을 가져올 필드명
 * @param $date_arr = 날짜 배열
 * @param $ret      = 반환값 배열, 연속성 때문에 추가
 *
 * @return 기간별 증감값 배열
 */
function calcPrdtVary($arr, $dvs, $date_arr, $ret = array()) {
    $cur       = cutDateStr($date_arr[0]);
    $last_year = cutDateStr($date_arr[1]);
    $m1        = cutDateStr($date_arr[2]);
    $m2        = cutDateStr($date_arr[3]);
    $m3        = cutDateStr($date_arr[4]);

    foreach ($arr as $cate_top => $sort_arr) {
        // 건수
        $cur_val       = doubleval($sort_arr[$cur][$dvs]);
        $last_year_val = doubleval($sort_arr[$last_year][$dvs]);
        $m1_val        = doubleval($sort_arr[$m1][$dvs]);
        $m2_val        = doubleval($sort_arr[$m2][$dvs]);
        $m3_val        = doubleval($sort_arr[$m3][$dvs]);

        // 분모가 0일경우 전부 0으로 처리
        if (empty($last_year_val)) {
            $cur_val_vary       = 0;
        } else {
            $cur_val_vary       = calcRate($cur_val, $last_year_val);
        }

        if (empty($cur_val)) {
            $last_year_val_vary = 0;
            $m1_val_vary        = 0;
            $m2_val_vary        = 0;
            $m3_val_vary        = 0;
        } else {
            $last_year_val_vary = calcRate($last_year_val, $cur_val);
            $m1_val_vary        = calcRate($m1_val, $cur_val);
            $m2_val_vary        = calcRate($m2_val, $cur_val);
            $m3_val_vary        = calcRate($m3_val, $cur_val);
        }

        $ret[$cate_top][$cur][$dvs]       = $cur_val_vary;
        $ret[$cate_top][$last_year][$dvs] = $last_year_val_vary;
        $ret[$cate_top][$m1][$dvs]        = $m1_val_vary;
        $ret[$cate_top][$m2][$dvs]        = $m2_val_vary;
        $ret[$cate_top][$m3][$dvs]        = $m3_val_vary;
    }

    return $ret;
}

/**
 * @brief 차트용 json 생성
 *
 * @param $sum_arr         = 총합계 데이터 배열
 * @param $detail_sort_arr = 정렬된 리스트 배열값
 * @param $cate_arr        = 카테고리 한글명 배열
 * @param $date_arr        = 날짜 배열
 *
 * @return 비율
 */
function makeChartDataJson($sum_arr, $detail_sort_arr, $cate_arr, $date_arr) {
    $data_form  = '{';
    $data_form .=  "\"title\" : {\"text\" : \"%s\"}";
    $data_form .= ",\"categories\" : %s";
    $data_form .= ",\"data\"       : [%s]";
    $data_form .= '}';

    $cate_str = json_encode($date_arr);

    $date_arr_count = count($date_arr);

    $ret = '';
    $data_str = '';
    for($i = 0; $i < $date_arr_count; $i++) {
        $date = cutDateStr($date_arr[$i]);
        $data_arr = $sum_arr[$date];

        $data_str .= intval($data_arr["price"]) . ',';
    }

    $ret .= sprintf($data_form, "총합계"
                              , $cate_str
                              , substr($data_str, 0, -1));
    $ret .= ',';

    foreach ($detail_sort_arr as $cate_top => $sort_arr) {

        $data_str = '';
        for($i = 0; $i < $date_arr_count; $i++) {
            $date = cutDateStr($date_arr[$i]);
            $data_arr = $sort_arr[$date];

            $data_str .= intval($data_arr["price"]) . ',';
        }

        $ret .= sprintf($data_form, $cate_arr[$cate_top]
                                  , $cate_str
                                  , substr($data_str, 0, -1));
        $ret .= ',';
    }

    $ret = substr($ret, 0, -1);

    return $ret;
}

/**
 * @brief 비율 계산
 *
 * @param $numerator = 분자
 * @param $denominator = 분모
 *
 * @return 비율
 */
function calcRate($numerator, $denominator) {
    return ($numerator / $denominator) * 100;
}

/**
 * @brief yyyy-mm-dd 을 yyyy-mm 으로 변경
 *
 * @param $str = 날짜 문자열
 *
 * @return yyyy-mm 문자열
 */
function cutDateStr($str) {
    if (strlen($str) > 7) {
        return substr($str, 0, -3);
    }

    return $str;
}
?>
