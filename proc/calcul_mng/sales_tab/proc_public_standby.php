<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();
$check = 1;
$conn->StartTrans();

$sell_site = $fb->form("sell_site");
$member_dvs = $fb->form("member_dvs");
$year = $fb->form("year");
$mon = $fb->form("mon");

$param = array();
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["year"] = $year;
$param["mon"] = $mon;

if ($fb->form("corp_name")) {
    $param["corp_name"] = $fb->form("corp_name");
}

$rs = $dao->selectStandbyListSeqno($conn, $param);
if (!$rs) {
    $check = 0;
} else {

    $param = array();
    $param["table"] = "public_admin";
    $param["col"]["public_state"] = "완료";
    $param["prk"] = "public_admin_seqno";

    while ($rs && !$rs->EOF) {
        $param["prkVal"] = $rs->fields["public_admin_seqno"];
        $rs2 = $dao->updateData($conn, $param);
        if (!$rs2) {
            $check = 0;
        }
        $rs->moveNext();
    }
}

$conn->CompleteTrans();
$conn->Close();
echo $check;
?>
