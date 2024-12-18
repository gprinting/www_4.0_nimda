<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceHtmlLib.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_price_mng/AfterPriceListDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new AfterPriceListDAO();
$util = new ErpCommonUtil();
$htmlLib = new PriceHtmlLib();

$fb = $fb->getForm();

$cate_sortcode = $fb["cate_sortcode"];
$sell_site     = $fb["sell_site"];

$after_name = $fb["after_name"];
$depth1     = $fb["dep1_val"];
$depth2     = $fb["dep2_val"];
$depth3     = $fb["dep3_val"];
$size       = $fb["size"];
$min_amt    = $fb["min_amt"];
$max_amt    = $fb["max_amt"];
$tax_yn     = $fb["tax_yn"];

//$conn->debug = 1;

$param = array();

//* 넘어온 정보에 해당하는 후공정 맵핑코드 검색
$info_fld_arr = array(
    "basic_yn",
    "after_name",
    "depth1",
    "depth2",
    "depth3",
    "size",
    "crtr_unit"
);

$param["cate_sortcode"] = $cate_sortcode;
$param["size"]          = $size;
//$param["subpaper"]   = $subpaper;
$param["after_name"] = $after_name;
$param["depth1"] = $depth1;
$param["depth2"] = $depth2;
$param["depth3"] = $depth3;

//$conn->debug = 1;
$aft_rs = $dao->selectCateAftInfo($conn, "SEQ", $param);
$aft_total_arr = $util->makeTotalInfoArr($aft_rs, $info_fld_arr);
$aft_mpcode_arr = $aft_total_arr["mpcode"];
$aft_info_arr   = $aft_total_arr["info"];

unset($aft_rs);
unset($aft_total_arr);

//* 후공정 맵핑코드만큼 가격 검색
$aft_mpcode_arr_count = count($aft_mpcode_arr);

// 정보를 저장할 배열들
$amt_arr        = array(); // 수량 배열
$title_arr      = array(); // 제목 배열
$title_info_arr = array(); // 가격수정을 위한 제목 정보 배열
$price_arr      = array(); // 가격 배열

// 각 정보항목 폼
// $title_form 늘어날 경우 $title_info_form, $title_id_arr 같이 수정
// 기본여부|후공정명|depth1|depth2|depth3|사이즈|기준수량
$title_form      = "%s|%s|%s|%s|%s|%s|%s";
// $title에 해당하는 식별값(기본여부, 판매채널, 맵핑코드)
$title_info_form = "%s|%s|%s|-|-|-|-|-";
// 일련번호|기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s|%s";

$param["sell_site"] = $sell_site;
$param["min_amt"] = $min_amt;
$param["max_amt"] = $max_amt;

for ($i = 0; $i < $aft_mpcode_arr_count; $i++) {
    $aft_mpcode = $aft_mpcode_arr[$i];
    $aft_info   = $aft_info_arr[$i];

    $param["after_mpcode"] = $aft_mpcode;

    $price_info_rs = $dao->selectCateAftPriceList($conn, $param);

    if ($price_info_rs->EOF === true) {
        continue;
    }

    while ($price_info_rs && !$price_info_rs->EOF) {
        $price_seqno = $price_info_rs->fields["price_seqno"];
        $amt         = $price_info_rs->fields["amt"];
        $basic_price = $price_info_rs->fields["basic_price"];
        $rate        = $price_info_rs->fields["sell_rate"];
        $aplc_price  = $price_info_rs->fields["sell_aplc_price"];
        $new_price   = $price_info_rs->fields["sell_price"];

        $cate_name = $price_info_rs->fields["cate_name"];

        $basic_yn = "기본후공정";
        if ($aft_info["basic_yn"] === 'N') {
            $basic_yn = "추가후공정";
        }

        $title = sprintf($title_form, $basic_yn
                                    , $aft_info["after_name"]
                                    , $aft_info["depth1"]
                                    , $aft_info["depth2"]
                                    , $aft_info["depth3"]
                                    , $aft_info["size"]
                                    //, $aft_info["affil"]
                                    //, $aft_info["subpaper"]
                                    , $aft_info["crtr_unit"]);

        $title_info = sprintf($title_info_form, $aft_info["basic_yn"]
                                              , $sell_site
                                              , $aft_mpcode);

        $price = sprintf($price_form, $price_seqno
                                    , $basic_price
                                    , $rate
                                    , $aplc_price
                                    , $new_price);

        $amt_arr[$amt] = $amt;
        $title_arr[$title] = $title;
        $title_info_arr[$title] = $title_info;
        $price_arr[$title][$amt] = $price;

        $price_info_rs->MoveNext();
    }
}

if (count($amt_arr) === 0) {
    goto NOT_PRICE;
}

//* 생성된 정보배열 인덱스 정수로 바꿔서 정렬
// 수량배열 정렬
$amt_arr = $util->sortDvsArr($amt_arr);

// 제목배열, 제목 정보배열, 가격배열 정렬
$title_arr_sort      = array();
$title_info_arr_sort = array();
$price_arr_sort      = array();

$i = 0;
foreach ($title_arr as $key => $val) {
    $title          = $val;
    $title_info     = $title_info_arr[$key];
    $price_arr_temp = $price_arr[$key];

    $title_arr_sort[$i]      = $title; // 종이정보
    $title_info_arr_sort[$i] = $title_info; // 종이정보 식별값
    $price_arr_sort[$i++]    = $price_arr_temp; // 가격정보
}

unset($title_arr);
unset($title_info_arr);
unset($price_arr);

$title_id_arr = array(
    "basic_yn",
    "sell_site",
    "mpcode",
    "",
    "",
    "",
    ""
);

$htmlLib->initInfo(count($title_id_arr), 2, "수량");

$thead = $htmlLib->getPriceTheadHtml($title_arr_sort,
                                     $title_id_arr,
                                     $title_info_arr_sort,
                                     true);
$tbody = $htmlLib->getPriceTbodyHtml(count($title_arr_sort),
                                     $price_arr_sort,
                                     $amt_arr,
                                     $tax_yn,
                                     true);
$colgroup = $htmlLib->getColgroupHtml(count($title_arr_sort));

echo $colgroup . $thead . $tbody;

$conn->Close();

exit;

NOT_PRICE :
    $conn->Close();
    echo "<tr><td>검색된 내용이 없습니다.</td></tr>";
    exit;

/******************************************************************************
                            함수 영역
 *****************************************************************************/

/**
 * @brief 후공정 정보 검색결과를 가격검색 및 제목생성에
 * 사용할 수 있도록 가공하는 함수
 *
 * @param $rs = 검색결과
 *
 * @return 가공된 배열
 */
function makeAftTotalInfoArr($rs) {
    $ret = array(
        "mpcode" => array(), // 종이 맵핑코드 배열, 가격검색시 사용
        "info"   => array()  // 종이 정보 배열, 제목 생성시 사용
    );

    $i = 0;
    while ($rs && !$rs->EOF) {
        $mpcode      = $rs->fields["mpcode"];

        $after_name = $rs->fields["after_name"];
        $depth1     = $rs->fields["depth1"];
        $depth2     = $rs->fields["depth2"];
        $depth3     = $rs->fields["depth3"];

        $ret["mpcode"][$i] = $mpcode;
        $ret["info"][$i++] = array(
            "after_name" => $after_name,
            "depth1"     => $depth1,
            "depth2"     => $depth2,
            "depth3"     => $depth3
        );

        $rs->MoveNext();
    }

    return $ret;
}
?>
