<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/07 이청산 생성
 * 2017/07/25 이청산 수정
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/OrderMngUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new OrderMngUtil();

$fb = $fb->getForm();

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);

//회원 일련번호
$member_seqno = $fb["seqno"];

$param = array();
$param["member_seqno"] = $member_seqno;
$param["page"]         = $page;

//$conn->debug = 1;

$page_count = ($page - 1) * 5;
$rs = $dao->selectSalesDepo($conn, $param, $page_count);

$json = "{\"list\" : \"%s\", \"pre\" : \"%s\", \"result_cnt\" : \"%s\"}";
$list = '';

if ($rs->EOF) {
    $list = "<td colspan=\"4\">검색결과없음</td>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

//잔고 가져옴
$pre_bal = '';
$pre_res = $dao->selectMemberPrepayPrice($conn, $member_seqno);
$pre_bal = number_format($pre_res);
$list = makeSalesDepoList($rs, $page, $util);

FIN :
    echo sprintf($json, $util->convJsonStr($list)
                      , $pre_bal
                      , $result_cnt);

    $conn->Close();
    exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/** 
 * @brief 매출액 list 생성
 *
 * @param $rs = 검색결과
 *
 * @return list
 */
function makeSalesDepoList($rs, $page, $util) {
    $tbody_form .= "<tr id=\"sales_depo_tr_%s\" ";
    $tbody_form .=     "class=\"sales_depo_tr\"> ";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "    %s";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $list = '';

    $price_dat = '';

    $page_block = ($page * 5) - 4;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $input_typ = $fields["input_typ"];
        $typ = $util->selectDepoInputType($input_typ);

        // 입금액
        $pay_price  = $fields['pay_price'];
        $depo_price = $fields['depo_price'];
        if ($pay_price == '0') {
            $price_dat = $depo_price; 
        } else if ($depo_price == '0') {
            $price_dat = $pay_price;
        }
        
        $mark_html = "";
        $cont_html = "";
        // 주문번호 입력유무에 따라 부호와 색상을 붙임(-, 파랑) 
        $order_num       = $fields['order_num'];
        $order_cancel_yn = $fields['order_cancel_yn'];
        if ($order_num != "" || $order_num != null) {
            $mark_html = "<td style=\"color:blue;\">- ". number_format($price_dat) ." </td>";
            $cont_html = "주문사용";
            if ($order_cancel_yn == "Y") {
                $mark_html = "<td style=\"color:red;\">+ ". number_format($price_dat) ." </td>";
                $cont_html = "주문취소";
            }
        // 입금구분으로 부호(+/-)와 색상을 붙임(사용자 선입금 기준)
        } else {
            $depo_dvs  = $fields['dvs'];
            if ($depo_dvs == "매출감소" || $depo_dvs == "입금증가" || $depo_dvs == "입금"|| $typ == "DC") {
                $mark_html = "<td style=\"color:red;\">+ ". number_format($price_dat) ." </td>";
            } else if ($depo_dvs == "매출증가" || $depo_dvs == "입금감소") {
                $mark_html = "<td style=\"color:blue;\">- ". number_format($price_dat) ." </td>";
            } else {
                $mark_html = "<td>". number_format($price_dat) ."</td>";
            }
            $cont_html = $fields["cont"];
        }
        $list .= sprintf($tbody_form, $page_block++
                                    , substr($fields["deal_date"], 0, 10)
                                    , $cont_html
                                    , $mark_html
                                    , $typ);

        $rs->MoveNext();
    }

    return $list;
}

?>
