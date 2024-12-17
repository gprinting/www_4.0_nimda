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

$param = array();
//외부업체 회원
$param["table"] = "extnl_etprs_member";
//담당자 이름
$param["col"]["mng"] = $fb->form("mem_name");
//비밀번호
$param["col"]["access_code"] = $fb->form("mem_passwd");
//전화번호
if ($fb->form("mem_tel_top")) {
    $param["col"]["tel_num"] = $fb->form("mem_tel_top") . "-" . 
                               $fb->form("mem_tel_mid") . "-" .
                               $fb->form("mem_tel_btm");
}

//휴대폰번호
if ($fb->form("mem_cel_top")) {
    $param["col"]["cell_num"] = $fb->form("mem_cel_top") . "-" .
                                $fb->form("mem_cel_mid") . "-" . 
                                $fb->form("mem_cel_btm");
}

//메일
if ($fb->form("mem_mail_top")) {
    $param["col"]["mail"] = $fb->form("mem_mail_top") . "@" .
                            $fb->form("mem_mail_btm");

}

//담당업무
$param["col"]["resp_task"] = $fb->form("mem_task");
//직책
$param["col"]["job"] = $fb->form("mem_job");
//외부업체 일련번호
$param["col"]["extnl_etprs_seqno"] = $fb->form("etprs_seqno");

//외부업체 회원 추가
if ($fb->form("add_yn") == "Y") {

    $param["col"]["id"] = $fb->form("mem_id");
    $result = $purDAO->insertData($conn, $param);

//외부업체 회원 수정
} else {

    $param["prk"] = "id";
    $param["prkVal"] = $fb->form("mem_id");

    $result = $purDAO->updateData($conn, $param);
     
}

if ($result) {
    
    echo "1";

} else {

    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>

