<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();

$paper_op_seqno = $fb->form("paper_op_seqno");
$paper_op_seqnos = explode("&", $paper_op_seqno);

$html  = "\n<br />";
$html .= "\n<label class=\"control-label cp\">발주번호 : %s</label>";
$html .= "\n<br />";
$html .= "\n<label class=\"control-label cp\">수주처 : %s</label>";
$html .= "\n<br />";
$html .= "\n<label class=\"control-label cp\">종이명 : %s</label>";
$html .= "\n<br />";
$html .= "\n<label class=\"control-label cp\">계열 : %s</label>";
$html .= "\n<br />";
$html .= "\n<label class=\"control-label cp\">수량 : %s %s</label>";
$html .= "\n<br />";
$html .= "\n<label class=\"control-label cp\">입고사이즈 : %s</label>";
$html .= "\n<br />";

$info_html  = "<label class=\"control-label tar\">○ 종이 발주서</label>";
for ($i=0;$i<count($paper_op_seqnos);$i++) {
    $tmp = explode("=", $paper_op_seqnos[$i]);
  
    $param = array();
    $param["paper_op_seqno"] = $tmp[1];

    $rs = $dao->selectPaperDirectionsView($conn, $param);
 
    $info_html .= sprintf($html, $rs->fields["paper_op_seqno"]
                              , $rs->fields["manu_name"]
                              , $rs->fields["name"]
                              , $rs->fields["op_affil"]
                              , $rs->fields["amt"]
                              , $rs->fields["amt_unit"]
                              , $rs->fields["stor_size"]);    
}

echo makePaperOpPrintHtml($info_html);
$conn->close();
?>
