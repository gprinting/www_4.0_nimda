<?php
require($_SERVER["DOCUMENT_ROOT"] ."/application/libraries/FPDF/korean.php");
require($_SERVER["DOCUMENT_ROOT"] ."/application/libraries/FPDF/fpdi.php");

header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=aligned.pdf");
header("Content-Description: PHP5 Generated Data");
Header("Cache-Control: cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

ini_set('display_errors', 1);

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/output_mng/OutputListDAO.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/nimda/ErpCommonUtil.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new OutputListDAO();
$util = new ErpCommonUtil();

$sheet_typset_seqno = explode('|',$fb->form("sheet_typset_seqno"));
$param_sheet_typset_seqno = $util->arr2delimStr($sheet_typset_seqno);

foreach ($sheet_typset_seqno as $tmp_sheet_typset) {
    $param = array();
    $param["table"] = "bylabel_print_record";
    $param["col"]["sheet_typset_seqno"] = $tmp_sheet_typset;
    $param["col"]["empl_seqno"] = $_SESSION["empl_seqno"];
    $param["col"]["page"] = "bylabel";

    $rs = $dao->insertData($conn, $param);
}

$rs = $dao->selectLabelInfo($conn, $param_sheet_typset_seqno);

$i = 0;
while ($rs && !$rs->EOF) {
    $fields = $rs->fields;

    $param = array();
    $param["order_common_seqno"] = $fields["order_common_seqno"];
    $order_after_history = $dao->selectOrderAfterInfo($conn, $param);
    if($fields["cate_sortcode"] == "008001005")
        $order_after_history = "문고리가공";
    $pdf = new PDF_Korean();
    $pdf->AddUHCFont('명조');
    $pdf->AddUHCFont('고딕', 'HYGoThic-Medium-Acro');
    $pdf->AddUHCFont('돋움', 'Dotum');
    $pdf->AddUHCFont('바탕', 'Batang');
    $pdf->AddUHCFont('궁서', 'Gungsuh');
    $pdf->AddUHCFont('굴림', 'Gulim');
    $pdf->AddPage('L', array(198,160));

    $x = 30;
    if(strlen($fields["member_name"]) > 8) $x = 25;

    $pdf->SetFont('돋움','B',$x);
    $pdf->SetXY(4, 4);
    $pdf->Cell(80, 10,$util->utf2euc($fields["member_name"]),0,0,'C');
    //$pdf->Cell(0,10,'Center text:',0,0,'C');

    $pdf->SetFont('돋움','',15);
    $pdf->SetXY(3.5, 17);
    $pdf->MultiCell(80, 7,$util->utf2euc($fields["title"]),0,"C",0);


    $pdf->SetFont('돋움','B',20);
    $pdf->SetXY(60, 45);
    $pdf->Cell(80, 8,$util->utf2euc(explode(" / ",$fields["order_detail"])[0],0,0,'C'));
    //$pdf->Cell(85, 8,$util->utf2euc($fields["order_detail"]),0,1,0);

    //X : 11 -> 9
    $pdf->SetFont('돋움','',14);
    $pdf->SetXY(9, 57);
    $pdf->Cell(30, 8,$util->utf2euc($fields["page_cnt"]."매"),0,0,'C');

    //이미지
    $path = "https://orderplatform.s3.ap-northeast-2.amazonaws.com" . $fields["preview_file_path"];
    $name = explode('||', $fields["preview_file_name"])[0];
    $wid = $fields["cut_size_wid"];
    $vert = $fields["cut_size_vert"];
    if($wid > $vert) {
        $wid = $fields["cut_size_vert"];
        $vert = $fields["cut_size_wid"];
    }

    $pdf->SetAlpha(0.6);
    $pdf->Image($path . $name , 125, 10, 70, 297 / 3);
    $pdf->SetAlpha(1);
    //X : 49 -> 47
    $pdf->SetFont('돋움','',13);
    $pdf->SetXY(47, 57);
    $pdf->Cell(30, 8,$util->utf2euc($fields["count"]."건"),0,0,'C');

    //X : 87 -> 85
    $pdf->SetFont('돋움','',13);
    $pdf->SetXY(85, 57);
    $pdf->Cell(30, 8,$util->utf2euc($fields["cut_size_wid"] . "x" . $fields["cut_size_vert"]),0,0,'C');
    //$pdf->Text(73, 60, $util->utf2euc($fields["cut_size_wid"] . "x" . $fields["cut_size_vert"]));

    $arr_order_detail = explode(" / ",$fields["order_detail"]);
    $pdf->SetFont('돋움','',13);
    $pdf->SetXY(41, 68);
    $pdf->MultiCell(80, 8,$util->utf2euc($arr_order_detail[1] . " / " . $arr_order_detail[2] . " / " . $arr_order_detail[3]),0,1,0);

    $pdf->SetTextColor(255 ,0,0);
    $pdf->SetFont('돋움','B',15);
    $pdf->SetXY(36, 85);
    $pdf->Cell(85, 8,$util->utf2euc($order_after_history),0,1,'C');

    $pdf->SetTextColor(0 ,0,0);
    //y : 90 -> 110
    $pdf->SetFont('돋움','B',13);
    $pdf->SetXY(23, 100);
    $pdf->Cell(55, 8,$util->utf2euc($fields["work_memo"]),0,0,'L');
    //$pdf->Cell(55, 8,$util->utf2euc("C20kg1B1-00802"),0,0,'L');
    //$pdf->Text(73, 60, $util->utf2euc($fields["typset_num"]));

    //y : 105 -> 115
    $pdf->SetFont('돋움','',13);
    $pdf->SetXY(28, 128);
    $pdf->Cell(55, 4,$util->utf2euc($fields["typset_num"]),0,0,'L');

    $dlvr = "미정";
    switch ($fields["dlvr_way"]) {
        case "01" :  $dlvr = "택배"; $fields["dlvr_add_info"] = "롯데택배"; break;
        case "02" :  $dlvr = "직배"; break;
        case "04" :  $dlvr = "오토바이"; $fields["dlvr_add_info"] = "퀵"; break;
        case "05" :  $dlvr = "다마스"; $fields["dlvr_add_info"] = "퀵"; break;
        case "06" :  $dlvr = "인현동"; $fields["dlvr_add_info"] = "방문"; break;
        case "07" :  $dlvr = "성수동"; $fields["dlvr_add_info"] = "방문"; break;
        default : $dlvr = $fields["dlvr_way"];break;
    }


    $x = 35;
    if($dlvr == "오토바이") $x = 25;
    $pdf->SetFont('돋움','B',$x);
    $pdf->SetXY(87, 110);
    $pdf->Cell(26, 13,$util->utf2euc($dlvr),0,0,'C');

    $x = 35;
    if(strlen($fields["dlvr_add_info"]) > 6) $x = 17;
    $pdf->SetFont('돋움','B',$x);
    $pdf->SetXY(87, 15);
    $pdf->Cell(26, 13,$util->utf2euc($fields["dlvr_add_info"]),0,0,'C');

    $pdf->SetFont('돋움','',7);
    $pdf->SetXY(28, 105);
    $pdf->Cell(55, 8,$util->utf2euc($fields["order_num"]),0,0,'L');

    $code='CODE 128';
    //$pdf->Code128(137,122,$fields["order_num"],50,8);
    $pdf->Code128(17,112,$fields["order_num"],50,8);

    if (!is_dir(INC_PATH . "/attach/gp/by_label_file/". $fields["typset_num"])) {
        mkdir(INC_PATH . "/attach/gp/by_label_file/". $fields["typset_num"]);
    }

    $arr_file[$i++] = INC_PATH . "/attach/gp/by_label_file/". $fields["typset_num"] ."/" . $fields["order_num"] . ".pdf";
    $pdf->Output(INC_PATH . "/attach/gp/by_label_file/". $fields["typset_num"] ."/" . $fields["order_num"] . ".pdf","F");

    $rs->MoveNext();
}

$pdf2 = new FPDI();
$pdf2->AddPage();
$i = 0;
$cnt = 0;
$Kind = "2";

$path = dirname($arr_file[0]);
foreach($arr_file as $file) {
    $pdf2->setSourceFile($file);
    $tpl = $pdf2->importPage(1, '/MediaBox');
    $size = $pdf2->getTemplateSize($tpl);

    if($Kind == "2") {
        if(($i == 1 && $path != dirname($file)) || $i == 2) {
            $path = dirname($file);
            $pdf2->AddPage();
            $i = 0;
        }

        $pdf2->SetFont('Arial','B',17);
        if($i == 0) {
            $pdf2->useTemplate($tpl,6,6,$size['w']);
        } else if($i == 1) {
            $pdf2->useTemplate($tpl,6,154,$size['w']);
        }
    } else {
        if($i == 10) {
            $pdf2->AddPage();
            $i = 0;
        }

        if($i == 0) {
            $pdf2->useTemplate($tpl,6,6,$size['w']);
        } else if($i == 1) {
            $pdf2->useTemplate($tpl,111,6,$size['w']);
        } else if($i == 2) {
            $pdf2->useTemplate($tpl,6,56,$size['w']);
        } else if($i == 3) {
            $pdf2->useTemplate($tpl,111,56,$size['w']);
        } else if($i == 4) {
            $pdf2->useTemplate($tpl,6,106,$size['w']);
        } else if($i == 5) {
            $pdf2->useTemplate($tpl,111,106,$size['w']);
        } else if($i == 6) {
            $pdf2->useTemplate($tpl,6,156,$size['w']);
        } else if($i == 7) {
            $pdf2->useTemplate($tpl,111,156,$size['w']);
        } else if($i == 8) {
            $pdf2->useTemplate($tpl,6,206,$size['w']);
        } else if($i == 9) {
            $pdf2->useTemplate($tpl,111,206,$size['w']);
        }
    }
    $i++;
}

$pdf2->Output('F', INC_PATH . "/attach/gp/by_label_file/aligned.pdf");


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
