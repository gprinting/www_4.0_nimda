<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2018-02-02
 * Time: 오전 11:21
 */
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/fakepDAO.inc');
require_once('/home/sitemgr/nimda/application/libraries/FPDF/vendor/autoload.php');
require_once('/home/sitemgr/nimda/application/libraries/FPDF/vendor/korean.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();

class PDF_Rotate extends PDF_Korean {

    var $angle = 0;

    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle*=M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function AddPanPage($pan_width, $pan_height, $trim_left, $trim_top, $trim_right, $trim_bottom, $angle = 0) {
        if ($pan_width > $pan_height) {
            $this->AddPage('L', [$pan_width, $pan_height, 'Rotate' => $angle]);
        } else {
            $this->AddPage('P', [$pan_width, $pan_height, 'Rotate' => $angle]);
        }

        $this->setSourceFile("dombo/CMYK.pdf");
        $this->importPage(1);
        $tpl_dombo = $this->importPage(1);
        $size_dombo = $this->getTemplateSize($tpl_dombo);

        $x = $pan_width / 2 - $size_dombo['w'] / 2;
        $y = $trim_top - $size_dombo['h'];
        $this->useTemplate($tpl_dombo, $x, $y, $size_dombo['w']);

// Right
        $this->setSourceFile("dombo/CMYK90.pdf");
        $this->importPage(1);
        $tpl_dombo = $this->importPage(1);
        $size_dombo = $this->getTemplateSize($tpl_dombo);

        $x = $pan_width - $trim_right;
        $y = $pan_height / 2 - $size_dombo['h'] / 2;
        $this->useTemplate($tpl_dombo, $x, $y, $size_dombo['w']);

// Bottom
        $this->setSourceFile("dombo/CMYK180.pdf");
        $this->importPage(1);
        $tpl_dombo = $this->importPage(1);
        $size_dombo = $this->getTemplateSize($tpl_dombo);

        $x = $pan_width / 2 - $size_dombo['w'] / 2;
        $y = $pan_height - $trim_bottom;
        $this->useTemplate($tpl_dombo, $x, $y, $size_dombo['w']);

// Left
        $this->setSourceFile("dombo/CMYK270.pdf");
        $this->importPage(1);
        $tpl_dombo = $this->importPage(1);
        $size_dombo = $this->getTemplateSize($tpl_dombo);

        $x = $trim_left - $size_dombo['w'];
        $y = $pan_height / 2 - $size_dombo['h'] / 2;
        $this->useTemplate($tpl_dombo, $x, $y, $size_dombo['w']);
    }

    function _endpage() {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }


}

$dao = new fakepDAO();
$pdf = new PDF_Rotate();

$pan_width = $fb->form("pan_width");
$pan_height = $fb->form("pan_height");
$trim_left = $fb->form("trim_left");
$trim_right = $fb->form("trim_right");
$trim_top = $fb->form("trim_top");
$trim_bottom = $fb->form("trim_bottom");

$pdf->AddPanPage($pan_width, $pan_height, $trim_left, $trim_top, $trim_right, $trim_bottom);

$files = explode("|", $fb->form("position_info"));

$pdf->SetLineWidth(0.1);
foreach($files as $file) {
    $infos = explode("/",$file);
    //$pdf->SetLineWidth(0.1);
    // TopLeft
    $pdf->Line($infos[1] - 2, $infos[2], $infos[1] - 1, $infos[2]); // ㅡ
    $pdf->Line($infos[1], $infos[2] - 2, $infos[1], $infos[2] - 1); // ㅣ

    // TopRight
    $pdf->Line($infos[1] + $infos[3], $infos[2] - 2, $infos[1] + $infos[3], $infos[2] - 1); // l
    $pdf->Line($infos[1] + $infos[3] + 1, $infos[2], $infos[1] + $infos[3] + 2, $infos[2]); // ㅡ


    // BottomLeft
    $pdf->Line($infos[1] - 2, $infos[2] + $infos[4],     $infos[1] - 1, $infos[2] + $infos[4]);
    $pdf->Line($infos[1], $infos[2] + $infos[4] + 2, $infos[1],     $infos[2] + $infos[4] + 1);

    // BottomRight
    $pdf->Line($infos[1] + $infos[3] + 2, $infos[2] + $infos[4], $infos[1] + $infos[3] + 1, $infos[2] + $infos[4]); // ㅡ
    $pdf->Line($infos[1] + $infos[3], $infos[2] + $infos[4] + 2, $infos[1] + $infos[3], $infos[2] + $infos[4] + 1); // |
}

$i = 0;

foreach($files as $file) {
    $infos = explode("/",$file);

    $param = array();
    $param['order_detail_file_num'] = $infos[0];
    $file_path = $dao->selectPDFPath($conn, $param);

    $pdf->setSourceFile($file_path);

    $tpl = $pdf->importPage(1);
    $size = $pdf->getTemplateSize($tpl);

    if($infos[5] != "0") {
        $pdf_tmp = new PDF_Rotate();
        if($size['w'] > $size['h']) {
            if($infos[5] == "180")
                $pdf_tmp->AddPage('L', [$size['w'], $size['h'], 'Rotate' => $infos[5]]);
            else
                $pdf_tmp->AddPage('P', [$size['w'], $size['h'], 'Rotate' => $infos[5]]);
        }
        else
        {
            if($infos[5] == "180")
                $pdf_tmp->AddPage('P', [$size['w'], $size['h'], 'Rotate' => $infos[5]]);
            else
                $pdf_tmp->AddPage('L', [$size['w'], $size['h'], 'Rotate' => $infos[5]]);
        }

        $pdf_tmp->setSourceFile($file_path);
        $tpl_tmp = $pdf_tmp->importPage(1);
        $size_tmp = $pdf_tmp->getTemplateSize($tpl_tmp);

        $pdf_tmp->Rotate(-$infos[5],0,0);
        if($infos[5] == "90")
            $pdf_tmp->useTemplate($tpl_tmp, 0, -$size_tmp['h'], $size_tmp['w']);
        else if($infos[5] == "180")
            $pdf_tmp->useTemplate($tpl_tmp, -$size_tmp['w'], -$size_tmp['h'], $size_tmp['w']);
        else if($infos[5] == "270")
            $pdf_tmp->useTemplate($tpl_tmp, -$size_tmp['w'], 0, $size_tmp['w']);
        else
            $pdf_tmp->useTemplate($tpl_tmp, 0, -$size_tmp['h'],$size_tmp['w']);

        $pdf_tmp->Output("tmp_rotated_pdf/rotated". $i . ".pdf", "F");
        $file_path = "tmp_rotated_pdf/rotated" .$i . ".pdf";
        $pdf->setSourceFile($file_path);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);
        //$tpl = $pdf->importPage(1);
    } else {
        $pdf->SetFillColor(255,255,255);
        $pdf->Rect($infos[1], $infos[2], $infos[3], $infos[4], 'F');
    }
    $pdf->useTemplate($tpl, $infos[1], $infos[2], $size['w']);
    $i++;
}

$pdf->Rotate(180, $pan_width / 2, $pan_height / 2);

foreach($files as $file) {
    $infos = explode("/",$file);
    $infos[2] = $pan_height - ($infos[2] + $infos[4]);
    //$pdf->SetLineWidth(0.1);
    // TopLeft
    $pdf->Line($infos[1] - 2, $infos[2], $infos[1] - 1, $infos[2]); // ㅡ
    $pdf->Line($infos[1], $infos[2] - 2, $infos[1], $infos[2] - 1); // ㅣ

    // TopRight
    $pdf->Line($infos[1] + $infos[3], $infos[2] - 2, $infos[1] + $infos[3], $infos[2] - 1); // l
    $pdf->Line($infos[1] + $infos[3] + 1, $infos[2], $infos[1] + $infos[3] + 2, $infos[2]); // ㅡ


    // BottomLeft
    $pdf->Line($infos[1] - 2, $infos[2] + $infos[4],     $infos[1] - 1, $infos[2] + $infos[4]);
    $pdf->Line($infos[1], $infos[2] + $infos[4] + 2, $infos[1],     $infos[2] + $infos[4] + 1);

    // BottomRight
    $pdf->Line($infos[1] + $infos[3] + 2, $infos[2] + $infos[4], $infos[1] + $infos[3] + 1, $infos[2] + $infos[4]); // ㅡ
    $pdf->Line($infos[1] + $infos[3], $infos[2] + $infos[4] + 2, $infos[1] + $infos[3], $infos[2] + $infos[4] + 1); // |
}

foreach($files as $file) {
    $infos = explode("/",$file);
    $infos[2] = $pan_height - ($infos[2] + $infos[4]);

    if($infos[5] == "0" || $infos[5] == "180")
        $infos[5] = ($infos[5] + 180) % 360;

    $param = array();
    $param['order_detail_file_num'] = $infos[0];
    $file_path = $dao->selectPDFPath($conn, $param);

    $cnt = $pdf->setSourceFile($file_path);
    if($cnt == 1) continue;

    $tpl = $pdf->importPage(2);
    $size = $pdf->getTemplateSize($tpl);


    if($infos[5] != "0") {
        $pdf_tmp = new PDF_Rotate();
        if($size['w'] > $size['h']) {
            if($infos[5] == "180")
                $pdf_tmp->AddPage('L', [$size['w'], $size['h'], 'Rotate' => $infos[5]]);
            else
                $pdf_tmp->AddPage('P', [$size['w'], $size['h'], 'Rotate' => $infos[5]]);
        }
        else
        {
            if($infos[5] == "180")
                $pdf_tmp->AddPage('P', [$size['w'], $size['h'], 'Rotate' => $infos[5]]);
            else
                $pdf_tmp->AddPage('L', [$size['w'], $size['h'], 'Rotate' => $infos[5]]);
        }

        $pdf_tmp->setSourceFile($file_path);
        $tpl_tmp = $pdf_tmp->importPage(2);
        $size_tmp = $pdf_tmp->getTemplateSize($tpl_tmp);

        $pdf_tmp->Rotate(-$infos[5],0,0);
        if($infos[5] == "90")
            $pdf_tmp->useTemplate($tpl_tmp, 0, -$size_tmp['h'], $size_tmp['w']);
        else if($infos[5] == "180")
            $pdf_tmp->useTemplate($tpl_tmp, -$size_tmp['w'], -$size_tmp['h'], $size_tmp['w']);
        else if($infos[5] == "270")
            $pdf_tmp->useTemplate($tpl_tmp, -$size_tmp['w'], 0, $size_tmp['w']);
        else
            $pdf_tmp->useTemplate($tpl_tmp, 0, 0,$size_tmp['w']);

        $pdf_tmp->Output("tmp_rotated_pdf/rotated". $i . ".pdf", "F");
        $file_path = "tmp_rotated_pdf/rotated" .$i . ".pdf";
        $pdf->setSourceFile($file_path);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);
        //$tpl = $pdf->importPage(1);
    } else {
        $pdf->SetFillColor(255,255,255);
        $pdf->Rect($infos[1], $infos[2], $infos[3], $infos[4], 'F');
    }
    $pdf->useTemplate($tpl, $infos[1], $infos[2], $size['w']);
    $i++;
}

$pdf->Output('F', 'imposition_result.pdf');

$file_size = filesize('imposition_result.pdf');
header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=imposition_result.pdf");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
flush();
readfile("imposition_result.pdf");

?>