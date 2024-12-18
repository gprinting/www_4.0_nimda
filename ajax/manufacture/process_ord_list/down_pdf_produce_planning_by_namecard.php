<?php
require($_SERVER["DOCUMENT_ROOT"] ."/application/libraries/FPDF/korean.php");
require($_SERVER["DOCUMENT_ROOT"] ."/application/libraries/FPDF/fpdi.php");

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=aligned.pdf");
header("Content-Description: PHP5 Generated Data");
Header("Cache-Control: cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

//ini_set("display_errors", 1);

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/typset_mng/ProcessOrdListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new ProcessOrdListDAO();
$util = new ErpCommonUtil();

$param = array();
$param["date"] = $fb->form("date");

$rs = $dao->selectProduceListByNamecardSum($conn, $param);

$suip_pansu = 0;
$suip_pansu_cnt = 0;
$suip_amt = 0;
$sticker_pansu = 0;
$sticker_pansu_cnt = 0;
$sticker_amt = 0;
$coating_pansu = 0;
$coating_pansu_cnt = 0;
$coating_amt = 0;
$no_coating_pansu = 0;
$no_coating_pansu_cnt = 0;
$no_coating_amt = 0;
$dtp_pansu = 0;
$dtp_amt = 0;

$pansu_sum1 = 0;
$pansu_sum2 = 0;
$amt_sum1 = 0;
$amt_sum2 = 0;

$coating_content = array();
$no_coating_content = array();
$suip_content = array();
$sticker_content1 = array();
$sticker_content2 = array();
$sticker_content3 = array();
$dtp_content = array();

$sticker_paper_changed = false;
while($rs && !$rs->EOF) {
    $typset_num_mid = explode('-', $rs->fields['typset_num'])[1];
    if(startsWith($typset_num_mid, "11")) {
        $coating_pansu += 1;
        $coating_amt += $rs->fields['print_amt'];
        $tmpt = $rs->fields['tmpt'];
        if($tmpt == 8 && $rs->fields['honggak_yn'] == "N") {
            $tmpt = 4;
        }
        $coating_pansu_cnt += $tmpt;

        $paper_name = explode(' ',$rs->fields['paper_name'])[0];
        $content = $typset_num_mid . "\t\t" . $paper_name . " 코팅" . "\t\t" . $rs->fields['tmpt'] . "\t" . $rs->fields['print_amt'] . "매" . "\t" . ($rs->fields['honggak_yn'] == "Y" ? "" : "돈땡") . "\t" . $rs->fields['memo'];
        array_push($coating_content, $content);
    }

    if(startsWith($typset_num_mid, "12")) {
        $no_coating_pansu += 1;
        $no_coating_amt += $rs->fields['print_amt'];
        $tmpt = $rs->fields['tmpt'];
        if($tmpt == 8 && $rs->fields['honggak_yn'] == "N") {
            $tmpt = 4;
        }
        $no_coating_pansu_cnt += $tmpt;

        $paper_name = explode(' ',$rs->fields['paper_name'])[0];
        $content = $typset_num_mid . "\t\t" . $paper_name . " 무코팅" . "\t\t" . $rs->fields['tmpt'] . "\t" . $rs->fields['print_amt']. "매" . "\t" . ($rs->fields['honggak_yn'] == "Y" ? "" : "돈땡"). "\t" . $rs->fields['memo'];
        array_push($no_coating_content, $content);
    }

    if(startsWith($typset_num_mid, "13")) {
        $suip_pansu += 1;
        $suip_amt += $rs->fields['print_amt'];
        $tmpt = $rs->fields['tmpt'];
        if($tmpt == 8 && $rs->fields['honggak_yn'] == "N") {
            $tmpt = 4;
        }
        $suip_pansu_cnt += $tmpt;
        $paper_name = explode(' ',$rs->fields['paper_name'])[0];
        if($paper_name == "스타드림") {
            $paper_name = $rs->fields['paper_name'];
        }
        if(strpos('273', $rs->fields['paper_name']) !== false) {
            $paper_name = $rs->fields['paper_name'];
        }

        $content = $typset_num_mid . "\t" . $paper_name . "\t\t" . $rs->fields['tmpt'] . "\t" . $rs->fields['print_amt']. "매" . "\t" . ($rs->fields['honggak_yn'] == "Y" ? "" : "돈땡"). "\t" . $rs->fields['memo'];
        array_push($suip_content, $content);
    }

    if(startsWith($typset_num_mid, "14")) {
        $sticker_pansu += 1;
        $sticker_amt += $rs->fields['print_amt'];
        $sticker_pansu_cnt += 4;

        //$sticker_content1

        $paper_name = explode(' ',$rs->fields['paper_name'])[0];
        if($paper_name == "스티커 아트지 코팅") {
            $paper_name = "코팅";
        } else if($paper_name == "스티커 아트지 무코팅") {
            $paper_name = "무코팅";
        }

        $content = $typset_num_mid . "\t" . $paper_name . "\t" . $rs->fields['print_title'] . "\t" . $rs->fields['print_amt']. "매" . "\t" . $rs->fields['memo'];
        if($rs->fields['print_etprs'] == "8색기(B2)") {
            array_push($sticker_content1, $content);
        } else if($rs->fields['print_etprs'] == "V3000(B1)") {
            array_push($sticker_content2, $content);
        }
    }

    if(startsWith($typset_num_mid, "15")) {
        $dtp_pansu += 1;
        $dtp_amt += $rs->fields['print_amt'];

        $paper_name = explode(' ',$rs->fields['paper_name'])[0];
        if($paper_name == "스타드림") {
            $paper_name = $rs->fields['paper_name'];
        }
        if($paper_name == "키칼라메탈릭") {
            $paper_name = explode(' ', $rs->fields['paper_name'])[0] . " " . explode(' ', $rs->fields['paper_name'])[1];
        }
        if(strpos($rs->fields['paper_name'],'273') !== false) {
            $paper_name = $rs->fields['paper_name'];
        }
        $content = $typset_num_mid . "\t\t" . $paper_name . "\t\t" . $rs->fields['tmpt'] . "\t" . $rs->fields['print_amt'] . "매" . "\t" . ($rs->fields['honggak_yn'] == "Y" ? "" : "돈땡") . "\t" . $rs->fields['memo'];
        array_push($dtp_content, $content);
    }

    if(startsWith($typset_num_mid, "16")) {
        $sticker_pansu += 1;
        $sticker_amt += $rs->fields['print_amt'];
        $sticker_pansu_cnt += 4;

        $paper_name = explode(' ',$rs->fields['paper_name'])[0];
        if($paper_name == "스타드림") {
            $paper_name = $rs->fields['paper_name'];
        }
        $content = $typset_num_mid . "\t" . $paper_name . "\t" . $rs->fields['print_title'] . "\t" . $rs->fields['print_amt']. "매" . "\t" . $rs->fields['memo'];
        array_push($sticker_content3, $content);
    }
    $rs->MoveNext();
}

$pdf = new PDF_Korean();
$pdf->AddUHCFont('명조');
$pdf->AddUHCFont('고딕', 'HYGoThic-Medium-Acro');
$pdf->AddUHCFont('돋움', 'Dotum');
$pdf->AddUHCFont('바탕', 'Batang');
$pdf->AddUHCFont('궁서', 'Gungsuh');
$pdf->AddUHCFont('굴림', 'Gulim');
$pdf->AddPage('P', array(210,297));


$pdf->SetFont('돋움','B',25);
$pdf->SetXY(4, 4);
$pdf->Cell(200, 15,$util->utf2euc("작 업 지 시 서"),0,0,'C');

//$param["date"]
$pdf->SetFont('돋움','B',15);
$pdf->SetXY(160, 15);
$pdf->Cell(30, 8,$util->utf2euc($param["date"]),0,0,'C');

// Colors, line width and bold font
$pdf->SetFillColor(168,168,168);
$pdf->SetTextColor(30);
$pdf->SetDrawColor(0,0,0);
$pdf->SetLineWidth(.3);
$pdf->SetFont('돋움','B');
// Header
$w = array(20, 30, 30);
$w2 = array(20, 30, 30);
$header = array('', '수입지', '스티커');
$header = array('', '합계', '총합');


$fill = true;
$data = array('', '');

$pdf->SetFont('돋움','B',15);
$i = 1;

$pdf->SetXY(16.5, $i * 8 + 15);
$pdf->Cell($w[0],8,$util->utf2euc(""),'LRTB',0,'C',$fill);
$pdf->Cell($w[1],8,$util->utf2euc("수입지"),'LRTB',0,'C',$fill);
$pdf->Cell($w[2],8,$util->utf2euc("스티커"),'LRTB',0,'C',$fill);

$i++;
$fill = false;

$suip_data = array($suip_pansu, $suip_amt);
$sticker_data = array($sticker_pansu, $sticker_amt);
$z = 0;
foreach($data as $row)
{
    $pdf->SetXY(16.5, $i * 8 + 15);
    $fill = true;
    if($i == 2) {
        $pdf->Cell($w[0], 8, $util->utf2euc("판 수"), 'LRTB', 0, 'C', $fill);
    } else if($i == 3) {
        $pdf->Cell($w[0], 8, $util->utf2euc("매 수"), 'LRTB', 0, 'C', $fill);
    }
    $fill = false;
    $pdf->Cell($w[1],8,$util->utf2euc($suip_data[$z]),'LRTB',0,'C',$fill);
    $pdf->Cell($w[2],8,$util->utf2euc($sticker_data[$z]),'LRTB',0,'C',$fill);

    $i++;
    $z++;
    //$fill = !$fill;
}

$i = 1;
$fill = true;
$pdf->SetXY(113.5, $i * 8 + 15);
$pdf->Cell($w[0],8,$util->utf2euc(""),'LRTB',0,'C',$fill);
$pdf->Cell($w[1],8,$util->utf2euc("합계"),'LRTB',0,'C',$fill);
$pdf->Cell($w[2],8,$util->utf2euc("총합"),'LRTB',0,'C',$fill);
$i++;
foreach($data as $row)
{
    $pdf->SetXY(113.5, $i * 8 + 15);
    if($i == 2) {
        $pdf->Cell($w[0], 8, $util->utf2euc("판 수"), 'LRTB', 0, 'C', true);
        $pdf->Cell($w[1],8,"\t\t" . ($coating_pansu + $sticker_pansu + $no_coating_pansu + $suip_pansu),'LRTB',0,'L',false);
        $pdf->Cell($w[2],8,"\t\t" .($coating_pansu + $sticker_pansu + $no_coating_pansu + $suip_pansu + $dtp_pansu),'LRTB',0,'L',false);
    } else if($i == 3) {
        $pdf->Cell($w[0], 8, $util->utf2euc("매 수"), 'LRTB', 0, 'C', true);
        $pdf->Cell($w[1],8,"\t" . ($coating_amt + $sticker_amt + $no_coating_amt + $suip_amt),'LRTB',0,'L',false);
        $pdf->Cell($w[2],8,"\t" .($coating_amt + $sticker_amt + $no_coating_amt + $suip_amt + $dtp_amt),'LRTB',0,'L',false);
    }

    $i++;
    //$fill = !$fill;
}

// 수입지
$pdf->SetXY(10, 4 * 8 + 15);
$pdf->Cell(92.5,8,$util->utf2euc("수입지"),'LRTB',0,'C',true);

$pdf->SetXY(10, 4 * 8 + 15 + 8);
$pdf->Cell(92.5,210,$util->utf2euc(""),'LRTB',0,'L',false);

$i = 1;
$pdf->SetFont('돋움','B',11);
foreach($suip_content as $content) {
    $pdf->SetXY(10, 4 * 8 + 15 + 8 + $i * 8 - 5);
    $pdf->Cell(92.5,8,$util->utf2euc($content),'',0,'L',false);
    $i++;
}

$pdf->SetFont('돋움','B',15);
$pdf->SetXY(10, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(23,8,$util->utf2euc("판 합계"),'LRTB',0,'C',true);

$pdf->SetXY(33, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(23,8,$util->utf2euc($suip_pansu),'LRTB',0,'C',false);

$pdf->SetXY(56, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(24,8,$util->utf2euc("매수 합계"),'LRTB',0,'C',true);

$pdf->SetXY(80, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(22.5,8,$util->utf2euc($suip_amt),'LRTB',0,'C',false);

// 스티커
$pdf->SetXY(107.5, 4 * 8 + 15);
$pdf->Cell(92.5,8,$util->utf2euc("스티커"),'LRTB',0,'C',true);

$pdf->SetXY(107.5, 4 * 8 + 15 + 8);
$pdf->Cell(92.5,210,$util->utf2euc(""),'LRTB',0,'C',false);

$pdf->SetFont('돋움','B',8);
$i = 1;
foreach($sticker_content1 as $content) {
    $pdf->SetXY(108.5, 4 * 8 + 15 + 8 + $i * 8 - 5);
    $pdf->Cell(92.5,8,$util->utf2euc($content),'',0,'L',false);
    $i++;
}

foreach($sticker_content2 as $content) {
    $pdf->SetXY(108.5, 4 * 8 + 15 + 8 + $i * 8 + 5);
    $pdf->Cell(92.5,8,$util->utf2euc($content),'',0,'L',false);
    $i++;
}

foreach($sticker_content3 as $content) {
    $pdf->SetXY(108.5, 4 * 8 + 15 + 8 + $i * 8 + 15);
    $pdf->Cell(92.5,8,$util->utf2euc($content),'',0,'L',false);
    $i++;
}
$pdf->SetFont('돋움','B',15);
$pdf->SetXY(107.5, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(23,8,$util->utf2euc("판 합계"),'LRTB',0,'C',true);

$pdf->SetXY(130.5, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(23,8,$util->utf2euc($sticker_pansu),'LRTB',0,'C',false);

$pdf->SetXY(153.5, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(24,8,$util->utf2euc("매수 합계"),'LRTB',0,'C',true);

$pdf->SetXY(177.5, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(22.5,8,$util->utf2euc($sticker_amt),'LRTB',0,'C',false);


//////// 2페이지
$pdf->AddPage('P', array(210,297));


$pdf->SetFont('돋움','B',25);
$pdf->SetXY(4, 4);
$pdf->Cell(200, 15,$util->utf2euc("작 업 지 시 서"),0,0,'C');

$pdf->SetFont('돋움','B',15);
$pdf->SetXY(160, 15);
$pdf->Cell(30, 8,$util->utf2euc($param["date"]),0,0,'C');

// Colors, line width and bold font
$pdf->SetFillColor(168,168,168);
$pdf->SetTextColor(30);
$pdf->SetDrawColor(0,0,0);
$pdf->SetLineWidth(.3);
$pdf->SetFont('돋움','B');
// Header
$w = array(15, 20, 25, 20);
$w2 = array(20, 30, 30);
$header = array('', '수입지', '스티커');
$header = array('', '합계', '총합');


$fill = true;
$data = array('', '', '');

$pdf->SetFont('돋움','B',15);
$i = 1;

$pdf->SetXY(16.5, $i * 8 + 15);
$pdf->Cell($w[0],8,$util->utf2euc(""),'LRTB',0,'C',$fill);
$pdf->Cell($w[1],8,$util->utf2euc("코 팅"),'LRTB',0,'C',$fill);
$pdf->Cell($w[2],8,$util->utf2euc("무 코 팅"),'LRTB',0,'C',$fill);
$pdf->Cell($w[3],8,$util->utf2euc("D T P"),'LRTB',0,'C',$fill);

$i++;
$fill = false;

$pansu_data = array($coating_pansu, $no_coating_pansu, $dtp_pansu);
$amt_data = array($coating_amt, $no_coating_amt, $dtp_amt);
$z = 0;
foreach($data as $row)
{
    $pdf->SetXY(16.5, $i * 8 + 15);
    $fill = true;
    if($i == 2) {
        $pdf->Cell($w[0], 8, $util->utf2euc("판 수"), 'LRTB', 0, 'C', $fill);
    } else if($i == 3) {
        $pdf->Cell($w[0], 8, $util->utf2euc("매 수"), 'LRTB', 0, 'C', $fill);
    }
    $fill = false;

    if($i == 2) {
        $pdf->Cell($w[1],8,"\t  " .$pansu_data[0],'LRTB',0,'L',$fill);
        $pdf->Cell($w[2],8,"\t\t" .$pansu_data[1],'LRTB',0,'L',$fill);
        $pdf->Cell($w[3],8,"\t " .$pansu_data[2],'LRTB',0,'L',$fill);
    } else if($i == 3) {
        $pdf->Cell($w[1],8,"  " .$amt_data[0],'LRTB',0,'L',$fill);
        $pdf->Cell($w[2],8,"\t" .$amt_data[1],'LRTB',0,'L',$fill);
        $pdf->Cell($w[3],8,"  " .$amt_data[2],'LRTB',0,'L',$fill);
    }

    $i++;
    $z++;
    //$fill = !$fill;
}

$i = 1;
$fill = true;
$pdf->SetXY(113.5, $i * 8 + 15);
$pdf->Cell(array_sum($w),8,$util->utf2euc("판 사용량"),'LRTB',0,'C',$fill);

$pdf->SetXY(113.5, $i * 8 + 15 + 8);
$pdf->Cell($w[0],8,$util->utf2euc("코 팅"),'LRTB',0,'C',$fill);
$pdf->Cell($w[1],8,$util->utf2euc("무 코 팅"),'LRTB',0,'C',$fill);
$pdf->Cell($w[2],8,$util->utf2euc("수 입 지"),'LRTB',0,'C',$fill);
$pdf->Cell($w[3],8,$util->utf2euc("스 티 커"),'LRTB',0,'C',$fill);

$pdf->SetXY(113.5, $i * 8 + 15 + 16);

$pdf->Cell($w[0],8,"  " . $coating_pansu_cnt,'LRTB',0,'L',false);
$pdf->Cell($w[1],8,"\t" . $no_coating_pansu_cnt,'LRTB',0,'L',false);
$pdf->Cell($w[2],8,"\t  " . $suip_pansu_cnt,'LRTB',0,'L',false);
$pdf->Cell($w[3],8,"\t" . $sticker_pansu_cnt,'LRTB',0,'L',false);

// 코팅명함
$pdf->SetXY(10, 4 * 8 + 15);
$pdf->Cell(92.5,8,$util->utf2euc("코 팅 명 함"),'LRTB',0,'C',true);

$pdf->SetXY(10, 4 * 8 + 15 + 8);
$pdf->Cell(92.5,100,$util->utf2euc(""),'LRTB',0,'C',false);

$i = 1;
$pdf->SetFont('돋움','B',11);
foreach($coating_content as $content) {
    $pdf->SetXY(10, 4 * 8 + 15 + 8 + $i * 8 - 5);
    $pdf->Cell(92.5,8,$util->utf2euc($content),'',0,'L',false);
    $i++;
}

$pdf->SetFont('돋움','B',15);

$pdf->SetXY(10, 4 * 8 + 15 + 8 + 100);
$pdf->Cell(23,8,$util->utf2euc("판 합계"),'LRTB',0,'C',true);

$pdf->SetXY(33, 4 * 8 + 15 + 8 + 100);
$pdf->Cell(23,8,$util->utf2euc($coating_pansu),'LRTB',0,'C',false);

$pdf->SetXY(56, 4 * 8 + 15 + 8 + 100);
$pdf->Cell(24,8,$util->utf2euc("매수 합계"),'LRTB',0,'C',true);

$pdf->SetXY(80, 4 * 8 + 15 + 8 + 100);
$pdf->Cell(22.5,8,$util->utf2euc($coating_amt),'LRTB',0,'C',false);

// 무코팅명함
$pdf->SetXY(10, 4 * 8 + 15 + 8 + 100 + 8);
$pdf->Cell(92.5,8,$util->utf2euc("무 코 팅 명 함"),'LRTB',0,'C',true);

$pdf->SetXY(10, 4 * 8 + 15 + 8 + 100 + 8 + 8);
$pdf->Cell(92.5,102,$util->utf2euc(""),'LRTB',0,'C',false);

$i = 1;
$pdf->SetFont('돋움','B',11);
foreach($no_coating_content as $content) {
    $pdf->SetXY(10, 4 * 8 + 15 + 8 + 100 + 8 + 8 + $i * 8 - 5);
    $pdf->Cell(92.5,8,$util->utf2euc($content),'',0,'L',false);
    $i++;
}

$pdf->SetFont('돋움','B',15);

$pdf->SetXY(10, 4 * 8 + 15 + 8 + 100 + 8 + 102);
$pdf->Cell(23,8,$util->utf2euc("판 합계"),'LRTB',0,'C',true);

$pdf->SetXY(33, 4 * 8 + 15 + 8 + 100 + 8 + 102);
$pdf->Cell(23,8,$util->utf2euc($no_coating_pansu),'LRTB',0,'C',false);

$pdf->SetXY(56, 4 * 8 + 15 + 8 + 100 + 8 + 102);
$pdf->Cell(24,8,$util->utf2euc("매수 합계"),'LRTB',0,'C',true);

$pdf->SetXY(80, 4 * 8 + 15 + 8 + 100 + 8 + 102);
$pdf->Cell(22.5,8,$util->utf2euc($no_coating_amt),'LRTB',0,'C',false);

// D T P
$pdf->SetXY(107.5, 4 * 8 + 15);
$pdf->Cell(92.5,8,$util->utf2euc("D T P"),'LRTB',0,'C',true);

$pdf->SetXY(107.5, 4 * 8 + 15 + 8);
$pdf->Cell(92.5,210,$util->utf2euc(""),'LRTB',0,'C',false);

$pdf->SetFont('돋움','B',11);
$i = 1;
foreach($dtp_content as $content) {
    $pdf->SetXY(108.5, 4 * 8 + 15 + 8 + $i * 8 - 5);
    $pdf->Cell(92.5,8,$util->utf2euc($content),'',0,'L',false);
    $i++;
}
$pdf->SetFont('돋움','B',15);
$pdf->SetXY(107.5, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(23,8,$util->utf2euc("판 합계"),'LRTB',0,'C',true);

$pdf->SetXY(130.5, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(23,8,$util->utf2euc($dtp_pansu),'LRTB',0,'C',false);

$pdf->SetXY(153.5, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(24,8,$util->utf2euc("매수 합계"),'LRTB',0,'C',true);

$pdf->SetXY(177.5, 4 * 8 + 15 + 8 + 210);
$pdf->Cell(22.5,8,$util->utf2euc($dtp_amt),'LRTB',0,'C',false);


$pdf->Output('F', INC_PATH . "/attach/gp/by_label_file/aligned2.pdf");


function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}
?>
