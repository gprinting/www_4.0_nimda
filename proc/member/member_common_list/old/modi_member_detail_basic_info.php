<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$fileDAO = new FileAttachDAO();
$dao = new MemberCommonListDAO();

$check = 1;

$param = array();
$param["member_seqno"] = $fb->form("seqno");
$param["office_nick"] = $fb->form("office_nick");
$param["mailing_yn"] = $fb->form("mailing_yn");
$param["sms_yn"] = $fb->form("sms_yn");
$param["member_state"] = $fb->form("member_state");
$param["member_state"] = $fb->form("member_state");
$param["office_eval"] = $fb->form("office_eval");
$param["nc_release_resp"] = $fb->form("nc_release_resp");
$param["bl_release_resp"] = $fb->form("bl_release_resp");
//$param["dlvr_resp"] = $fb->form("dlvr_resp");

$conn->StartTrans();

$rs = $dao->updateMemberDetailBasicInfo($conn, $param);

if (!$rs) {
    $check = 0;
}

//회원 담당자 - 일반
$param = array();
$param["table"] = "member_mng";
$param["col"] = "member_mng_seqno";
$param["where"]["member_seqno"] = $fb->form("seqno");
$param["where"]["mng_dvs"] = "일반";

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "member_mng";
$param["col"]["mng_dvs"] = "일반";
$param["col"]["resp_deparcode"] = $fb->form("gene_resp");
$param["col"]["tel_mng"] = $fb->form("gene_tel_receipt_mng");
$param["col"]["ibm_mng"] = $fb->form("gene_ibm_receipt_mng");
$param["col"]["mac_mng"] = $fb->form("gene_mac_receipt_mng");

if ($sel_rs->EOF == 1) {
    $param["col"]["member_seqno"] = $fb->form("seqno");
    $rs = $dao->insertData($conn, $param);
} else {
    $param["prk"] = "member_mng_seqno";
    $param["prkVal"] = $sel_rs->fields["member_mng_seqno"];
    $rs = $dao->updateData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

//회원 담당자 - 상업
$param = array();
$param["table"] = "member_mng";
$param["col"] = "member_mng_seqno";
$param["where"]["member_seqno"] = $fb->form("seqno");
$param["where"]["mng_dvs"] = "상업";

$sel_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "member_mng";
$param["col"]["mng_dvs"] = "상업";
$param["col"]["resp_deparcode"] = $fb->form("busi_resp");
$param["col"]["tel_mng"] = $fb->form("busi_tel_receipt_mng");
$param["col"]["ibm_mng"] = $fb->form("busi_ibm_receipt_mng");
$param["col"]["mac_mng"] = $fb->form("busi_mac_receipt_mng");

if ($sel_rs->EOF == 1) {
    $param["col"]["member_seqno"] = $fb->form("seqno");
    $rs = $dao->insertData($conn, $param);
} else {
    $param["prk"] = "member_mng_seqno";
    $param["prkVal"] = $sel_rs->fields["member_mng_seqno"];
    $rs = $dao->updateData($conn, $param);
}

if (!$rs) {
    $check = 0;
}

//인증정보 유무여부
if ($fb->form("certi_yn") == "Y") {

    //파일 첨부여부
    if ($fb->form("certi_upload_yn") == "Y") {
        //파일 업로드 경로
        $param = array();
        $param["file_path"] = SITE_DEFAULT_MEMBER_CERTI_FILE; 
        $param["tmp_name"] = $_FILES["file_img"]["tmp_name"];
        $param["origin_file_name"] = $_FILES["file_img"]["name"];

        //파일을 업로드 한 후 저장된 경로를 리턴한다.
        $rs = $fileDAO->upLoadFile($param);
   
        if($rs) {
            $param = array();
            $param["table"] = "member_certi";
            $param["col"] = "member_seqno";
            $param["where"]["member_seqno"] = $fb->form("seqno");

            $sel_rs = $dao->selectData($conn,$param);

            //이미지 등록
            $param = array();
            $param["table"] = "member_certi";
            $param["col"]["certinum"] = $fb->form("certinum");
            $param["col"]["origin_file_name"] = $_FILES["file_img"]["name"];
            $param["col"]["save_file_name"] = $rs["save_file_name"];
            $param["col"]["file_path"] = $rs["file_path"];

            if ($sel_rs->EOF == 1) {
                $param["col"]["member_seqno"] = $fb->form("seqno");
                $rs = $dao->insertData($conn,$param);
            } else {
                $param["prk"] = "member_seqno";
                $param["prkVal"] = $fb->form("seqno");
                $rs = $dao->updateData($conn,$param);
            }

            if (!$rs) {
                $check = 0;
            }
        } else {
            $check = 2;
        }
    }
} else if ($fb->form("certi_yn") == "N") {
    $param = array();
    $param["table"] = "member_certi";
    $param["prk"] = "member_seqno";
    $param["prkVal"] = $fb->form("seqno");

    $rs = $dao->deleteData($conn,$param);
 
    if (!$rs) {
        $check = 0;
    }
}

echo $check;
$conn->CompleteTrans();
$conn->close();
?>
