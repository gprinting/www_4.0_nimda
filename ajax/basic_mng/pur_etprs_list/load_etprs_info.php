<?
//ini_set('display_errors', 1);
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/common_define/common_info.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$purDAO = new PurEtprsListDAO();

$member_list = "";
    
$param = array();
//매입업체 일련번호
$param["table"] = "extnl_etprs_member";
$param["col"] = "mng, extnl_etprs_seqno, id, access_code,
    tel_num, cell_num, mail, resp_task, extnl_etprs_member_seqno";
$param["where"]["extnl_etprs_seqno"] = $fb->form("etprs_seqno");


//매입업체 회원 결과리스트를 가져옴
$result = $purDAO->selectData($conn, $param);
$member_list = makeExtnlMemberList($result);

$brand_list = "";

$param = array();
$param["table"] = "extnl_brand";
$param["col"] = "name, extnl_brand_seqno";
$param["where"]["extnl_etprs_seqno"] = $fb->form("etprs_seqno");

$result = $purDAO->selectData($conn, $param);
$brand_list = makeBrandList($result);

$param = array();
//매입업체 일련번호
$param["seqno"] = $fb->form("etprs_seqno");

//매입업체 담당자 결과값을 가져옴
$mng_result = $purDAO->selectViewPurMng($conn, $param);
$acct_result = $purDAO->selectViewAcctMng($conn, $param);

$result = "";
//매입업체와 매입업체 사업자등록증 결과값을 가져옴
$result = $purDAO->selectViewPurEtprs($conn, $param);
//매입품목
$pur_prdt = $result->fields["pur_prdt"];

//매입업체 결과를 담을 파라미터
$param = array();
//매입업체 일련번호
$param["etprs_seqno"] = $fb->form("etprs_seqno");
//매입업체 이름
$param["etprs_name"] = $result->fields["etprs_name"];
//판매 품목
$param["pur_prdt"] = $pur_prdt;
//active할 판매 품목
$param[$pur_prdt] = "active";
//회사 이름
$param["cpn_name"] = $result->fields["cpn_name"];
//홈페이지
$param["hp"] = $result->fields["hp"];
//전화 번호
$param["tel_num"] = $result->fields["tel_num"];
//팩스 번호
$param["fax"] = $result->fields["fax"];
//이메일
$param["mail"] = $result->fields["mail"];
//우편번호
$param["zipcode"] = $result->fields["zipcode"];
//주소 
$param["addr"] = $result->fields["addr"];
//주소 상세
$param["addr_detail"] = $result->fields["addr_detail"];

//거래 여부
$deal_yn = $result->fields["deal_yn"];

if ($deal_yn == "Y") {
    $param["deal_y"] = "checked";
    $param["deal_n"] = "";

} else {
    $param["deal_y"] = "";
    $param["deal_n"] = "checked";
}

//매입업체 담당자
$param["mng_name"] = $mng_result->fields["name"];
$param["mng_tel_num"] = $mng_result->fields["tel_num"];
$param["mng_mail"] = $mng_result->fields["mail"];
$param["mng_job"] = $mng_result->fields["job"];
$param["mng_depar"] = $mng_result->fields["depar"];
$param["mng_exten_num"] = $mng_result->fields["exten_num"];
$param["mng_cell_num"] = $mng_result->fields["cell_num"];

//매입업체 회계 담당자
$param["acct_name"] = $acct_result->fields["name"];
$param["acct_tel_num"] = $acct_result->fields["tel_num"];
$param["acct_mail"] = $acct_result->fields["mail"];
$param["acct_job"] = $acct_result->fields["job"];
$param["acct_depar"] = $acct_result->fields["depar"];
$param["acct_exten_num"] = $acct_result->fields["exten_num"];
$param["acct_cell_num"] = $acct_result->fields["cell_num"];

//사업자 회사 이름
$param["bls_cpn_name"] = $result->fields["bls_cpn_name"];
//사업자 대표 이름
$param["repre_name"] = $result->fields["repre_name"];
//사업자 등록증 번호
$param["crn"] = $result->fields["crn"];
/*
$crn = $result->fields["crn"];
$param["crn_first"] = substr($crn, 0, 3);
$param["crn_scd"] = substr($crn, 3, 2);
$param["crn_thd"] = substr($crn, 5, 5);
*/
//사업자 업태
$param["tob"] = $result->fields["tob"];
//사업자 우편번호
$param["bls_zipcode"] = $result->fields["bls_zipcode"];
//업종
$param["bc"] = $result->fields["bc"];
//사업자 주소
$param["bls_addr"] = $result->fields["bls_addr"];
//사업자 주소 상세
$param["bls_addr_detail"] = $result->fields["bls_addr_detail"];
//은행 이름
$param["bank_name"] = $result->fields["bank_name"];
//계좌 번호
$param["ba_num"] = $result->fields["ba_num"];
//추가 사항
$param["add_items"] = $result->fields["add_items"];
//매입업체 회원 리스트
$param["extnl_member"] = $member_list;
//브랜드 리스트
$param["brand_list"] = $brand_list;


$option_html = "\n<option value=\"%s\"%s>%s</option>";
//은행정보
$bank_html = "";
foreach (BANK_INFO as $val) {
    $checked = "";
    if ($result->fields["bank_name"] == $val) {
        $checked = " checked=\"checked\"";
    }
    $bank_html .= sprintf($option_html, $val, $checked ,$val);
}

if ($pur_prdt != '기타') {
    $param["pur_prdt_html"] = "<li><a href=\"#tab5\" data-toggle=\"tab\">" . $pur_prdt . "매입업체 공급품 리스트</a></li>";
}

//매입품목 뷰 팝업창 그리기
$param["bank_html"] = $bank_html;
//매입품목 수정 팝업창 그리기
$html = getPurEtprsEdit($param);

echo $html . "♪" . $pur_prdt;
$conn->close();
?>
