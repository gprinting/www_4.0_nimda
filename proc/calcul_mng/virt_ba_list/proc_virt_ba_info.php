<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/virt_ba_mng/VirtBaListDAO.inc");
$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$virtDAO = new VirtBaListDAO();
$conn->StartTrans();
$check = 1;

$member_seqno = $fb->form("member_seqno");

if ($member_seqno) {

    //회원과 맵핑된 가상계좌 일련번호 select
    $param = array();
    $param["member_seqno"] = $member_seqno;

    $result = $virtDAO->selectMemberVirtBa($conn, $param);
    if (!$result) $check = 0;

    //회원 맵핑된 가상계좌가 있을때
    if ($result->recordCount() > 0) {

        while ($result && !$result->EOF) {

            //회원과 맵핑된 가상계좌 삭제
            $param = array();
            $param["virt_seqno"] = $result->fields["virt_ba_admin_seqno"];
            $m_result = $virtDAO->updateMemberVirtBa($conn, $param);
            if (!$m_result) $check = 0;

            $result->moveNext();
        }
    }
}

//가상계좌 정보 수정
$param = array();
$param["cpn_admin_seqno"] = $fb->form("pop_sell_site");
$param["bank_name"] = $fb->form("bank_name");
$param["member_seqno"] = $member_seqno;
$param["virt_seqno"] = $fb->form("virt_ba_admin_seqno");

$result = $virtDAO->updateVirtBaInfo($conn, $param);
if (!$result) $check = 0;

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
