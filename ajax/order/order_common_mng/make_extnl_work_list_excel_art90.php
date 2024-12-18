<?
//ini_set('display_errors', 'On');
define(INC_PATH, $_SERVER["INC"]);
include_once($_SERVER["DOCUMENT_ROOT"] . "/common/sess_common.php");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel/IOFactory.php');
include_once(INC_PATH . '/com/nexmotion/job/nimda/business/order_mng/OrderCommonMngDAO.inc');
include_once(INC_PATH . "/common_define/common_config.inc");
include_once(INC_PATH . "/common_define/cpn_info_define.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderCommonMngDAO();
$zip = new ZipArchive;

$kind = $fb->form("kind");

// 엑셀관련 초기화
$base_path = $_SERVER["DOCUMENT_ROOT"] . EXCEL_TEMPLATE;
$objPHPExcel = PHPExcel_IOFactory::load($base_path . "extnl_master_template.xlsx");

$sheet = $objPHPExcel->getActiveSheet();

// 엑셀 문서 설정
$sheet->getDefaultStyle()->getFont()->setName("맑은 고딕");
//$sheet->getDefaultStyle()->getFont()->setSize(9);

$param = array();
$param["order_num"] = explode("|", $fb->form("ordernums"));
$rs = $dao->selectExtnlOrder($conn, $param);

$sheet->setCellValue("B3", date('Y-m-d'));

$i = 11;
$files = array();
$record_file_name = array();
while ($rs && !$rs->EOF) {
    $cate_sortcode = $rs->fields['cate_sortcode'];
    $member_name = $rs->fields['member_name'];
    $title = $rs->fields['title'];
    $paper = explode(' / ', $rs->fields['order_detail'])[1];
    $size = explode(' / ', $rs->fields['order_detail'])[2];
    $tmpt = explode(' / ', $rs->fields['order_detail'])[3];
    $binding = explode(' / ', $rs->fields['order_detail'])[4];
    $amt = ($rs->fields['amt'] / 10) * 10;
    $count = $rs->fields['count'];
    $dlvr_info = $rs->fields['zipcode'] . " " .
        $rs->fields['addr'] . " " . $rs->fields['addr_detail'] . " / " .
        $rs->fields['recei'] . " / " . $rs->fields['tel_num'] . " _ " . $rs->fields['cell_num'];
    $count = $rs->fields['count'];
    $param["order_common_seqno"] = $rs->fields['order_common_seqno'];
    $after_detail = $dao->selectOrderAfterInfo2($conn, $param);
    $memo = $rs->fields['work_memo'];

    if ($after_detail == "") $after_detail = "후가공없음";
    $str_amt = $amt;
    if ($count != 1) {
        $str_amt .= ' x ' . $count;
    }
    $tot_tmpt = $rs->fields['tot_tmpt'];
    $dlvr_way = "";
    if ($rs->fields["dlvr_way"] == "01") {
        if($rs->fields["dlvr_sum_way"] == "01") {
            $dlvr_way = "선불택배";
        } else if ($rs->fields["dlvr_sum_way"] == "02") {
            $dlvr_way = "착불택배";
        } else {
            $dlvr_way = "택배";
        }
    } else if ($rs->fields["dlvr_way"] == "02"){
        $dlvr_way = "직배";
    } else if ($rs->fields["dlvr_way"] == "03"){
        $dlvr_way = "화물";
    } else if ($rs->fields["dlvr_way"] == "04"){
        $dlvr_way = "퀵";
    } else if ($rs->fields["dlvr_way"] == "05"){
        $dlvr_way = "퀵";
    } else if ($rs->fields["dlvr_way"] == "06"){
        $dlvr_way = "인현동방문";
    } else if ($rs->fields["dlvr_way"] == "07"){
        $dlvr_way = "성수동방문";
    }

    $file_name = date('md') . '-' .
        $member_name . '-' .
        $title . '-' .
        $paper . '-' .
        $tmpt . '-' .
        $amt . '매-' .
        $count . '건-' .
        $after_detail .
        ".pdf";

    $z = 2;
    while(1) {
        if(!in_array($file_name, $record_file_name)) {
            array_push($record_file_name, $file_name);
            break;
        } else {
            $file_name = date('md') . '-' .
                $member_name . '-' .
                $title . '-' .
                $amt . '매-' .
                $count . '건-' . $z++ .
                ".pdf";
        }
    }

    array_push($files, [
        "path" => "http://file.gprinting.co.kr" . $rs->fields['accept_file_path'],
        "name" => $rs->fields['accept_file_name'],
        "save_name" => $file_name
    ]);

    $sheet->setCellValue("A" . $i, $member_name); // 상단날짜
    $sheet->setCellValue("B" . $i, $title); // 상단날짜
    $sheet->setCellValue("C" . $i, $paper); // 상단날짜
    $sheet->setCellValue("D" . $i, $size); // 상단날짜
    $sheet->setCellValue("E" . $i, $tmpt); // 상단날짜
    $sheet->setCellValue("F" . $i, ""); // 상단날짜
    $sheet->setCellValue("G" . $i, $amt . "권" . " x " . $count . "건"); // 상단날짜
    $sheet->setCellValue("H" . $i, $binding); // 상단날짜
    $sheet->setCellValue("I" . $i, $after_detail); // 상단날짜
    $sheet->setCellValue("J" . $i, $dlvr_way); // 상단날짜
    $sheet->setCellValue("K" . $i++, $memo); // 상단날짜

    $rs->MoveNext();
}



$save_name = uniqid();
$path = $_SERVER["DOCUMENT_ROOT"] . "/down_excel/";

array_push($files, [
    "path" => $path,
    "name" => $save_name. ".xlsx",
    "save_name" => date('md') . ' 당일판(전단) 제작의뢰서.xlsx'
]);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($path . $save_name . ".xlsx");

$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);

if (count($files) > 1) {
    // pdf파일 여러개면 zip으로 묶음
    $zip_path = $_SERVER["DOCUMENT_ROOT"] . "/tmp/" . $save_name . ".zip";

    if ($zip->open($zip_path, ZipArchive::CREATE) !== true) {
        echo "<script>alert('파일생성에 실패했습니다.');</script>";
        exit;
    }

    foreach ($files as $pdf_path) {
        if(preg_match('/^https?\:/', $pdf_path["path"])) {

            // Looks like a URL

            // Generate a file name for including in the zip
            $url_components = explode('/', $pdf_path["path"] . $pdf_path["name"]);
            $file_name = array_pop($url_components);

            // Make sure we only have safe characters in the filename
            $file_name = $pdf_path["save_name"];

            // If all else fails, default to a random filename
            if(empty($file_name)) $file_name = time() . rand(10000, 99999);

            // Make sure we have a .pdf extension
            if(!preg_match('/\.pdf$/', $file_name)) $file_name .= '.pdf';

            // Download file
            $ch = curl_init($pdf_path["path"] . $pdf_path["name"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            $file_content = curl_exec($ch);
            curl_close($ch);

            // Add to zip
            $zip->addFromString($file_name, $file_content);

        }
        //if(!file_exists($pdf_path["path"] . $pdf_path["name"])) {
        //    echo "<script>alert('파일생성에 실패했습니다.(" . $pdf_path["name"] .")');</script>";
        //    exit;
        //}
        else {
            $zip->addFile($pdf_path["path"] . $pdf_path["name"], $pdf_path["save_name"]);
        }
    }

    $zip->close();

    $file_path = $zip_path;
    $file_size = filesize($zip_path);
    $down_file_name = date('md') . ' 당일판(전단) 제작의뢰서.zip';
} else {
    $file_path = $files[0]["path"] . $files[0]["name"];
    $file_size = filesize($file_path);
    $down_file_name = date('md') . ' 당일판(전단) 제작의뢰서.zip';
}



//echo $save_name . ".zip";
$conn->Close();


header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$down_file_name\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
flush();
readfile($file_path);

exit;

/******************************************************************************
 * 엑셀 함수영역
 ******************************************************************************/

/**
 * 셀의 경계선 스타일을 상하좌우 전체 변경 하는 함수
 */
function setCellBorder(&$sheet, $cells) {
    $thick = PHPExcel_Style_Border::BORDER_THIN;

    $style_arr = array();
    $style_arr["borders"]["top"]["style"] = $thick;
    $style_arr["borders"]["bottom"]["style"] = $thick;
    $style_arr["borders"]["left"]["style"] = $thick;
    $style_arr["borders"]["right"]["style"] = $thick;

    $sheet->getStyle($cells)->applyFromArray($style_arr);
}

/**
 * 셀의 문자서식을 숫자형으로 변경하는 함수(1,111,111...)
 */
function setCellNumberFormatting(&$sheet, $cells) {
    $sheet->getStyle($cells)
        ->getNumberFormat()
        ->setFormatCode('₩#,##0;[Red]-₩#,##0');
}

/**
 * 셀의 수평정렬을 설정하는 함수
 *
 * PHPExcel_Style_Alignment::HORIZONTAL_CENTER : 가운데 정렬
 * PHPExcel_Style_Alignment::HORIZONTAL_RIGHT  : 오른쪽 정렬
 */
function setCellHAlign(&$sheet, $cells, $style) {
    $sheet->getStyle($cells)
        ->getAlignment()
        ->setHorizontal($style);
}

/**
 * 셀의 수직정렬을 설정하는 함수
 *
 * PHPExcel_Style_Alignment::VERTICAL_TOP    : 상단 정렬
 * PHPExcel_Style_Alignment::VERTICAL_BOTTOM : 하단 정렬
 * PHPExcel_Style_Alignment::VERTICAL_CENTER : 가운데 정렬
 */
function setCellVAlign(&$sheet, $cells) {
    $sheet->getStyle($cells)
        ->getAlignment()
        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
}

/**
 * 셀의 글자크기 변경
 */
function setCellFontSize(&$sheet, $cells) {
    $sheet->getStyle($cells)
        ->getFont()
        ->setSize(9);
}
?>
