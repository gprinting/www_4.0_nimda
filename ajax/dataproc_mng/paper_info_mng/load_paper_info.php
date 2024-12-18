<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/PaperInfoMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperInfoMngDAO();

$fb = $fb->getForm();

$dvs = $fb["dvs"];
$paper_name = $fb["paper_name"];
$paper_dvs  = $fb["paper_dvs"];

$param = array();
$param["name"] = $paper_name;
$param["dvs"]  = $paper_dvs;

$html = $dao->selectPrdtPaperInfo($conn, $dvs, $param);

echo $html;
?>
