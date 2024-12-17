<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 에누리액 VIEW 팝업 정보 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/12 이청산 생성
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

$depo_dvs_arr = DEPO_INPUT_TYPE;
$is_out = false;
$depo_dvs = null;
foreach ($depo_dvs_arr as $code_f => $code_b_arr) {
    foreach ($code_b_arr as $code_b => $typ) {
        if ($typ === "DC") {
            $is_out = true;
            $depo_dvs = $code_f;
        }

        if ($is_out) {
            break 2;
        }
    }
}

$param["depo_dvs"]     = $depo_dvs;

//$conn->debug = 1;

$page_count = ($page - 1) * 5;
$rs = $dao->selectDiscountViewData($conn, $param, $page_count);

$json = "{\"list\" : \"%s\", \"result_cnt\" : \"%s\"}";
$list = '';

if ($rs->EOF) {
    $list = "<td colspan=\"5\" style=\"text-align:center;\">검색결과없음</td>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$list = makeDiscountViewList($rs, $page, $util);

FIN :
    echo sprintf($json, $util->convJsonStr($list)
                      , $result_cnt);

    $conn->Close();
    exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/** 
 * @brief 에누리액정보 list 생성
 *
 * @param $rs = 검색결과
 *
 * @return list
 */
function makeDiscountViewList($rs, $page, $util) {
    $tbody_form .= "<tr id=\"discount_view_tr_%s\" ";
    $tbody_form .=     "class=\"discount_view_tr\"> ";
    $tbody_form .=     "<td style=\"text-align:center\">%s</td>";
    $tbody_form .=     "<td style=\"text-align:center\">%s</td>";
    $tbody_form .=     "<td style=\"text-align:center\">%s</td>";
    $tbody_form .=     "<td style=\"text-align:right\">%s 원</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $list = '';

    $page_block = ($page * 5) - 4;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $input_typ = $fields["input_typ"];

        $list .= sprintf($tbody_form, $page_block
                                    , $page_block++
                                    , substr($fields["deal_date"], 0, 10)
                                    , $fields["order_num"]
                                    , number_format($fields["depo_price"])
                                    , $fields["cont"]);

        $rs->MoveNext();

    }
    
    return $list;
}

?>
