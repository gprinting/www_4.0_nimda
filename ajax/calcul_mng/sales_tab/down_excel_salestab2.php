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

$param = array();
$param["sell_site"] = $sell_site;
$param["member_dvs"] = $member_dvs;
$param["year"] = $year;
$param["mon"] = $mon;
$param["dvs"] = "SEQ";

$rs = $dao->selectPublicStandByListExcel($conn, $param);

$input_param = array();
$z = -1;
$member_seqno = 0;
while($rs && !$rs->EOF) {
    $fields = $rs->fields;
    //셀 병합
    //$sheet->mergeCells('C'.$i.':E'.$i);
    //$sheet->mergeCells('F'.$i.':K'.$i);
    //엑셀 데이터 바디부분
    //$input_param["seq"] = $i-$j;
    $fields["deal_date"] = $year . "-" . $mon;
    if($member_seqno != $fields["member_seqno"]) {
        $all_pay_price = 0; // 매출금액
        $total_depo_price = 0; // 선입금충전(가상계좌)
        $total_card_depo_price = 0; // 선입금충전(카드)
        $total_account_all_pay_price = 0; // 선입금으로 결제한 금액 V
        $total_account_not_issued_pay_price = 0; // 가상계좌로 결제한 금액(아직 미발행) V
        $total_account_issued_pay_price = 0; // 가상계좌로 결제한 금액(발행완료) V
        $total_account_no_issue_pay_price = 0; // 미발행 V
        $total_cash_receipt_pay_price = 0; // 현금영수증 V
        $total_card_pay_price = 0; //카드(홈페이지) V
        $total_card_direct_pay_price = 0; //카드(방문) V
        $adjust_price = 0;
        $z++;

        $member_seqno = $fields["member_seqno"];
    }

    $public_dvs = $rs->fields["public_dvs"];
    $public_state = $rs->fields["public_state"];
    $total_account_all_pay_price += $rs->fields["pay_price"];
    if($public_dvs == "세금계산서") {
        if($public_state == "대기")
            $total_account_not_issued_pay_price = $rs->fields["pay_price"];
        if($public_state == "완료")
            $total_account_issued_pay_price = $rs->fields["pay_price"];
    }

    if($public_dvs == "미발행") {
        $total_account_no_issue_pay_price += $rs->fields["pay_price"];
        $total_card_pay_price += $rs->fields["card_pay_price"]; //카드(홈페이지)
    }

    if($public_dvs == "소득공제" || $public_dvs == "지출증빙") {
        $total_cash_receipt_pay_price += $rs->fields["pay_price"];
    }

    $total_depo_price += $rs->fields["depo_price"];
    $total_card_depo_price += $rs->fields["card_depo_price"];
    /*
    while($rs2 && !$rs2->EOF) {
        $public_dvs = $rs2->fields["public_dvs"];
        $public_state = $rs2->fields["public_state"];
        $pay_price = $rs2->fields["pay_price"];
        $card_pay_price = $rs2->fields["card_pay_price"];
        $depo_price = $rs2->fields["depo_price"];
        $card_depo_price = $rs2->fields["card_depo_price"];
        $adjust_price = $rs2->fields["adjust_price"];

        $all_pay_price += ($pay_price + $card_pay_price);
        $total_depo_price += $depo_price;
        $total_card_depo_price += $card_depo_price;
        $total_account_all_pay_price += $pay_price;
        if($public_dvs == "세금계산서") {
            if($public_state == "대기")
                $total_account_not_issued_pay_price = $rs->fields["pay_price"];
            if($public_state == "완료")
                $total_account_issued_pay_price = $pay_price;
        }

        if($public_dvs == "미발행") {
            $total_account_no_issue_pay_price += $pay_price;
        }

        if($public_dvs == "소득공제" || $public_dvs == "지출증빙") {
            $total_cash_receipt_pay_price += $pay_price;
        }
        $rs2->MoveNext();
    }
*/
    $input_param[$z]["deal_date"] = $year . "-" . $mon; // 신청일자
    $input_param[$z]["issued_date"] = $year . "-" . $mon; // 발급일자
    $input_param[$z]["member_name"] = $fields["member_name"]; // 업체명
    $input_param[$z]["corp_name"] = $fields["corp_name"]; // 사업자 상호
    $input_param[$z]["crn"] = $fields["crn"];  // 사업자번호
    $input_param[$z]["all_pay_price"] = // 총 매출금액 - F
          $total_account_not_issued_pay_price  // 가상계좌 매출
        + $total_account_no_issue_pay_price  // 현금(미발행)
        + $total_cash_receipt_pay_price  // 현금영수증
        + $total_account_issued_pay_price // 세금계산서 선발행금액
        + $total_card_direct_pay_price; // 카드(단말기)
    $input_param[$z]["adjust_price"] = $adjust_price; // 기타 - G
    // H = F - G
    $input_param[$z]["card_depo_price"] = $total_card_depo_price; // 카드 충전금 - I
    $input_param[$z]["depo_price"] = $total_depo_price; // 가상계좌 충전금 - J
    $input_param[$z]["cash_price_issue"] = 0; // 현금결제 - K
    $input_param[$z]["total_cash_receipt_pay_price"] = $total_cash_receipt_pay_price; // 현금영수증 - L
    $input_param[$z]["total_account_issued_pay_price"] = $total_account_issued_pay_price; // 세금계산서 선발행금액 - M
    $input_param[$z]["total_account_no_issue_pay_price"] = $total_account_no_issue_pay_price; // 가상계좌(미발행요청) - N
    $input_param[$z]["total_account_not_issued_pay_price"] = $total_account_not_issued_pay_price; // 가상계좌(아직 미발행)
    $input_param[$z]["total_card_pay_price"] = $total_card_pay_price; // 카드건별결제(웹) - O
    $input_param[$z]["total_card_direct_pay_price"] = $total_card_direct_pay_price; // 카드(단말기) - P
    $input_param[$z]["cash_price_no_issue"] = 0; // 현금결제 - Q
    $input_param[$z]["issue_price"] = $total_account_not_issued_pay_price; // 발급금액 - R

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
    $sheet->setCellValue('A'.$i, '신청일자');
    $sheet->setCellValue('B'.$i, '발급일자');
    $sheet->setCellValue('C'.$i, '업체명');
    $sheet->setCellValue('D'.$i, '사업자상호');
    $sheet->setCellValue('E'.$i, '사업자번호');
    $sheet->setCellValue('F'.$i, '총매출');
    $sheet->setCellValue('G'.$i, '기타');
    $sheet->setCellValue('H'.$i, '순매출');
    $sheet->setCellValue('I'.$i, '충전금(카드)');
    $sheet->setCellValue('J'.$i, '충전금(가상계좌)');
    $sheet->setCellValue('K'.$i, '충전금(현금)');
    $sheet->setCellValue('L'.$i, '현금결제');
    $sheet->setCellValue('M'.$i, '현금영수증');
    $sheet->setCellValue('N'.$i, '세금계산서선발행');
    $sheet->setCellValue('O'.$i, '가상계좌(미발행요청)');
    $sheet->setCellValue('P'.$i, '카드건별결제');
    $sheet->setCellValue('Q'.$i, '카드(단말기)');
    $sheet->setCellValue('R'.$i, '현금(미발행)');
    $sheet->setCellValue('S'.$i, '발급금액');
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
    $sheet->setCellValue('A'.$i, '신청일자');
    $sheet->setCellValue('B'.$i, '발급일자');
    $sheet->setCellValue('C'.$i, '업체명');
    $sheet->setCellValue('D'.$i, '사업자상호');
    $sheet->setCellValue('E'.$i, '사업자번호');
    $sheet->setCellValue('F'.$i, '총매출');
    $sheet->setCellValue('G'.$i, '기타');
    $sheet->setCellValue('H'.$i, '순매출');
    $sheet->setCellValue('I'.$i, '충전금(카드)');
    $sheet->setCellValue('J'.$i, '충전금(가상계좌)');
    $sheet->setCellValue('K'.$i, '충전금(현금)');
    $sheet->setCellValue('L'.$i, '현금결제');
    $sheet->setCellValue('M'.$i, '현금영수증');
    $sheet->setCellValue('N'.$i, '세금계산서선발행');
    $sheet->setCellValue('O'.$i, '가상계좌(미발행요청)');
    $sheet->setCellValue('P'.$i, '카드건별결제');
    $sheet->setCellValue('Q'.$i, '카드(단말기)');
    $sheet->setCellValue('R'.$i, '현금(미발행)');
    $sheet->setCellValue('S'.$i, '발급금액');
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
function createExcelInner($rs, $sheet, $styleArray) {
    // 셀 세로위치
    $i = 2;
    // 인덱스 넘버링을 위한 변수
    $j = 1;
    foreach($rs as $fields) {
        $sheet->setCellValue('A'.$i, $fields["deal_date"]); // 신청일자
        $sheet->setCellValue('B'.$i, $fields["issued_date"]); // 발급일자
        $sheet->setCellValue('C'.$i, $fields["member_name"]); // 업체명
        $sheet->setCellValue('D'.$i, $fields["corp_name"]); // 사업자상호
        $sheet->setCellValue('E'.$i, $fields["crn"]); // 사업자번호
        $sheet->setCellValue('F'.$i, $fields["all_pay_price"]);
        $sheet->setCellValue('G'.$i, $fields["adjust_price"]);
        $sheet->setCellValue('H'.$i, $fields["all_pay_price"] - $fields["adjust_price"]);
        $sheet->setCellValue('I'.$i, $fields["card_depo_price"]);
        $sheet->setCellValue('J'.$i, $fields["depo_price"]);
        $sheet->setCellValue('K'.$i, $fields["cash_price_issue"]);
        $sheet->setCellValue('L'.$i, $fields["total_cash_receipt_pay_price"]);
        $sheet->setCellValue('M'.$i, $fields["total_account_issued_pay_price"]);
        $sheet->setCellValue('N'.$i, $fields["total_account_no_issue_pay_price"]);
        $sheet->setCellValue('O'.$i, $fields["total_card_pay_price"]);
        $sheet->setCellValue('P'.$i, $fields["total_card_direct_pay_price"]);
        $sheet->setCellValue('Q'.$i, $fields["cash_price_no_issue"]);
        $sheet->setCellValue('R'.$i, $fields["issue_price"]);
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
