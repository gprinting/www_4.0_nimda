<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/produce_plan/PrintProducePlanDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$dao = new PrintProducePlanDAO();

$date = $fb->form("date");

//추후 세션에서 담당 제조사 값 받아와서 해당 리스트만 수정할 수 있도록
$param = array();
$param["date"] = $date;

$rs = $dao->selectPrintProductPlanList($conn, $param);
$list = "";
while ($rs && !$rs->EOF) {
    $html = "";
    $html .= "<tr>";
    $html .= "  <td>";
    $html .= "%s";
    $html .= "  </td>";
    $html .= "  <td>";
    $html .= "%s";
    $html .= "  </td>";
    $html .= "  <td>";
    $html .= "%s";
    $html .= "  </td>";
    $html .= "  <td>";
    $html .= "      <button type=\"button\" onclick=\"plusSeoulExec('%s', '%s', '%s'); return false;\"class=\"btn btn-sm btn-info\"><i class=\"fa fa-plus\"></i></button>";
    $html .= "      <button type=\"button\" onclick=\"minusSeoulExec('%s', '%s', '%s'); return false;\" class=\"btn btn-sm btn-info\"><i class=\"fa fa-minus\"></i></button>";
    $html .= "  </td>";
    $html .= "  <td>";
    $html .= "%s";
    $html .= "  </td>";
    $html .= "  <td>";
    $html .= "%s";
    $html .= "  </td>";
    $html .= "  <td>";
    $html .= "      <button type=\"button\" onclick=\"plusRegionExec('%s', '%s', '%s'); return false;\"class=\"btn btn-sm btn-info\"><i class=\"fa fa-plus\"></i></button>";
    $html .= "      <button type=\"button\" onclick=\"minusRegionExec('%s', '%s', '%s'); return false;\" class=\"btn btn-sm btn-info\"><i class=\"fa fa-minus\"></i></button>";
    $html .= "  </td>";
    $html .= "</tr>";

    $html = sprintf($html, 
                       $rs->fields["manu_name"]
                     , $rs->fields["seoul_directions"]
                     , $rs->fields["seoul_exec"]
                     , $rs->fields["print_produce_sch_seqno"]
                     , $rs->fields["seoul_directions"]
                     , $rs->fields["seoul_exec"]
                     , $rs->fields["print_produce_sch_seqno"]
                     , $rs->fields["seoul_directions"]
                     , $rs->fields["seoul_exec"]
                     , $rs->fields["region_directions"]
                     , $rs->fields["region_exec"]
                     , $rs->fields["print_produce_sch_seqno"]
                     , $rs->fields["region_directions"]
                     , $rs->fields["region_exec"]
                     , $rs->fields["print_produce_sch_seqno"]
                     , $rs->fields["region_directions"]
                     , $rs->fields["region_exec"]);
    $list .= $html;
    $rs->moveNext();
}

echo $list;
$conn->close();
?>
