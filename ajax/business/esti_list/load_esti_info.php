<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/esti_mng/EstiListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/business/esti_mng/EstiRegiInfo.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new EstiListDAO();
$commonDAO = $dao;

$param = array();
$param["esti_seqno"] = $fb->form("seqno");
$param["dvs"] = "search"; //search는 아무의미 없음

$rs = $dao->selectEstiListCond($conn, $param);

$param = array();
$param["table"] = "esti_file";
$param["col"] = "origin_file_name, esti_file_seqno";
$param["where"]["esti_seqno"] = $fb->form("seqno");
$file_rs = $dao->selectData($conn, $param);

$file_html = "";
if (!$file_rs->EOF == 1) {
    $i = 0;
    while ($file_rs && !$file_rs->EOF) {

        if ($i == 0) {
            $file_html .= "<label class=\"control-label cp\">";
            $file_html .= "<a href=\"/common/esti_file_down.inc?seqno=";
            $file_html .= $fb->form("seqno") . "\">";
            $file_html .= $file_rs->fields["origin_file_name"] . "</a></label>";
        } else {
            $file_html .= "<br /><label class=\"fix_width393\"></label>";
            $file_html .= "<label class=\"control-label cp\">";
            $file_html .= "<a href=\"/common/esti_file_down.inc?seqno=";
            $file_html .= $fb->form("seqno") . "\">";
            $file_html .= $file_rs->fields["origin_file_name"] . "</a></label>";
        }

        $i++;
        $file_rs->moveNext();
    }
} else {
    $file_html .= "<label class=\"control-label cp\">";
    $file_html .= "첨부파일 없음";
    $file_html .= "</label>";
}

$param = array();
$param["table"] = "admin_esti_file";
$param["col"] = "origin_file_name, admin_esti_file_seqno";
$param["where"]["esti_seqno"] = $fb->form("seqno");
$admin_file_rs = $dao->selectData($conn, $param);

if ($admin_file_rs->EOF == 1) {
    $admin_file_html = "";
} else {
    $admin_file_html .= "<br /><label class=\"fix_width140\"></label>";	
    $admin_file_html .= "\n<input type=\"text\" class=\"input_dis_co2 fix_width201\" value=\"" . $admin_file_rs->fields["origin_file_name"] . "\" disabled>";     
    $admin_file_html .= "\n<button type=\"button\" class=\"btn btn-sm bred fa fa-times\" onclick=\"delAdminEstiFile.exec('" . $fb->form("seqno") . "');\"></button>";
}

$param = array();
$param["table"] = "prdt_paper";
$param["col"] = "DISTINCT name";

$paper_rs = $dao->selectData($conn, $param);
$paper_option_html = makeOptionHtml($paper_rs, "name", "name", "종이명(전체)");

$param = array();
$param["table"] = "prdt_output_info";
$param["col"] = "DISTINCT output_name";

$output_rs = $dao->selectData($conn, $param);
$output_option_html = makeOptionHtml($output_rs, "output_name", "output_name", "출력명(전체)");

$print_rs = $dao->selectCate($conn);
$print_option_html = makeOptionHtml($print_rs, "cate_sortcode", "cate_name", "카테고리중분류(전체)");

$param = array();
$param["table"] = "prdt_after";
$param["col"] = "DISTINCT after_name";

$after_rs = $dao->selectData($conn, $param);
$after_option_html = makeOptionHtml($after_rs, "after_name", "after_name", "후공정명(전체)");

$paper = "";
$size = "";
$print_tmpt = "";
$after = "";
$etc = "";

if ($rs->fields["paper"]) {
    $paper = getOrderHtml("주문종이", $rs->fields["paper"]);
}

if ($rs->fields["size"]) {
    $size = getOrderHtml("주문사이즈", $rs->fields["size"]);
}

if ($rs->fields["print_tmpt"]) {
    $print_tmpt = getOrderHtml("주문도수", $rs->fields["print_tmpt"]);
}

if ($rs->fields["after"]) {
    $after = getOrderHtml("주문후공정", $rs->fields["after"]);
}

$state = "";
if ($rs->fields["state"] == "견적완료") {
    $state = "견적수정";
} else if ($rs->fields["state"] == "견적대기") {
    $state = "견적중";
} else if ($rs->fields["state"] == "견적수정") {
    $state = "견적수정";
} else if ($rs->fields["state"] == "견적중") {
    $state = "견적중";
}

$param = array();
$param["state"] = $state;
$param["esti_seqno"] = $fb->form("seqno");
$dao->updateEstiState($conn, $param);

$expec_order_date = NULL;
if ($rs->fields["expec_order_date"]) {
    $expec_order_date = $rs->fields["expec_order_date"];
}

$param = array();
$param["title"] = $rs->fields["title"];
$param["member_name"] = $rs->fields["member_name"];
$param["office_nick"] = $rs->fields["office_nick"];
$param["amt"] = $rs->fields["amt"];
$param["count"] = $rs->fields["count"];
$param["inq_cont"] = $rs->fields["inq_cont"];
$param["paper"] = $paper;
$param["size"] = $size;
$param["print_tmpt"] = $print_tmpt;
$param["after"] = $after;
$param["etc"] = $rs->fields["etc"];
$param["memo"] = $rs->fields["memo"];
$param["expec_order_date"] = $expec_order_date;
$param["supply_price"] = number_format($rs->fields["supply_price"]);
$param["vat"] = number_format($rs->fields["vat"]);
$param["sale_price"] = number_format($rs->fields["sale_price"]);
$param["esti_price"] = number_format($rs->fields["esti_price"]);
$param["answ_cont"] = $rs->fields["answ_cont"];
$param["file_html"] = $file_html;
$param["paper_option_html"] = $paper_option_html;
$param["output_option_html"] = $output_option_html;
$param["print_option_html"] = $print_option_html;
$param["after_option_html"] = $after_option_html;
$param["esti_seqno"] = $fb->form("seqno");
$param["admin_file_html"] = $admin_file_html;
echo makeEstiRegiContHtml($param);
$conn->Close();

/*******************************************************************************
                                 함수 영역
 ******************************************************************************/
/**
 * @brief 페이지 반환
 *
 * @param $title      = 제목
 * @param $cont       = 내용
 *
 * @return 상태진행 코드값 배열
 */
function getOrderHtml($title, $cont) {

    $html = <<<HTML
					<div class="form-group">
						<label class="control-label fix_width120 tar">$title</label><label class="fix_width20 fs14 tac">:</label>
						<label class="control-label cp">$cont</label>
						<br />
					</div>

HTML;

    return $html;
}
?>
