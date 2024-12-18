<?php
require($_SERVER["DOCUMENT_ROOT"] ."/application/libraries/FPDF/korean.php");
require($_SERVER["DOCUMENT_ROOT"] ."/application/libraries/FPDF/fpdi.php");

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=외주 입고리스트.pdf");
header("Content-Description: PHP5 Generated Data");
Header("Cache-Control: cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

//ini_set("display_errors", 1);

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . '/com/nexmotion/job/nimda/business/order_mng/OrderCommonMngDAO.inc');
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OrderCommonMngDAO();
$util = new ErpCommonUtil();

$param = array();
$param["order_num"] = explode("|", $fb->form("ordernums"));
$rs = $dao->selectExtnlOrder($conn, $param);

$pdf = new PDF_Korean();
$pdf->AddUHCFont('명조');
$pdf->AddUHCFont('고딕', 'HYGoThic-Medium-Acro');
$pdf->AddUHCFont('돋움', 'Dotum');
$pdf->AddUHCFont('바탕', 'Batang');
$pdf->AddUHCFont('궁서', 'Gungsuh');
$pdf->AddUHCFont('굴림', 'Gulim');
$pdf->AddPage();
$pdf->SetMargins(10, 10);
//$pdf->SetAutoPageBreak(true, 10);

if (!is_dir(INC_PATH . "/attach/gp/outsource_file/". date('Ymd'))) {
    mkdir(INC_PATH . "/attach/gp/outsource_file/". date('Ymd'));
}

$x = 15;
$y = 15;
while ($rs && !$rs->EOF) {
    $member_name = $rs->fields['member_name'];
    $title = $rs->fields['title'];
    $paper = explode(' / ', $rs->fields['order_detail'])[1];
    $amt = ($rs->fields['amt'] / 10) * 10;
    $count = $rs->fields['count'];
    $tot_tmpt = $rs->fields['tot_tmpt'];
    $preview_file_path = $rs->fields['preview_file_path'];
    $preview_file_names = explode('||', $rs->fields['preview_file_name']);

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

    $tmp_paper = explode(' ', $paper)[0];
    if($tmp_paper == "Extra") {
        $tmp_paper .= " " . explode(' ', $paper)[1];
    }
    $str_order = date('md') . '-' .
        $member_name . '-' .
        $tmp_paper . '-' .
        $tot_tmpt . '도-' .
        $amt . '매-' .
        $count . '건-' .
        $dlvr_way;

    $pdf->SetFont('돋움','B',15);
    $pdf->SetXY(40, $y);
    $pdf->Cell(80, 8,$util->utf2euc($str_order),0,0,'C');

    $x = 35;
    $i = 0;
    foreach($preview_file_names as $preview_file_name) {
        if($i % 2 == 0)
            $x = 35;
        else
            $x = 115;
        $pdf->Image("https://orderplatform.s3.ap-northeast-2.amazonaws.com" . $preview_file_path . $preview_file_name , $x, $y + 15, 90 / 3 * 2, 58 / 3 * 2);
        if($x == 35 && $tot_tmpt == 4) $y += 50;
        else if($x == 115) {
            $y += 50;
        }
        $i++;

        if($y > 237) {
            $pdf->AddPage();
            $y = 15;
        }
    }


    if($y != 15)
        $y = $y + 20;
    if($y > 237) {
        $pdf->AddPage();
        $y = 15;
    }
    $rs->MoveNext();
}


$pdf->Output(INC_PATH . "/attach/gp/outsource_file/". date('Ymd') ."/extnl_income_list.pdf","F");

ob_clean();
flush();
readfile(INC_PATH . "/attach/gp/outsource_file/". date('Ymd') ."/extnl_income_list.pdf");

/*
$pdf = new FPDI();
$pdf->AddPage();


$i = 0;
$cnt = 0;
foreach ($files as $file) {
    if($file['path'] == "") {
        $i++;
        continue;
    }

    $pdf->setSourceFile($file['path']);
    $tpl = $pdf->importPage(1, '/MediaBox');
    $size = $pdf->getTemplateSize($tpl);

    if($Kind == "2") {
        if($i == 2) {
            $pdf->AddPage();
            $i = 0;
        }

        $pdf->SetFont('Arial','B',17);
        if($i == 0) {
            $pdf->useTemplate($tpl,6,6,$size['w']);
            $pdf->Text(27,138,$file['CompositionCode']);
        } else if($i == 1) {
            $pdf->useTemplate($tpl,6,154,$size['w']);
            $pdf->Text(27,286,$file['CompositionCode']);
        }
    } else {
        if($i == 10) {
            $pdf->AddPage();
            $i = 0;
        }

        if($i == 0) {
            $pdf->useTemplate($tpl,6,6,$size['w']);
        } else if($i == 1) {
            $pdf->useTemplate($tpl,111,6,$size['w']);
        } else if($i == 2) {
            $pdf->useTemplate($tpl,6,56,$size['w']);
        } else if($i == 3) {
            $pdf->useTemplate($tpl,111,56,$size['w']);
        } else if($i == 4) {
            $pdf->useTemplate($tpl,6,106,$size['w']);
        } else if($i == 5) {
            $pdf->useTemplate($tpl,111,106,$size['w']);
        } else if($i == 6) {
            $pdf->useTemplate($tpl,6,156,$size['w']);
        } else if($i == 7) {
            $pdf->useTemplate($tpl,111,156,$size['w']);
        } else if($i == 8) {
            $pdf->useTemplate($tpl,6,206,$size['w']);
        } else if($i == 9) {
            $pdf->useTemplate($tpl,111,206,$size['w']);
        }
    }
    $i++;
}

// output the pdf as a file (http://www.fpdf.org/en/doc/output.htm)
//
$pdf->Output('F','aligned.pdf');








echo "@aa";


function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

?>
