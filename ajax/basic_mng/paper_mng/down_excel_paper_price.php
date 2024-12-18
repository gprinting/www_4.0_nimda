<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PriceExcelUtil.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdc_prdt_mng/PaperMngDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new PaperMngDAO();
$util = new ErpCommonUtil();
$excelLib = new PriceExcelUtil();

$paper_name  = $fb->form("name");
$etprs_seqno = $fb->form("manu_seqno");
$brand_seqno = $fb->form("brand_seqno");
$paper_affil = $util->getUseAffilParam($conn, $dao, $fb);

if (empty($etprs_seqno)) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

// 정보를 저장할 배열들
$basisweight_arr = array(); // 평량 배열
$title_arr       = array(); // 제목 배열
$price_arr       = array(); // 가격 배열

// 각 정보항목 폼
// 제조사|브랜드|종이분류|종이정보|계열|사이즈|기준단위
$title_form      = "%s|%s|%s|%s!%s!%s|%s|%s|%s";
// 기본가격|요율|적용금액|신규가격
$price_form      = "%s|%s|%s|%s";

$param["paper_name"]  = $paper_name;
$param["affil"]       = $paper_affil;
$param["brand_seqno"] = $brand_seqno;
$param["etprs_seqno"] = $etprs_seqno;

$price_rs = $dao->selectPrdcPaperPrice($conn, $param);

if ($price_rs->EOF) {
    $conn->Close();
    echo "NOT_INFO";
    exit;
}

while ($price_rs && !$price_rs->EOF) {
    $fields = $price_rs->fields;
    
    $paper_manu        = $fields["manu_name"];
    $paper_brand       = $fields["brand_name"];
    $paper_sort        = $fields["sort"];
    $paper_name        = $fields["name"];
    $paper_dvs         = $fields["dvs"];
    $paper_color       = $fields["color"];
    $paper_basisweight = $fields["basisweight"];
    $paper_affil       = $fields["affil"];
    $paper_size        = $fields["size"];
    $crtr_unit         = $fields["crtr_unit"];

    $basic_price    = round(doubleval($fields["basic_price"]) / 1.1);
    $pur_rate       = $fields["pur_rate"];
    $pur_aplc_price = round(doubleval($fields["pur_aplc_price"]) / 1.1);
    $pur_price      = $fields["pur_price"];

    if (empty($paper_name)) {
        $paper_name = $paper_brand . "_종이명";
    }
    if (empty($paper_dvs)) {
        $paper_dvs = "종이구분";
    }
    if (empty($paper_color)) {
        $paper_color = "종이색상";
    }

    $title = sprintf($title_form, $paper_manu
                                , $paper_brand
                                , $paper_sort
                                , $paper_name
                                , $paper_dvs
                                , $paper_color
                                , $paper_affil
                                , $paper_size
                                , $crtr_unit);

    $price = sprintf($price_form, $basic_price
                                , $pur_rate
                                , $pur_aplc_price
                                , $pur_price);

    $basisweight_arr[$paper_basisweight] = $paper_basisweight;
    $title_arr[$paper_name][$title] = $title;
    $price_arr[$paper_name][$title][$paper_basisweight] = $price;

    $price_rs->MoveNext();
}

//* 생성된 정보배열 인덱스 정수로 바꿔서 정렬
// 수량배열 정렬
$basisweight_arr = $util->sortDvsArr($basisweight_arr);

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

$info_dvs_arr = array(1 => "제조사",
                      2 => "브랜드",
                      3 => "종이분류",
                      4 => "종이",
                      5 => "계열",
                      6 => "사이즈",
                      7 => "기준단위",
                      8 => "평량");

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
                                   $basisweight_arr,
                                   $price_arr_sort[$sheet_name]);
}

$file_name = uniqid();

$file_path = $excelLib->createExcelFile($file_name);

if (is_file($file_path)) {
    echo "paper_pur_price_list!" . $file_name;
} else {
    echo "FALSE";
}

$conn->Close();
?>
