<?
/*
 * Copyright (c) 2015-20166 Nexmotion, Inc.
 * All rights reserved.
 * 
 * REVISION HISTORY (reverse chronological order)
 *=============================================================================
 * 2016/09/26 엄준현 수정(사이즈 유형 관련 추가, 사이즈 삭제)
 *=============================================================================
 *
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/common_lib/CommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PrdtPriceListDAO();
$util = new CommonUtil();

$cate_sortcode = $fb->form("cate_sortcode");
$type = $fb->form("type");

$param = array();
$param["cate_sortcode"] = $cate_sortcode;

$ret  = "{";

//$conn->debug = 1;
if ($type === "NAME") {
    /*
     * 종이명 셀렉트 박스 변경시
     * 파라미터에 종이명 추가
     */

    $html = array();
    
    $param["name"] = $fb->form("paper_name");

    $html["dvs"] =
        $dao->selectCatePaperHtml($conn, "DVS", $param);
    $html["color"] =
        $dao->selectCatePaperHtml($conn, "COLOR", $param);
    $html["basisweight"] =
        $dao->selectCatePaperHtml($conn, "BASISWEIGHT", $param);

    $html = $util->convJsonStr($html);

    $ret .= " \"dvs\"         : \"%s\",";
    $ret .= " \"color\"       : \"%s\",";
    $ret .= " \"basisweight\" : \"%s\"";
    $ret .= "}";

    $ret  = sprintf($ret, $html["dvs"]
                        , $html["color"]
                        , $html["basisweight"]);

} else if ($type === "DVS") {
    /*
     * 구분 셀렉트 박스 변경시
     * 파라미터에 종이명, 구분 추가
     */

    $html = array();
    
    $param["name"] = $fb->form("paper_name");
    $param["dvs"]  = $fb->form("paper_dvs");

    $html["color"] =
        $dao->selectCatePaperHtml($conn, "COLOR", $param);
    $html["basisweight"] =
        $dao->selectCatePaperHtml($conn, "BASISWEIGHT", $param);

    $html = $util->convJsonStr($html);

    $ret .= " \"color\"       : \"%s\",";
    $ret .= " \"basisweight\" : \"%s\"";
    $ret .= "}";

    $ret  = sprintf($ret, $html["color"]
                        , $html["basisweight"]);

} else if ($type === "COLOR") {
    /*
     * 색상 셀렉트 박스 변경시
     * 파라미터에 종이명, 구분, 색상 추가
     */

    $html = array();
    
    $param["name"]  = $fb->form("paper_name");
    $param["dvs"]   = $fb->form("paper_dvs");
    $param["color"] = $fb->form("paper_color");

    $html["basisweight"] =
        $dao->selectCatePaperHtml($conn, "BASISWEIGHT", $param);

    $html = $util->convJsonStr($html);

    $ret .= " \"basisweight\" : \"%s\"";
    $ret .= "}";

    $ret  = sprintf($ret, $html["basisweight"]);
}

echo $ret;

$conn->Close();
?>
