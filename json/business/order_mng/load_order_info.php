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
 * 2017/05/08 엄준현 생성
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);;
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/common_define/common_info.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new CommonUtil();

$fb = $fb->getForm();

$member_seqno = $fb["seqno"];
$from         = $fb["from"];
$to           = $fb["to"];
$cate_top     = $fb["cate_top"];
$cate_mid     = $fb["cate_mid"];
$cate_bot     = $fb["cate_bot"];
$search_dvs     = $fb["search_dvs"];
$search_keyword = $fb["search_keyword"];

$cate_sortcode = null;
if (!empty($cate_bot)) {
    $cate_sortcode = $cate_bot;
} else if (!empty($cate_mid)) {
    $cate_sortcode = $cate_mid;
} else if (!empty($cate_top)) {
    $cate_sortcode = $cate_top;
}


$param = array();
$param["cate_sortcode"] = $cate_sortcode;
$param["member_seqno"]  = $member_seqno;
$param["from"]          = $from;
$param["to"]            = $to;
$param[$search_dvs] = $search_keyword;

if ($search_dvs === "typset_num") {
    $param["searchTxt"] = $search_keyword;
    $order_num_arr = [];
    switch(substr($search_keyword, 0, 3)) {
        case "NGC": // 코팅명함
        case "NGN": // 무코팅명함
        case "SPC": // 코팅스티커
        case "SPN": // 무코팅스티커
        case "SSP": // 특수지스티커
        case "JGA": // 아트지합판전단
        case "JGM": // 모조지합판전단
        case "JSG": // 일반지독판전단
        case "JSH": // 고급지독판전단
        case "JSS": // 특수지독판전단
        case "JSN": // 수입지독판전단
        case "APP": // 포스터
        case "APL": // 리플렛, 팜플렛
        case "APF": // 문어발
        case "APD": // 문고리
        case "APM": // 메모지
        case "EGC": // 봉투
        case "MJG": // 초소량인쇄
        case "DNC": // 디지털명함인쇄
        case "ETC": // 기타
            $rs = $dao->selectSalesSheetTypset($conn, $param);

            while ($rs && !$rs->EOF) {
                $order_num_arr[] = $rs->fields["order_num"];

                $rs->MoveNext();
            }

            $param["order_num"] = $dao->arr2paramStr($conn, $order_num_arr);
            break;
        default : 
            $res = $dao->selectSalesBrochureTypset($conn, $param);

            while ($rs && !$rs->EOF) {
                $order_detail_dvs_num = $rs->fields["order_detail_dvs_num"];
                $order_num_arr[] = substr($order_detail_dvs_num, 1, 16);

                $rs->MoveNext();
            }

            $param["order_num"] = $dao->arr2paramStr($conn, $order_num_arr);
            break;

    }
}
$rs = $dao->selectOrderInfoList($conn, $param);

if ($rs->EOF) {
    $html_arr = array();
    $html_arr["thead"] = '';
    $html_arr["tbody"] = "<td colspan=\"7\">검색결과없음</td>";
    goto FIN;
}

$sort_arr = sortOrderDataRs($rs);
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

//이미지 파일 경로
$file_path = FILE_ARROW_DOWN;
$html_arr = makeOrderInfoListHtml($param, $file_path);

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
 * @brief 주문정보 검색결과 카테고리별로 구분
 *
 * @param $rs = 검색결과
 *
 * @return array(
 *     "총 건수(sum_count)" => x
 *     "총 결재금액(sum_pay_price)" => x
 *     "명함(001)" => [데이터1, 데이터2...]
 *     "스티커(002)" => [데이터1, 데이터2...] ...
 * )
 */
function sortOrderDataRs($rs) {
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
 * @brief 주문정보 html 생성
 *
 * @param $param = html 생성용 데이터
 * @detail $cate_top_arr = 카테고리 대분류 배열
 * $sort_arr = 검색결과 데이터 배열
 *
 * @return array(
 *     "thead_html"
 *     "tbody_html"
 * )
 */
function makeOrderInfoListHtml($param, $file_path) {
    $cate_top_arr = $param["cate_top_arr"];
    $sort_arr     = $param["sort_arr"];
    $state_arr    = $param["state_arr"];

    $sum_count = $sort_arr["sum_count"];
    $sum_pay_price = $sort_arr["sum_pay_price"];
    unset($sort_arr["sum_count"]);
    unset($sort_arr["sum_pay_price"]);

    $thead_form  = "<th class=\"th_table_accent\" colspan=\"2\">총계</th>";
    $thead_form .= "<th class=\"th_table_accent\">총 %s건</th>";
    $thead_form .= "<th class=\"th_table_accent\" style=\"text-align:right;\">&#65510; %s</th>";
    $thead_form .= "<th class=\"th_table_accent\" colspan=\"3\"></th>";

    $tr_top_form  = "<tr onclick=\"toggleRow('%s', 'mid');\"class=\"tr_table_accent cursor\">";
    $tr_top_form .=     "<td>%s</td>";
    $tr_top_form .=     "<td>%s</td>";
    $tr_top_form .=     "<td>총 %s건</td>";
    $tr_top_form .=     "<td style=\"text-align:right;\">&#65510; %s</td>";
    $tr_top_form .=     "<td colspan=\"3\"></td>";
    $tr_top_form .= "</tr>";
    /*
    $tr_mid_form  = "<tr class=\"toggle_mid_%s row_bg hidden_row\" onclick=\"toggleRow('%s', 'bot');\">";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td class=\"tooltip\" style=\"text-align:left;\">%s";
    $tr_mid_form .=     "<span class=\"tooltiptext\">%s</span>";
    $tr_mid_form .=     "</td>";
    $tr_mid_form .=     "<td style=\"text-align:right;\">&#65510; %s</td>";
    $tr_mid_form .=     "<td class=\"ctrl_p\">%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td onclick=\"showOrderCustMemo('%s');\" style=\"cursor:pointer;\">메모</td>";
    $tr_mid_form .= "</tr>";
    */

    /*
    $tr_mid_form  = "<tr class=\"toggle_mid_%s row_bg hidden_row\" onclick=\"toggleRow('%s', 'bot');\">";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td><span class=\"tooltip\" style=\"text-align:left;\">%s";
    $tr_mid_form .=     "<span class=\"tooltiptext\">%s</span>";
    $tr_mid_form .=     "</span></td>";
    $tr_mid_form .=     "<td style=\"text-align:right;\">&#65510; %s</td>";
    $tr_mid_form .=     "<td class=\"ctrl_p\">%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td onclick=\"showOrderCustMemo('%s');\" style=\"cursor:pointer;\">메모</td>";
    $tr_mid_form .= "</tr>";
    */
    $tr_mid_form  = "<tr class=\"toggle_mid_%s %s row_bg hidden_row\" onclick=\"set_enuri('%s');\">";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "<td class=\"cursor\" style=\"text-align:left;overflow: initial;\"><span class=\"tooltip\">%s";
    $tr_mid_form .=     "<span class=\"tooltiptext\">%s</span><img class=\"img_view_depth\" src=". $file_path .">";
    $tr_mid_form .=     "</span></td>";
    $tr_mid_form .=     "<td style=\"text-align:right;\">&#65510; %s</td>";
    $tr_mid_form .=     "<td class=\"ctrl_p\">%s</td>";
    $tr_mid_form .=     "<td>%s</td>";
    $tr_mid_form .=     "%s";
                        /* 메모가 있는 곳에는 클래스 "memo_active"를 선언 부탁드립니다. */
    $tr_mid_form .= "</tr>";
    
    $memo_form .=     "<input type=\"hidden\" value=\"%s\" />";
    $memo_form .=     "<td onclick=\"showOrderCustMemo('%s');\" class=\"memo_active\" style=\"cursor:pointer;\">메모</td>";

    $tr_bot_form .= "<tr class=\"toggle_bot_%s toggle_bot hidden_row hidden_ground\"></tr>";

    $thead_html = sprintf($thead_form, number_format($sum_count)
                                     , number_format($sum_pay_price));

    $tbody_html = '';
    $prev_cate_top = null;
    $i = 1;
    foreach ($sort_arr as $cate_top => $info_arr) {
        $tr_top = sprintf($tr_top_form, $i
                                      , $i
                                      , $cate_top_arr[$cate_top]
                                      , number_format($info_arr["sum_count"])
                                      , number_format($info_arr["sum_pay_price"]));

        $tbody_html .= $tr_top;

        $data_arr = $info_arr["data_arr"];
        $data_arr_count = count($data_arr);
        
        for($j = 0; $j < $data_arr_count; $j++) {
            $data = $data_arr[$j];

            if ($j % 2 == 0) {
                $class = ""; 
            } else if ($j % 2 == 1) {
                $class = "cellbg";
            }

            if (!empty($data["cust_memo"])) { 
                $memo = sprintf($memo_form, $data["cust_memo"]
                                          , $data["order_num"]);
            } else {
                $memo = "<td>메모</td>";
            }

            $tr_mid = sprintf($tr_mid_form, $i
                                          , $class
                                          , explode(' ', $data["order_num"])[0]
                                          , $j + 1
                                          , $data["order_num"]
                                          , mb_substr($data["title"], 0, 34)
                                          , nl2br($data["title"])
                                          , number_format($data["pay_price"])
                                          , $data["pay_way"]
                                          , DLVR_TYP[$data["dlvr_way"]]
                                          //, $data["cust_memo"]
                                          , $memo);
            $tr_bot = sprintf($tr_bot_form, $data["order_num"]);

            $tbody_html .= $tr_mid . $tr_bot;
        }

        $i++;
    }

    return array(
        "thead" => $thead_html,
        "tbody" => $tbody_html
    );
}
?>
