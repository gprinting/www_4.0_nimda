<?
define("INC_PATH", $_SERVER["INC"]);
/*
 * Copyright (c) 2017 Nexmotion, Inc.
 * All rights reserved.
 *
 * REVISION HISTORY (reverse chronological order)
 *============================================================================
 * 2017/06/12 이청산 작성
 *============================================================================
 *
 */

//ini_set('display_errors', 1);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
//include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PopSpcExcelUtil.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/storage_mng/StorageMngDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel.php');
include_once(INC_PATH . '/define/nimda/excel_define.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new StorageMngDAO();
$util = new ErpCommonUtil();
//$excelUtil = new PopSpcExcelUtil();

//$fb = $fb->getForm();

$obj_PHPExcel = new PHPExcel();
$obj_PHPExcel->setActiveSheetIndex(0);
$sheet = $obj_PHPExcel->getActiveSheet();

//$conn->debug = 1;
$param = array();
$param["dvs"] = "SEQ";
$param["dlvr_way"] = "01";
$param["from"] = $fb->form("date_from");
$param["to"] = $fb->form("date_to");
$param["member_name"] = $fb->form("member_name");
$param["title"] = $fb->form("title");
$param['dlvr_dvs'] = $fb->form("dlvr_dvs");
$param['theday_yn'] = $fb->form("theday_yn");
//$param["dlvr_way"] = $fb->form("dlvr_way");
$param["dlvr_way_detail"] = $fb->form("dlvr_way_detail");
$param["state"] = $fb->form("state");

//$conn->debug = 1;
$rs = $dao->selectDeliveryList($conn, $param);

$input_param = array();
$z = 0;
while($rs && !$rs->EOF) {
    $fields = $rs->fields;
    //셀 병합
    //$sheet->mergeCells('C'.$i.':E'.$i);
    //$sheet->mergeCells('F'.$i.':K'.$i);
    //엑셀 데이터 바디부분
    //$input_param["seq"] = $i-$j;
    $weight = $fields['expec_weight'];
    $dlvr_dvs = $fields['dlvr_dvs'];
    if($fields['cate_sortcode'] == "003001001" || $fields['cate_sortcode'] == "003002001" || $fields['cate_sortcode'] == "003003001")
        $dlvr_dvs = "namecard";
    $box_count = 0;
    if($dlvr_dvs == "leaflet") {
        $box_count = ceil(($weight / $fields["count"]) / 22);
        $box_count *= $fields["count"];
    } else {
        $box_count = ceil($weight / 12);
    }

    if($dlvr_dvs == "leaflet") {
        $part_price = 0;

        $weight_degree = 17;
        $price_degree = 3650;
        if(($fields["cate_paper_mpcode"] == "243"
                || $fields["cate_paper_mpcode"] == "248") && strpos($fields["stan_name"], 'A') !== false) {
            $weight_degree = 12;
            $price_degree = 2450;
        }

        if(strpos($fields["cate_sortcode"], '006') !== false) {
            $weight_degree = 12;
            $price_degree = 2450;
        }

        $part_price = 0;
        $tmp_BoxCount = (int)($weight / $weight_degree) + 1;
        $weight_price = $price_degree;

    } else if($dlvr_dvs == "namecard") {
        $part_price = 0;
        if ($weight <= 10) {
            $tmp_BoxCount = 1;
            $price_degree = 2000;
        } else if ($weight <= 12) {
            $tmp_BoxCount = 1;
            $price_degree = 2450;
        } else {
            $tmp_BoxCount = (int)($weight / 12);
            $part_weight = $weight % 12;
            $price_degree = 2450;
            if ($part_weight <= 10) {
                $part_price = 2000;
            } else {
                $part_price = 2450;
            }
        }

        $weight_price = $price_degree;
        if ($part_price != 0) $tmp_BoxCount++;
    }

    $box_count = $tmp_BoxCount;

    $tmp_order_details = explode('+', $fields["order_detail"]);
    $tmp_cut_sizes = explode('+', $fields["cut_size"]);
    $tmp_cate_sortcodes = explode('+', $fields["cate_sortcode"]);
    $order_detail = "";
    $j = 0;
    foreach ($tmp_order_details as $tmp_order_detail) {
        $sortcode = $tmp_cate_sortcodes[$j];
        if($j == 0) {
            
            $tmp_detail = explode(' ',explode('/',$tmp_order_detail)[1])[1];
            //20231220 오류 수정1
            if($tmp_detail == "상"){
                $tmp_detail =  $tmp_order_detail;
            }
            if($sortcode == "003001001")
                $tmp_detail = str_replace('스노우지','', $tmp_detail);
            if($sortcode == "003002001") {
                if(explode(' ',explode('/',$tmp_order_detail)[1])[2] == "골드")
                    $tmp_detail = str_replace('스타드림','스타골드', $tmp_detail);
                if(explode(' ',explode('/',$tmp_order_detail)[1])[2] == "실버")
                    $tmp_detail = str_replace('스타드림','스타실버', $tmp_detail);
            }
            $order_detail = $tmp_detail;
        } else {
            $tmp_detail = explode(' ',explode('/',$tmp_order_detail)[1])[1];
            if($sortcode == "003001001")
                $tmp_detail = str_replace('스노우지','', $tmp_detail);
            if($sortcode == "003002001") {
                if(explode(' ',explode('/',$tmp_order_detail)[1])[2] == "골드")
                    $tmp_detail = str_replace('스타드림','스타골드', $tmp_detail);
                if(explode(' ',explode('/',$tmp_order_detail)[1])[2] == "실버")
                    $tmp_detail = str_replace('스타드림','스타실버', $tmp_detail);
            }
            $order_detail .= " + " . $tmp_detail;
        }

        if(startsWith($sortcode, "003")) {
            if($sortcode == "003001001") {
                if (strpos(explode('/', $tmp_order_detail)[1], '무광코팅') !== false) {
                    $order_detail .= "코팅명함";
                } else {
                    $order_detail .= "무코팅명함";
                }
            } else {
                $order_detail .= "명함";
            }
        }

        if(startsWith($sortcode, "004")) {
            if(startsWith($sortcode, "004003"))
                $order_detail .= "도무송스티커";
            else
                $order_detail .= "사각재단스티커";
        }

        if(startsWith($sortcode, "005") // 합판전단
            || startsWith($sortcode, "001") // 리플렛
            || startsWith($sortcode, "006") // 봉투
            || startsWith($sortcode, "011")) { // 디지털전단
            $tmp_str = explode(' / ',$tmp_order_detail)[1];
            $gg = explode(' ',$tmp_str)[2];
            $order_detail .= " " . $gg;
        }

        if(startsWith($sortcode, "006")) {
            $order_detail .= "봉투";
        }
        $order_detail .= "/(" . $tmp_cut_sizes[$j] . ")";

        $j++;
    }

    $temp["order_common_seqno"] = $fields["order_common_seqno"];
    $input_param[$z]["name"] = $fields["d_recei"];
    $input_param[$z]["zipcode"] = "";
    $input_param[$z]["address"] = $fields["d_addr"] . " " . $fields["d_addr_detail"];
    $input_param[$z]["dlvr_req"] = $fields["d_dlvr_req"];
    $d_tel_num = $fields["d_tel_num"];
    $d_cell_num = $fields["d_cell_num"];

    if(explode('-', $d_tel_num)[1] == '')
        $d_tel_num = $fields["d_cell_num"];

    if(explode('-', $d_cell_num)[1] == '')
        $d_cell_num = $fields["d_tel_num"];

    $input_param[$z]["tel_num"] = $d_tel_num;
    $input_param[$z]["cell_num"] = $d_cell_num;
    $input_param[$z]["count"] = $box_count;
    $input_param[$z]["dlvr_price"] = $weight_price;
    $input_param[$z]["dlvr_sum_way"] = $fields["dlvr_sum_way"] == "01" ? "3" : "2";
    $input_param[$z]["title"] = $fields["title"];
    //$input_param[$z]["memo"] = $dao->selectPackageCount($conn, $fields["bun_dlvr_order_num"], $fields["order_common_seqno"], $dlvr_dvs, $fields["member_seqno"]);
    $input_param[$z]["memo"] = $fields["memo"];
    $rs2 = $dao->selectOrdererInfo($conn, $fields["order_common_seqno"]);
    $input_param[$z]["order_name"] = $rs2->fields['name'];
    $input_param[$z]["order_address"] = $rs2->fields['addr'] . " " . $rs2->fields['addr_detail'];
    $input_param[$z]["order_cell"] = $rs2->fields['tel_num'];
    $input_param[$z]["dlvr_way"] = "택배(" . ($fields["dlvr_sum_way"] == "01" ? "선불" : "착불") . ")";
    $input_param[$z]["seqnos"] = explode(',' ,$fields["seqnos"]);
    $input_param[$z]["order_details"] = explode(' + ' ,$order_detail);
    //$strstr = $dao->selectOrderAfterInfoForParsel($conn, $temp);
    $input_param[$z]["dlvr_req"] = $fields["d_dlvr_req"];
    $input_param[$z++]["order_num"] = $fields["order_num"];
    //테두리 설정
    //$sheet->getStyle('A'.$i.':P'.$i)->applyFromArray($styleArray);
    ////가운데 정렬
    //$sheet->getStyle('A'.$i.':K'.$i)
    //    ->getAlignment()
    //    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    //$sheet->getStyle('A'.$i.':K'.$i)
    //    ->getAlignment()
    //    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    ////오른쪽 정렬
    //$sheet->getStyle('L'.$i.':P'.$i)
    //    ->getAlignment()
    //    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    ////텍스트 줄바꿈
    //$sheet->getStyle('F' . $i)->getAlignment()->setWrapText(true);

    $rs->MoveNext();
}




// 엑셀 스타일 테두리
$styleArray = array(
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    )
);
// 엑셀 스타일 컬러
$styleColorArr = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FFFF00')
    )
);
$sheet_name = "택배송장 리스트";
makePopSpcExcelSheet($sheet_name, $sheet, $styleArray, $styleColorArr);
//makeTopExcelSheet($param, $sheet, $styleArray);
createExcelInner($input_param, $sheet, $styleArray, $dao, $conn);
$file_name = uniqid();

$file_path = createExcelFile($file_name, $obj_PHPExcel);

if (is_file($file_path)) {
    echo "delivery!" . $file_name;
} else {
    echo "FALSE";
}

$conn->Close();
exit;

NOT_PRICE:
$conn->Close();
echo "NOT_PRICE";
exit;

/******************************************************************************
함수 영역
 *****************************************************************************/
/**
 * @brief 엑셀상단부분 생성
 *
 * @detail 엑셀상단 내용을 입력한다.
 *
 * @param $param       = html에서 넘겨받은 파라미터
 */
function makeTopExcelSheet($param, $sheet, $styleArray) {
    //셀 위치(A1, B1, ...)
    $i = 1;
    //셀 병합
    //$sheet->mergeCells('G'.$i.':K'.$i);
    ////열 사이즈 조정
    $sheet->getColumnDimension('A')->setWidth(50);
    //$sheet->getColumnDimension('B')->setWidth(13);
    //$sheet->getColumnDimension('C')->setWidth(3);
    //$sheet->getColumnDimension('D')->setWidth(13);
    //엑셀 데이터 상단 필요부분 작성
    $sheet->setCellValue('A'.$i, '받으실분');
    $sheet->setCellValue('B'.$i, '우편번호');
    $sheet->setCellValue('C'.$i, '주소');
    $sheet->setCellValue('D'.$i, '전화번호');
    $sheet->setCellValue('E'.$i, '핸드폰번호');
    $sheet->setCellValue('F'.$i, '수량');
    $sheet->setCellValue('G'.$i, '금액');
    $sheet->setCellValue('H'.$i, '선착불');
    $sheet->setCellValue('I'.$i, '제작물내용');
    $sheet->setCellValue('J'.$i, '메모');
    $sheet->setCellValue('K'.$i, '주문자정보');
    $sheet->setCellValue('L'.$i, '주문자주소');
    $sheet->setCellValue('M'.$i, '주문자전화번호');
    $sheet->setCellValue('N'.$i, '배송방법');
    $sheet->setCellValue('O'.$i, '주문내용');
    $sheet->setCellValue('P'.$i, '배송메시지');
    $sheet->setCellValue('Q'.$i, '주문번호');
    $sheet->setCellValue('R'.$i, '생산번호');
    //테두리 설정
    //$sheet->getStyle('A'.$i.':D'.$i)->applyFromArray($styleArray);
    //$sheet->getStyle('F'.$i.':K'.$i)->applyFromArray($styleArray);
    ////가운데 정렬
    //$sheet->getStyle('A'.$i.':F'.$i)
    //    ->getAlignment()
    //    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    //$sheet->getStyle('A'.$i.':F'.$i)
    //    ->getAlignment()
    //    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
}

/**
 * @brief 엑셀시트명지정, 셀헤드부분 생성
 *
 * @detail 엑셀시트와 상단 기본내용을 입력한다.
 *
 * @param $sheet_name       = 엑셀파일 워크시트명
 * @param $obj_PHPExcel     = PHPExcel 오브젝트
 * @param $styleArray       = 엑셀 테두리 설정
 * @param $styleColorArr    = 엑셀 컬러 설정
 */
function makePopSpcExcelSheet($sheet_name, $sheet, $styleArray, $styleColorArr) {
    //셀의 위치
    $i = 1;

    $sheet_name = strval($sheet_name);
    //셀 병합
    //$sheet->mergeCells('C'.$i.':E'.$i);
    //$sheet->mergeCells('F'.$i.':K'.$i);
    $sheet->getColumnDimension('A')->setWidth(40);
    $sheet->getColumnDimension('C')->setWidth(60);
    $sheet->getColumnDimension('D')->setWidth(15);
    $sheet->getColumnDimension('E')->setWidth(15);
    $sheet->getColumnDimension('I')->setWidth(25);
    $sheet->getColumnDimension('J')->setWidth(25);
    $sheet->getColumnDimension('K')->setWidth(25);
    $sheet->getColumnDimension('L')->setWidth(60);
    $sheet->getColumnDimension('M')->setWidth(15);
    $sheet->getColumnDimension('N')->setWidth(15);
    $sheet->getColumnDimension('O')->setWidth(30);
    $sheet->getColumnDimension('P')->setWidth(25);
    $sheet->getColumnDimension('Q')->setWidth(20);
    $sheet->getColumnDimension('R')->setWidth(20);

    ////엑셀시트명 지정
    $sheet->setTitle($sheet_name);
    //엑셀 데이터 헤드부분
    $sheet->setCellValue('A'.$i, '받으실분');
    $sheet->setCellValue('B'.$i, '우편번호');
    $sheet->setCellValue('C'.$i, '주소');
    $sheet->setCellValue('D'.$i, '전화번호');
    $sheet->setCellValue('E'.$i, '핸드폰번호');
    $sheet->setCellValue('F'.$i, '수량');
    $sheet->setCellValue('G'.$i, '금액');
    $sheet->setCellValue('H'.$i, '선착불');
    $sheet->setCellValue('I'.$i, '제작물내용');
    $sheet->setCellValue('J'.$i, '메모');
    $sheet->setCellValue('K'.$i, '주문자정보');
    $sheet->setCellValue('L'.$i, '주문자주소');
    $sheet->setCellValue('M'.$i, '주문자전화번호');
    $sheet->setCellValue('N'.$i, '배송방법');
    $sheet->setCellValue('O'.$i, '주문내용');
    $sheet->setCellValue('P'.$i, '배송메시지');
    $sheet->setCellValue('Q'.$i, '주문번호');
    $sheet->setCellValue('R'.$i, '생산번호');

    //테두리 설정
    $sheet->getStyle('A'.$i.':S'.$i)->applyFromArray($styleArray);
    ////가운데 정렬
    //$sheet->getStyle('A'.$i.':Q'.$i)
    //    ->getAlignment()
    //    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    //$sheet->getStyle('A'.$i.':Q'.$i)
    //    ->getAlignment()
    //    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    ////볼드처리
    //$sheet->getStyle('A'.$i.':Q'.$i)->getFont()->setBold(true);
    ////색상처리
    //$sheet->getStyle('A'.$i.':Q'.$i)->applyFromArray($styleColorArr);
}

/**
 * @brief 엑셀내용 생성
 *
 * @param $rs = 쿼리 결과 내용
 *
 * @return 엑셀 내용
 */
function createExcelInner($rs, $sheet, $styleArray, $dao, $conn) {
    // 셀 세로위치
    $i = 2;
    // 인덱스 넘버링을 위한 변수
    $j = 1;
    foreach($rs as $fields) {
        $sheet->setCellValue('A'.$i, $fields["name"]);
        $sheet->setCellValue('B'.$i, $fields["zipcode"]);
        $sheet->setCellValue('C'.$i, $fields["address"]);
        $sheet->setCellValue('D'.$i, $fields["tel_num"]);
        $sheet->setCellValue('E'.$i, $fields["cell_num"]);
        $sheet->setCellValue('F'.$i, $fields["count"]);
        $sheet->setCellValue('G'.$i, $fields["dlvr_price"]);
        $sheet->setCellValue('H'.$i, $fields["dlvr_sum_way"]);
        $sheet->setCellValue('I'.$i, $fields["title"]);
        $sheet->setCellValue('J'.$i, $fields["memo"]);
        $sheet->setCellValue('K'.$i, $fields["order_name"]);
        $sheet->setCellValue('L'.$i, $fields["order_address"]);
        $sheet->setCellValue('M'.$i, $fields["order_cell"]);
        $sheet->setCellValue('N'.$i, $fields["dlvr_way"]);

        $objRichText = new PHPExcel_RichText();
        $seqnos = $fields["seqnos"];
        $order_details = $fields["order_details"];
        for($t = 0; $t < count($seqnos); $t++) {
            $temp["order_common_seqno"] = $seqnos[$t];
            $strstr = $dao->selectOrderAfterInfoForParsel($conn, $temp);
            $fields["order_detail"] .= ($order_details[$t] . $strstr);

            $objRichText->createText($order_details[$t]);

            if($strstr != "") {
                $objBlue = $objRichText->createTextRun("[후가공 : " . $strstr . "]");
                $objBlue->getFont()->setColor(new PHPExcel_Style_Color(PHPExcel_Style_Color::COLOR_BLUE));
            }
            //$sheet->setCellValue('O'.$i,$objRichText);

            if($t < count($seqnos) - 1)
                $objRichText->createText(" + ");
        }
        $sheet->getCell('O' . $i)->setValue($fields["order_detail"]);
        //$sheet->setCellValue('O'.$i, $fields["order_detail"]);
        $sheet->setCellValue('P'.$i, $fields["dlvr_req"]);
        $sheet->setCellValue('Q'.$i, $fields["order_num"]);
        $sheet->setCellValue('R'.$i, $fields["order_num"]);

        if($fields["after_detail"] != "") {
            $sheet->getStyle('P' . $i)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => '0099FF')
                    )
                )
            );
        }

        //테두리 설정
        //$sheet->getStyle('A'.$i.':P'.$i)->applyFromArray($styleArray);
        ////가운데 정렬
        //$sheet->getStyle('A'.$i.':K'.$i)
        //    ->getAlignment()
        //    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        //$sheet->getStyle('A'.$i.':K'.$i)
        //    ->getAlignment()
        //    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        ////오른쪽 정렬
        //$sheet->getStyle('L'.$i.':P'.$i)
        //    ->getAlignment()
        //    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        ////텍스트 줄바꿈
        //$sheet->getStyle('F' . $i)->getAlignment()->setWrapText(true);

        $i++;
    }
}

/**
 * @brief 엑셀파일 생성
 *
 * @param $file_name = 생성할 파일이름
 *
 * @return 엑셀파일 경로
 */
function createExcelFile($file_name, $obj_PHPExcel) {
    $file_path = DOWNLOAD_PATH . '/' . $file_name . ".xlsx";

    $obj_writer = new PHPExcel_Writer_Excel2007($obj_PHPExcel);
    $obj_writer->setPreCalculateFormulas(false);
    $obj_writer->save($file_path);

    return $file_path;
}

function startsWith($haystack, $needle){
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

?>
