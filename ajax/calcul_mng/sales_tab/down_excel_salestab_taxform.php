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

include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
//include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PopSpcExcelUtil.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/calcul_mng/tab/SalesTabListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel.php');
include_once(INC_PATH . '/define/nimda/excel_define.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new SalesTabListDAO();
$util = new ErpCommonUtil();
//$excelUtil = new PopSpcExcelUtil();

//$fb = $fb->getForm();

$obj_PHPExcel = new PHPExcel();
$obj_PHPExcel->setActiveSheetIndex(0);
$sheet = $obj_PHPExcel->getActiveSheet();

$sell_site = $fb->form("sell_site");
$member_dvs = $fb->form("member_dvs");
$year = $fb->form("year");
$mon = $fb->form("mon");

if($sell_site == "전체") $sell_site = "";

$param = array();
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["year"] = $year;
$param["mon"] = $mon;
$param["dvs"] = "SEQ";
$param["s_num"] = "0";
$param["list_num"] = "99999";
$rs = $dao->selectPublicStandByList($conn, $param);

$input_param = array();
$z = 0;
$member_seqno = 0;
while($rs && !$rs->EOF) {
    $fields = $rs->fields;
    //셀 병합
    //$sheet->mergeCells('C'.$i.':E'.$i);
    //$sheet->mergeCells('F'.$i.':K'.$i);
    //엑셀 데이터 바디부분
    //$input_param["seq"] = $i-$j;

    $object_price = $rs->fields["pay_price"] - $rs->fields["adjust_sales"] - $rs->fields["enuri"];
    // 20231129 세금계산서 수정 발급을 위한 체크 
    if($rs->fields['change_price'] != 0 ) {
        $object_price = $rs->fields['change_price'];
    }  
    
    if($object_price < 0) $object_price = 0;

    $supply_price = ceil($object_price / 1.1);
    $tax_price = $object_price - $supply_price;
    $input_param[$z]["year"] = $year;
    $input_param[$z]["month"] = $mon;
    $input_param[$z]["day"] = date("t", strtotime($year . "-" . $mon . "-01"));
    $input_param[$z]["corp_name"] = $rs->fields["corp_name"];
    $input_param[$z]["crn"] = $rs->fields["crn"];
    $input_param[$z]["object_price"] = $supply_price;
    $input_param[$z++]["tax"] = $tax_price;


    //$input_param[$z]["memo"] = $dao->selectPackageCount($conn, $fields["bun_dlvr_order_num"], $fields["order_common_seqno"], $dlvr_dvs, $fields["member_seqno"]);
    //$rs2 = $dao->selectOrdererInfo($conn, $fields["order_common_seqno"]);
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
createExcelInner($input_param, $sheet, $styleArray);
$file_name = uniqid();

$file_path = createExcelFile($file_name, $obj_PHPExcel);

if (is_file($file_path)) {
    echo "salestab!" . $file_name;
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
    //$sheet->getColumnDimension('A')->setWidth(13);
    //$sheet->getColumnDimension('B')->setWidth(13);
    //$sheet->getColumnDimension('C')->setWidth(3);
    //$sheet->getColumnDimension('D')->setWidth(13);
    //엑셀 데이터 상단 필요부분 작성
    $sheet->setCellValue('A'.$i, '년도');
    $sheet->setCellValue('B'.$i, '월');
    $sheet->setCellValue('C'.$i, '일');
    $sheet->setCellValue('D'.$i, '매입매출구분(1-매출/2-매입)');
    $sheet->setCellValue('E'.$i, '과세유형');
    $sheet->setCellValue('F'.$i, '불공제사유');
    $sheet->setCellValue('G'.$i, '신용카드거래처코드');
    $sheet->setCellValue('H'.$i, '신용카드사명');
    $sheet->setCellValue('I'.$i, '신용카드(가맹점)번호');
    $sheet->setCellValue('J'.$i, '거래처명');
    $sheet->setCellValue('K'.$i, '사업자(주민)번호');
    $sheet->setCellValue('L'.$i, '공급가액');
    $sheet->setCellValue('M'.$i, '부가세');
    $sheet->setCellValue('N'.$i, '품명');
    $sheet->setCellValue('O'.$i, '전자세금(1전자)');
    $sheet->setCellValue('P'.$i, '기본계정');
    $sheet->setCellValue('Q'.$i, '상대계정');
    $sheet->setCellValue('R'.$i, '현금영수증승인번호');
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
    ////엑셀시트명 지정
    $sheet->setTitle($sheet_name);
    //엑셀 데이터 헤드부분
    $sheet->setCellValue('A'.$i, '년도');
    $sheet->setCellValue('B'.$i, '월');
    $sheet->setCellValue('C'.$i, '일');
    $sheet->setCellValue('D'.$i, '매입매출구분(1-매출/2-매입)');
    $sheet->setCellValue('E'.$i, '과세유형');
    $sheet->setCellValue('F'.$i, '불공제사유');
    $sheet->setCellValue('G'.$i, '신용카드거래처코드');
    $sheet->setCellValue('H'.$i, '신용카드사명');
    $sheet->setCellValue('I'.$i, '신용카드(가맹점)번호');
    $sheet->setCellValue('J'.$i, '거래처명');
    $sheet->setCellValue('K'.$i, '사업자(주민)번호');
    $sheet->setCellValue('L'.$i, '공급가액');
    $sheet->setCellValue('M'.$i, '부가세');
    $sheet->setCellValue('N'.$i, '품명');
    $sheet->setCellValue('O'.$i, '전자세금(1전자)');
    $sheet->setCellValue('P'.$i, '기본계정');
    $sheet->setCellValue('Q'.$i, '상대계정');
    $sheet->setCellValue('R'.$i, '현금영수증승인번호');
    //테두리 설정
    $sheet->getStyle('A'.$i.':R'.$i)->applyFromArray($styleArray);
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
function createExcelInner($rs, $sheet, $styleArray) {
    // 셀 세로위치
    $i = 2;
    // 인덱스 넘버링을 위한 변수
    $j = 1;
    foreach($rs as $fields) {
        $sheet->setCellValue('A'.$i, $fields["year"]); // 신청일자
        $sheet->setCellValue('B'.$i, $fields["month"]); // 발급일자
        $sheet->setCellValue('C'.$i, $fields["day"]); // 업체명
        $sheet->setCellValue('D'.$i, "1"); // 사업자상호
        $sheet->setCellValue('E'.$i, "11"); // 사업자번호
        $sheet->setCellValue('F'.$i, "");
        $sheet->setCellValue('G'.$i, "");
        $sheet->setCellValue('H'.$i, "");
        $sheet->setCellValue('I'.$i, "");
        $sheet->setCellValue('J'.$i, $fields["corp_name"]);
        $sheet->setCellValue('K'.$i, $fields["crn"]);
        $sheet->setCellValue('L'.$i, $fields["object_price"]);
        $sheet->setCellValue('M'.$i, $fields["tax"]);
        $sheet->setCellValue('N'.$i, "인쇄비");
        $sheet->setCellValue('O'.$i, "0");
        $sheet->setCellValue('P'.$i, "40400");
        $sheet->setCellValue('Q'.$i, "10800");
        $sheet->setCellValue('R'.$i, "");
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

?>
