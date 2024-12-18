<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OutputListDAO();
$util = new CommonUtil();
$fileDAO = new FileAttachDAO();

if (!$fb->form("seqno")) {
    echo "<script>alert('잘못된 접근입니다.');</script>";
    exit;
}

$seqno = $fb->form("seqno");

//상세보기 출력 발주
$param = array();
$param["seqno"] = $seqno;

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "sheet_typset_seqno, typset_num, print_amt, paper_name, print_etprs, size";
$param["where"]["sheet_typset_seqno"] = $seqno;

$sel_rs = $dao->selectData($conn, $param);
$html_param["sheet_typset_seqno"] = $sel_rs->fields["sheet_typset_seqno"];
$html_param["typset_num"] = $sel_rs->fields["typset_num"];
$html_param["print_amt"] = $sel_rs->fields["print_amt"];

$yeon = ceil($sel_rs->fields["print_amt"] / 500 * 20) / 20;

$html_param["yeon"] = $yeon;
$html_param["paper_name"] = $sel_rs->fields["paper_name"];
$html_param["print_etprs"] = $sel_rs->fields["print_etprs"];
$html_param["size"] = $sel_rs->fields["size"];

//제조사 종이
$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "extnl_etprs_seqno ,etprs_name";
$param["where"]["tob"] = "제지사";

$rs = $dao->selectData($conn, $param);
$paper_manu_html = "<option value=\"\">종이제조사(전체)</option>";
$option_html = "<option value=\"%s\" %s>%s</option>";
while ($rs && !$rs->EOF) {
    $selected = "";
    if($rs->fields["extnl_etprs_seqno"] == 108)
        $selected = "selected";

    $paper_manu_html .= sprintf($option_html
        , $rs->fields["extnl_etprs_seqno"]
        , $selected
        , $rs->fields["etprs_name"]);

    $rs->moveNext();
}
$html_param["paper_manu_html"] = $paper_manu_html;
echo getPaperOpDetailPopup($html_param);
$conn->close();
?>
