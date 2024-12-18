<?php

require('fpdf.php');
 require('fpdi.php');


$files = array('L17121300335.pdf', 'L17121300373.pdf');

$pdf = new FPDI();

// iterate over array of files and merge
foreach ($files as $file) {
    $pdf->setSourceFile($file);
    $tpl = $pdf->importPage(1, '/MediaBox');
	$size = $pdf->getTemplateSize($tpl);
    if ($size['w'] > $size['h']) {
                $pdf->AddPage('L', array($size['w'], $size['h']));
            } else {
                $pdf->AddPage('P', array($size['w'], $size['h']));
            }
    $pdf->useTemplate($tpl);
}

// output the pdf as a file (http://www.fpdf.org/en/doc/output.htm)
$pdf->Output('F','merged.pdf');
echo "string";

?>