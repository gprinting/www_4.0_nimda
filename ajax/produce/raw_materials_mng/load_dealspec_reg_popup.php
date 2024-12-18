<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/raw_materials_mng/RawMaterialsMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/produce/raw_materials_mng/RawMaterialsMngDOC.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/pageLib.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new RawMaterialsMngDAO();

//기타제조사
$rs = $dao->selectPurPrdtEtc($conn);

$opt = "";
while ($rs && !$rs->EOF) {
    $opt .= "<option value=\"" . $rs->fields["extnl_etprs_seqno"] . "\">" . $rs->fields["manu_name"] . "</option>";
    $rs->moveNext();
}
$param["opt"] = $opt;

$param["vat_y"] = "checked";
$param["save"] = "<button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"regiRawMaterials();\">저장</button>";
$list = makeDealspecPop($param);

echo $list;
$conn->close();
?>
