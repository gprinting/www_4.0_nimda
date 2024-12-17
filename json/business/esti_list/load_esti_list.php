<?php
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * 견적관리 리스트 생성
 *
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2017/08/23 이청산 생성
 * 2017/09/15 이청산 수정 
 *=============================================================================
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

//$conn->debug = 1;

$page = empty($fb["page"]) ? 1 : intval($fb["page"]);
$page_dvs = $fb["page_dvs"];

$basic_from     = $fb["basic_from"];
$basic_to       = $fb["basic_to"];
$depar          = $fb["depar"];
$empl           = $fb["empl"];
$member_typ     = $fb["member_typ"];
$search_dvs     = $fb["search_dvs"];
$search_keyword = $fb["search_keyword"];

$flattyp_arr = [];
$flattyp_rs = $dao->selectEstiFlattypYn($conn, "010001");
while ($flattyp_rs && !$flattyp_rs->EOF) {
    $fields = $flattyp_rs->fields;
    $flattyp_arr[$fields["sortcode"]] = $fields["flattyp_yn"];

    $flattyp_rs->MoveNext();
}

$param = [];
$param["page_dvs"]   = $page_dvs;
$param["from"]       = $basic_from;
$param["to"]         = $basic_to;
$param["depar"]      = $depar;
$param["empl"]       = $empl;
$param["member_typ"] = $member_typ;
$param[$search_dvs]  = $search_keyword;

$page_count = ($page - 1) * 5;
$rs = $dao->selectEstiList($conn, $param, $page_count);

$json = "{\"list\" : \"%s\", \"result_cnt\" : \"%s\"}";

if ($rs->EOF) {
    $list = "<td colspan=\"8\" style=\"text-align:center\">검색결과없음</td>";
    $result_cnt = 0;

    goto FIN;
}

$result_cnt = '';
if (empty($page_dvs)) {
    $result_cnt = $dao->selectFoundRows($conn);
}

$list = makeEstiTopListHtml($rs, $page, $session["state_arr"], $flattyp_arr);

FIN :
    echo sprintf($json, $util->convJsonStr($list)
                      , $result_cnt);

    $conn->Close();
    exit;

/*************************************함수 영역 ********************************************/

/**
 * @brief html 영역 생성 함수
 */
function makeEstiTopListHtml($rs, $page, $state_arr, $flattyp_arr) {
    $list_form .= "<tr id=\"esti_top_%s\" seqno=\"%s\" "; //#1 esti_Seqno
    $list_form .=     "class=\"esti_top\" ";
    $list_form .=     "onclick=\"loadEstiBaseInfo.exec('%s', '%s')\"> "; //#2 esti_seqno, flattyp_yn
    $list_form .=     "<td>%s</td>"; //#3 member_name
    $list_form .=     "<td>%s</td>"; //#4 state
    $list_form .=     "<td>%s</td>"; //#5 regi_date
    $list_form .=     "<td>%s</td>"; //#6 esti_date
    $list_form .=     "<td>%s</td>"; //#7 esti_price
    $list_form .=     "<td>%s</td>"; //#8 order_price
    $list_form .=     "<td class=\"subject\">";
    $list_form .=         "<span class=\"category_text\">[%s]</span>&nbsp;"; //#9 cate_name
    $list_form .=         "<span class=\"order_list_title_text\">%s</span><br/>"; //#9 title
    $list_form .=         "<ul class=\"information\">%s</ul>"; //#9 esti_detail
    $list_form .=     "</td>";
    $list_form .=     "<td>%s</td>"; //#10 esti_mng
    $list_form .= "</tr>";

    $list_html = '';

    $page_block = ($page * 5) - 4;

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        switch ($fields["state"]) {
            case $state_arr["견적대기"] :
                $state = "견적대기";
                break;
            case $state_arr["견적중"] :
                $state = "견적중";
                break;
            case $state_arr["견적완료"] :
                $state = "견적완료";
                break;
        }

        $esti_detail = $fields["esti_detail"];
        $cate_name = trim(explode(',', (explode('/', $esti_detail)[0]))[0]);
        $esti_detail = explode('/', $esti_detail, 2)[1];

        $list_html .= sprintf($list_form, $fields["esti_seqno"] //#1
                                        , $fields["esti_seqno"] //#1
                                        , $fields["esti_seqno"] //#2
                                        , $flattyp_arr[$fields["cate_sortcode"]] //#2
                                        , $fields["member_name"] //#3
                                        , $state //#4
                                        , explode(' ', $fields["regi_date"])[0] //#5
                                        , explode(' ', $fields["esti_date"])[0] //#6
                                        , number_format($fields["esti_price"]) //#7
                                        , number_format($fields["order_price"]) //#8
                                        , $cate_name //#9
                                        , $fields["title"] //#9
                                        , $esti_detail //#9
                                        , $fields["esti_mng"]); //10

        $rs->MoveNext();

    }

    return $list_html;
}
