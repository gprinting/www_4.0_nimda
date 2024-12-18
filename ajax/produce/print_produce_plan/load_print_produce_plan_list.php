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

$date = $fb->form("date");

$param = array();
$param["date"] = $date;

$rs = $dao->selectPrintProductPlanList($conn, $param);
$list = "";
while ($rs && !$rs->EOF) {

    $html = "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>";
    $html = sprintf($html, $rs->fields["manu_name"]
                     , $rs->fields["seoul_directions"]
                     , $rs->fields["seoul_exec"]
                     , $rs->fields["region_directions"]
                     , $rs->fields["region_exec"]
                     , $rs->fields["tot_directions"]
                     , $rs->fields["tot_exec"]);
    $list .= $html;
    $rs->moveNext();
}

echo $list;
$conn->close();
?>
