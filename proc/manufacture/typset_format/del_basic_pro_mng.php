<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetFormatDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetFormatDAO();
$check = 1;

$el = $fb->form("el");
$conn->StartTrans();

if ($fb->form("seqno")) {

    $param = array();
    $param["table"] = "basic_produce_" . $el;
    $param["prk"] = $el . "_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
