<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2018-02-18
 * Time: 오전 10:53
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
        $this->AddUHCFont('명조');
        $this->AddUHCFont('고딕', 'HYGoThic-Medium-Acro');
        $this->AddUHCFont('돋움', 'Dotum');
        $this->AddUHCFont('바탕', 'Batang');
        $this->AddUHCFont('궁서', 'Gungsuh');
        $this->AddUHCFont('굴림', 'Gulim');
        $this->AddUHCFont('한겨레결체', '한겨레결체');
        $this->AddUHCFont('없는글꼴', '없는글꼴');

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

    var $extgstates = array();

    // alpha: real value from 0 (transparent) to 1 (opaque)
    // bm:    blend mode, one of the following:
    //          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
    //          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
    function SetAlpha($alpha, $bm='Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
        $this->SetExtGState($gs);
    }

    function AddExtGState($parms)
    {
        $n = count($this->extgstates)+1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    function _enddoc()
    {
        if(!empty($this->extgstates) && $this->PDFVersion<'1.4')
            $this->PDFVersion='1.4';
        parent::_enddoc();
    }

    function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++)
        {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_out('<</Type /ExtGState');
            $parms = $this->extgstates[$i]['parms'];
            $this->_out(sprintf('/ca %.3F', $parms['ca']));
            $this->_out(sprintf('/CA %.3F', $parms['CA']));
            $this->_out('/BM '.$parms['BM']);
            $this->_out('>>');
            $this->_out('endobj');
        }
    }

    function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_out('/ExtGState <<');
        foreach($this->extgstates as $k=>$extgstate)
            $this->_out('/GS'.$k.' '.$extgstate['n'].' 0 R');
        $this->_out('>>');
    }

    function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }
}

$dao = new fakepDAO();
$pdf = new PDF_Rotate();
//$alabel = "<text x=\"133.510940950834\" y=\"110.938895570488\" width=\"50.8446457544354\" height=\"7.57607640072443\" align=\"center\" isbinding=\"true\"><stroke color=\"#FF000000\" weight=\"0.25\" /><fill color=\"#00FFFFFF\" /><contents>@주문번호</contents><font size=\"6\" name=\"\" /></text>\";

$pan_width = $fb->form("pan_width");
$pan_height = $fb->form("pan_height");
$trim_left = $fb->form("trim_left");
$trim_right = $fb->form("trim_right");
$trim_top = $fb->form("trim_top");
$trim_bottom = $fb->form("trim_bottom");
$alabel = $fb->form("shapes");

$pdf->AddPanPage($pan_width, $pan_height, $trim_left, $trim_top, $trim_right, $trim_bottom);
$object = simplexml_load_string($alabel);

$channel = $object;
$json = json_encode($object);
$array = json_decode($json,TRUE);

foreach($array as $label) {
    for($i = 0 ; $i < count($label) ; $i++) {
        $label_rotated_x = $label[$i]["@attributes"]["rotated_x"];
        $label_rotated_y = $label[$i]["@attributes"]["rotated_y"];
        $label_width = $label[$i]["@attributes"]["width"];
        $label_height = $label[$i]["@attributes"]["height"];
        $label_angle = $label[$i]["@attributes"]["angle"];
        $label_template_width = $label[$i]["@attributes"]["template_width"];
        $label_template_height = $label[$i]["@attributes"]["template_height"];
        $label_trimleft = ($label_width - $label_template_width) / 2;
        $label_trimtop = ($label_height - $label_template_height) / 2;

        $pdf_tmp = new PDF_Rotate();
        $pdf_tmp->AddUHCFont('명조');
        $pdf_tmp->AddUHCFont('고딕', 'HYGoThic-Medium-Acro');
        $pdf_tmp->AddUHCFont('돋움', 'Dotum');
        $pdf_tmp->AddUHCFont('바탕', 'Batang');
        $pdf_tmp->AddUHCFont('궁서', 'Gungsuh');
        $pdf_tmp->AddUHCFont('굴림', 'Gulim');
        $pdf_tmp->AddUHCFont('한겨레결체', '한겨레결체');
        $pdf_tmp->AddUHCFont('없는글꼴', '없는글꼴');
        $pdf_tmp->SetFont('굴림', '', 5);
        if ($label_width > $label_height) {
            $pdf_tmp->AddPage('L', [$label_width, $label_height]);
        } else {
            $pdf_tmp->AddPage('P', [$label_width, $label_height]);
        }

        //$pdf_tmp->Rotate(-$label_angle, $label_width / 2, $label_height / 2);
        $pdf_tmp->SetAutoPageBreak(false);

//$pdf->Cell($x, $y, mb_convert_encoding($contents,'EUC-KR', 'UTF-8'),$width, $height, 'D');
        //$pdf->Rotate(-$label_angle, $label_x + ($label_width / 2), $label_y + ($label_height / 2));
        //$pdf->Rotate($label_angle, $label_x,  $label_y);

        if (array_key_exists("rect", $label[$i])) {
            foreach ($label[$i]["rect"] as $value) {
                $x = $value["@attributes"]['x'] + $label_trimleft;
                $y = $value["@attributes"]['y'] + $label_trimtop;
                $width = $value["@attributes"]['width'];
                $height = $value["@attributes"]['height'];
                $angle = $value["@attributes"]['angle'];
                $fill = $value["fill"]["@attributes"]["color"];
                $stroke_color = $value["stroke"]["@attributes"]["color"];
                $stroke_weight = $value["stroke"]["@attributes"]["weight"];
                $rgb = hex2rgb($fill);
                $stroke_rgb = hex2rgb($stroke_color);
                $pdf_tmp->Rotate(-$angle, $x + ($width / 2), $y + ($height / 2));
                $pdf_tmp->SetFillColor($rgb[1],$rgb[2],$rgb[3]);
                $pdf_tmp->SetLineWidth($stroke_weight);
                $pdf_tmp->SetDrawColor($stroke_rgb[1],$stroke_rgb[2],$stroke_rgb[3]);
                //$pdf_tmp->SetAlpha($rgb[0] / 255);
                $pdf_tmp->Rect($x, $y, $width, $height, "D");
            }
        }

        if (array_key_exists("text", $label[$i])) {
            foreach ($label[$i]["text"] as $value) {
                $x = $value["@attributes"]['x'] + $label_trimleft;
                $y = $value["@attributes"]['y'] + $label_trimtop;// + $label_gap_y;
                $width = $value["@attributes"]['width'];
                $height = $value["@attributes"]['height'];
                $angle = $value["@attributes"]['angle'];
                $fill = $value["fill"]["@attributes"]["color"];
                $stroke_color = $value["stroke"]["@attributes"]["color"];
                $stroke_weight = $value["stroke"]["@attributes"]["weight"];
                $font_size = $value["font"]["@attributes"]["size"];
                $contents = mb_convert_encoding($value["contents"], 'EUC-KR', 'UTF-8');
                $rgb = hex2rgb($fill);
                $stroke_rgb = hex2rgb($stroke_color);
                $pdf_tmp->Rotate(-$angle, $x + ($width / 2), $y + ($height / 2));
                $pdf_tmp->SetFillColor($rgb[1],$rgb[2],$rgb[3]);
                $pdf_tmp->SetDrawColor($stroke_rgb[1],$stroke_rgb[2],$stroke_rgb[3]);
                $pdf_tmp->SetLineWidth($stroke_weight);

                //$pdf_tmp->SetAlpha($rgb[0] / 255);
                $pdf_tmp->SetFillColor($rgb[1],$rgb[2],$rgb[3]);
                $pdf_tmp->SetXY($x, $y);
                $pdf_tmp->SetFontSize($font_size);
                $pdf_tmp->Cell($width, $height, $contents,1,1, 'L', true);
            }
        }

        if (array_key_exists("image", $label[$i])) {
            foreach ($label[$i]["image"] as $value) {
                $x = $value['x'] + $label_trimleft;
                $y = $value['y'] + $label_trimtop;// + $label_gap_y;
                $width = $value['width'];
                $height = $value['height'];
                $angle = $value['angle'];
                $path = $value['path'];

                $pdf_tmp->Rotate(-$angle, $x + ($width / 2), $y + ($height / 2));
                if(file_exists($path))
                    $pdf_tmp->Image($path,$x, $y, $width, $height);
            }
        }
        $pdf_tmp->Output('F', 'tmp_label_pdf/' . $i . '.pdf');

        if($label_angle != "0") {
            $pdf_tmp = new PDF_Rotate();
            if($label_width > $label_height) {
                if($label_angle == "180")
                    $pdf_tmp->AddPage('L', [$label_width, $label_height, 'Rotate' => $label_angle]);
                else
                    $pdf_tmp->AddPage('P', [$label_width, $label_height, 'Rotate' => $label_angle]);
            }
            else
            {
                if($label_angle == "180")
                    $pdf_tmp->AddPage('P', [$label_width, $label_height, 'Rotate' => $label_angle]);
                else
                    $pdf_tmp->AddPage('L', [$label_width, $label_height, 'Rotate' => $label_angle]);
            }

            $pdf_tmp->setSourceFile('tmp_label_pdf/' . $i . '.pdf');
            $tpl_tmp = $pdf_tmp->importPage(1);
            $size_tmp = $pdf_tmp->getTemplateSize($tpl_tmp);

            $pdf_tmp->Rotate(-$label_angle,0,0);
            if($label_angle == "90")
                $pdf_tmp->useTemplate($tpl_tmp, 0, -$size_tmp['h'], $size_tmp['w']);
            else if($label_angle == "180")
                $pdf_tmp->useTemplate($tpl_tmp, -$size_tmp['w'], -$size_tmp['h'], $size_tmp['w']);
            else if($label_angle == "270")
                $pdf_tmp->useTemplate($tpl_tmp, -$size_tmp['w'], 0, $size_tmp['w']);
            else
                $pdf_tmp->useTemplate($tpl_tmp, 0, 0,$size_tmp['w']);

            $pdf_tmp->Output('F', 'tmp_label_pdf/' . $i . '.pdf');
        }
    }
}


foreach($array as $label) {
    for ($i = 0; $i < count($label); $i++) {
        $label_rotated_x = $label[$i]["@attributes"]["rotated_x"];
        $label_rotated_y = $label[$i]["@attributes"]["rotated_y"];
        $pdf->setSourceFile('tmp_label_pdf/' . $i . '.pdf');

        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);

        $pdf->useTemplate($tpl, $label_rotated_x, $label_rotated_y, $size['w']);
    }
}

$pdf->Output('F', 'label_result.pdf');


$file_size = filesize('label_result.pdf');
header("Pragma: public");
header("Expires: 0");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=label_result.pdf");
header("Content-Transfer-Encoding: binary");
header("Content-Length: $file_size");

ob_clean();
flush();
readfile("label_result.pdf");


function hex2rgb($hex) {
    $hex = str_replace("#", "", $hex);

    $a = hexdec(substr($hex,0,2));
    $r = hexdec(substr($hex,2,2));
    $g = hexdec(substr($hex,4,2));
    $b = hexdec(substr($hex,6,2));
    $rgb = array($a, $r, $g, $b);
    //return implode(",", $rgb); // returns the rgb values separated by commas
    return $rgb; // returns an array with the rgb values
}

?>