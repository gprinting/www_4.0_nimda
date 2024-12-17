<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/TypsetListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new TypsetListDAO();
$util = new CommonUtil();
$check = 1;

$after_op_seqno = $fb->form("after_op_seqno");

$conn->StartTrans();
$state = $util->status2statusCode("주문후공정준비");

$param = array();
$param["table"] = "after_op";
$param["col"]["seq"] = $fb->form("seq");
$param["col"]["after_name"] = $fb->form("after_name");
$param["col"]["depth1"] = $fb->form("depth1");
$param["col"]["depth2"] = $fb->form("depth2");
$param["col"]["depth3"] = $fb->form("depth3");
$param["col"]["detail"] = $fb->form("detail");
$param["col"]["memo"] = $fb->form("memo");
$param["col"]["dlvrboard"] = $fb->form("dlvrboard");
$param["col"]["order_detail_dvs_num"] = $fb->form("order_detail_dvs_num");
$param["col"]["order_common_seqno"] = $fb->form("order_common_seqno");
$param["col"]["extnl_brand_seqno"] = $fb->form("extnl_brand_seqno");
$param["col"]["order_after_history_seqno"] = $fb->form("order_after_history_seqno");
$param["col"]["amt"] = $fb->form("amt");

if ($after_op_seqno) {
    $param["prk"] = "after_op_seqno";
    $param["prkVal"] = $after_op_seqno;

    $rs = $dao->updateData($conn, $param);
} else {
    $param["col"]["amt_unit"] = "장";
    $param["col"]["state"] = $state;
    $param["col"]["basic_yn"] = "N";
    $param["col"]["op_typ"] = "자동발주";
    $param["col"]["op_typ_detail"] = "자동생성";
    $param["col"]["regi_date"] = date("Y-m-d H:i:s");
    $param["col"]["orderer"] = $fb->session("name");

    $rs = $dao->insertData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
