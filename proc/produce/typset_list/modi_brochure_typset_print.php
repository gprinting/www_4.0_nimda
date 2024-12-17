<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/produce/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$conn->StartTrans();

$param = array();
$param["table"] = "print_op";
$param["col"]["name"] = $fb->form("name");
$param["col"]["affil"] = $fb->form("affil");
$param["col"]["size"] = $fb->form("size");
$param["col"]["beforeside_tmpt"] = $fb->form("beforeside_tmpt");
$param["col"]["beforeside_spc_tmpt"] = $fb->form("beforeside_spc_tmpt");
$param["col"]["aftside_tmpt"] = $fb->form("aftside_tmpt");
$param["col"]["aftside_spc_tmpt"] = $fb->form("aftside_spc_tmpt");
$param["col"]["tot_tmpt"] = $fb->form("tot_tmpt");
$param["col"]["amt"] = $fb->form("amt");
$param["col"]["amt_unit"] = $fb->form("amt_unit");
$param["col"]["memo"] = $fb->form("memo");
$param["col"]["extnl_brand_seqno"] = $fb->form("brand_seqno");
$param["col"]["typ"] = $fb->form("typ");
$param["col"]["typ_detail"] = $fb->form("typ_detail");
$param["col"]["orderer"] = $fb->session("name");
$param["col"]["flattyp_dvs"] = "N";
$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["prk"] = "typset_num";
$param["prkVal"] = $fb->form("typset_num");

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "produce_process_flow";
$param["col"]["print_save_yn"] = "Y";
$param["prk"] = "typset_num";
$param["prkVal"] = $fb->form("typset_num");
 
$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
