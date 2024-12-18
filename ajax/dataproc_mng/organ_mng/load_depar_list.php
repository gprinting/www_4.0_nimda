<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/organ_mng/OrganMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$organDAO = new OrganMngDAO();

$param = array();
//부서 리스트 생성
$result = $organDAO->selectDeparAdminList($conn, $param);
$depar_list = makeDeparList($result);

if ($depar_list == "") {

    "<tr><td colspan='4'>\"검색된 결과가 없습니다.\"</td></tr>";
}
echo $depar_list;

$conn->close();
?>
