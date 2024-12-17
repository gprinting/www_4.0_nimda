<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$check = 1;

$typset_num = $fb->form("typset_num");
$beforeside_tmpt = $fb->form("beforeside_tmpt");
$beforeside_spc_tmpt = $fb->form("beforeside_spc_tmpt");
$aftside_tmpt = $fb->form("aftside_tmpt");
$aftside_spc_tmpt = $fb->form("aftside_spc_tmpt");
$tot_tmpt = $beforeside_tmpt + $beforeside_spc_tmpt + $aftside_tmpt + $aftside_spc_tmpt;

$affil = $fb->form("affil");
$subpaper = $fb->form("subpaper");
$wid_size = $fb->form("wid_size");
$vert_size = $fb->form("vert_size");

$conn->StartTrans();

$param = array();
$param["table"] = "sheet_typset";

$param["col"]["regi_date"] = date("Y-m-d H:i:s");
$param["col"]["beforeside_tmpt"] = $beforeside_tmpt;
$param["col"]["beforeside_spc_tmpt"] = $beforeside_spc_tmpt;
$param["col"]["aftside_tmpt"] = $aftside_tmpt;
$param["col"]["aftside_spc_tmpt"] = $aftside_spc_tmpt;
$param["col"]["honggak_yn"] = $fb->form("honggak_yn");
$param["col"]["dlvrboard"] = $fb->form("dlvrboard");
$param["col"]["memo"] = $fb->form("memo");
$param["col"]["print_amt"] = $fb->form("print_amt");
$param["col"]["print_amt_unit"] = "장";
$param["col"]["after_list"] = $fb->form("after_list");
$param["col"]["opt_list"] = $fb->form("opt_list");
$param["col"]["specialty_items"] = $fb->form("specialty_items");
$param["col"]["empl_seqno"] = $fb->session("empl_seqno");
$param["col"]["op_typ"] = "자동발주";
$param["col"]["op_typ_detail"] = "자동생성";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "output_op";
$param["col"]["subpaper"] = $subpaper;
$param["col"]["amt"] = $tot_tmpt;
$param["col"]["flattyp_dvs"] = "N";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "print_op";
$param["col"]["beforeside_tmpt"] = $beforeside_tmpt;
$param["col"]["beforeside_spc_tmpt"] = $beforeside_spc_tmpt;
$param["col"]["aftside_tmpt"] = $aftside_tmpt;
$param["col"]["aftside_spc_tmpt"] = $aftside_spc_tmpt;
$param["col"]["subpaper"] = $subpaper;
$param["col"]["tot_tmpt"] = $tot_tmpt;
$param["col"]["amt"] = $fb->form("print_amt");
$param["col"]["amt_unit"] = "장";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;


$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "basic_after_op";
$param["col"]["typset_num"] = $typset_num;
$param["col"]["amt"] = $fb->form("print_amt");
$param["col"]["amt_unit"] = "장";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "produce_process_flow";
$param["col"]["typset_save_yn"] = "Y";
$param["col"]["output_yn"] = "Y";
$param["col"]["print_yn"] = "Y";
$param["prk"] = "typset_num";
$param["prkVal"] = $typset_num;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
