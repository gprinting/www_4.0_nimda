<?php
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");
include_once(INC_PATH . "/define/front/product_info_class.inc");
include_once(INC_PATH . "/common_define/prdt_default_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();
$util = new CommonUtil();

$session = $fb->getSession();
$fb = $fb->getForm();

$member_seqno = $session["org_member_seqno"];
$esti_seqno = $fb["esti_seqno"];
$flattyp_yn = $fb["flattyp_yn"];
$cate_sortcode = $fb["cs"];

//$conn->debug = 1;

// paper_sort html 생성
$paper_sort_html = option("전체");
$paper_rs = $dao->selectPrdtPaper($conn, [], "sort");
while ($paper_rs && !$paper_rs->EOF) {
    $fields = $paper_rs->fields;

    $paper_sort_html .= option($fields["sort"], $fields["sort"]);

    $paper_rs->MoveNext();
}
unset($paper_rs);

// prdt_output_info html 생성
$prdt_output_info_html = '';
$output_rs = $dao->selectPrdtOutputInfo($conn);
while ($output_rs && !$output_rs->EOF) {
    $fields = $output_rs->fields;

    $name = $fields["output_name"];
    $mpcode = $fields["mpcode"];
    $board_dvs = $fields["output_board_dvs"];

    $name = $name . '(' . $board_dvs . ')';

    $prdt_output_info_html .= option($name, $mpcode);

    $output_rs->MoveNext();
};
unset($output_rs);

// prdt_print_info html 생성
$prdt_print_info_html = '';
$print_rs = $dao->selectPrdtPrintInfo($conn, "010001");
while ($print_rs && !$print_rs->EOF) {
    $fields = $print_rs->fields;

    $name = $fields["print_name"];
    $mpcode = $fields["mpcode"];
    $purp_dvs = $fields["purp_dvs"];

    $name = $name . '(' . $purp_dvs . ')';

    $prdt_print_info_html .= option($name, $mpcode);

    $print_rs->MoveNext();
};
unset($print_rs);

$param = [];
$param["esti_seqno"] = $esti_seqno;

if ($flattyp_yn === 'Y') {
    $table = "esti_detail";
} else {
    $table = "esti_detail_brochure";
}

$mpcode_rs = $dao->selectEstiDetailMpcode($conn, $param, $table);

$typ_arr = [];
$paper_arr  = [];
$output_arr = [];
$print_arr  = [];
while ($mpcode_rs && !$mpcode_rs->EOF) {
    $fields = $mpcode_rs->fields;

    $typ = $fields["typ"];

    $typ_arr[$typ] = $fields["esti_detail_dvs_num"];
    $paper_arr[$typ]  = $fields["paper_mpcode"];
    $output_arr[$typ] = $fields["output_mpcode"];
    $print_arr[$typ]  = [
         "bef" => $fields["bef_print_mpcode"]
        ,"aft" => $fields["aft_print_mpcode"]
    ];

    $mpcode_rs->MoveNext();
}
unset($mpcode_rs);

$after_name_rs = $dao->selectAfterInfo($conn,
                                       ["cate_sortcode" => $cate_sortcode],
                                       "DISTINCT after_name");
$conn->debug = 0;
$after_name_html = '';
while ($after_name_rs && !$after_name_rs->EOF) {
    $name = $after_name_rs->fields["after_name"];

    $after_name_html .= option($name, $name);

    $after_name_rs->MoveNext();
}

$data = [
     "paper_sort" => $paper_sort_html
    ,"prdt_output_info" => $prdt_output_info_html
    ,"prdt_print_info" => $prdt_print_info_html
    ,"after_name_html" => $after_name_html
];

$html = '';
foreach ($typ_arr as $typ => $dvs_num) {
    // 후공정 검색
    $paper_mpcode  = $paper_arr[$typ];
    $output_mpcode = $output_arr[$typ];
    $print_mpcode  = $print_arr[$typ];

    // 카테고리 종이 검색
    unset($param);
    $param["mpcode"] = $paper_mpcode;
    $cate_paper = $dao->selectCatePaper($conn, $param);

    // 종이이름 html 생성
    $paper_name_html = '';
    $paper_name_rs = $dao->selectPrdtPaper($conn, [], "name");
    while ($paper_name_rs && !$paper_name_rs->EOF) {
        $name = $paper_name_rs->fields["name"];

        $selected = '';
        if ($cate_paper["name"] === $name) {
            $selected = "selected=\"selected\"";
        }

        $paper_name_html .= option($name, $name, $selected);

        $paper_name_rs->MoveNext();
    }

    // 나머지 종이정보 html 생성
    unset($param);
    $paper_info_html = '';
    $param["sort"] = $cate_paper["sort"];
    $param["name"] = $cate_paper["name"];
    $paper_info_rs = $dao->selectPrdtPaper($conn, $param, "info");
    while ($paper_info_rs && !$paper_info_rs->EOF) {
        $fields = $paper_info_rs->fields;

        $info = sprintf("%s %s %s%s (%s계열)", $fields["dvs"]
                                             , $fields["color"]
                                             , $fields["basisweight"]
                                             , $fields["basisweight_unit"]
                                             , $fields["affil"]);

        $selected = '';
        if ($cate_paper["dvs"] === $fields["dvs"]
                && $cate_paper["color"] === $fields["color"]
                && $cate_paper["basisweight"] === $fields["basisweight"] . $fields["basisweight_unit"]) {
            $selected = "selected=\"selected\"";
        }

        $paper_info_html .= option($info, $fields["mpcode"], $selected);

        $paper_info_rs->MoveNext();
    }

    // 견적계산 종이/인쇄/출력 html 생성
    $data["typ"] = $typ;
    $data["paper_name"] = $paper_name_html;
    $data["paper_info"] = $paper_info_html;
    $html .= getDetailHtml($data);

    // 견적상세 후가공 html 생성
    unset($param);
    $param["esti_detail_dvs_num"] = $dvs_num;
    $after_rs = $dao->selectEstiAfterHistory($conn, $param);

    $data["detail_dvs_num"] = $dvs_num;
    $html .= getAfterHtml($after_rs, $data);
}

// 해당 견적 상태 견적중으로 변경, 견적담당자 입력
unset($param);
$param["state"]      = $session["state_arr"]["견적중"];
$param["esti_mng"]   = $session["name"];
$param["esti_seqno"] = $esti_seqno;
$ret = $dao->updateEstiState($conn, $param);

$success = "1";
if (!$ret) {
    $success = "-1";
}

$json = "{\"html\" : \"%s\", \"success\" : %s}";
echo sprintf($json, $util->convJsonStr($html), $success);

$conn->Close();

////////////////////////////////////////

function getDetailHtml($param) {
    $typ_arr = [
        "표지" => "cover"
        ,"내지1" => "inner1"
        ,"내지2" => "inner2"
        ,"내지3" => "inner3"
    ];

    $typ_en = $typ_arr[$param["typ"]];

    $html = <<<html
        <table class="table_esti_service_guide_detail">
            <colgroup>
                <col style="width:100px">
                <col style="width:100px">
                <col style="width:160px">
                <col style="width:140px">
                <col style="width:50px">
                <col style="width:50px">
                <col style="width:50px">
                <col style="width:135px">
                <col style="width:115px">
            </colgroup>
            <thead>
                <tr>
                    <th></th>
                    <th>구분</th>
                    <th>항목</th>
                    <th>단가</th>
                    <th>도수</th>
                    <th>대수</th>
                    <th>연수</th>
                    <th>금액</th>
                    <th>비고</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td rowspan="3">{$param["typ"]}</td>
                    <td>종이(지대)</td>
                    <td>
                        <select id="{$typ_en}_paper_sort" class="select_esti_list_detail_01" onchange="loadPaperInfo('{$typ_en}', 'name');">
                            {$param["paper_sort"]}
                        </select>
                        <select id="{$typ_en}_paper_name" class="select_esti_list_detail_01" onchange="loadPaperInfo('{$typ_en}', 'info');">
                            {$param["paper_name"]}
                        </select>
                        <select id="{$typ_en}_paper_info" class="select_esti_list_detail_01" onchange="loadUnitPrice('{$typ_en}', 'paper');">
                            {$param["paper_info"]}
                        </select>
                    </td>
                    <td>
                        <input type="text" value="0" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'paper');" id="{$typ_en}_paper_unit_price" class="input_esti_list_detail_01" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'paper');" id="{$typ_en}_paper_tmpt" class="input_esti_list_detail_02" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'paper');" id="{$typ_en}_paper_mach_count" class="input_esti_list_detail_03" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'paper');" id="{$typ_en}_paper_r_count" class="input_esti_list_detail_04" />
                    </td>
                    <td>
                        <input type="text" value="0" onkeyup="this.value = inputOnlyNumber(this.value);" id="{$typ_en}_paper_price" class="input_esti_list_detail_05" />
                    </td>
                    <td>
                        <input type="text" value="" id="{$typ_en}_paper_note" class="input_esti_list_detail_06" />
                    </td>
                </tr>
                <tr>
                    <td>출력비</td>
                    <td>
                        <select id="{$typ_en}_output_mpcode" class="select_esti_list_detail_01" onchange="loadUnitPrice('{$typ_en}', 'output');">
                            {$param["prdt_output_info"]}
                        </select>
                    </td>
                    <td>
                        <input type="text" value="0" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'output');" id="{$typ_en}_output_unit_price" class="input_esti_list_detail_01" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'output');" id="{$typ_en}_output_tmpt" class="input_esti_list_detail_02" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'output');" id="{$typ_en}_output_mach_count" class="input_esti_list_detail_03" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'output');" id="{$typ_en}_output_r_count" class="input_esti_list_detail_04" />
                    </td>
                    <td>
                        <input type="text" value="0" onkeyup="this.value = inputOnlyNumber(this.value);" id="{$typ_en}_output_price" class="input_esti_list_detail_05" />
                    </td>
                    <td>
                        <input type="text" value="" id="{$typ_en}_output_note" class="input_esti_list_detail_06" />
                    </td>
                </tr>
                <tr>
                    <td>인쇄비</td>
                    <td>
                        <select id="{$typ_en}_print_mpcode" class="select_esti_list_detail_01" onchange="loadUnitPrice('{$typ_en}', 'print');">
                            {$param["prdt_print_info"]}
                        </select>
                    </td>
                    <td>
                        <input type="text" value="0" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'print');" id="{$typ_en}_print_unit_price" class="input_esti_list_detail_01" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'print');" id="{$typ_en}_print_tmpt" class="input_esti_list_detail_02" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'print');" id="{$typ_en}_print_mach_count" class="input_esti_list_detail_03" />
                    </td>
                    <td>
                        <input type="text" value="1" onkeyup="this.value = inputOnlyNumber(this.value);" onblur="calcPrice('{$typ_en}', 'print');" id="{$typ_en}_print_r_count" class="input_esti_list_detail_04" />
                    </td>
                    <td>
                        <input type="text" value="0" onkeyup="this.value = inputOnlyNumber(this.value);" id="{$typ_en}_print_price" class="input_esti_list_detail_05" />
                    </td>
                    <td>
                        <input type="text" value="" id="{$typ_en}_print_note" class="input_esti_list_detail_06" />
                    </td>
                </tr>
            </tbody>
        </table>
html;

    return $html;
}

function getAfterHtml($rs, $param) {
    $typ_arr = [
        "표지" => "cover"
        ,"내지1" => "inner1"
        ,"내지2" => "inner2"
        ,"내지3" => "inner3"
    ];
    $after_arr = ProductInfoClass::AFTER_ARR;

    $typ_en = $typ_arr[$param["typ"]];

    $start = true;
    $tbody_html = '';
    $after_count = $rs->RecordCount();
    while ($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $seqno  = $fields["esti_after_history_seqno"];
        $mpcode = $fields["mpcode"];
        $name   = $fields["after_name"];
        $depth1 = $fields["depth1"];
        $depth2 = $fields["depth2"];
        $depth3 = $fields["depth3"];

        $after_en = $after_arr[$name];

        $temp = "<tr id=\"aft_" . $seqno . "\">";

        if ($start) {
            $start = false;
            $temp .=     "<td rowspan=\"" . $after_count . "\">" . $param["typ"] . "<br>후가공</td>";
        }

        $temp .=     "<td>";
        $temp .=         "<span class=\"" . $typ_en . "_after\" aft_en=\"" . $after_en . "\" seqno=\"" . $seqno . "\" mpcode=\"" . $mpcode. "\">%s</span>"; //#1 after_name
        $temp .=         "<span class=\"span_small_btn_wrapper\">";
        $temp .=         "<button type=\"button\" id=\"\" class=\"btn_small_minus_esti_list\" onclick=\"deleteAfterHistory('" . $seqno . "');\"></button>";
        $temp .=         "</span>";
        $temp .=     "</td>";
        $temp .=     "<td>";
        $temp .=         "%s %s %s"; //#1 after_depth1, 2, 3
        $temp .=     "</td>";
        $temp .=     "<td>";
        $temp .=         "<input type=\"text\" value=\"0\" onkeyup=\"this.value = inputOnlyNumber(this.value);\" onblur=\"calcPrice('" . $typ_en . "', '" . $after_en . "');\" id=\"" . $typ_en . '_' . $after_en . "_unit_price\" class=\"input_esti_list_detail_01\" />";
        $temp .=     "</td>";
        $temp .=     "<td>";
        $temp .=         "<input type=\"text\" value=\"1\" onkeyup=\"this.value = inputOnlyNumber(this.value);\" onblur=\"calcPrice('" . $typ_en . "', '" . $after_en . "');\" id=\"" . $typ_en . '_' . $after_en . "_tmpt\" class=\"input_esti_list_detail_02\" />";
        $temp .=     "</td>";
        $temp .=     "<td>";
        $temp .=         "<input type=\"text\" value=\"1\" onkeyup=\"this.value = inputOnlyNumber(this.value);\" onblur=\"calcPrice('" . $typ_en . "', '" . $after_en . "');\" id=\"" . $typ_en . '_' . $after_en . "_mach_count\" class=\"input_esti_list_detail_03\" />";
        $temp .=     "</td>";
        $temp .=     "<td>";
        $temp .=         "<input type=\"text\" value=\"1\" onkeyup=\"this.value = inputOnlyNumber(this.value);\" onblur=\"calcPrice('" . $typ_en . "', '" . $after_en . "');\" id=\"" . $typ_en . '_' . $after_en . "_r_count\" class=\"input_esti_list_detail_04\" />";
        $temp .=     "</td>";
        $temp .=     "<td>";
        $temp .=         "<input type=\"text\" value=\"0\" onkeyup=\"this.value = inputOnlyNumber(this.value);\" id=\"" . $typ_en . '_' . $after_en . "_price\" class=\"input_esti_list_detail_05\" />";
        $temp .=     "</td>";
        $temp .=     "<td>";
        $temp .=         "<input type=\"text\" value=\"\" id=\"" . $typ_en . '_' . $after_en . "_note\" class=\"input_esti_list_detail_06\" />";
        $temp .=     "</td>";
        $temp .= "</tr>";

        $tbody_html .= sprintF($temp, $name
                                    , $depth1, $depth2, $depth3);

        $rs->MoveNext();
    }

    $empty_html = getEmptyAfterTbodyHtml($typ_en,
                                         $param["detail_dvs_num"],
                                         $param["after_name_html"]);

    $ret = <<<html
        <table class="table_esti_service_guide_detail">
            <colgroup>
                <col style="width:100px">
                <col style="width:100px">
                <col style="width:160px">
                <col style="width:140px">
                <col style="width:50px">
                <col style="width:50px">
                <col style="width:50px">
                <col style="width:135px">
                <col style="width:115px">
            </colgroup>
            <thead>
                <tr>
                    <th></th>
                    <th>구분</th>
                    <th>항목</th>
                    <th>단가</th>
                    <th>도수</th>
                    <th>대수</th>
                    <th>연수</th>
                    <th>금액</th>
                    <th>비고</th>
                </tr>
            </thead>
            <tbody id="{$typ_en}_aft_tbody">
                {$tbody_html}
            </tbody>
            <tbody>
                {$empty_html}
            </tbody>
        </table>
html;

    return $ret;
}

function getEmptyAfterTbodyHtml($typ_en, $dvs_num, $name_html) {
    $html .= "<tr>";
    $html .=     "<td>후가공추가</td>";
    $html .=     "<td>";
    $html .=         "<select id=\"" . $typ_en . "_after_name\" class=\"select_esti_list_detail_01\" onchange=\"loadAfterDepth('" . $typ_en . "', 'depth1');\">";
    $html .=             "<option value=\"\">-</option>";
    $html .=             $name_html;
    $html .=         "</select>";
    $html .=         "<span class=\"span_small_btn_wrapper\">";
    $html .=         "<button type=\"button\" id=\"\" class=\"btn_small_plus_esti_list\" onclick=\"addAfterHistory('" . $typ_en . "', '" . $dvs_num. "');\"></button>";
    $html .=         "</span>";
    $html .=     "</td>";
    $html .=     "<td>";
    $html .=         "<select id=\"" . $typ_en . "_after_depth1\" class=\"select_esti_list_detail_01\" onchange=\"loadAfterDepth('" . $typ_en . "', 'depth2');\">";
    $html .=             "<option value=\"\">-</option>";
    $html .=         "</select>";
    $html .=         "<select id=\"" . $typ_en . "_after_depth2\" class=\"select_esti_list_detail_01\" onchange=\"loadAfterDepth('" . $typ_en . "', 'depth3');\">";
    $html .=             "<option value=\"\">-</option>";
    $html .=         "</select>";
    $html .=         "<select id=\"" . $typ_en . "_after_depth3\" class=\"select_esti_list_detail_01\">";
    $html .=             "<option value=\"\">-</option>";
    $html .=         "</select>";
    $html .=     "</td>";
    $html .=     "<td colspan=\"6\">";
    $html .=     "</td>";
    $html .= "</tr>";

    return $html;
}
