<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/basic_mng/pur_etprs_mng/PurEtprsListDOC.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PurEtprsListDAO();

$dvs = $fb->form("dvs");
$seqno = $fb->form("seqno");
$html = "";

if ($dvs == "mng") {
    if ($seqno) {
        //외부업체 회원 정보 가져오기
        $param = array();
        $param["table"] = "extnl_etprs_member";
        $param["col"] = "mng, extnl_etprs_member_seqno, id,
            access_code, tel_num, cell_num,
            job, mail, resp_task";
        $param["where"]["extnl_etprs_member_seqno"] = $seqno;

        $result = $dao->selectData($conn, $param);

        $mail = explode("@", $result->fields["mail"]);
        $tel_num = explode("-", $result->fields["tel_num"]);
        $cel_num = explode("-", $result->fields["cell_num"]);

        $param = array();
        $param["chk_flag"] = "N";
        $param["readonly_id"] = "readonly=\"readonly\"";
        $param["name"] = $result->fields["mng"];
        $param["member_seqno"] = $result->fields["member_seqno"];
        $param["id"] = $result->fields["id"];
        $param["access_code"] = $result->fields["access_code"];
        $param["tel_mid"] = $tel_num[1];
        $param["tel_btm"] = $tel_num[2];
        $param["cel_mid"] = $cel_num[1];
        $param["cel_btm"] = $cel_num[2];
        $param["mail_top"] = $mail[0];
        $param["mail_btm"] = $mail[1];
        $param["resp_task"] = $result->fields["resp_task"];
        $param["job"] = $result->fields["job"];
        $param["del_btn"] = "<label class=\"fix_width5\"></label><button onclick=\"delExtnlMember('$seqno');\" type=\"button\" class=\"btn btn-sm btn-danger\">삭제</button>";
    } else {
 
        $param = array();
        $param["chk_flag"] = "Y";
        $param["check_id"] = "<button onclick=\"checkId();\" type=\"button\" class=\"btn btn-info\"><i class=\"fa fa-check-square\"></i> 아이디중복체크</button>";      
        $param["del_btn"] = "";
    }

    $option_html = "\n<option value=\"%s\" %s>%s</option>";
    //이메일
    $email_html = "";
    foreach (EMAIL_DOMAIN as $val) {
        $ck = "";
        if ($val == $mail[1]) {
            $ck = "selected";
        }
        $email_html .= sprintf($option_html, $val, $ck, $val);
    }

    //전화번호
    $tel_html = "";
    foreach (TEL_NUM as $val) {
        $ck = "";
        if ($val == $tel_num[0]) {
            $ck = "selected";
        }
        $tel_html .= sprintf($option_html, $val, $ck, $val);
    }

    //휴대폰 번호
    $cel_html = "";
    foreach (CEL_NUM as $val) {
        $ck = "";
        if ($val == $cel_num[0]) {
            $ck = "selected";
        }
        $cel_html .= sprintf($option_html, $val, $ck, $val);
    }

    $param["email_html"] = $email_html;
    $param["tel_html"] = $tel_html;
    $param["cel_html"] = $cel_html;

    $html = extnlLoginPopup($param);
} else {

    if ($seqno) {
        $param = array();
        $param["table"] = "extnl_brand";
        $param["col"] = "name";
        $param["where"]["extnl_brand_seqno"] = $seqno;

        $name = $dao->selectData($conn, $param)->fields["name"];

        $param = array();
        $param["name"] = $name;
        $param["seqno"] = $seqno;
        $param["del_btn"] = "<label class=\"fix_width5\"></label><button onclick=\"delBrand('$seqno');\" type=\"button\" id=\"del_brand\" class=\"btn btn-sm btn-danger\">삭제</button>";
    } else {
        $param["del_btn"] = "";
    }

    $html = extnlBrandPopup($param);
}

echo $html;
$conn->close();
?>
