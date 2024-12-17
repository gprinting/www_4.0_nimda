<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 클레임관리 리스트 생성
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/09/01 이청산 생성(기존 로직에서 변경)
 * 2017/09/05 이청산 수정(팀별 검색 관련 수정)
 *=============================================================================
 */

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/oto_inq_mng/OtoInqMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OtoInqMngDAO();
$util = new CommonUtil();
$claim_dao = new ClaimListDAO();

$fb = $fb->getForm();

//$conn->debug = 1;

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);

$date_sel       = $fb["date_sel"];
$basic_from     = $fb["basic_from"];
$basic_to       = $fb["basic_to"];
$sell_site      = $fb["sell_site"];
$depar          = $fb["depar"];
$member_seqno   = $fb["member_seqno"];
$answ_yn        = $fb["answ_yn"];

$param = array();
$param["date_sel"]     = $date_sel;
$param["from"]         = $basic_from;
$param["to"]           = $basic_to;
$param["sell_site"]    = $sell_site;
$param["depar"]        = $depar;
$param["member_seqno"] = $member_seqno;
$param["answ_yn"]      = $answ_yn;

$page_count = ($page - 1) * 5;

//직원 팀 검색
$pre_rs = $claim_dao->selectEmplTeam($conn, $param);
$empl_seqno = "";
while(!$pre_rs->EOF && $pre_rs) {
    $empl_seqno .= $pre_rs->fields["empl_seqno"];
    $empl_seqno .= ",";

    $pre_rs->MoveNext();
}
$empl_seqno = substr($empl_seqno, 0, -1);
// 빈값일경우 확실히 빈값을 넘김 
if ($empl_seqno == "") {
    $empl_seqno = "";
}

$param["empl_seqno"] = $empl_seqno;

$rs = $dao->selectOtoInquireList($conn, $param, $page_count);

$json = "{\"list\" : \"%s\", \"total\" : \"%s\", \"result_cnt\" : \"%s\"}";
 
if ($rs->EOF) {
    $list  = "<tr>";
    $list .= "<td colspan=9 style=\"text-align:center\">검색결과없음</td>";
    $list .= "</tr>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$total = '';
$total = makeOtoListTotalHtml(array(
    "result_cnt" => $result_cnt,
));
$list = makeOtoListByHtml($rs, $page);

FIN :
    echo sprintf($json, $util->convJsonStr($list)
                      , $util->convJsonStr($total)
                      , $result_cnt);

    $conn->Close();
    exit;

/*************************************함수 영역 ********************************************/

/**
 * @brief 1:1문의 리스트 총계 생성
 *
 * @param $rs = 검색결과
 *
 * @return total_html
 */
function makeOtoListTotalHtml($param) {
    $total_form  = "<th class=\"th_table_accent\"></th>";
    $total_form .= "<th class=\"th_table_accent\">TOTAL</th>";
    $total_form .= "<th class=\"th_table_accent\">%s</th>";
    $total_form .= "<th class=\"th_table_accent\" colspan=\"6\"></th>";

    $total_html = sprintf($total_form, $param["result_cnt"]);

    return $total_html;
}

/**
 * @brief 클레임 리스트 생성
 *
 * @param $rs = 검색결과
 *
 * @return list_html
 */
function makeOtoListByHtml($rs, $page) {

    $list_form .= "<tr class='%s'>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td>%s</td>";
    $list_form .= "   <td><button type=\"button\" class=\"orge btn_pu btn fix_height20 fix_width40\" onclick=\"getInq.exec('%s');\">수정</button></td>";
    $list_form .= "</tr>";

    $list_html  = '';

    $page_block = ($page * 5) - 4;
    $class = "";
    $num = 0;

    while ($rs && !$rs->EOF) {

        $num = $page_block;
        if ($num % 2 == 0) {
            $class = "cellbg";
        } else if ($num % 2 == 1) {
            $class = "";
        }
       
        $fields = $rs->fields;

        $answ_yn = "";
        if ($fields["answ_yn"] == "Y") {
            $answ_yn = "답변완료";
        } else {
            $answ_yn = "답변대기";
        }

        $inq_date = "";
        if ($fields["inq_date"]) {
            $inq_date = date("Y-m-d", strtotime($fields["inq_date"]));
        }
        $reply_date = "";
        if ($fields["reply_date"]) {
            $reply_date = date("Y-m-d", strtotime($fields["reply_date"]));
        } 

        $list_html .= sprintf($list_form, $class
                                        , $page_block++ 
                                        , $inq_date
                                        , $fields["member_name"] . "<span style=\"color:blue; font-weight: bold;\">[" . $fields["office_nick"] . "]</span>"
                                        , $fields["inq_typ"]
                                        , $fields["title"]
                                        , $reply_date
                                        , $fields["name"]
                                        , $answ_yn
                                        , $fields["oto_inq_seqno"]);
        $rs->moveNext();
    }

    return $list_html;
}

?>
