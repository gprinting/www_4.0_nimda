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
//$sheet->getDefaultStyle()->getFont()->setSize(9);


$base_path = $_SERVER["DOCUMENT_ROOT"] . EXCEL_TEMPLATE;
$objPHPExcel2 = PHPExcel_IOFactory::load($base_path . "extnl_banner_template.xlsx");

$sheet2 = $objPHPExcel2->getActiveSheet();
$sheet2->getDefaultStyle()->getFont()->setName("맑은 고딕");
$sheet2->setCellValue("B3", date('Y-m-d'));

$param = array();
$param["order_num"] = explode("|", $fb->form("ordernums"));
$rs = $dao->selectExtnlOrder($conn, $param);
$i = 5;
$ii = 1;
$files = array();
$record_file_name = array();
while ($rs && !$rs->EOF) {
    $tmp_param = array();
    $tmp_param["order_common_seqno"] = $rs->fields['order_common_seqno'];
    $after_info = $dao->selectOrderAfterInfo3($conn, $tmp_param);

    $member_name = $rs->fields['member_name'];
    $order_regi_date = explode(' ', $rs->fields['order_regi_date']);
    $title = $rs->fields['title'];
    $name = $rs->fields['name'];
    $zipcode = $rs->fields['zipcode'];
    $addr = $rs->fields['addr'] . " " . $rs->fields['addr_detail'];
    $tel_num = $rs->fields['tel_num'];
    $cell_num = $rs->fields['cell_num'];
    $cust_memo = $rs->fields['cust_memo'];

    $cut_size_wid = $rs->fields['cut_size_wid'];
    $cut_size_vert = $rs->fields['cut_size_vert'];
    $paper = explode(' / ', $rs->fields['order_detail'])[1];
    $amt = $rs->fields['amt'];
    $count = $rs->fields['count'];
    $date = date("m") . "월 " . date("d") . "일";
    $product = explode(' / ', $rs->fields['order_detail'])[0];
    $order_num = $rs->fields['order_num'];

    $order_detail = explode(' / ', $rs->fields['order_detail'])[0];
    $size_name = "";
    if($rs->fields["cut_size_wid"] == 390 || $rs->fields["cut_size_wid"] == 400)
        $size_name .= "(대)";
    if($rs->fields["cut_size_wid"] == 340 || $rs->fields["cut_size_wid"] == 350)
        $size_name .= "(중)";
    if($rs->fields["cut_size_wid"] == 290 || $rs->fields["cut_size_wid"] == 300)
        $size_name .= "(소)";

    if($rs->fields["member_seqno"] == 1976) {
        if(strpos($order_detail,"(시트지") === false)
            $order_detail = str_replace("A형 철재입간판 SET","A형 철재입간판 SET(시트지-사각)",$order_detail);
    } else {
        if(strpos($order_detail,"(자석스티커") === false) {
            $order_detail = str_replace("A형 철재입간판 SET", "A형 철재입간판 SET(자석스티커-모양)", $order_detail);
            $order_detail = str_replace("A형 철재입간판 출력물", "A형 철재입간판 출력물(자석스티커-모양)", $order_detail);
            $order_detail = str_replace("(자석스티커-사각)", "", $order_detail);
        }
        $order_detail = str_replace("(자석스티커-사각)", "(자석스티커-모양)", $order_detail);
    }


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

    $file_name = $ii . ". " . date('md') . '-' .
        $title . '-' .
        $order_detail . '-' .
        $size_name . '-' .
        $count . '건' .
        ".pdf";

    $file_name2 = $ii . ". " . date('md') . '-' .
        $title . '-' .
        $order_detail . '-' .
        $size_name . '-' .
        $count . '건-' .
        $dlvr_way .
        ".xlsx";

    $z = 2;
    while(1) {
        if(!in_array($file_name, $record_file_name)) {
            array_push($record_file_name, $file_name);
            break;
        } else {
            $file_name = $ii . ". " . date('md') . '-' .
                $title . '-' .
                explode(' ', $paper)[1] . '-' .
                $size_name . '-' .
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


    $objPHPExcel = PHPExcel_IOFactory::load($base_path . "extnl_ametal_template.xlsx");
    $sheet = $objPHPExcel->getActiveSheet();

// 엑셀 문서 설정
    $sheet->getDefaultStyle()->getFont()->setName("맑은 고딕");
    $sheet->setCellValue("B2", "주문일 : " . date('Y-m-d'));

    $sheet->setCellValue("A5", "1"); // 상단날짜
    $sheet->setCellValue("B5", $member_name); // 상단날짜
    $sheet->setCellValue("C5", $order_regi_date); // 상단날짜
    $sheet->setCellValue("D5", $title); // 상단날짜
    $sheet->setCellValue("E5", $order_detail); // 상단날짜
    $sheet->setCellValue("F5", $amt); // 상단날짜
    $sheet->setCellValue("G5", $count); // 상단날짜
    $sheet->setCellValue("H5", $name); // 상단날짜
    $sheet->setCellValue("I5", $zipcode); // 상단날짜
    $sheet->setCellValue("J5", $addr); // 상단날짜
    $sheet->setCellValue("K5", $tel_num); // 상단날짜
    $sheet->setCellValue("L5", $cell_num); // 상단날짜
    $sheet->setCellValue("M5", $dlvr_way); // 상단날짜
    $sheet->setCellValue("N5", ""); // 상단날짜
    $sheet->setCellValue("O5", $cust_memo); // 상단날짜
    $sheet->setCellValue("P5", $order_num); // 상단날짜
    $sheet->setCellValue("Q5", ""); // 상단날짜

    $save_name = uniqid();
    $path = $_SERVER["DOCUMENT_ROOT"] . "/down_excel/";

    array_push($files, [
        "path" => $path,
        "name" => $save_name. ".xlsx",
        "save_name" => $file_name2
    ]);

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($path . $save_name . ".xlsx");

    $objPHPExcel->disconnectWorksheets();
    unset($objPHPExcel);

    $sheet2->setCellValue("B" . $i, $member_name); // 상단날짜
    $sheet2->setCellValue("F" . $i, $order_detail); // 상단날짜
    $sheet2->setCellValue("G" . $i, $date); // 상단날짜
    $sheet2->setCellValue("I" . $i, $product); // 상단날짜
    $sheet2->setCellValue("L" . $i, $cut_size_wid . "x" . $cut_size_vert); // 상단날짜
    $sheet2->setCellValue("N" . $i, $amt); // 상단날짜
    $sheet2->setCellValue("O" . $i, $title); // 상단날짜
    $sheet2->setCellValue("S" . $i, $rs->fields['pay_price']); // 상단날짜
    $sheet2->setCellValue("U" . $i, "진행"); // 상단날짜
    $sheet2->setCellValue("W" . $i++, $dlvr_way); // 상단날짜

    $stand_rs = $dao->selectStandAfterInfo($conn, $tmp_param);
    while ($stand_rs && !$stand_rs->EOF) {
        $after_name = $stand_rs->fields['after_name'];
        $detail = $stand_rs->fields['detail'];
        $after_price = $stand_rs->fields['price'] * 1.1;
        $price = $rs->fields['pay_price'];
        $sheet2->setCellValue("B" . $i, $member_name); // 상단날짜
        $sheet2->setCellValue("F" . $i, $after_name . " " . $detail); // 상단날짜
        $sheet2->setCellValue("G" . $i, $date); // 상단날짜
        $sheet2->setCellValue("I" . $i, "거치대"); // 상단날짜
        $sheet2->setCellValue("L" . $i, ""); // 상단날짜
        $sheet2->setCellValue("N" . $i, "1"); // 상단날짜
        $sheet2->setCellValue("O" . $i, $title); // 상단날짜
        $sheet2->setCellValue("S" . $i, $after_price); // 상단날짜
        $sheet2->setCellValue("S" . ($i - 1), $price - $after_price); // 상단날짜
        $sheet2->setCellValue("U" . $i, "진행"); // 상단날짜
        $sheet2->setCellValue("W" . $i++, $dlvr_way); // 상단날짜

        $stand_rs->MoveNext();
    }


    $rs->MoveNext();

}

$save_name = uniqid();
$path = $_SERVER["DOCUMENT_ROOT"] . "/down_excel/";

//array_push($files, [
//    "path" => $path,
//    "name" => $save_name. ".xlsx",
//    "save_name" => date('md') . ' A형철재물 제작의뢰서.xlsx'
//]);

$objWriter1 = PHPExcel_IOFactory::createWriter($objPHPExcel2, 'Excel2007');
$objWriter1->save($path . $save_name . ".xlsx");

$objPHPExcel2->disconnectWorksheets();

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
            $file_name = preg_replace('/[^A-z0-9_\.-]/', '', $file_name);

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
    $down_file_name = date('md') . ' A형철재물 제작의뢰서.zip';
} else {
    $file_path = $files[0]["path"] . $files[0]["name"];
    $file_size = filesize($file_path);
    $down_file_name = date('md') . ' A형철재물 제작의뢰서.zip';
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
