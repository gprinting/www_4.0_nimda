<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/business/claim_mng/ClaimListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/doc/nimda/business/claim_mng/ClaimInfo.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/basic_mng/pur_etprs_mng/PurEtprsListDAO.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ClaimListDAO();
$util = new CommonUtil();
$dao_etprs = new PurEtprsListDAO();

$pur_prdt  = $fb->form("pur_prdt");
$pur_manu  = $fb->form("pur_manu");
$pur_brand = $fb->form("pur_brand");

$param = array();
$param["pur_prdt"]  = $pur_prdt;
$rs = $dao_etprs->selectPurManu($conn, $param);
$manu_html = makeManuListhtml($rs, $pur_manu, "extnl_etprs_seqno", "manu_name", "업체(전체)");

$param["extnl_etprs_seqno"] = $pur_manu;
$rs_sec = $dao_etprs->selectPurBrand($conn, $param);
$brand_html = makeBrandOpt($rs_sec, $pur_manu, "name", "extnl_brand");

$json = "{\"pur_prdt\" : \"%s\", \"pur_manu\" : \"%s\", \"pur_brand\" : \"%s\"}";

FIN : 
    echo sprintf($json, $pur_prdt
                      , $util->convJsonStr($manu_html)
                      , $util->convJsonStr($brand_html));

    $conn->Close();
    exit;

/**************************** 함수 영역 ******************************/

/**
 * @brief resp manu 셀렉트박스 생성
 *
 * @param $rs   = 검색결과
 * @param $val  = option value에 들어갈 
 * @param $dvs  = option에 표시할 필드값
 * @param $base = 기본으로 추가할 option
 * @param $flag = $base를 사용할지 flag
 * @return html
 */
function makeManuListhtml($rs, $pur_manu, $val, $dvs, $base="전체", $flag="Y" ) {
    
    if ($flag == "Y") {
        $html = "\n" . option($base);
        
    } else {
        $html = "";
    }

    $attr = "selected=\"selected\"";

    while ($rs && !$rs->EOF) {
        $fields = $rs->fields[$dvs];

         //만약 $val 빈값이면
        if ($val === "") {
            $value = $fields;
        } else {
            $value = $rs->fields[$val];
        }

        if ($value == $pur_manu) { 
            $html .= "\n" . option($fields, $value, $attr); 
        } else {
            $html .= "\n" . option($fields, $value);   
        }

        $rs->MoveNext();
    }

    return $html;

}

/*
 * select 옵션 값 생성
 * 
 * return : list
 */
function makeBrandOpt($rs_sec, $pur_manu, $type, $name) {

    $buff = "";

    $cnt = $rs_sec->recordCount();

    while ($rs_sec && !$rs_sec->EOF) {

        $data = $rs_sec->fields[$type]; 
        $seqno = $rs_sec->fields[$name . "_seqno"]; 
        
        if ($data == "") {

        } else {

            $opt_arr[$seqno] = $data; 
        }

        $rs_sec->moveNext();
    }

    //후공정 옵션 값을 셋팅
    if (is_array($opt_arr)) {

        $buff .= "<option value=\"\">전체</option>";   
        foreach($opt_arr as $key => $val) {
            if ($key = $pur_manu) {
                $buff .= "<option selected=\"selected\" value=\"" . $key . "\">";
                $buff .= $val . "</option>";
            } else {
                $buff .= "<option value=\"" . $key . "\">";
                $buff .= $val . "</option>";
            }
        }
    } else {

        $buff = "<option value=\"\">전체</option>";   
    }

    return $buff;
}

?>
