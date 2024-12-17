<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$seqno = $fb->form("seqno");
$typset_format_seqno = $fb->form("typset_format_seqno");

$conn->StartTrans();

$param = array();
$param["table"] = "sheet_typset";
$param["col"]["aftside_tmpt"] = $fb->form("aftside_tmpt");
$param["col"]["aftside_spc_tmpt"] = $fb->form("aftside_spc_tmpt");
$param["col"]["beforeside_tmpt"] = $fb->form("beforeside_tmpt");
$param["col"]["beforeside_spc_tmpt"] = $fb->form("beforeside_spc_tmpt");
$param["col"]["print_amt"] = $fb->form("print_amt");
$param["col"]["print_amt_unit"] = $fb->form("print_amt_unit");
$param["col"]["dlvrboard"] = $fb->form("dlvrboard");
$param["col"]["memo"] = $fb->form("memo");
$param["col"]["op_typ"] = $fb->form("op_typ");
$param["col"]["op_typ_detail"] = $fb->form("op_typ_detail");
$param["col"]["typset_format_seqno"] = $typset_format_seqno;
$param["col"]["honggak_yn"] = $fb->form("honggak_yn");
$param["col"]["op_date"] = date("Y-m-d H:i:s");
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $seqno;

//echo $conn->debug = 1; exit;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
