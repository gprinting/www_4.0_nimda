<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/item_mng/PaperOpMngDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperOpMngDAO();

$basisweight_tmp = $fb->form("basisweight");

preg_match('/(.+)(.)/', $basisweight_tmp, $match); 

$basisweight = $match[1];
$basisweight_unit = $match[2];

//종이 정보리스트
$param = array();
$param["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["sorting"] = $fb->form("sorting");
$param["sorting_type"] = $fb->form("sorting_type");
$param["name"] = $fb->form("name");
$param["dvs"] = $fb->form("dvs");
$param["color"] = $fb->form("color");
$param["basisweight"] = $basisweight;
$param["basisweight_unit"] = $basisweight_unit;

$rs = $dao->selectPaperInfoList($conn, $param);

$list = makePaperInfoListHtml($rs, $param);

echo $list;
$conn->close();
?>
