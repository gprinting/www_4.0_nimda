<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/common/BasicMngCommonDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$basicDAO = new BasicMngCommonDAO();
$conn->StartTrans();

$check = 1;

$seqno_set = explode(",", $fb->form("select_prdt"));

for ($i = 0; $i < count($seqno_set); $i++) {

    $param = array();
    $param["table"] = "opt";
    $param["prk"] = "opt_seqno";
    $param["prkVal"] = $seqno_set[$i];
    $result = $basicDAO->deleteData($conn, $param);

    if (!$result) $check = 0;
   
}

if ($check == 1) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>

