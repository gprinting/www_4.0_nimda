<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 주문상태별 리스트 table html 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/05/08 엄준현 생성
 * 2017/08/31 이청산 수정
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
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

$state_arr = $fb->session("state_arr");

//$conn->debug = 1;

// 상태배열 한글->코드 에서 코드->한글로 변경
$reverse_state_arr = [];
foreach ($state_arr as $ko => $status) {
    $reverse_state_arr[$status] = $ko;
}

$fb = $fb->getForm();

$seqno          = $fb["seqno"];
$depar          = $fb["depar"];
$member_typ     = $fb["member_typ"];
$sortcode_t     = $fb["sortcode_t"];
$sortcode_m     = $fb["sortcode_m"];
$search_dvs     = $fb["search_dvs"];
$search_keyword = $fb["search_keyword"];
$order_status   = $fb["order_status"];
$from           = $fb["from"];
$to             = $fb["to"];
// 페이징용
$page     = empty($fb["page"]) ? 1 : intval($fb["page"]);
$page     = ($page - 1) * 10;
// 페이지 이동인지 아닌지 구분값, SQL_CALC_FOUND_ROWS 사용때문에 필요
$page_dvs = $fb["page_dvs"];

$param = [];
$param["page"]     = $page;
$param["page_dvs"] = $page_dvs;

$cate_sortcode = $sortcode_t;
if (!empty($sortcode_m)) {
    $cate_sortcode = $sortcode_m;
}

$param["member_seqno"]  = $seqno;
$param["order_state"]   = $order_status;
$param["cate_sortcode"] = $cate_sortcode;
$param["from"]          = $from;
$param["to"]            = $to;
$param["member_typ"]    = $member_typ;
$param["depar"]         = $depar;
$param[$search_dvs]     = $search_keyword;

//$param["order_state"]   = '1320';

$price_rs = $dao->selectTotalOrderPriceByStatus($conn, $param);
$rs       = $dao->selectOrderCommonSumByStatus($conn, $param, $page);
$count_rs = $dao->selectOrderCommonCountByStatus($conn, $param);

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$sum_price = intval($price_rs["sell_price"]) +
             intval($price_rs["grade_sale_price"]) +
             intval($price_rs["dlvr_price"]);

$status = $reverse_state_arr[$order_status];
$html_info = makeOrderStatusList($rs, ["status_ko"   => $status,
                                       "status_code" => $order_status,
                                       "page" => $page,
                                       "from" => $from,
                                       "to"   => $to]);

$count_info = makeOrderCount($count_rs);

$tbody_html  = $html_info["tbody"];
$order_count = $count_info["order_cnt"];
$sum_sell    = $count_info["sell"];
$sum_sale    = $count_info["sale"];
$sum_pay     = $count_info["pay"];

if (empty($tbody_html)) {
    $tbody_html = "<td colspan=\"17\">검색정보 없음</td>";
}

$json  = '{';
$json .=   "\"order_cnt\"  : \"%s\",";
$json .=   "\"result_cnt\" : \"%s\",";
$json .=   "\"sum_price\"  : \"%s\",";
$json .=   "\"tbody\"      : \"%s\",";
$json .=   "\"sum_sell\"   : \"%s\",";
$json .=   "\"sum_sale\"   : \"%s\",";
$json .=   "\"sum_pay\"    : \"%s\"";
$json .= '}';

echo sprintf($json, $util->convJsonStr($order_count)
                  , $result_cnt
                  , number_format($sum_price)
                  , $util->convJsonStr($tbody_html)
                  , $util->convJsonStr($sum_sell)
                  , $util->convJsonStr($sum_sale)
                  , $util->convJsonStr($sum_pay));

$conn->Close();
exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/**
 * @brief 주문진행정보 html 생성
 *
 * @param $rs = 검색결과
 *
 * @return array(
 *     "tbody"
 * )
 */
function makeOrderStatusList($rs, $param) {
    $member_seqno = $param["member_seqno"];
    $from = $param["from"];
    $to   = $param["to"];
    $status_code = $param["status_code"];
    $status_ko   = $param["status_ko"];
    $page   = $param["page"];
    $dlvr_typ_arr = DLVR_TYP;

    $tr_html  = "<tr class=\"%s\">";
    $tr_html .=     "<td>%s</td>"; // no
    $tr_html .=     "<td>%s</td>"; // order_staus
    $tr_html .=     "<td>%s</td>"; // 주문/등록일
    $tr_html .=     "<td>%s</td>"; // 접수일
    $tr_html .=     "<td>%s</td>"; // 회원명
    $tr_html .=     "<td>%s</td>"; // 휴대폰 번호
    $tr_html .=     "<td>%s 외 %s건</td>"; // 제작물 내용
    //$tr_html .=     "<td>%s</td>"; // 재질 및 규격
    $tr_html .=     "<td class=\"cursor\" style=\"text-align:left;overflow: initial;\"><span class=\"tooltip\">%s"; // 재질 및 규격
    $tr_html .=     "<span class=\"tooltiptext\">%s</span>";
    $tr_html .=     "</span</td>";
    $tr_html .=     "<td>%s</td>"; // 수량
    $tr_html .=     "<td>%s</td>"; // 건수
    $tr_html .=     "<td>%s</td>"; // 주문금액
    $tr_html .=     "<td>%s</td>"; // 할인금액
    $tr_html .=     "<td>%s</td>"; // 결제금액
    $tr_html .=     "<td>%s</td>"; // 배송방법
    $tr_html .=     "<td><button type=\"button\" onclick=\"showOrderStatusDetailPop(this, '%s', '%s', '%s', '%s');\" class=\"btn_yellow\">상세보기</button></td>"; // 상세보기
    $tr_html .=     "<td><button type=\"button\" onclick=\"showOrderStatusMmsPop(this, 'order_status_mms');\" class=\"btn_yellow\">문자</button></td>"; // 문자
    $tr_html .= "</tr>";

    $tbody_html = '';

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $sell_price        = intval($fields["sell_price"]);
        $grade_sale_price  = intval($fields["grade_sale_price"]);
        $member_sale_price = intval($fields["member_sale_price"]);
        $cp_price          = intval($fields["cp_price"]);
        $use_point_price   = intval($fields["use_point_price"]);
        //$dlvr_price        = intval($fields["dlvr_price"]);
        $pay_price         = intval($fields["pay_price"]);

        $sum_sale = $grade_sale_price +
                    $member_sale_price +
                    $cp_price +
                    $use_point_price;
        if ($page % 2 == 0) {
            $class = ""; 
        } else if ($page % 2 == 1) {
            $class = "cellbg";
        }

        $sum_sell_price = $sum_sell_price + $sell_price;
        $sum_sale_price = $sum_sale_price + $sum_sale;
        $sum_pay_price  = $sum_pay_price  + $pay_price;

        $tbody_html .= sprintf($tr_html, $class
                                       , ++$page // no
                                       , $status_ko // order_status
                                       , $fields["order_regi_date"] // 주문/등록일
                                       , $fields["receipt_start_date"] // 접수일
                                       , $fields["member_name"] // 회원명
                                       , $fields["cell_num"] // 휴대폰 번호
                                       , $fields["title"] // 제작물 내용
                                       , intval($fields["count"]) - 1 // 제작물 내용
                                       , mb_substr($fields["order_detail"], 0, 16) // 재질 및 규격
                                       , $fields["order_detail"] // 재질 및 규격
                                       , $fields["amt"] // 수량
                                       , $fields["count"] // 건수
                                       , number_format($sell_price) // 주문금액
                                       , number_format($sum_sale) // 할인금액
                                       , number_format($pay_price) // 결제금액
                                       , $dlvr_typ_arr[$dlvr_way] // 배송방법
                                       , $status_code // 상세보기
                                       , $fields["member_seqno"] // 상세보기
                                       , $from // 상세보기
                                       , $to // 상세보기
                                       );
        $rs->MoveNext();
    }

    return [
        "tbody"      => $tbody_html
    ];
}

/**
 * @brief 주문진행정보 count 생성
 *
 * @param $count_rs = 검색결과
 *
 * @return $order_cnt
 */
function makeOrderCount($count_rs) {
    
    $order_cnt = 0;
    $sum_sell_price = 0;
    $sum_sale_price = 0;
    $sum_pay_price  = 0;

    while ($count_rs && !$count_rs->EOF) {
        $fields = $count_rs->fields;

        $sell_price        = intval($fields["sell_price"]);
        $grade_sale_price  = intval($fields["grade_sale_price"]);
        $member_sale_price = intval($fields["member_sale_price"]);
        $cp_price          = intval($fields["cp_price"]);
        $use_point_price   = intval($fields["use_point_price"]);
        //$dlvr_price        = intval($fields["dlvr_price"]);
        $pay_price         = intval($fields["pay_price"]);

        $sum_sale = $grade_sale_price +
                    $member_sale_price +
                    $cp_price +
                    $use_point_price;

        $sum_sell_price = $sum_sell_price + $sell_price;
        $sum_sale_price = $sum_sale_price + $sum_sale;
        $sum_pay_price  = $sum_pay_price  + $pay_price;

        $order_cnt = $order_cnt + $fields["count"] ;

        $count_rs->MoveNext();
    }

    return [
        "order_cnt"  => $order_cnt,
        "sell"       => number_format($sum_sell_price),
        "sale"       => number_format($sum_sale_price),
        "pay"        => number_format($sum_pay_price)

    ];

}
