<?
define("INC_PATH", $_SERVER["INC"]);
/**
 * @file excel_download_price_regi_modi.inc
 *
 * @brief 엑셀 다운로드를 처리
 */

include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/ErpCommonUtil.inc');
include_once(INC_PATH . '/define/nimda/excel_define.inc');

// 세션체크 필요함

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$util = new ErpCommonUtil();

$name      = $_GET["name"];
$mono_yn   = $_GET["mono_yn"];
$sell_site = $_GET["sell_site"];
$cate_name = $_GET["cate_name"];
$etprs_dvs = $_GET["etprs_dvs"];
$file_dvs  = $_GET["file_dvs"];

if (empty($sell_site) === false) {
    // 판매채널 검색이 필요한 경우에만

    $sell_site = $conn->qstr($sell_site, get_magic_quotes_gpc());

    // 판매채널명 검색
    $query = "SELECT sell_site FROM cpn_admin WHERE cpn_admin_seqno = %s";
    $query = sprintf($query, $sell_site);

    $rs = $conn->Execute($query);

    if ($rs->EOF) {
        echo "<script>alert('존재하지않는 판매채널입니다.');</script>";
        exit;
    }

    $sell_site = $rs->fields["sell_site"];
}
$conn->Close();

$down_file_name = null;

switch ($file_dvs) {
    case "prdt_price_list" :
        $mono_yn = ($mono_yn === "0") ? "확정형" : "계산형";
        $etprs_dvs = ($etprs_dvs === "new") ? "신규" : "기존";
        $down_file_name = $sell_site . '_' .
                          $cate_name . '_' .
                          $mono_yn   . '_' .
                          $etprs_dvs. ".xlsx"; 
        break;
    case "aft_sell_price_list" :
        $down_file_name = "상품_후공정_판매가격.xlsx"; 
        break;
    case "opt_sell_price_list" :
        $down_file_name = "상품_옵션_판매가격.xlsx"; 
        break;
    case "paper_sell_price_list" :
        $down_file_name = "상품_종이_판매가격.xlsx"; 
        break;
    case "output_sell_price_list" :
        $down_file_name = "상품_출력_판매가격.xlsx"; 
        break;
    case "print_sell_price_list" :
        $down_file_name = "상품_인쇄_판매가격.xlsx"; 
        break;
    case "amt_paper_sale_price_list" :
        $down_file_name = "수량별_종이_할인가격.xlsx"; 
        break;
    case "paper_pur_price_list" :
        $down_file_name = "종이_매입가격.xlsx"; 
        break;
    case "output_pur_price_list" :
        $down_file_name = "출력_매입가격.xlsx"; 
        break;
    case "print_pur_price_list" :
        $down_file_name = "인쇄_매입가격.xlsx"; 
        break;
    case "after_pur_price_list" :
        $down_file_name = "후공정_매입가격.xlsx"; 
        break;
    case "opt_pur_price_list" :
        $down_file_name = "옵션_매입가격.xlsx"; 
        break;
    case "cashreceipt" :
        $down_file_name = date('m') . "월_현금영수증양식.xlsx"; 
        break;
    case "pub_comp" :
        $down_file_name = date('m') . "월_세금계산서양식.xlsx";
        break;
    case "pub_exce" :
        $down_file_name = date('m') . "월_예외처리세금계산서양식.xlsx"; 
        break;
    case "grade_sale_price_list" :
        $mono_yn = ($mono_yn === "0") ? "확정형" : "계산형";
        $etprs_dvs = ($etprs_dvs === "new") ? "신규" : "기존";
        $down_file_name = $sell_site . '_' .
                          $cate_name . '_' .
                          $mono_yn   . '_' .
                          $etprs_dvs. "_등급할인가격.xlsx"; 
        break;
    case "member_sale_price_list" :
        $down_file_name = "회원_수량별_할인.xlsx";
        break;
    case "aft_member_sale_price_list" :
        $down_file_name = "후공정_회원_수량별_할인.xlsx";
        break;
    case "pop_specification" :
        $down_file_name = "명세서_출력.xlsx";
        break;
    case "delivery" :
        $down_file_name = "택배송장 리스트.xlsx";
        break;
    case "salestab" :
        $down_file_name = "세금계산서 대기 리스트.xlsx";
        break;
    default :
        $down_file_name = "이름_미지정.xlsx";
        break;
}

$file_path = DOWNLOAD_PATH . '/' . $name . ".xlsx";
if (!is_file($file_path)) {
    echo "<script>alert('가격 엑셀이 존재하지 않습니다.');</script>";
    exit;
}

$file_size = filesize($file_path);
if ($util->isIe()) {
    $down_file_name = $util->utf2euc($down_file_name);
}

header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$down_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

flush();
readfile($file_path);

unlink($file_path);
?>
