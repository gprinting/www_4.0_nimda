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

$seqno = $fb->form("seqno");

$conn->StartTrans();

//상태는 출력준비이지만, 조판지시서리스트는 조판완료
$param = array();
$param["table"] = "sheet_typset";
$param["col"]["state"] = "605";
$param["prk"] = "sheet_typset_seqno";
$param["prkVal"] = $seqno;

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$param = array();
$param["table"] = "sheet_typset";
$param["col"] = "typset_format_seqno";
$param["where"]["sheet_typset_seqno"] = $seqno;

$rs = $dao->selectData($conn, $param);

//조판 판형 공정여부 수정
$param = array();
$param["table"] = "typset_format";
$param["col"]["process_yn"] = "Y";
$param["prk"] = "typset_format_seqno";
$param["prkVal"] = $rs->fields["typset_format_seqno"];

$rs = $dao->updateData($conn, $param);

if (!$rs) {
    $check = 0;
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
