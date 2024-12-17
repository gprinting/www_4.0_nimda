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

if ($fb->form($el . "_produce_yn") === "N") {

    $param = array();
    $param["table"] = "basic_produce_" . $el;
    $param["prk"] = "typset_format_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $dao->deleteData($conn, $param);

    if (!$rs) {
        $check = 0;
    }

    $conn->CompleteTrans();
    $conn->Close();
    echo $check;
    exit;
} 

$param = array();
$param["table"] = "basic_produce_after";
$param["col"]["typset_format_seqno"] = $fb->form("seqno");
$param["col"]["extnl_etprs_seqno"] = $fb->form("extnl_etprs_seqno");
$param["col"]["after_seqno"] = $fb->form("after_seqno");

$rs = $dao->insertData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "typset_format";
$param["col"]["process_yn"] = "Y";
$param["prk"] = "typset_format_seqno";
$param["prkVal"] = $fb->form("seqno");

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
