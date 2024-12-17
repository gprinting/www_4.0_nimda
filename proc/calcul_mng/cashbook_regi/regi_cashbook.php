<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookRegiDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

$cashbookDAO = new CashbookRegiDAO();
$conn->StartTrans();

//증빙일자
$evid_date = $fb->form("evid_date");
//마감일자 확인
$result = $cashbookDAO->selectCloseDate($conn, $param);
$close_date = $result->fields["date"];

//마감일자가 증빙일자보다 크면
if ($close_date > $evid_date) {

    echo "3";
    exit;
}

//지출수입 구분
$dvs = $fb->form("dvs");

//잔액 확인
$result = $cashbookDAO->selectBalance($conn, $param);
$bal = $result->fields["bal"];

//잔액이 없으면
if (!$bal) {

    echo "4";
    exit;
}

//금전출납부
$param = array();
$param["table"] = "cashbook";
$param["col"]["cpn_admin_seqno"] = $fb->form("sell_site");
$param["col"]["dvs"] = $fb->form("dvs");
$param["col"]["sumup"] = $fb->form("sumup");
$param["col"]["depo_withdraw_path"] = $fb->form("path");
$param["col"]["depo_withdraw_path_detail"] = $fb->form("path_detail");
$param["col"]["evid_date"] = $fb->form("evid_date");
$param["col"]["regi_date"] = date("Y-m-d", time());
$param["col"][$fb->form("dvs") . "_price"] = $fb->form("price");
$param["col"]["member_seqno"] = $fb->form("member_seqno");
$param["col"]["extnl_etprs_seqno"] = $fb->form("etprs_seqno");

if ($dvs == "income" || $dvs == "trsf_income") {

    $param["col"]["bal"] = $bal + $fb->form("price");

} else {

    $param["col"]["bal"] = $bal - $fb->form("price");

} 

//팀 일련번호
if ($fb->form("depar_list")) {

    $param["col"]["depar_admin_seqno"] = $fb->form("depar_list");

} else {

    $param["col"]["depar_admin_seqno"] = NULL;

}

//계정 상세 일련번호
if ($fb->form("acc_subject_detail")) {

    $param["col"]["acc_detail_seqno"] = $fb->form("acc_subject_detail");

} else {

    $param["col"]["acc_detail_seqno"] = NULL;

}



$result = $cashbookDAO->insertData($conn, $param);

echo $result;

$conn->CompleteTrans();
$conn->close();
?>
