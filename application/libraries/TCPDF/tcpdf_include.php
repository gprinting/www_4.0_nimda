<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 2017-08-04
 * Time: 오전 10:27
 */

/**
 * Search and include the TCPDF library.
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Include the main class.
 * @author Nicola Asuni
 * @since 2013-05-14
 */

// always load alternative config file for examples
require_once('examples/config/tcpdf_config_alt.php');

// Include the main TCPDF library (search the library on the following directories).
$tcpdf_include_dirs = array(
    realpath('tcpdf.php')
);

var_dump($tcpdf_include_dirs);
foreach ($tcpdf_include_dirs as $tcpdf_include_path) {
    echo $tcpdf_include_path;
    if (@file_exists($tcpdf_include_path)) {
        echo $tcpdf_include_path;
        require_once($tcpdf_include_path);
        break;
    }
}
exit;
//============================================================+
// END OF FILE
//============================================================+

?>