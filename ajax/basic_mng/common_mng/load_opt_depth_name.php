<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/BasicMngCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$basicDAO = new BasicMngCommonDAO();

//검색 조건에 들어갈 이름
$name = $fb->form("name");
//load 하려는 depth
$depth = $fb->form("depth");

$param = array();
$param["name"] = $name;
$param["depth"] = $depth;

$result = $basicDAO->selectOptPrdcDepthName($conn, $param);

$arr = [];
$arr["flag"] = "Y";
$arr["def"] = $depth . "(전체)";
$arr["dvs"] = $depth;
$arr["val"] = $depth;

$buff = makeSelectOptionHtml($result, $arr);

echo $buff;
$conn->close();
?>
