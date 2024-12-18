<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/Template.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/member/member_mng/MemberCommonListDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$template = new Template();
$dao = new MemberCommonListDAO();

//상세정보
$param = array();
$param["table"] = "member_detail_info";
$param["col"] = "wd_yn ,wd_anniv ,occu1 ,occu2 
                ,occu_detail ,interest_field1, interest_field2
                ,interest_field_detail ,design_outsource_yn 
                ,produce_outsource_yn ,use_opersys ,use_pro 
                ,add_interest_items ,interest_prior ,plural_deal_yn 
                ,plural_deal_site_name1 ,plural_deal_site_detail1 
                ,plural_deal_site_name2 ,plural_deal_site_detail2 
                ,recomm_id ,recomm_id_detail ,memo ,regi_date 
                ,member_seqno";
$param["where"]["member_seqno"] = $fb->form("seqno");

$detail_rs = $dao->selectData($conn, $param);

//관심 상품
$param = array();
$param["table"] = "member_interest_prdt";
$param["col"] = "interest_1 ,interest_2 ,interest_3 ,interest_4 
                ,interest_5 ,interest_6 ,interest_7 ,interest_8
                ,interest_9 ,interest_10 ,interest_11 ,interest_12";
$param["where"]["member_seqno"] = $fb->form("seqno");

$inter_prdt_rs = $dao->selectData($conn, $param);

//관심 이벤트
$param = array();
$param["table"] = "member_interest_event";
$param["col"] = "interest_1 ,interest_2 ,interest_3 ,interest_4 
                ,interest_5";
$param["where"]["member_seqno"] = $fb->form("seqno");

$inter_event_rs = $dao->selectData($conn, $param);

//관심 디자인
$param = array();
$param["table"] = "member_interest_design";
$param["col"] = "interest_1 ,interest_2 ,interest_3 ,interest_4 
                ,interest_5 ,interest_6";
$param["where"]["member_seqno"] = $fb->form("seqno");

$inter_design_rs = $dao->selectData($conn, $param);

//관심요구사항
$param = array();
$param["table"] = "member_interest_needs";
$param["col"] = "interest_1 ,interest_2 ,interest_3 ,interest_4 
                ,interest_5 ,interest_6 ,interest_7 ,interest_8
                ,interest_9 ,interest_10";
$param["where"]["member_seqno"] = $fb->form("seqno");

$inter_needs_rs = $dao->selectData($conn, $param);


$pro_html = "\n<label class=\"form-radio form-normal\"><input type=\"radio\" name=\"use_pro\" class=\"radio_box\" value=\"%s\"%s>%s</label>";

$use_pro_html = "";

//OS 별 사용프로그램
$param = array();
$param["table"] = "pro_typ";
$param["col"] = "pro";
$param["where"]["oper_sys"] = $detail_rs->fields["use_opersys"];

$rs = $dao->selectData($conn, $param);

while ($rs && !$rs->EOF) {

    $checked = "";
    if ($detail_rs->fields["use_pro"] == $rs->fields["pro"]) {
        $checked = " checked=\"checked\"";
    }
    $use_pro_html .= sprintf($pro_html
            , $rs->fields["pro"]
            , $checked
            , $rs->fields["pro"]);
    $rs->moveNext();
}

$param = array();
$param["wd_anniv"] = date("Y-m-d", strtotime($detail_rs->fields["wd_anniv"]));
$param["occu_detail"] = $detail_rs->fields["occu_detail"];
$param["interest_field_detail"] = $detail_rs->fields["interest_field_detail"];
$param["add_interest_items"] = $detail_rs->fields["add_interest_items"];
$param["plural_deal_site_detail1"] = $detail_rs->fields["plural_deal_site_detail1"];
$param["plural_deal_site_detail2"] = $detail_rs->fields["plural_deal_site_detail2"];
$param["recomm_id"] = $detail_rs->fields["recomm_id"];
$param["recomm_id_detail"] = $detail_rs->fields["recomm_id_detail"];
$param["memo"] = $detail_rs->fields["memo"];
$param["member_seqno"] = $fb->form("seqno");
$param["use_pro_html"] = $use_pro_html;

echo makeMemberAddInfoHtml($param) . "♪" . $detail_rs->fields["wd_yn"] . "♪" . 
       $detail_rs->fields["occu1"] . "♪" . $detail_rs->fields["occu2"] . "♪" .
       $detail_rs->fields["interest_field1"] . "♪" . $detail_rs->fields["interest_field2"] . "♪" .
       //5

       $inter_prdt_rs->fields["interest_1"] . "♪" . $inter_prdt_rs->fields["interest_2"] . "♪" .
       $inter_prdt_rs->fields["interest_3"] . "♪" . $inter_prdt_rs->fields["interest_4"] . "♪" .
       $inter_prdt_rs->fields["interest_5"] . "♪" . $inter_prdt_rs->fields["interest_6"] . "♪" .
       $inter_prdt_rs->fields["interest_7"] . "♪" . $inter_prdt_rs->fields["interest_8"] . "♪" . 
       $inter_prdt_rs->fields["interest_9"] . "♪" . $inter_prdt_rs->fields["interest_10"] . "♪" . 
       $inter_prdt_rs->fields["interest_11"] . "♪" . $inter_prdt_rs->fields["interest_12"] . "♪" .
       //17

       $inter_event_rs->fields["interest_1"] . "♪" . $inter_event_rs->fields["interest_2"] . "♪" . 
       $inter_event_rs->fields["interest_3"] . "♪" . $inter_event_rs->fields["interest_4"] . "♪" .
       $inter_event_rs->fields["interest_5"] . "♪" . $inter_event_rs->fields["interest_6"] . "♪" .
       //23

       $inter_design_rs->fields["interest_1"] . "♪" . $inter_design_rs->fields["interest_2"] . "♪" . 
       $inter_design_rs->fields["interest_3"] . "♪" . $inter_design_rs->fields["interest_4"] . "♪" . 
       $inter_design_rs->fields["interest_5"] . "♪" . $inter_design_rs->fields["interest_6"] . "♪" . 
       //29

       $inter_needs_rs->fields["interest_1"] . "♪" . $inter_needs_rs->fields["interest_2"] . "♪" . 
       $inter_needs_rs->fields["interest_3"] . "♪" . $inter_needs_rs->fields["interest_4"] . "♪" . 
       $inter_needs_rs->fields["interest_5"] . "♪" . $inter_needs_rs->fields["interest_6"] . "♪" . 
       $inter_needs_rs->fields["interest_7"] . "♪" . $inter_needs_rs->fields["interest_8"] . "♪" . 
       $inter_needs_rs->fields["interest_9"] . "♪" . $inter_needs_rs->fields["interest_10"] . "♪" .
       //39

       $detail_rs->fields["design_outsource_yn"] . "♪" . $detail_rs->fields["produce_outsource_yn"] . "♪" .
       $detail_rs->fields["use_opersys"] . "♪" . $detail_rs->fields["use_pro"] . "♪" . 
       $detail_rs->fields["interest_prior"] . "♪" . $detail_rs->fields["plural_deal_yn"] . "♪" . 
       $detail_rs->fields["plural_deal_site_name1"] . "♪" . $detail_rs->fields["plural_deal_site_name2"];

$conn->close();
?>
