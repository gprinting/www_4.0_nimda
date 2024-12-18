<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$MoamoaDAO = new MoamoaDAO();
$OutputDAO = new OutputListDAO();
$util = new CommonUtil();


$param = array();
$param['state'] = $fb->form("state");
$param['empl_id'] = $fb->getSession()["id"];
$param['ordernum'] = $fb->form("order_num");

$MoamoaDAO->updateProductStatecode($conn, $param);
$MoamoaDAO->insertStateHistory($conn, $param);

echo "1";
$conn->Close();
?>
