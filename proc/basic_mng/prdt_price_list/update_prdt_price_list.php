<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/PrdtPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$dao = new PrdtPriceListDAO();

// 공통사용 정보
$val = $util->rmComma($fb->form("val"));
$val = ($val[0] === '.') ? '0' . $val : $val; // 소수점 처리
$dvs = $fb->form("dvs");
$cate_sortcode = $fb->form("cate_sortcode");
$sell_site     = $fb->form("sell_site");
$mono_yn       = intval($fb->form("mono_yn"));
$etprs_dvs     = $fb->form("etprs_dvs");
$min_amt       = $fb->form("min_amt");
$max_amt       = $fb->form("max_amt");

// 개별수정시 넘어오는 정보
$price_seqno = $fb->form("price_seqno");

//* 판매채널에 해당하는 가격 테이블 검색
$table_name = "ply_price";

//$conn->debug = 1;
if (empty($price_seqno) === true) {
    // 일괄수정
    $paper_mpcode = $fb->form("paper_mpcode");
    $stan_info    = explode('!', $fb->form("stan_info"));

    $stan_name   = $stan_info[0];
    $stan_typ    = $stan_info[1];

    $param = array();
    $param["cate_sortcode"] = $cate_sortcode;
    $param["typ"]           = $stan_typ;
    $param["name"]          = $stan_name;

    $stan_mpcode = $dao->selectCateSizeInfo($conn, "MPCODE", $param)
                       ->fields["mpcode"];

    //* 인쇄 맵핑코드 검색
    $bef_print_tmpt     = $fb->form("bef_print_tmpt");
    $bef_add_print_tmpt = $fb->form("bef_add_print_tmpt");
    $aft_print_tmpt     = $fb->form("aft_print_tmpt");
    $aft_add_print_tmpt = $fb->form("aft_add_print_tmpt");

    unset($param);
    $param["cate_sortcode"] = $cate_sortcode;
    // 전면
    $param["tmpt"] = $bef_print_tmpt;
    $rs = $dao->selectCatePrintMpcode($conn, $param);
    $bef_print_mpcode = $rs->fields["mpcode"];
    // 전면추가
    if ($bef_add_print_tmpt !== '-') {
        $param["tmpt"] = $bef_add_print_tmpt;
        $rs = $dao->selectCatePrintMpcode($conn, $param);
        $bef_print_add_mpcode = $rs->fields["mpcode"];
    }
    // 후면
    if ($bef_add_print_tmpt !== '-') {
        $param["tmpt"] = $aft_print_tmpt;
        $rs = $dao->selectCatePrintMpcode($conn, $param);
        $aft_print_mpcode = $rs->fields["mpcode"];
    }
    // 후면추가
    if ($bef_add_print_tmpt !== '-') {
        $param["tmpt"] = $aft_add_print_tmpt;
        $rs = $dao->selectCatePrintMpcode($conn, $param);
        $aft_print_add_mpcode = $rs->fields["mpcode"];
    }

    unset($rs);
    unset($param);

    //* 가격 업데이트 대상 검색
    $param["cate_sortcode"] = $cate_sortcode;
    $param["paper_mpcode"]  = $paper_mpcode;
    $param["stan_mpcode"]   = $stan_mpcode;
    $param["bef_print_mpcode"]     = $bef_print_mpcode;
    $param["bef_add_print_mpcode"] = $bef_add_print_mpcode;
    $param["aft_print_mpcode"]     = $aft_print_mpcode;
    $param["aft_add_print_mpcode"] = $aft_add_print_mpcode;
    $param["min_amt"] = $min_amt;
    $param["max_amt"] = $max_amt;

    $rs = $dao->selectCatePriceListExcel($conn, $table_name, $param);

    unset($param);

    $conn->StartTrans();
    while ($rs && !$rs->EOF) {
        $price_seqno = $rs->fields["price_seqno"];
        $basic_price = $rs->fields["basic_price"];
        $rate =
            ($dvs === 'R') ? $val : $rs->fields["rate"];
        $aplc_price  =
            ($dvs === 'A') ? $val : $rs->fields["aplc_price"];

        $param["price_seqno"] = $price_seqno;
        $param["rate"]        = $rate;
        $param["aplc_price"]  = $aplc_price;
        $param["new_price"]   = $util->getNewPrice($basic_price,
                                                   $rate,
                                                   $aplc_price);

        $update_ret = $dao->updateCatePrice($conn, $table_name, $param);

        if (!$update_ret) {
            goto ERR;
        }

        $rs->MoveNext();
    }
    $conn->CompleteTrans();


} else {
    // 개별수정
    //* 가격 업데이트 대상 검색
    $param["price_seqno"]   = $price_seqno;

    $rs = $dao->selectCatePriceListExcel($conn, $table_name, $param);

    unset($param);

    $basic_price = $rs->fields["basic_price"];
    $rate =
        ($dvs === 'R') ? $val : $rs->fields["rate"];
    $aplc_price  =
        ($dvs === 'A') ? $val : $rs->fields["aplc_price"];

    $param["price_seqno"] = $price_seqno;
    $param["rate"]        = $rate;
    $param["aplc_price"]  = $aplc_price;
    $param["new_price"]   = $util->getNewPrice($basic_price,
                                               $rate,
                                               $aplc_price);

    $update_ret = $dao->updateCatePrice($conn, $table_name, $param);

    if (!$update_ret) {
        goto ERR;
    }
}

echo "T";
$conn->close();
exit;

ERR :
    $conn->CompleteTrans();
    $conn->close();
    echo "";
    exit;
?>
