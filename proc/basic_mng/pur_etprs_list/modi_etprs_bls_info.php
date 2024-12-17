<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

$conn->StartTrans();

$check = 1;
$etprs_seqno = $fb->form("etprs_seqno");

$param = array();
$param["table"] = "extnl_etprs_bls_info";
$param["prk"] = "extnl_etprs_seqno";
$param["prkVal"] = $etprs_seqno;

$result = $purDAO->deleteData($conn, $param);
if (!$result) $check = 0;

//외부 업체 사업자등록증 정보
$param = array();
$param["table"] = "extnl_etprs_bls_info";
//사업자 회사이름
$param["col"]["cpn_name"] = $fb->form("bls_cpn_name");
//사업자 대표 이름
$param["col"]["repre_name"] = $fb->form("repre_name");
//사업자 등록 번호
$param["col"]["crn"] = $fb->form("crn");
//$param["col"]["crn"] = $fb->form("crn_first") . $fb->form("crn_scd") . $fb->form("crn_thd");
//업태
$param["col"]["bc"] = $fb->form("bc");
//업종
$param["col"]["tob"] = $fb->form("tob");
//사업자 우편번호
$param["col"]["zipcode"] = $fb->form("bls_zipcode");
//사업자 주소
$param["col"]["addr"] = $fb->form("bls_addr");
//사업자 주소 상세
$param["col"]["addr_detail"] = $fb->form("bls_addr_detail");
//사업자 은행
$param["col"]["bank_name"] = $fb->form("bls_bank_name");
//사업자 은행 계좌 번호
$param["col"]["ba_num"] = $fb->form("ba_num");
//사업자 참고 사항
$param["col"]["add_items"] = $fb->form("add_items");
//외부 업체 일련번호
$param["col"]["extnl_etprs_seqno"] = $etprs_seqno;

$result = $purDAO->insertData($conn, $param);
if (!$result) $check = 0;

if ($check == 1) {
    echo "수정 되었습니다.";

} else {
    echo "수정에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->close();
?>
