<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/AfterPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$dao = new AfterPriceListDAO();

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
    $aft_mpcode = $fb->form("aft_mpcode");

    //* 가격 업데이트 대상 검색
    $param = array();
    $param["sell_site"]    = $sell_site;
    //$param["basic_yn"]     = $fb->form("basic_yn");
    $param["after_mpcode"] = $aft_mpcode;

    $rs = $dao->selectCateAftPriceList($conn, $param);

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

        $update_ret = $dao->updateCateAftPrice($conn, $param);

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

    $rs = $dao->selectCateAftPriceList($conn, $param);

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

    $update_ret = $dao->updateCateAftPrice($conn, $param);

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