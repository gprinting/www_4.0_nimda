<?php
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

$fb = $fb->getForm();

$esti_seqno = $fb["esti_seqno"];
$flattyp_yn = $fb["flattyp_yn"];

$param = [];
$param["esti_seqno"] = $esti_seqno;

if ($flattyp_yn === 'Y') {
    $detail = $dao->selectEstiDetail($conn, $param);    
} else {
    $detail = $dao->selectEstiDetailBrochure($conn, $param);    
}

$file_seqno = '';
$file_name  = '';
$state = '';
$esti_mng = '';
$sortcode = $detail->fields["cate_sortcode"];
$json  = '{';
$json .=      "\"base_info\" : \"%s\"";
$json .=     ",\"file_seqno\" : \"%s\"";
$json .=     ",\"file_name\" : \"%s\"";
$json .=     ",\"state\" : \"%s\"";
$json .=     ",\"esti_mng\" : \"%s\"";
$json .=     ",\"sortcode\" : \"%s\"";
$json .= '}';

if ($detail->EOF) {
    $html  = "<tr>";
    $html .=     "<td colspan=\"9\" style=\"text-align:center\">결과 없음</td>";
    $html .= "</tr>";

    goto FIN;
}

// 견적정보 검색
$base = $dao->selectEstiBaseInfo($conn, $param);    
$state    = $base["state"];
$esti_mng = $base["esti_mng"];

if ($flattyp_yn === 'Y') {
    $html = makeEstiDetailSheetHtml($base, $detail);
} else {
    $param["after_name"] = "제본";
    $binding = $dao->selectEstiAfterHistory($conn, $param)->fields;

    $html = makeEstiDetailBookletHtml($base, $detail, $binding);
}

// 견적 파일정보 검색
$file = $dao->selectEstiFile($conn, $param);
$file_seqno = $file["esti_file_seqno"];
$file_name  = $file["origin_file_name"];

FIN :
    echo sprintf($json, $util->convJsonStr($html)
                      , $file_seqno, $file_name
                      , $state, $esti_mng, $sortcode);
    $conn->Close();
    exit;

/*************************************함수 영역 ********************************************/

// 낱장형 정보 html 생성
function makeEstiDetailSheetHtml($base, $detail) {
    $detail_form  = "<tr>";
    $detail_form .=     "<td rowspan=\"9\">기본정보</td>";
    $detail_form .=     "<td>제목</td>";
    $detail_form .=     "<td>%s</td>"; //#1 title
    $detail_form .=     "<td></td>";
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>종이</td>";
    $detail_form .=     "<td>%s</td>"; //#2 
    $detail_form .=     "<td>%s</td>"; //#3
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>전면도수</td>";
    $detail_form .=     "<td>%s</td>"; //#4
    $detail_form .=     "<td>%s</td>"; //#5
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>후면도수</td>";
    $detail_form .=     "<td>%s</td>"; //#6
    $detail_form .=     "<td>%s</td>"; //#7
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>인쇄유형</td>";
    $detail_form .=     "<td>%s</td>"; //#8
    $detail_form .=     "<td>%s</td>"; //#9
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>규격</td>";
    $detail_form .=     "<td>%s(%s)</td>"; //#10
    $detail_form .=     "<td></td>";
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>수량</td>";
    $detail_form .=     "<td>%s%s(%s장) x %s건</td>"; //#11
    $detail_form .=     "<td>%s</td>"; //#12
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>후가공</td>";
    $detail_form .=     "<td>%s</td>"; //#13
    $detail_form .=     "<td>%s</td>"; //#14
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>메모</td>";
    $detail_form .=     "<td>%s</td>"; //#15
    $detail_form .=     "<td></td>"; //#14
    $detail_form .= "</tr>";

    $detail = $detail->fields;

    $cut_size = $detail["cut_size_wid"] . '*' . $detail["cut_size_vert"];

    $amt = intval($base["amt"]) < 0 ? 0 : $base["amt"];
    $page_cnt = intval($base["page_cnt"]) < 0 ? 0 : $base["page_cnt"];

    $detail_html = sprintf($detail_form, $base["title"] //#1
                                       , $detail["paper_info"] //#2
                                       , $detail["paper_info_note"] //#3
                                       , $detail["bef_tmpt_info"] //#4
                                       , $detail["bef_tmpt_info_note"] //#5
                                       , $detail["aft_tmpt_info"] //#6
                                       , $detail["aft_tmpt_info_note"] //#7
                                       , $detail["print_purp_info"] //#8
                                       , $detail["print_purp_info_note"] //#9
                                       , $detail["size_info"] //#10
                                       , $cut_size //#10
                                       , $amt < 1 ? $amt : number_format($amt) //#11
                                       , $base["amt_unit_dvs"] //#11
                                       , number_format($page_cnt) //#11
                                       , number_format($base["count"]) //#11
                                       , $base["amt_note"] //#12
                                       , $detail["after_info"] //#13
                                       , $detail["after_info_note"] //#14
                                       , $base["memo"] //#15
                                       );
    return $detail_html;
}

// 책자형 정보 html 생성
function makeEstiDetailBookletHtml($base, $detail_rs, $binding) {
    $common_form  = "<tr>";
    $common_form .=     "<td rowspan=\"5\">기본정보</td>";
    $common_form .=     "<td>제목</td>";
    $common_form .=     "<td>%s</td>"; //#1 title
    $common_form .=     "<td></td>";
    $common_form .= "</tr>";
    $common_form .= "<tr>";
    $common_form .=     "<td>규격</td>";
    $common_form .=     "<td>%s(%s)</td>"; //#2
    $common_form .=     "<td></td>";
    $common_form .= "</tr>";
    $common_form .= "<tr>";
    $common_form .=     "<td>제본</td>";
    $common_form .=     "<td>%s</td>"; //#3
    $common_form .=     "<td></td>";
    $common_form .= "</tr>";
    $common_form .= "<tr>";
    $common_form .=     "<td>수량</td>";
    $common_form .=     "<td>%s%s(%s장)</td>"; //#4
    $common_form .=     "<td>%s</td>"; //#5
    $common_form .= "</tr>";
    $common_form .= "<tr>";
    $common_form .=     "<td>메모</td>";
    $common_form .=     "<td>%s</td>"; //#6
    $common_form .=     "<td></td>";
    $common_form .= "</tr>";

    $detail_form  = "<tr>";
    $detail_form .=     "<td rowspan=\"6\">%s</td>"; //#1 typ
    $detail_form .=     "<td>종이</td>";
    $detail_form .=     "<td>%s</td>"; //#2 
    $detail_form .=     "<td>%s</td>"; //#3
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>전면도수</td>";
    $detail_form .=     "<td>%s</td>"; //#4
    $detail_form .=     "<td>%s</td>"; //#5
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>후면도수</td>";
    $detail_form .=     "<td>%s</td>"; //#6
    $detail_form .=     "<td>%s</td>"; //#7
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>인쇄유형</td>";
    $detail_form .=     "<td>%s</td>"; //#8
    $detail_form .=     "<td>%s</td>"; //#9
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>페이지</td>";
    $detail_form .=     "<td>%sp</td>"; //#10
    $detail_form .=     "<td>%s</td>"; //#11
    $detail_form .= "</tr>";
    $detail_form .= "<tr>";
    $detail_form .=     "<td>후가공</td>";
    $detail_form .=     "<td>%s</td>"; //#12
    $detail_form .=     "<td>%s</td>"; //#13
    $detail_form .= "</tr>";

    $temp = $detail_rs->fields;
    $cut_size = $temp["cut_size_wid"] . '*' . $temp["cut_size_vert"];
    $amt = intval($base["amt"]) < 0 ? 0 : $base["amt"];
    $page_cnt = intval($base["page_cnt"]) < 0 ? 0 : $base["page_cnt"];

    $common_html = sprintf($common_form, $base["title"] //#1
                                       , $temp["size_info"] //#2
                                       , $cut_size //#2
                                       , $binding["depth1"] . '/' . $binding["depth2"] //#3
                                       , number_format($amt) //#4
                                       , $base["amt_unit_dvs"] //#4
                                       , number_format($page_cnt) //#4
                                       , $base["amt_note"] //#12
                                       , $base["memo"] //#15
                                       );

    $detail_html = '';
    while ($detail_rs && !$detail_rs->EOF) {
        $detail = $detail_rs->fields;

        $detail_html .= sprintf($detail_form, $detail["typ"] //#1
                                            , $detail["paper_info"] //#2
                                            , $detail["paper_info_note"] //#3
                                            , $detail["bef_tmpt_info"] //#4
                                            , $detail["bef_tmpt_info_note"] //#5
                                            , $detail["aft_tmpt_info"] //#6
                                            , $detail["aft_tmpt_info_note"] //#7
                                            , $detail["print_purp_info"] //#8
                                            , $detail["print_purp_info_note"] //#9
                                            , $detail["page_amt"] //#10
                                            , $detail["page_note"] //#11
                                            , $detail["after_info"] //#12
                                            , $detail["after_info_note"] //#13
                                            );

        $detail_rs->MoveNext();
    }

    return $common_html . $detail_html;
}
