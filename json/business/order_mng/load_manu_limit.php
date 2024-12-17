<?
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 생산투입한도설정 정보 검색 후
 * json 생성 후 반환
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/07/05 이청산 생성
 * 2017/07/26 이청산 생성
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
$rs = $dao->selectManuLimit($conn, $param, $page_count);

$json = "{\"thead\" : \"%s\", \"tbody\" : \"%s\", \"result_cnt\" : \"%s\"}";

if ($rs->EOF) {
    $thead_html = '';
    $tbody_html = "<td colspan=\"11\">검색결과없음</td>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$thead_html = '';
$thead_html = makeManuLimitTheadHtml(array(
    "result_cnt" => $result_cnt,
));
$tbody_html = makeManuLimitTbodyHtml($rs, $page, $util);

FIN :
    echo sprintf($json, $util->convJsonStr($thead_html)
                      , $util->convJsonStr($tbody_html)
                      , $result_cnt);

    $conn->Close();
    exit;

/******************************************************************************
 ******************** 공통사용 함수
 ******************************************************************************/

/**
 * @brief 생산한도설정정보 thead 생성
 *
 * @param $rs = 검색결과
 *
 * @return thead_html
 */
function makeManuLimitTheadHtml($param) {
    $thead_form  = "<th class=\"th_table_accent\">총계</th>";
    $thead_form .= "<th class=\"th_table_accent\">%s</th>";
    $thead_form .= "<th class=\"th_table_accent\" colspan=\"9\"></th>";

    $thead_html = sprintf($thead_form, $param["result_cnt"]);

    return $thead_html;
}

/** 
 * @brief 생산한도설정정보 tbody 생성
 *
 * @param $rs = 검색결과
 *
 * @return tbody_html
 */
function makeManuLimitTbodyHtml($rs, $page, $util) {
    $tbody_form .= "<tr id=\"manu_limit_tr_%s\" ";
    $tbody_form .=     "class=\"manu_limit_tr %s\"> ";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>"; 
    $tbody_form .=     "<td>%s</td>"; 
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "<td class=\"cursor\" style=\"overflow: initial;\"><span class=\"tooltip\">%s";
    $tbody_form .=     "<span class=\"tooltiptext\">%s</span>";
    $tbody_form .=     "</span></td>";
    $tbody_form .=     "<td>%s</td>";
    $tbody_form .=     "%s";
    $tbody_form .= "</tr>";

    $tbody_html = '';

    $page_block = ($page * 5) - 4;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;
        
        if ($page_block % 2 == 0) {
            $class = "cellbg";
        } else if ($page_block % 2 == 1) {
            $class = ""; 
        }

        $depo_yn = $fields["depo_yn"];
        $depo_name = $util->selectDepoYn($depo_yn);
       
        $limit_cate = $fields["limit_cate"]; 
        $cate_name  = $util->selectLimitCate($limit_cate);

        $tbody_html .= sprintf($tbody_form, $page_block
                                          , $class
                                          , $page_block++
                                          , $fields["member_name"]
                                          , $fields["regi_date"]
                                          , number_format($fields["limit_price"])
                                          , $fields["deal_date"]
                                          , $fields["depo_promi_date"]
                                          , $fields["regi_empl"]
                                          , $cate_name
                                          , mb_substr($fields["memo"], 0, 7)
                                          , $fields["memo"]
                                          , $fields["release_empl"]
                                          , $depo_name);

        $rs->MoveNext();
    }

    return $tbody_html;
}

?>
