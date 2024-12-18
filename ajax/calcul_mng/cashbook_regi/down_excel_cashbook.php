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

include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/calcul_mng/cashbook/CashbookRegiDAO.inc');
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel.php');
include_once(INC_PATH . '/define/nimda/excel_define.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$fb = new FormBean();
$cashbookDAO = new CashbookRegiDAO();
//$excelUtil = new PopSpcExcelUtil();

//$fb = $fb->getForm();

$obj_PHPExcel = new PHPExcel();
$obj_PHPExcel->setActiveSheetIndex(0);
$sheet = $obj_PHPExcel->getActiveSheet();

$param = array();
//판매채널 일련번호
$param["cpn_admin_seqno"] = $fb->form("search_sell_site");
//수입지출 구분
$param["dvs"] = $fb->form("search_dvs");
//적요
$param["sumup"] = $fb->form("search_sumup");
//계정 과목 일련번호
$param["acc_subject"] = $fb->form("acc_subject");
//계정 과목 상세 일련번호
$param["acc_subject_detail"] = $fb->form("acc_subject_detail");
//입출금경로
$param["depo_withdraw_path"] = $fb->form("search_path");
//증빙 시작 일자
$param["date_from"] = $fb->form("date_from");
//증빙 종료 일자
$param["date_to"] = $fb->form("date_to");
if($param["date_to"] != "") {
    //$param["date_to"] .= " 23:59:59";
}
//팀 일련번호
$param["depar_admin_seqno"] = $fb->form("search_depar_list");
//제조사 일련번호
$param["extnl_etprs_seqno"] = $fb->form("etprs_seqno");
//회원 일련번호
$param["member_seqno"] = $fb->form("member_seqno");

$param["start"] = "0";
$param["end"] = "99999";

$result = $cashbookDAO->selectCashbookList($conn, $param);

$input_param = array();
$z = 0;
$i = 0;
$member_seqno = 0;
while($result && !$result->EOF) {
    $acc_subject = $result->fields["acc_subject"];
    $acc_detail = $result->fields["detail"];
    $income_price = number_format($result->fields["income_price"]);
    $expen_price = number_format($result->fields["expen_price"]);
    $trsf_income_price = number_format($result->fields["trsf_income_price"]);
    $trsf_expen_price = number_format($result->fields["trsf_expen_price"]);
    $sumup = $result->fields["sumup"];
    $depo_path = $result->fields["depo_withdraw_path"];
    $depo_path_detail = $result->fields["depo_withdraw_path_detail"];
    $path = "";
    if ($depo_path != "") {

        $path = $depo_path . "-" . $depo_path_detail;

    }

    $input_param[$z]["regi_date"] = $result->fields["regi_date"]; // 발급일자
    $input_param[$z]["acc_detail"] = $acc_subject . '-' . $acc_detail; // 업체명
    $input_param[$z]["income_price"] = number_format($result->fields["income_price"]);
    $input_param[$z]["expen_price"] = number_format($result->fields["expen_price"]);
    $input_param[$z]["trsf_income_price"] = number_format($result->fields["trsf_income_price"]);
    $input_param[$z]["trsf_expen_price"] = number_format($result->fields["trsf_expen_price"]);
    $input_param[$z]["sumup"] = $sumup;
    $input_param[$z++]["path"] = $path;

    $i++;
    $result->MoveNext();
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
$sheet_name = "금전출납 리스트";
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
    $sheet->setCellValue('A'.$i, '증빙일자');
    $sheet->setCellValue('B'.$i, '계정상세');
    $sheet->setCellValue('C'.$i, '수입');
    $sheet->setCellValue('D'.$i, '지출');
    $sheet->setCellValue('E'.$i, '이체수입');
    $sheet->setCellValue('F'.$i, '이체지출');
    $sheet->setCellValue('G'.$i, '적요');
    $sheet->setCellValue('H'.$i, '입출금경로');
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
    $sheet->setCellValue('A'.$i, '증빙일자');
    $sheet->setCellValue('B'.$i, '계정상세');
    $sheet->setCellValue('C'.$i, '수입');
    $sheet->setCellValue('D'.$i, '지출');
    $sheet->setCellValue('E'.$i, '이체수입');
    $sheet->setCellValue('F'.$i, '이체지출');
    $sheet->setCellValue('G'.$i, '적요');
    $sheet->setCellValue('H'.$i, '입출금경로');

    //테두리 설정
    $sheet->getStyle('A'.$i.':H'.$i)->applyFromArray($styleArray);
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
        $sheet->setCellValue('A'.$i, $fields["regi_date"]); // 신청일자
        $sheet->setCellValue('B'.$i, $fields["acc_detail"]); // 발급일자
        $sheet->setCellValue('C'.$i, $fields["income_price"]); // 업체명
        $sheet->setCellValue('D'.$i, $fields["expen_price"]); // 사업자상호
        $sheet->setCellValue('E'.$i, $fields["trsf_income_price"]); // 사업자번호
        $sheet->setCellValue('F'.$i, $fields["trsf_expen_price"]);
        $sheet->setCellValue('G'.$i, $fields["sumup"]);
        $sheet->setCellValue('H'.$i, $fields["path"]);
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
