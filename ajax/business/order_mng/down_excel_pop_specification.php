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
include_once(INC_PATH . '/com/nexmotion/job/nimda/business/order_mng/OrderMngDAO.inc');
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel.php'); 
include_once(INC_PATH . '/define/nimda/excel_define.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderMngDAO();
$util = new ErpCommonUtil();
//$excelUtil = new PopSpcExcelUtil();

$fb = $fb->getForm();

$obj_PHPExcel = new PHPExcel();
$obj_PHPExcel->setActiveSheetIndex(0);
$sheet = $obj_PHPExcel->getActiveSheet();

//$conn->debug = 1;

$from         = $fb["from"];
$to           = $fb["to"];
$member_seqno = $fb["member_seqno"];
$office_nick  = $fb["office_nick"];

$param = array();
$param["member_seqno"] = $member_seqno;
$param["from"]         = $from;
$param["to"]           = $to;
$param["office_nick"]  = $office_nick;

// 결과물 검색
$rs = $dao->selectOrderCommon($conn, $param);

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
$sheet_name = "명세서";
makePopSpcExcelSheet($sheet_name, $sheet, $styleArray, $styleColorArr);
makeTopExcelSheet($param, $sheet, $styleArray);
createExcelInner($rs, $sheet, $styleArray);
$file_name = uniqid();

$file_path = createExcelFile($file_name, $obj_PHPExcel);

if (is_file($file_path)) {
    echo "pop_specification!" . $file_name;
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
    $sheet->mergeCells('G'.$i.':K'.$i);
    //열 사이즈 조정
    $sheet->getColumnDimension('A')->setWidth(13);
    $sheet->getColumnDimension('B')->setWidth(13);
    $sheet->getColumnDimension('C')->setWidth(3);
    $sheet->getColumnDimension('D')->setWidth(13);
    //행 사이즈 조정
    $sheet->getRowDimension('2')->setRowHeight(3);
    //엑셀 데이터 상단 필요부분 작성
    $sheet->setCellValue('A'.$i, '거래일자:');
    $sheet->setCellValue('B'.$i, explode(' ',$param['from'])[0]);
    $sheet->setCellValue('C'.$i, '~');
    $sheet->setCellValue('D'.$i, $param['to']);
    $sheet->setCellValue('F'.$i, '업체명:');
    $sheet->setCellValue('G'.$i, $param['office_nick']);
    //테두리 설정 
    $sheet->getStyle('A'.$i.':D'.$i)->applyFromArray($styleArray);
    $sheet->getStyle('F'.$i.':K'.$i)->applyFromArray($styleArray);
    //가운데 정렬(행,열)
    $sheet->getStyle('A'.$i.':F'.$i)
          ->getAlignment()
          ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A'.$i.':F'.$i)
          ->getAlignment()
          ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

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
    $i = 3;

    $sheet_name = strval($sheet_name);
    //셀 병합
    $sheet->mergeCells('C'.$i.':E'.$i);
    $sheet->mergeCells('F'.$i.':K'.$i);
    //엑셀시트명 지정
    $sheet->setTitle($sheet_name);
    //엑셀 데이터 헤드부분
    $sheet->setCellValue('A'.$i, 'No');
    $sheet->setCellValue('B'.$i, '일자');
    $sheet->setCellValue('C'.$i, '제작물 내용');
    $sheet->setCellValue('F'.$i, '상세');
    $sheet->setCellValue('L'.$i, '수량');
    $sheet->setCellValue('M'.$i, '건수');
    $sheet->setCellValue('N'.$i, '공급가');
    $sheet->setCellValue('O'.$i, '차감액');
    $sheet->setCellValue('P'.$i, '합계');
    //테두리 설정 
    $sheet->getStyle('A'.$i.':P'.$i)->applyFromArray($styleArray);
    //가운데 정렬
    $sheet->getStyle('A'.$i.':P'.$i)
          ->getAlignment()
          ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A'.$i.':P'.$i)
          ->getAlignment()
          ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    //볼드처리
    $sheet->getStyle('A'.$i.':P'.$i)->getFont()->setBold(true);
    //색상처리
    $sheet->getStyle('A'.$i.':P'.$i)->applyFromArray($styleColorArr);

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
    $i = 4;
    // 인덱스 넘버링을 위한 변수
    $j = 3;
    while($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $sell_price = doubleval($fields["sell_price"]);
        $sum_sale   = doubleval($fields["grade_sale_price"]) +
                      doubleval($fields["member_sale_price"]);

        //셀 병합
        $sheet->mergeCells('C'.$i.':E'.$i);
        $sheet->mergeCells('F'.$i.':K'.$i);
        //엑셀 데이터 바디부분
        $sheet->setCellValue('A'.$i, $i-$j);
        $sheet->setCellValue('B'.$i, explode(' ', $fields["depo_finish_date"])[0]);
        $sheet->setCellValue('C'.$i, $fields["title"]);
        $sheet->setCellValue('F'.$i, $fields["order_detail"]);
        $sheet->setCellValue('L'.$i, number_format($fields["amt"]));
        $sheet->setCellValue('M'.$i, $fields["count"]);
        $sheet->setCellValue('N'.$i, number_format($sell_price));
        $sheet->setCellValue('O'.$i, number_format($sum_sale));
        $sheet->setCellValue('P'.$i, number_format($sell_price + $sum_sale));
        //테두리 설정
        $sheet->getStyle('A'.$i.':P'.$i)->applyFromArray($styleArray);
        //가운데 정렬
        $sheet->getStyle('A'.$i.':K'.$i)
              ->getAlignment()
              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$i.':P'.$i)
              ->getAlignment()
              ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //오른쪽 정렬
        $sheet->getStyle('L'.$i.':P'.$i)
              ->getAlignment()
              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        //텍스트 줄바꿈
        $sheet->getStyle('F'.$i)->getAlignment()->setWrapText(true);
        //셀 세로 사이즈
        $sheet->getRowDimension($i)->setRowHeight(40);

        $i++;
        $rs->MoveNext();
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
