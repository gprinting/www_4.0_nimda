<?
//ini_set('display_errors', 1);
define("INC_PATH", $_SERVER["INC"]);

include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");
//include_once(INC_PATH . '/com/nexmotion/common/util/nimda/PopSpcExcelUtil.inc');
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/ProcessOrdListDAO.inc");
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel.php');
include_once(INC_PATH . '/define/nimda/excel_define.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessOrdListDAO();
$util = new ErpCommonUtil();
//$excelUtil = new PopSpcExcelUtil();

$obj_PHPExcel = new PHPExcel();
$obj_PHPExcel->setActiveSheetIndex(0);
$sheet = $obj_PHPExcel->getActiveSheet();

$param = array();
$param["print_etprs"] = $fb->form("print_etprs");
$param["date"] = $fb->form("date");

$rs = $dao->selectProduceListByPrinter($conn, $param);

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
$sheet_name = "인쇄기별";
//makePopSpcExcelSheet($sheet_name, $sheet, $styleArray, $styleColorArr);
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
    //열 사이즈 조정
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(13);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(13);
    $sheet->getColumnDimension('I')->setWidth(30);
    $sheet->getColumnDimension('J')->setWidth(30);
    //행 사이즈 조정
    $sheet->getRowDimension('2')->setRowHeight(1);
    //엑셀 데이터 상단 필요부분 작성
    $sheet->setCellValue('A'.$i, 'No');
    $sheet->setCellValue('B'.$i, '판번호');
    $sheet->setCellValue('C'.$i, '종이');
    $sheet->setCellValue('D'.$i, '절수');
    $sheet->setCellValue('E'.$i, '도수');
    $sheet->setCellValue('F'.$i, '수량');
    $sheet->setCellValue('G'.$i, '출력');
    $sheet->setCellValue('H'.$i, '판구분');
    $sheet->setCellValue('I'.$i, '후가공');
    $sheet->setCellValue('J'.$i, '비고');
    //가운데 정렬(행,열)
    $sheet->getStyle('A'.$i.':J'.$i)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A'.$i.':J'.$i)
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
    $i = 1;

    $sheet_name = strval($sheet_name);
    //셀 병합
    //$sheet->mergeCells('C'.$i.':E'.$i);
    //$sheet->mergeCells('F'.$i.':K'.$i);
    //엑셀시트명 지정
    $sheet->setTitle($sheet_name);
    //엑셀 데이터 헤드부분
    $sheet->setCellValue('A'.$i, 'No');
    $sheet->setCellValue('B'.$i, '판번호');
    $sheet->setCellValue('C'.$i, '종이');
    $sheet->setCellValue('D'.$i, '절수');
    $sheet->setCellValue('E'.$i, '도수');
    $sheet->setCellValue('F'.$i, '수량');
    $sheet->setCellValue('G'.$i, '출력');
    $sheet->setCellValue('H'.$i, '판구분');
    $sheet->setCellValue('I'.$i, '후가공');
    $sheet->setCellValue('J'.$i, '비고');
    //테두리 설정
    $sheet->getStyle('A'.$i.':J'.$i)->applyFromArray($styleArray);
    //가운데 정렬
    $sheet->getStyle('A'.$i.':J'.$i)
        ->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A'.$i.':J'.$i)
        ->getAlignment()
        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    //볼드처리
    $sheet->getStyle('A'.$i.':J'.$i)->getFont()->setBold(true);
    //색상처리
    $sheet->getStyle('A'.$i.':J'.$i)->applyFromArray($styleColorArr);

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
    while($rs && !$rs->EOF) {
        $fields = $rs->fields;

        $paper_name = $fields["paper_name"];
        if($fields["paper_name"] == "아트지") {
            $paper_name = "아트지 100g";
        }

        $size = $rs->fields["size"];
        //엑셀 데이터 바디부분
        $sheet->setCellValue('A'.$i, $i-$j);
        $sheet->setCellValue('B'.$i, $rs->fields["typset_num"]);
        $sheet->setCellValue('C'.$i, $paper_name);
        $sheet->setCellValue('D'.$i, $size);
        $sheet->setCellValue('E'.$i, ($rs->fields["beforeside_tmpt"] + $rs->fields["aftside_tmpt"]) . "도");
        $sheet->setCellValue('F'.$i, $rs->fields["print_amt"] . $rs->fields["print_amt_unit"]);
        $sheet->setCellValue('G'.$i, "CTP?");
        $sheet->setCellValue('H'.$i, "서울판");
        $sheet->setCellValue('I'.$i, $rs->fields["after_name"]);
        $sheet->setCellValue('J'.$i, $rs->fields["memo"]);
        //테두리 설정
        $sheet->getStyle('A'.$i.':J'.$i)->applyFromArray($styleArray);
        //가운데 정렬
        $sheet->getStyle('A'.$i.':J'.$i)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A'.$i.':J'.$i)
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        //셀 세로 사이즈
        $sheet->getRowDimension($i)->setRowHeight(20);

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

/*
function getEnvelopeValue($key)
{
    $size = "";
    switch ($key) {
        case "국전-대봉2개-소봉4개|모조지":
        case "국전-대봉2개-소봉4개|100 모조":
        case "국전-대봉2개-소봉4개|120 모조":
        case "국전-대봉2개-소봉4개|150 모조":
        case "8색기-국전-대봉2개-소봉4개|모조지":
        case "8색기-국전-대봉2개-소봉4개|100 모조":
        case "8색기-국전-대봉2개-소봉4개|120 모조":
        case "8색기-국전-대봉2개-소봉4개|150 모조":
            $size = "1020 x 670";
            break;
        case "국전-대봉2개-소봉4개|레자크 #91(체크)":
        case "국전-대봉2개-소봉4개|#91 백색 레쟈크":
        case "8색기-국전-대봉2개-소봉4개|레자크 #91(체크)":
        case "8색기-국전-대봉2개-소봉4개|#91 백색 레쟈크":
            $size = "1020 x 670";
            break;
        case "국전-대봉2개-소봉4개|레자크 #92(줄)":
        case "국전-대봉2개-소봉4개|#92 백색 레쟈크":
        case "8색기-국전-대봉2개-소봉4개|레자크 #92(줄)":
        case "8색기-국전-대봉2개-소봉4개|#92 백색 레쟈크":
            $size = "1020 x 670";
            break;

        case "2절-대봉2개|모조지":
        case "2절-대봉2개|100 모조":
        case "2절-대봉2개|120 모조":
        case "2절-대봉2개|150 모조":
        case "우성-2절-대봉2개|모조지":
        case "우성-2절-대봉2개|100 모조":
        case "우성-2절-대봉2개|120 모조":
        case "우성-2절-대봉2개|150 모조":
        case "8색기-2절-대봉2개|모조지":
        case "8색기-2절-대봉2개|100 모조":
        case "8색기-2절-대봉2개|120 모조":
        case "8색기-2절-대봉2개|150 모조":
            $size = "545 x 788";break;
        case "2절-대봉2개|레자크 #91(체크)":
        case "2절-대봉2개|#91 백색 레쟈크":
        case "우성-2절-대봉2개|레자크 #91(체크)":
        case "우성-2절-대봉2개|#91 백색 레쟈크":
        case "8색기-2절-대봉2개|레자크 #91(체크)":
        case "8색기-2절-대봉2개|#91 백색 레쟈크":
            $size = "506 x 788";break;
        case "2절-대봉2개|레자크 #92(줄)":
        case "2절-대봉2개|#92 백색 레쟈크":
        case "우성-2절-대봉2개|레자크 #92(줄)":
        case "우성-2절-대봉2개|#92 백색 레쟈크":
        case "8색기-2절-대봉2개|레자크 #92(줄)":
        case "8색기-2절-대봉2개|#92 백색 레쟈크":
            $size = "506 x 788";break;


        case "국전-소봉투(12개)|모조지":
        case "국전-소봉투(12개)|100 모조":
        case "국전-소봉투(12개)|120 모조":
        case "국전-소봉투(12개)|150 모조":
        case "8색기-국전-소봉투(12개)|모조지":
        case "8색기-국전-소봉투(12개)|100 모조":
        case "8색기-국전-소봉투(12개)|120 모조":
        case "8색기-국전-소봉투(12개)|150 모조":
            $size = "1060 x 740";break;
        case "국전-소봉투(12개)|레자크 #91(체크)":
        case "국전-소봉투(12개)|#91 백색 레쟈크":
        case "8색기-국전-소봉투(12개)|레자크 #91(체크)":
        case "8색기-국전-소봉투(12개)|#91 백색 레쟈크":
            $size = "1060 x 740";break;
        case "국전-소봉투(12개)|레자크 #92(줄)":
        case "국전-소봉투(12개)|#92 백색 레쟈크":
        case "8색기-국전-소봉투(12개)|레자크 #92(줄)":
        case "8색기-국전-소봉투(12개)|#92 백색 레쟈크":
            $size = "1060 x 740";break;


        case "2절-대봉1개-소봉2개|모조지":
        case "2절-대봉1개-소봉2개|100 모조":
        case "2절-대봉1개-소봉2개|120 모조":
        case "2절-대봉1개-소봉2개|150 모조":
        case "우성-2절-대봉1개-소봉2개|모조지":
        case "우성-2절-대봉1개-소봉2개|100 모조":
        case "우성-2절-대봉1개-소봉2개|120 모조":
        case "우성-2절-대봉1개-소봉2개|150 모조":
        case "8색기-2절-대봉1개-소봉2개|모조지":
        case "8색기-2절-대봉1개-소봉2개|100 모조":
        case "8색기-2절-대봉1개-소봉2개|120 모조":
        case "8색기-2절-대봉1개-소봉2개|150 모조":
            $size = "545 x 788";break;
        case "2절-대봉1개-소봉2개|레자크 #91(체크)":
        case "2절-대봉1개-소봉2개|#91 백색 레쟈크":
        case "우성-2절-대봉1개-소봉2개|레자크 #91(체크)":
        case "우성-2절-대봉1개-소봉2개|#91 백색 레쟈크":
        case "8색기-2절-대봉1개-소봉2개|레자크 #91(체크)":
        case "8색기-2절-대봉1개-소봉2개|#91 백색 레쟈크":
            $size = "506 x 788";break;
        case "2절-대봉1개-소봉2개|레자크 #92(줄)":
        case "2절-대봉1개-소봉2개|#92 백색 레쟈크":
        case "우성-2절-대봉1개-소봉2개|레자크 #92(줄)":
        case "우성-2절-대봉1개-소봉2개|#92 백색 레쟈크":
        case "8색기-2절-대봉1개-소봉2개|레자크 #92(줄)":
        case "8색기-2절-대봉1개-소봉2개|#92 백색 레쟈크":
            $size = "788 x 506";break;


        case "2절-대봉1개-소봉1개-이삿짐봉투1개|모조지":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|100 모조":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|120 모조":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|150 모조":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|모조지":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|100 모조":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|120 모조":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|150 모조":
            $size = "545 x 788";break;
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|레자크 #91(체크)":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|#91 백색 레쟈크":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|레자크 #91(체크)":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|#91 백색 레쟈크":
            $size = "788 x 506";break;
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|레자크 #92(줄)":
        case "2절-대봉1개-소봉1개-이삿짐봉투1개|#92 백색 레쟈크":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|레자크 #92(줄)":
        case "8색기-2절-대봉1개-소봉1개-이삿짐봉투1개|#92 백색 레쟈크":
            $size = "788 x 506";break;


        case "2절-소봉6개|모조지":
        case "2절-소봉6개|100 모조":
        case "2절-소봉6개|120 모조":
        case "2절-소봉6개|150 모조":
        case "8색기-2절-소봉6개|모조지":
        case "8색기-2절-소봉6개|100 모조":
        case "8색기-2절-소봉6개|120 모조":
        case "8색기-2절-소봉6개|150 모조":
            $size = "788 x 545";break;
        case "2절-소봉6개|레자크 #91(체크)":
        case "2절-소봉6개|#91 백색 레쟈크":
        case "8색기-2절-소봉6개|레자크 #91(체크)":
        case "8색기-2절-소봉6개|#91 백색 레쟈크":
            $size = "788 x 545";break;
        case "2절-소봉6개|레자크 #92(줄)":
        case "2절-소봉6개|#92 백색 레쟈크":
        case "8색기-2절-소봉6개|레자크 #92(줄)":
        case "8색기-2절-소봉6개|#92 백색 레쟈크":
            $size = "788 x 545";break;
        default:
            $size = "";
    }

    return $size;
}
?>
