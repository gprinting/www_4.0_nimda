<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2018-01-23
 * Time: 오후 6:29
 */


define("CYPRESS", "/home/sitemgr/nimda/cypress");
define("INC_PATH", "/home/sitemgr/inc");

include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(CYPRESS . '/process/common/ConnectionPool.php');
include_once(INC_PATH . '/com/dprinting/PlateFileDAO.inc');


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new PlateFileDAO();
$imagik = new Imagick();
$param = array();

$newly_made_files = $dao->getNewReceipts($conn, $param);

foreach($newly_made_files as $newly_made_file) {
    $receiptfile_path = $newly_made_file["file_path"] . "/" . $newly_made_file["file_name"];
    $preview_path = "/home/sitemgr/ndrive/attach/gp/order_detail_count_preview_file/" . date("Y/m/d");
    $imagik->readImage($receiptfile_path);
    $imagik->setImageFormat("jpeg");
    $image_count = $imagik->getNumberImages();

    if(!file_exists($receiptfile_path))
        continue;

    for ($i = 0; $i < $image_count; $i++) {
        $imagik->setIteratorIndex($i);

        $param = array();
        $param["preview_file_path"] = $preview_path;
        $param["preview_file_name"] = $newly_made_file["order_detail_file_num"] . "_" . sprintf('%03d', $i+1) . ".jpg";
        $param["seq"] = $i+1;
        $param["order_detail_count_file_seqno"] = $newly_made_file["order_detail_count_file_seqno"];

        if(!is_dir($param["preview_file_path"])) {
            $old = umask(0);
            mkdir($param["preview_file_path"], 0777, true);
            umask($old);
        }

        $imagik->writeImage($param["preview_file_path"] . "/" . $param["preview_file_name"]);
        echo $param["preview_file_path"] . "생성완료" . "\n";

        $dao->insertPreviewFile($conn, $param);
    }

    $imagik->clear();
    $imagik->destroy();
}

?>
