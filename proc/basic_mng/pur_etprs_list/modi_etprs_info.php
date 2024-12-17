<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PurEtprsListDAO();

$conn->debug = 1;

$conn->StartTrans();

$check = 1;
$etprs_seqno = $fb->form("etprs_seqno");

//외부 업체 테이블
$param = array();
$param["table"] = "extnl_etprs";
//제조사
//$param["col"]["manu_name"] = $fb->form("pur_manu");
$param["col"]["manu_name"] = $fb->form("manu_name");
//매입품별
$param["col"]["pur_prdt"] = $fb->form("edit_pur_prdt");
//회사 이름
$param["col"]["cpn_name"] = $fb->form("cpn_name");
//홈페이지
$param["col"]["hp"] = $fb->form("hp");
//전화번호
$param["col"]["tel_num"] = $fb->form("tel_num");
//팩스
$param["col"]["fax"] = $fb->form("fax");
//이메일
$param["col"]["mail"] = $fb->form("mail");
//우편번호
$param["col"]["zipcode"] = $fb->form("zipcode");
//주소
$param["col"]["addr"] = $fb->form("addr");
//주소_상세
$param["col"]["addr_detail"] = $fb->form("addr_detail");
//거래 여부
$param["col"]["deal_yn"] = $fb->form("deal_type");
//외부 업체 일련번호 컬럼
$param["prk"] = "extnl_etprs_seqno";
//외부 업체 일련번호
$param["prkVal"] = $etprs_seqno;

$result = $dao->updateData($conn, $param);
if (!$result) $check = 0;

//외부 업체 담당자 테이블
$param = array();
$param["dvs"] = "매입업체";
//매입업체 담당자
$param["name"] = $fb->form("etprs_mng_name");
//부서
$param["depar"] = $fb->form("etprs_depar");
//직책
$param["job"] = $fb->form("etprs_job");
//담당자 전화번호
$param["tel_num"] = $fb->form("etprs_tel_num");
//담당자 내선
$param["exten_num"] = $fb->form("etprs_exten_num");
//담당자 핸드폰번호
$param["cell_num"] = $fb->form("etprs_cell_num");
//담당자 이메일
$param["mail"] = $fb->form("etprs_email");
//외부 업체 일련번호
$param["extnl_etprs_seqno"] = $etprs_seqno;

$result = $dao->updateExtnlMngData($conn, $param);

if (!$result) $check = 0;

//외부 업체 회계 담당자 테이블
$param = array();
$param["dvs"] = "회계";
//회계 담당자
$param["name"] = $fb->form("accting_mng_name");
//회계 부서
$param["depar"] = $fb->form("accting_depar");
//회계 직책
$param["job"] = $fb->form("accting_job");
//회계 전화 번호
$param["tel_num"] = $fb->form("accting_tel_num");
//회계 내선
$param["exten_num"] = $fb->form("accting_exten");
//회계 핸드폰 번호
$param["cell_num"] = $fb->form("accting_cell_num");
//회계 이메일
$param["mail"] = $fb->form("accting_email");
//외부 업체 일련번호
$param["extnl_etprs_seqno"] = $etprs_seqno;

$result = $dao->updateExtnlMngData($conn, $param);
if (!$result) $check = 0;

if ($check == 1) {
    echo "수정 되었습니다.";

} else {
    echo "수정에 실패하였습니다.";
}

$conn->CompleteTrans();
$conn->close();
?>
