<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$memberCommonListDAO = new MemberCommonListDAO();
$check = 1;

$conn->StartTrans();

$param = array();
$param["member_seqno"] = $fb->form("seqno");
$param["member_dvs"] = $fb->form("member_dvs");
$param["cell_num"] = $fb->form("cell_num");
$param["tel_num"] = $fb->form("tel_num");
$param["mail"] = $fb->form("mail");
$param["birth"] = $fb->form("birth");
$param["member_typ"] = $fb->form("member_typ");
$param["onefile_etprs_yn"] = $fb->form("onefile_etprs_yn");
$param["card_pay_yn"] = $fb->form("card_pay_yn");

$rs = $memberCommonListDAO->updateMemberBasicInfo($conn, $param);

if (!$rs) {
    $check = 0; 
} 

//if ($fb->form("member_typ") == "예외업체") {

    $param = array();
    $param["table"] = "excpt_member";
    $param["col"] = "excpt_member_seqno";
    $param["where"]["member_seqno"] = $fb->form("seqno");

    $result = $memberCommonListDAO->selectData($conn, $param);

    $param = array();
    $param["table"] = "excpt_member";
    $param["col"]["fix_oa"] = str_replace(",", "", $fb->form("fix_oa"));
    $param["col"]["bad_oa"] = str_replace(",", "", $fb->form("bad_oa"));
    $param["col"]["loan_limit_price"] = str_replace(",", "", $fb->form("loan_limit_price"));

    if ($result->EOF == 1) {
        $param["col"]["member_seqno"] = $fb->form("seqno");

        $rs = $memberCommonListDAO->insertData($conn, $param);
    } else {
        $param["prk"] = "member_seqno";
        $param["prkVal"] = $fb->form("seqno");

        $rs = $memberCommonListDAO->updateData($conn, $param);
    }

    if (!$rs) {
        $check = 0; 
    } 
/*
} else {
    $param = array();
    $param["table"] = "excpt_member";
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $memberCommonListDAO->deleteData($conn, $param);
 
    if (!$rs) {
        $check = 0; 
    } 
}
*/

echo $check;

$conn->CompleteTrans();
$conn->close();
?>
