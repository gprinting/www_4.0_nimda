<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/produce_plan/PrintProducePlanDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrintProducePlanDAO();

//제조사
$param = array();
$param["table"] = "extnl_etprs";
$param["col"] = "extnl_etprs_seqno ,manu_name";
$param["where"]["pur_prdt"] = "인쇄";

$rs = $dao->selectData($conn, $param);

$option_html = "<option value=\"%s\">%s</option>";
//$manu_html = "<option value=\"\">인쇄 제조사(전체)</option>";
$manu_html = "";
while ($rs && !$rs->EOF) {

    $manu_html .= sprintf($option_html
            , $rs->fields["extnl_etprs_seqno"]
            , $rs->fields["manu_name"]);

    $rs->moveNext();
}

$html = "";
$html .= "<tr>";
$html .= "   <td>";
$html .= "       <select class=\"fix_width150\" name=\"selManu[]\">" . $manu_html . "</select>";
$html .= "   </td>";
$html .= "   <td>";
$html .= "       <input type=\"number\" class=\"input_co2\" name=\"seoul[]\" min=\"0\" value=\"0\">";
$html .= "   </td>";
$html .= "   <td>";
$html .= "       <input type=\"number\" class=\"input_co2\" name=\"region[]\" min=\"0\" value=\"0\">";
$html .= "   </td>";
$html .= "   <td>";
$html .= "       <button type=\"button\" class=\"btn btn-sm btn-info\" onclick=\"plusLine(this); return false;\"><i class=\"fa fa-plus\"></i></button>";
$html .= "       <button type=\"button\" class=\"btn btn-sm btn-warning\" style=\"display:none;\" onclick=\"minusLine(this); return false;\"><i class=\"fa fa-minus\"></i></button>";
$html .= "   </td>";
$html .= "</tr>";

echo $html;
$conn->close();
?>
