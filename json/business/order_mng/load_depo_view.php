<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 매출액 VIEW 팝업 정보 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/10 이청산 생성
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
$from         = $fb["from"];
$to           = $fb["to"];
$met          = $fb["met"];

$param = array();
$param["member_seqno"] = $member_seqno;
$param["page"]         = $page;
$param["from"]         = $from;
$param["to"]           = $to;
$param["input_typ"]    = $met;

//$conn->debug = 1;

$page_count = ($page - 1) * 5;
$rs = $dao->selectDepoViewData($conn, $param, $page_count);

$json = "{\"list\" : \"%s\", \"result_cnt\" : \"%s\"}";
$list = '';

if ($rs->EOF) {
    $list = "<td colspan=\"14\">검색결과없음</td>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$list = makeDepoViewList($rs, $page, $util);

FIN :
    echo sprintf($json, $util->convJsonStr($list)
                      , $result_cnt);

    $conn->Close();
    exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/** 
 * @brief 매출액정보 list 생성
 *
 * @param $rs = 검색결과
 *
 * @return list
 */
function makeDepoViewList($rs, $page, $util) {
    $tbody_form .= "<tr id=\"depo_view_tr_%s\" ";
    $tbody_form .=     "class=\"depo_view_tr\"> ";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .= "</tr>";

    $list = '';

    $page_block = ($page * 5) - 4;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $input_typ = $fields["input_typ"];
        $typ = $util->selectDepoInputType($input_typ);

        $pay_price  = $fields['pay_price'];
        $depo_price = $fields['depo_price'];
        if ($pay_price == '0') {
            $price_dat = $depo_price; 
        } else if ($depo_price == '0') {
            $price_dat = $pay_price;
        }

        $list .= sprintf($tbody_form, $page_block
                                    , $page_block++
                                    , substr($fields["deal_date"], 0, 10)
                                    , number_format($price_dat)
                                    , $typ
                                    , $fields["member_name"]
                                    , $fields["card_num"]
                                    , $fields["mip_mon"]
                                    , $fields["aprvl_num"]
                                    , ""
                                    , ""
                                    , "" 
                                    , ""
                                    , "" 
                                    , ""
                                    , "");

        $rs->MoveNext();

    }
    
    return $list;
}

?>
