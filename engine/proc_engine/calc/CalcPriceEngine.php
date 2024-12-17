#! /usr/bin/php -f
<?
/**
 * @file CalcPriceEngine.php
 *
 * @brief 계산형 가격 등록
 */

$base_path     = $argv[1];
$sell_site     = $argv[2];
$cate_sortcode = $argv[3];

include_once($base_path . '/common/ConnectionPool.php');
include_once($base_path . '/common/CalcPriceUtil.php');
include_once($base_path . '/dao/CalcPriceRegiDAO.php');
include_once($base_path . '/dao/EngineDAO.php');
include_once($base_path . '/common_define/prdt_default_info.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$priceDAO  = new CalcPriceRegiDAO();
$engineDAO = new EngineDAO();
$util = new CalcPriceUtil();

$insert_ret = '';

//$conn->debug = 1;

$tb_name_arr = $priceDAO->selectPriceTableName($conn, $sell_site);

$param = array();

// 계산형 가격을 적용할 카테고리 검색
$cate_rs = $priceDAO->selectCalcCate($conn, $cate_sortcode);

while ($cate_rs && !$cate_rs->EOF) {
    $cate_sortcode = $cate_rs->fields["sortcode"];
    $flattyp_yn    = $cate_rs->fields["flattyp_yn"];
    $mono_dvs      = $cate_rs->fields["mono_dvs"];
    $tmpt_dvs      = $cate_rs->fields["tmpt_dvs"];

    // 카테고리 분류코드에 해당하는 종이, 도수, 사이즈 검색
    // 종이명, 구분, 색상, 평량, 평량단위, 기준단위, 맵핑코드, 계열
    $cate_paper_rs = $priceDAO->selectCatePaper($conn, $cate_sortcode);
    if ($cate_paper_rs->EOF) {
        $insert_ret .= "PAPER_FAIL!";
    }
    // 인쇄명, 전면도수, 후면도수, 추가도수, 총도수, 총출력판수, 기준단위, 맵핑코드
    $cate_tmpt_rs  = $priceDAO->selectCateTmpt($conn, $cate_sortcode);
    if ($cate_tmpt_rs->EOF) {
        $insert_ret .= "PRINT_FAIL!";
    }
    // 사이즈명, 맵핑코드
    $cate_size_rs  = $priceDAO->selectCateSize($conn, $cate_sortcode);
    if ($cate_size_rs->EOF) {
        $insert_ret .= "OUTPUT_FAIL!";
    }
    // 수량
    $cate_amt_arr  = null;
    if ($mono_dvs === '3') {
        $cate_amt_arr = PrdtDefaultInfo::AMT[$cate_sortcode];
    } else {
        $temp = array();
        $temp["table_name"]    = $tb_name_arr[0];
        $temp["cate_sortcode"] = $cate_sortcode;
        // 전체, 합판처럼 합판가격 테이블에 데이터가 존재할 경우
        $amt_rs = $priceDAO->selectCateAmt($conn, $temp);

        if ($amt_rs->EOF) {
            $insert_ret .= "AMT_FAIL!";
        }
        $cate_amt_arr = $util->rs2arr($amt_rs, "amt");
    }
    // 수량단위
    $cate_amt_unit = PrdtDefaultInfo::AMT_UNIT[$cate_sortcode];
    // 페이지수
    $cate_page_info_arr = null;
    if ($flattyp_yn === 'Y') {
        // 낱장형은 표지 2p만 사용
        $cate_page_info_arr = PrdtDefaultInfo::PAGE_INFO["FLAT"];
    } else {
        $cate_page_info_arr = PrdtDefaultInfo::PAGE_INFO[$cate_sortcode];
    }
    // 자리수
    $cate_pos_num_arr    = PrdtDefaultInfo::POSITION_NUMBER[$cate_sortcode];
    // 여분지 수량
    $extra_paper_amt_arr = PrdtDefaultInfo::EXTRA_PAPER_AMT;
    
    $param["sell_site"]           = $sell_site;
    $param["tb_name"]             = $tb_name_arr[1];
    $param["flattyp_yn"]          = $flattyp_yn;
    $param["cate_sortcode"]       = $cate_sortcode;
    $param["cate_paper_rs"]       = $cate_paper_rs;
    $param["cate_size_rs"]        = $cate_size_rs;
    $param["amt_arr"]             = $cate_amt_arr;
    $param["amt_unit"]            = $cate_amt_unit;
    $param["pos_num_arr"]         = $cate_pos_num_arr;
    $param["page_info_arr"]       = $cate_page_info_arr;
    $param["extra_paper_amt_arr"] = $extra_paper_amt_arr;

    // 해당 카테고리 가격 전부 삭제
    $del_ret .= $priceDAO->deleteCateCalcPrice($conn,
                                               $tb_name_arr[1],
                                               $cate_sortcode);
    /*
     */

    if ($del_ret === false) {
        $insert_ret .= "FAIL!";
        $cate_rs->MoveNext();
        continue;
    }

    if ($tmpt_dvs === '0') {
        $param["cate_tmpt_rs"] = $cate_tmpt_rs;
        $insert_ret .= $util->calcPriceSingleBoth($conn, $priceDAO, $param);
    } else {
        $cate_tmpt_arr = $util->getPrintSideDvsArr($cate_tmpt_rs);
        $param["print_purp_arr"] = $cate_tmpt_arr["purp_dvs"];
        $param["cate_bef_tmpt_arr"]     = $cate_tmpt_arr["bef_side"];
        $param["cate_bef_add_tmpt_arr"] = $cate_tmpt_arr["bef_side_add"];
        $param["cate_aft_tmpt_arr"]     = $cate_tmpt_arr["aft_side"];
        $param["cate_aft_add_tmpt_arr"] = $cate_tmpt_arr["aft_side_add"];

        $insert_ret .= $util->calcPriceAftBef($conn, $priceDAO, $param);
    }
    /*
     */

    $cate_rs->MoveNext();
}

// 결과로그 생성
$fp = fopen($base_path . "/log/CalcPrice.log", "w");
fwrite($fp, $insert_ret);
fclose($fp);
?>
