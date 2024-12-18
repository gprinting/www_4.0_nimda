<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$dao = new MemberCommonListDAO();

$param = array();
$param["member_seqno"] = $fb->form("seqno");

$rs = $dao->selectMemberDetailInfo($conn, $param);

$sell_site = $rs->fields["cpn_admin_seqno"];

$param = array();
$param["table"] = "member_certi";
$param["col"] = "certinum, origin_file_name, member_certi_seqno";
$param["where"]["member_seqno"] = $fb->form("seqno");

$certi_rs = $dao->selectData($conn, $param);

$certi_yn = "";
if ($certi_rs->EOF == 1) {
    $certi_yn = "N";
} else {
    $certi_yn = "Y";
}

// 미분류 직원(기본값) 정보 검색
$param = array();
$param["table"] = "empl";
$param["col"] = "name, empl_seqno";
$param["where"]["cpn_admin_seqno"] = $sell_site;
$param["where"]["empl_id"] = "temp";

$temp_empl = $dao->selectData($conn, $param);

$arr = [];
$arr["flag"] = "N";
$arr["def"] = "";
$arr["dvs"] = "name";
$arr["val"] = "empl_seqno";
$temp_empl = makeSelectOptionHtml($temp_empl, $arr);

$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_code";
$param["where"]["cpn_admin_seqno"] = $sell_site;
$param["where"]["depar_name"] = "출고실";

$nc_release_resp_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "empl";
$param["col"] = "name, empl_seqno";
$param["where"]["cpn_admin_seqno"] = $sell_site;
$param["where"]["depar_code"] = $nc_release_resp_rs->fields["depar_code"];

$nc_release_resp_rs = $dao->selectData($conn, $param);

$arr = [];
$arr["flag"] = "N";
$arr["def"] = "";
$arr["dvs"] = "name";
$arr["val"] = "empl_seqno";

$nc_release_resp = makeSelectOptionHtml($nc_release_resp_rs, $arr);

$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_code";
$param["where"]["cpn_admin_seqno"] = $sell_site;
$param["where"]["depar_name"] = "출고실";

$bl_release_resp_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "empl";
$param["col"] = "name, empl_seqno";
$param["where"]["cpn_admin_seqno"] = $sell_site;
$param["where"]["depar_code"] = $bl_release_resp_rs->fields["depar_code"];

$bl_release_resp_rs = $dao->selectData($conn, $param);

$arr = [];
$arr["flag"] = "N";
$arr["def"] = "";
$arr["dvs"] = "name";
$arr["val"] = "empl_seqno";

$bl_release_resp = makeSelectOptionHtml($bl_release_resp_rs, $arr);

$param = array();
$param["sell_site"] = $sell_site;

$gene_rs = $dao->selectGeneInfo($conn, $param);

$arr = [];
$arr["flag"] = "N";
$arr["def"] = "";
$arr["dvs"] = "depar_name";
$arr["val"] = "depar_code";

$gene_resp = makeSelectOptionHtml($gene_rs, $arr);

$param = array();
$param["table"] = "depar_admin";
$param["col"] = "depar_code, depar_name";
$param["where"]["cpn_admin_seqno"] = $sell_site;
$param["where"]["depar_name"] = "상업인쇄팀";

$busi_rs = $dao->selectData($conn, $param);

$arr = [];
$arr["flag"] = "N";
$arr["def"] = "";
$arr["dvs"] = "depar_name";
$arr["val"] = "depar_code";

$busi_resp = makeSelectOptionHtml($busi_rs, $arr);

/*
$param = array();
$param["sell_site"] = $sell_site;
$param["depar_code"] = "003";

$dlvr_rs = $dao->selectDeparInfo($conn, $param);

$arr = [];
$arr["flag"] = "N";
$arr["def"] = "";
$arr["dvs"] = "depar_name";
$arr["val"] = "depar_code";

$dlvr_resp = makeSelectOptionHtml($dlvr_rs, $arr);
*/

$param = array();
$param["table"] = "licensee_info";
$param["col"] = "crn, repre_name, bc, tob, zipcode, addr, addr_detail";
$param["where"]["member_seqno"] = $fb->form("seqno");

$licensee_rs = $dao->selectData($conn, $param);
if ($rs->fields["member_dvs"] == "기업") {

    $param = array();
    $param["group_id"] = $fb->form("seqno");

    $mng_info_rs = $dao->selectMemberGroupInfo($conn, $param);

    if (!$mng_info_rs->EOF == 1) {
        $mng_info_html = makeMemberMngDetailHtml($mng_info_rs);
    } else {
        $mng_info_html = "<tr><td colspan=\"5\">검색 된 내용이 없습니다.</td></tr>";
    }

    $param = array();
    $param["table"] = "accting_mng";
    $param["col"] = "name, tel_num, cell_num, mail";
    $param["where"]["member_seqno"] = $fb->form("seqno");
 
    $accting_mng_info_rs = $dao->selectData($conn, $param);

    if (!$accting_mng_info_rs->EOF == 1) {
        $accting_mng_info_html = makeMemberAcctingMngDetailHtml($accting_mng_info_rs);
    } else {
        $accting_mng_info_html = "<tr><td colspan=\"4\">검색 된 내용이 없습니다.</td></tr>";
    }

    $param = array();
    $param["table"] = "admin_licenseeregi";
    $param["col"] = "crn, corp_name, repre_name, tel_num, addr, addr_detail";
    $param["where"]["member_seqno"] = $fb->form("seqno");

    $admin_licenseeregi_info_rs = $dao->selectData($conn, $param);

    if (!$admin_licenseeregi_info_rs->EOF == 1) {
        $admin_licenseeregi_info_html = makeMemberidminLicenseeregiDetailHtml($admin_licenseeregi_info_rs);
    } else {
        $admin_licenseeregi_info_html = "<tr><td colspan=\"6\">검색 된 내용이 없습니다.</td></tr>";
    }
}

$param = array();
$param["table"] = "member_mng";
$param["col"] = "resp_deparcode, tel_mng, ibm_mng, mac_mng";
$param["where"]["member_seqno"] = $fb->form("seqno");
$param["where"]["mng_dvs"] = "일반";

$gene_rs = $dao->selectData($conn, $param);

$param = array();
$param["table"] = "member_mng";
$param["col"] = "resp_deparcode, tel_mng, ibm_mng, mac_mng";
$param["where"]["member_seqno"] = $fb->form("seqno");
$param["where"]["mng_dvs"] = "상업";

$busi_rs = $dao->selectData($conn, $param);

$option_html = "\n<option value=\"%s\">%s</option>";
//전화번호
$tel_html = "";
foreach (TEL_NUM as $val) {
    $tel_html .= sprintf($option_html, $val, $val);
}

//회원 상태
$member_state = "";
foreach (MEMBER_STATE as $key=>$val) {
    $member_state .= sprintf($option_html, $key, $val);
}

$member_certi_seqno = $certi_rs->fields["member_certi_seqno"];
$certi_file_name = $certi_rs->fields[origin_file_name];

$origin_file_name = "<a href=\"/common/certi_file_down.inc?seqno=" . $member_certi_seqno . "\">" . $certi_file_name . "</a><button onclick=\"removeCertiFile('" . $member_certi_seqno . "');\" type=\"button\" id=\"del_btn1\" class=\"btn btn-sm bred fa\" style=\"margin-left: 5px;\">이미지삭제</button>";

$param = array();
$param["member_seqno"] = $fb->form("seqno");
$param["office_nick"] = $rs->fields["office_nick"];
$param["office_eval"] = $rs->fields["office_eval"];
$param["certinum"] = $certi_rs->fields["certinum"];
if ($certi_rs->fields["origin_file_name"]) {
    $param["origin_file_name"] = $origin_file_name;
}
$param["cashreceipt_card_num"] = $rs->fields["cashreceipt_card_num"];
$param["nc_release_resp"] = $nc_release_resp;
$param["bl_release_resp"] = $bl_release_resp;
$param["gene_resp"] = $gene_resp;
$param["busi_resp"] = $busi_resp;
//$param["dlvr_resp"] = $dlvr_resp;
$param["crn"] = $licensee_rs->fields["crn"];
$param["repre_name"] = $licensee_rs->fields["repre_name"];
$param["bc"] = $licensee_rs->fields["bc"];
$param["tob"] = $licensee_rs->fields["tob"];
$param["zipcode"] = $licensee_rs->fields["zipcode"];
$param["addr"] = $licensee_rs->fields["addr"];
$param["addr_detail"] = $licensee_rs->fields["addr_detail"];
$param["mng_info_html"] = $mng_info_html;
$param["accting_mng_info_html"] = $accting_mng_info_html;
$param["admin_licenseeregi_info_html"] = $admin_licenseeregi_info_html;
$param["sell_site"] = $sell_site;
$param["member_certi_seqno"] = $certi_rs->fields["member_certi_seqno"];
$param["temp_empl"] = $temp_empl;
$param["member_state"] = $member_state;

// 0 1
echo makeMemberDetailInfoHtml($param) . "♪" . $rs->fields["member_dvs"] . "♪" . 
// 2 3
            $rs->fields["mailing_yn"] . "♪" . $rs->fields["sms_yn"] . "♪" . 
// 4 5
            $rs->fields["nc_release_resp"] . "♪" . $rs->fields["bl_release_resp"] . "♪" . 
// 6 7 8
            $rs->fields["dlvr_resp"] . "♪-♪" . $certi_yn . "♪" . 
// 9 10
            $gene_rs->fields["resp_deparcode"] . "♪" . $gene_rs->fields["tel_mng"] . "♪" . 
            $gene_rs->fields["ibm_mng"] . "♪" . $gene_rs->fields["mac_mng"] . "♪" . 
// 11 12
            $busi_rs->fields["resp_deparcode"] . "♪" . $busi_rs->fields["tel_mng"] . "♪" . 
// 13 14
            $busi_rs->fields["ibm_mng"] . "♪" . $busi_rs->fields["mac_mng"] . "♪" . 
// 15 16
            $sell_site . "♪" . $rs->fields["state"];
// 17 18
$conn->close();
?>
