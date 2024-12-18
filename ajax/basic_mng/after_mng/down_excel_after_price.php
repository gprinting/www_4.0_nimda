<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceExcelUtil.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/AfterMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new AfterMngDAO();
$util = new ErpCommonUtil();
$excelLib = new PriceExcelUtil();

$name        = $fb->form("name");
$depth1      = $fb->form("depth1");
$depth2      = $fb->form("depth2");
$depth3      = $fb->form("depth3");
$affil       = $fb->form("affil");
$subpaper    = $fb->form("subpaper");
$etprs_seqno = $fb->form("manu_seqno");
$brand_seqno = $fb->form("brand_seqno");

// 정보를 저장할 배열들
$amt_arr   = array(); // 평량 배열
$title_arr = array(); // 제목 배열
$price_arr = array(); // 가격 배열

// 각 정보항목 폼
// 제조사|브랜드|후공정명|depth1|depth2|depth3|계열|절수|기준단위
$title_form      = "%s|%s|%s|%s|%s|%s|%s|%s|%s";
// 기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s";

$param["name"]        = $name;
$param["depth1"]      = $depth1;
$param["depth2"]      = $depth2;
$param["depth3"]      = $depth3;
$param["affil"]       = $affil;
$param["subpaper"]    = $subpaper;
$param["brand_seqno"] = $brand_seqno;
$param["etprs_seqno"] = $etprs_seqno;

$price_rs = $dao->selectPrdcAfterPrice($conn, $param);

if ($price_rs->EOF) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

while ($price_rs && !$price_rs->EOF) {
    $fields = $price_rs->fields;

    $manu      = $fields["manu_name"];
    $brand     = $fields["brand_name"];
    $name      = $fields["name"];
    $depth1    = $fields["depth1"];
    $depth2    = $fields["depth2"];
    $depth3    = $fields["depth3"];
    $amt       = $fields["amt"];
    $affil     = $fields["affil"];
    $subpaper  = $fields["subpaper"];
    $crtr_unit = $fields["crtr_unit"];

    $basic_price    = round(doubleval($fields["basic_price"]) / 1.1);
    $pur_rate       = $fields["pur_rate"];
    $pur_aplc_price = round(doubleval($fields["pur_aplc_price"]) / 1.1);
    $pur_price      = $fields["pur_price"];

    if (empty($name)) {
        $name = $brand . "_후공정명";
    }

    $title = sprintf($title_form, $manu
                                , $brand
                                , $name
                                , $depth1
                                , $depth2
                                , $depth3
                                , $affil
                                , $subpaper
                                , $crtr_unit);

    $price = sprintf($price_form, $basic_price
                                , $pur_rate
                                , $pur_aplc_price
                                , $pur_price);

    $amt_arr[$amt] = $amt;
    $title_arr[$name][$title] = $title;
    $price_arr[$name][$title][$amt] = $price;

    $price_rs->MoveNext();
}


//* 생성된 정보배열 인덱스 정수로 바꿔서 정렬
// 수량배열 정렬
$amt_arr = $util->sortDvsArr($amt_arr);

// 제목배열, 제목 정보배열, 가격배열 정렬
$title_arr_sort = array();
$price_arr_sort = array();

foreach ($title_arr as $sheet_name => $title_arr_temp) {
    $price_arr_temp = $price_arr[$sheet_name];

    $i = 0;
    foreach ($title_arr_temp as $key => $val) {
        $title_arr_sort[$sheet_name][$i] = $val;
        $price_arr_sort[$sheet_name][$i++] = $price_arr_temp[$key];
    }
}

unset($title_arr);
unset($price_arr);

$info_dvs_arr = array(1  => "제조사",
                      2  => "브랜드",
                      3  => "후공정명",
                      4  => "Depth1",
                      5  => "Depth2",
                      6  => "Depth3",
                      7  => "계열",
                      8  => "절수",
                      9  => "기준단위",
                      10 => "수량");

$price_dvs_arr = array(0 => "매입가격",
                       1 => "기준가격",
                       2 => "요율",
                       3 => "적용가격");

$excelLib->initExcelFileWriteInfo((count($info_dvs_arr) - 1),
                                  count($price_dvs_arr),
                                  1);

foreach ($title_arr_sort as $sheet_name => $title_arr) {
    $excelLib->makePriceExcelSheet($sheet_name,
                                   $info_dvs_arr,
                                   $title_arr,
                                   $price_dvs_arr,
                                   $amt_arr,
                                   $price_arr_sort[$sheet_name]);
}

$file_name = uniqid();

$file_path = $excelLib->createExcelFile($file_name);

if (is_file($file_path)) {
    echo "after_pur_price_list!" . $file_name;
} else {
    echo "FALSE";
}

$conn->Close();
?>

