<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtBasicRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$prdtBasicRegiDAO = new PrdtBasicRegiDAO();

$param = array();
$param["name"] = $fb->form("output_name");

//사이즈 출력판구분
$output_board_dvs_rs = $prdtBasicRegiDAO->selectOutputInfo($conn, "BOARD", $param);
$output_board_dvs_html = makeOptionHtml($output_board_dvs_rs, "", "output_board_dvs", "", "N");

echo $output_board_dvs_html;
$conn->close();
?>
