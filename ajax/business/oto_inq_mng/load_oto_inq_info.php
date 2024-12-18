<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/oto_inq_mng/OtoInqMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/business/oto_inq_mng/OtoInquireInfo.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OtoInqMngDAO();

$seqno = $fb->form("seqno");

$param = array();
$param["oto_inq_seqno"] = $seqno;

$inq_rs = $dao->selectOtoInqCont($conn, $param);
$reply_rs = $dao->selectOtoReplyCont($conn, $param);

$param = array();
$param["table"] = "oto_inq_file";
$param["col"] = "origin_file_name, oto_inq_file_seqno";
$param["where"]["oto_inq_seqno"] = $seqno;

$inq_file_rs = $dao->selectData($conn, $param);

$inq_file_html = "";
if (!$inq_file_rs->EOF == 1) {
    $i = 0;
    while ($inq_file_rs && !$inq_file_rs->EOF) {

        if ($i == 0) {
            $inq_file_html .= "<label class=\"control-label cp\">";
            $inq_file_html .= "<a href=\"/common/esti_file_down.inc?seqno=";
            $inq_file_html .= $inq_file_rs->fields["esti_file_seqno"] . "\">";
            $inq_file_html .= $inq_file_rs->fields["origin_file_name"] . "</a></label>";
        } else {
            $inq_file_html .= "<br /><label class=\"fix_width174\"></label>";
            $inq_file_html .= "<label class=\"control-label cp\">";
            $inq_file_html .= "<a href=\"/common/esti_file_down.inc?seqno=";
            $inq_file_html .= $inq_file_rs->fields["esti_file_seqno"] . "\">";
            $inq_file_html .= $inq_file_rs->fields["origin_file_name"] . "</a></label>";
        }

        $i++;
        $inq_file_rs->moveNext();
    }
} else {
    $inq_file_html .= "<label class=\"control-label cp\">";
    $inq_file_html .= "첨부파일 없음";
    $inq_file_html .= "</label>";
}

$param = array();
$param["table"] = "oto_inq_reply_file";
$param["col"] = "origin_file_name, oto_inq_reply_file_seqno";
$param["where"]["oto_inq_reply_seqno"] = $reply_rs->fields["oto_inq_reply_seqno"];
$reply_file_rs = $dao->selectData($conn, $param);

/*
if ($reply_file_rs->EOF == 1) {
    $reply_file_html = "";
} else {
    $reply_file_html .= "<br /><label class=\"fix_width140\"></label>";	
    $reply_file_html .= "\n<input type=\"text\" class=\"input_dis_co2 fix_width201\" value=\"" . $reply_file_rs->fields["origin_file_name"] . "\" disabled>";     
    $reply_file_html .= "\n<button type=\"button\" class=\"btn btn-sm bred fa fa-times\" onclick=\"delReplyFile.exec('" . $reply_file_rs->fields["oto_inq_reply_file_seqno"] . "');\"></button>";
}
*/

//로그인 개발 후 추가 개발 필요
$param = array();
$param["title"] = $inq_rs->fields["title"];
$param["inq_typ"] = $inq_rs->fields["inq_typ"];
$param["member_name"] = $inq_rs->fields["member_name"];
$param["office_nick"] = $inq_rs->fields["office_nick"];
$param["tel_num"] = $inq_rs->fields["tel_num"];
$param["cell_num"] = $inq_rs->fields["cell_num"];
$param["mail"] = $inq_rs->fields["mail"];
$param["inq_cont"] = $inq_rs->fields["cont"];
$param["inq_file_html"] = $inq_file_html;
$param["reply_cont"] = $reply_rs->fields["cont"];
//$param["reply_file_html"] = $reply_file_html;
$param["reply_file"] = $reply_file_rs->fields["origin_file_name"];
$param["oto_inq_seqno"] = $seqno;

if ($inq_rs->fields["answ_yn"] == "Y") {
    $param["name"] = $reply_rs->fields["depar_name"] . " " . $reply_rs->fields["name"];
} else {
    $param["name"] = $fb->session("name");
}

echo makeInquireContHtml($param) . "♪" . $inq_rs->fields["answ_yn"];
$conn->close();
?>
