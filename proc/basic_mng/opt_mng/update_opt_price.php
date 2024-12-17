<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/OptMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$util = new ErpCommonUtil();
$dao = new OptMngDAO();

// 공통사용 정보
$val = $fb->form("val");
$val = ($val[0] === '.') ? '0' . $val : $val; // 소수점 처리
$dvs = $fb->form("dvs");

// 개별수정시 넘어오는 정보
$price_seqno = $fb->form("price_seqno");

//$conn->debug = 1;
if (empty($price_seqno) === true) {
    // 일괄수정
    $manu      = $fb->form("manu");
    $brand     = $fb->form("brand");
    $name      = $fb->form("name");
    $affil     = $fb->form("affil");
    $crtr_unit = $fb->form("crtr_unit");

    //* 가격 업데이트 대상 검색
    $param = array();
    $param["name"]      = $name;
    $param["depth1"]    = $depth1;
    $param["depth2"]    = $depth2;
    $param["depth3"]    = $depth3;
    $param["crtr_unit"] = $crtr_unit;

    $rs = $dao->selectPrdcOptPriceModi($conn, $param);

    unset($param);

    $conn->StartTrans();
    while ($rs && !$rs->EOF) {
        $price_seqno = $rs->fields["price_seqno"];
        $basic_price = $rs->fields["basic_price"];
        $pur_rate =
            ($dvs === 'R') ? $val : $rs->fields["pur_rate"];
        $pur_aplc_price =
            ($dvs === 'A') ? $val : $rs->fields["pur_aplc_price"];

        $param["price_seqno"] = $price_seqno;
        $param["pur_rate"]       = $pur_rate;
        $param["pur_aplc_price"] = $pur_aplc_price;
        $param["pur_price"]      = $util->getNewPrice($basic_price,
                                                      $pur_rate,
                                                      $pur_aplc_price);

        $update_ret = $dao->updatePrdcOptPrice($conn, $param);

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

    $rs = $dao->selectPrdcOptPriceModi($conn, $param);

    $basic_price = $rs->fields["basic_price"];
    $pur_rate =
        ($dvs === 'R') ? $val : $rs->fields["pur_rate"];
    $pur_aplc_price =
        ($dvs === 'A') ? $val : $rs->fields["pur_aplc_price"];

    $param["price_seqno"] = $price_seqno;
    $param["pur_rate"]       = $pur_rate;
    $param["pur_aplc_price"] = $pur_aplc_price;
    $param["pur_price"]      = $util->getNewPrice($basic_price,
                                                  $pur_rate,
                                                  $pur_aplc_price);

    $update_ret = $dao->updatePrdcOptPrice($conn, $param);

    if (!$update_ret) {
        goto ERR;
    }
}

echo "T";
$conn->close();
exit;

ERR:
    $conn->CompleteTrans();
    $conn->close();
    echo "";
?>
