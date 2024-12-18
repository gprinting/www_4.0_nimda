<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/produce_plan/PrintProducePlanDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintProducePlanDAO();

$param = array();
if ($fb->form("date")) {

    $param["date"] = $fb->form("date");

} else {

    $from_date = $fb->form("date_from");
    $from_time = "";
    $to_date = $fb->form("date_to");
    $to_time = "";
    
    if ($from_date) {
        $from_time = $fb->form("time_from");
        $from = $from_date . " " . $from_time;
    }
    
    if ($to_date) {
        $to_time = " " . $fb->form("time_to")+1;
        $to =  $to_date . " " . $to_time;
    }
    
    $param["search_cnd"] = $fb->form("search_cnd");
    $param["from"] = $from;
    $param["to"] = $to;
    $param["extnl_etprs_seqno"] = $fb->form("extnl_etprs");
}

$rs = $dao->selectPrintProductPlanList($conn, $param);
$list = "";
$i = 0;
while ($rs && !$rs->EOF) {

    if ($i%2)
        $class = "cellbg";
    else
        $class = "";

    $html = "<tr class=\"%s\"><td>%s</td><td>%s</td><td>%s</td><td><button type=\"button\" class=\"green btn_pu btn fix_height20 fix_width40\" onclick=\"openPlanResultPop(%s); return false;\">보기</button></td></tr>";
   
    $expec_perform = "";

    if ($rs->fields["expec_perform_mark"])
        $expec_perform .= "댓수 : " . number_format($rs->fields["expec_perform_mark"]) . "대<br/>";

    if ($rs->fields["expec_perform_paper"])
        $expec_perform .= "종이 : " . number_format($rs->fields["expec_perform_paper"]) . "R<br/>";

    if ($rs->fields["expec_perform_bucket"])
        $expec_perform .= "통수 : " . number_format($rs->fields["expec_perform_bucket"]) . "통";

    $html = sprintf($html, $class
                     , $rs->fields["manu_name"]
                     , $rs->fields["tot_directions"]
                     , $expec_perform
                     , $rs->fields["print_op_seqno"]);
    $list .= $html;
    $rs->moveNext();
    $i++;
}

echo $list;
$conn->close();
?>
