<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/CalculPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$dao = new CalculPriceListDAO();

// 공통사용 정보
$val = $fb->form("val");
$val = ($val[0] === '.') ? '0' . $val : $val; // 소수점 처리
$dvs = $fb->form("dvs");
$sell_site  = $fb->form("sell_site");

// 개별수정시 넘어오는 정보
$price_seqno = $fb->form("price_seqno");

//$conn->debug = 1;
if (empty($price_seqno) === true) {
    // 일괄수정
    $info_arr  = explode('!', $fb->form("info"));
    $affil = $fb->form("affil");
    $size  = $fb->form("size");

    //* 종이 정보에 해당하는 맵핑코드 검색
    $param = array();
    $param["paper_sort"]  = $info_arr[0];
    $param["paper_name"]  = $info_arr[1];
    $param["paper_dvs"]   = $info_arr[2];
    $param["paper_color"] = $info_arr[3];
    $param["paper_affil"] = $affil;
    $param["paper_size"]  = $size;

    $mpcode_rs = $dao->selectPrdtPaperMpcode($conn, $param);
    $mpcode_arr = array();

    $i = 0;
    while ($mpcode_rs && !$mpcode_rs->EOF) {
        $mpcode_arr[$i++] = $mpcode_rs->fields["mpcode"];
        $mpcode_rs->MoveNext();
    }

    $mpcode_arr = $dao->parameterArrayEscape($conn, $mpcode_arr);
    $mpcode = $util->arr2delimStr($mpcode_arr);

    unset($mpcode_rs);
    unset($mpcode_arr);
    unset($param);

    //* 가격 업데이트 대상 검색
    $param["sell_site"] = $sell_site;
    $param["mpcode"]    = $mpcode;

    $rs = $dao->selectPrdtPaperPriceExcel($conn, $param);

    unset($param);

    $conn->StartTrans();
    while ($rs && !$rs->EOF) {
        $price_seqno = $rs->fields["price_seqno"];
        $basic_price = $rs->fields["basic_price"];
        $sell_rate =
            ($dvs === 'R') ? $val : $rs->fields["sell_rate"];
        $sell_aplc_price =
            ($dvs === 'A') ? $val : $rs->fields["sell_aplc_price"];

        $param["price_seqno"] = $price_seqno;
        $param["sell_rate"]        = $sell_rate;
        $param["sell_aplc_price"]  = $sell_aplc_price;
        $param["sell_price"]       = $util->getNewPrice($basic_price,
                                                        $sell_rate,
                                                        $sell_aplc_price);

        $update_ret = $dao->updatePrdtPaperPrice($conn, $param);

        if (!$update_ret) {
            goto ERR;
        }

        $rs->MoveNext();
    }
    $conn->CompleteTrans();

} else {
    // 개별수정
    //* 가격 업데이트 대상 검색
    $param["price_seqno"] = $price_seqno;

    $rs = $dao->selectPrdtPaperPriceExcel($conn, $param);

    $basic_price = $rs->fields["basic_price"];
    $sell_rate =
        ($dvs === 'R') ? $val : $rs->fields["sell_rate"];
    $sell_aplc_price =
        ($dvs === 'A') ? $val : $rs->fields["sell_aplc_price"];

    $param["sell_rate"]        = $sell_rate;
    $param["sell_aplc_price"]  = $sell_aplc_price;
    $param["sell_price"]       = $util->getNewPrice($basic_price,
                                                    $sell_rate,
                                                    $sell_aplc_price);

    $update_ret = $dao->updatePrdtPaperPrice($conn, $param);

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
?>
