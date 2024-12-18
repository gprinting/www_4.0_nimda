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
$member_seqno = $fb->form("member_seqno");
$dlvr_way = $fb->form("dlvr_way");
$year = $fb->form("year");
$mon = $fb->form("mon");

$param = array();
$param["sell_site"] = $sell_site;
$param["member_seqno"] = $member_seqno;
$param["dlvr_way"] = $dlvr_way;
$param["year"] = $year;
$param["mon"] = $mon;
$param["dvs"] = "SEQ";
$param["s_num"] = "0";
$param["list_num"] = "99999";

$day = 1;
while(checkdate($mon, $day, $year)) {
    $day++;
}
$param["day"] = $day-1;
$rs = $dao->selectCardpayList($conn, $param);
$input_param = array();
$z = 0;
$i = 0;
$member_seqno = 0;
$param["kind"] = "card or cash";
while($rs && !$rs->EOF) {
    $param["member_seqno"] = $rs->fields["member_seqno"];
    $rs2 = $dao->selectAllPayprice2($conn, $param);
    $object_price = ($rs->fields["pay_price"] + $rs->fields["card_pay_price"]) - $rs->fields["adjust_sales"] - $rs->fields["enuri"];
    if($object_price < 0) $object_price = 0;
    $prev_channel = $rs->fields["sell_channel"];

    $input_param[$z]["no"] = $i; // No.
    $input_param[$z]["sell_channel"] = $rs->fields["sell_channel"]; // No.
    $input_param[$z]["member_name"] = $rs->fields["member_name"]; //
    $input_param[$z]["crn"] = $rs->fields["crn"];
    $input_param[$z]["corp_name"] = $rs->fields["corp_name"];
    $input_param[$z]["tel_num"] = $rs->fields["tel_num"]; // 발급일자
    $input_param[$z]["cell_num"] = $rs->fields["cell_num"]; // 업체명
    $input_param[$z]["all_pay_price"] = $rs->fields["all_pay_price"]; // 사업자 상호
    $input_param[$z]["corp_name"] = $rs->fields["corp_name"];  // 정식회원명
    $input_param[$z]["tel_num"] = $rs->fields["tel_num"];  // 정식회원명
    $input_param[$z]["cell_num"] = $rs->fields["cell_num"];  // 정식회원명
    $input_param[$z]["all_pay_price"] = ($rs2->fields["card_pay_price"] + $rs2->fields["pay_price"] - $rs2->fields["adjust_sales"]);  // 정식회원명
    $input_param[$z]["enuri"] = ($rs2->fields["enuri"]);  // 정식회원명
    $input_param[$z]["pure_pay_price"] = ($rs2->fields["card_pay_price"] + $rs2->fields["pay_price"] - $rs2->fields["adjust_sales"] - $rs2->fields["enuri"]);  // 정식회원명
    $input_param[$z++]["object_price"] = ($rs->fields["card_depo_price"] + $rs->fields["card_pay_price"]);  // 정식회원명

    $i++;
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
$sheet_name = "세금계산서(대기)";
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
    $sheet->setCellValue('A'.$i, 'No.');
    $sheet->setCellValue('B'.$i, '채널');
    $sheet->setCellValue('C'.$i, '별칭회원명');
    $sheet->setCellValue('D'.$i, '사업자번호');
    $sheet->setCellValue('E'.$i, '정식회원명');
    $sheet->setCellValue('F'.$i, '전화번호');
    $sheet->setCellValue('G'.$i, '휴대폰번호');
    $sheet->setCellValue('H'.$i, '총매출');
    $sheet->setCellValue('I'.$i, '에누리');
    $sheet->setCellValue('J'.$i, '순매출');
    $sheet->setCellValue('K'.$i, '승인금액');
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
    $sheet->setCellValue('A'.$i, 'No.');
    $sheet->setCellValue('B'.$i, '채널');
    $sheet->setCellValue('C'.$i, '별칭회원명');
    $sheet->setCellValue('D'.$i, '사업자번호');
    $sheet->setCellValue('E'.$i, '정식회원명');
    $sheet->setCellValue('F'.$i, '전화번호');
    $sheet->setCellValue('G'.$i, '휴대폰번호');
    $sheet->setCellValue('H'.$i, '총매출');
    $sheet->setCellValue('I'.$i, '에누리');
    $sheet->setCellValue('J'.$i, '순매출');
    $sheet->setCellValue('K'.$i, '승인금액');
    //테두리 설정
    $sheet->getStyle('A'.$i.':K'.$i)->applyFromArray($styleArray);
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
        $sheet->setCellValue('A'.$i, $fields["no"]); // 신청일자
        $sheet->setCellValue('B'.$i, $fields["issued_date"]); // 발급일자
        $sheet->setCellValue('C'.$i, $fields["member_name"]); // 업체명
        $sheet->setCellValue('D'.$i, $fields["crn"]); // 사업자상호
        $sheet->setCellValue('E'.$i, $fields["corp_name"]); // 사업자번호
        $sheet->setCellValue('F'.$i, $fields["tel_num"]);
        $sheet->setCellValue('G'.$i, $fields["cell_num"]);
        $sheet->setCellValue('H'.$i, $fields["all_pay_price"]);
        $sheet->setCellValue('I'.$i, $fields["enuri"]);
        $sheet->setCellValue('J'.$i, $fields["pure_pay_price"]);
        $sheet->setCellValue('K'.$i, $fields["object_price"]);
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
