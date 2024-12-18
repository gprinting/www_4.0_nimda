<?php
require_once('/home/sitemgr/nimda/application/libraries/FPDF/vendor/autoload.php');
//require_once ('/home/sitemgr/nimda/application/libraries/FPDF/fpdi.php');

$pdf = new FPDI();
$pdf->AddPage('L',array(545,700));

$files = array();
$files[0] =  '/home/sitemgr/ndrive/attach/gp/order_detail_count_file/2018/01/08/SGPT180108NC003990101.pdf';
$files[1] =  '/home/sitemgr/ndrive/attach/gp/order_detail_count_file/2018/01/08/SGPT180108NC003990101.pdf';

$x = 10;
foreach($files as $file) {
        $pdf->setSourceFile($file);
        $tpl = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tpl);

        $pdf->useTemplate($tpl, $x, 6, $size['w']);
        $x += 200;

}

$pdf->Output('F', 'asdasd.pdf');

?>

