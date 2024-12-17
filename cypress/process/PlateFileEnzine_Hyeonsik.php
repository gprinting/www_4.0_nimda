<?php
/**
 * Created by PhpStorm.
 * User: Hyeonsik Cho
 * Date: 2017-08-15
 * Time: 오후 6:55
 */

define("CYPRESS", "/home/sitemgr/nimda/cypress");
define("INC_PATH", "/home/sitemgr/inc");
define("LOGPATH", "/home/sitemgr/nimda/cypress/process/logs/" . date("Y_m_d"));

include_once(INC_PATH . "/define/nimda/cypress_init.inc");
include_once(INC_PATH . "/common_lib/cypress_file.inc");
include_once(CYPRESS . '/process/common/ConnectionPool.php');
include_once(INC_PATH . '/com/dprinting/PlateFileDAO.inc');


$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$dao = new PlateFileDAO();
$imagik = new Imagick();

$param = array();

$newly_made_files = $dao->getNewPlates($conn, $param);

foreach($newly_made_files as $newly_made_file) {
    $output_path = _CYP_FILE_ENZINE_OUTPUT . "/" . $newly_made_file["save_path"];
    $printfile_path = _WEB_FILE_ENZINE_PRINTFILE . "/" . $newly_made_file["save_path"];
    $preview_path = _WEB_FILE_ENZINE_PREVIEW . "/" . $newly_made_file["save_path"];
    $label_path = _WEB_FILE_ENZINE_LABEL . "/" . $newly_made_file["save_path"];

    if(!is_dir($printfile_path)) {
        $old = umask(0);
        mkdir($printfile_path, 0777, true);
        umask($old);
    }

    if(!is_dir($preview_path)) {
        $old = umask(0);
        mkdir($preview_path, 0777, true);
        umask($old);
    }

    if(!is_dir($label_path)) {
        $old = umask(0);
        mkdir($label_path, 0777, true);
        umask($old);
    }

    $file_list_in_output = CLS_File::FileList($output_path);
    if(count($file_list_in_output) == 0) {
        CLS_File::FileWrite(LOGPATH, $file_list_in_output . "디렉토리에 파일이 존재하지 않습니다","a+");
        continue;
    }

    foreach($file_list_in_output as $a_file_in_output) {
        CLS_File::FileWrite(LOGPATH, $a_file_in_output . " 처리...","a+");
        echo $a_file_in_output . " 처리...\n";
        if(pathinfo($a_file_in_output, PATHINFO_EXTENSION) == "png") {
            $param = array();
            $param["file_path"] = $preview_path;
            $param["save_file_name"] = md5($a_file_in_output) . ".png";
            $param["origin_file_name"] = $a_file_in_output;
            $param["sheet_typset_seqno"] = $newly_made_file["sheet_typset_seqno"];
            CLS_File::FileCopy($output_path . "/" . $a_file_in_output, $preview_path . "/" . $param["save_file_name"]);
            $dao->insertSheetTypsetPreviewFile($conn, $param);
        }
        else if(pathinfo($a_file_in_output, PATHINFO_EXTENSION) == "pdf") {
            $isLabel = substr($a_file_in_output, strlen($a_file_in_output) - 6, 6);
            if($isLabel == "-P.pdf" || $isLabel == "-L.pdf") {
                $param = array();
                $param["file_path"] = $label_path;
                $param["save_file_name"] = $a_file_in_output;
                $param["origin_file_name"] = $a_file_in_output;
                $param["sheet_typset_seqno"] = $newly_made_file["sheet_typset_seqno"];
                CLS_File::FileCopy($output_path . "/" . $a_file_in_output, $label_path . "/" . $param["save_file_name"]);

                $imagik->readImage($label_path . "/" . $param["save_file_name"]);
                $imagik->setImageFormat("png");
                $image_count = $imagik->getNumberImages();

                for ($i = 1; $i <= $image_count; $i++) {
                    $imagik->setIteratorIndex($i);

                    $save_path = $label_path . '/'
                        . md5(pathinfo($param["save_file_name"])["filename"] . '_' . $i) . ".png";

                    $save_path_arr[] = $save_path;

                    $imagik->writeImage($save_path);

                    $label_param = array();
                    $label_param["file_path"] = $label_path;
                    $label_param["save_file_name"] = md5(pathinfo($param["save_file_name"])["filename"] . '_' . $i) . ".png";
                    $label_param["origin_file_name"] = pathinfo($param["save_file_name"])["filename"] . '_' . $i . ".png";
                    $label_param["sheet_typset_seqno"] = $newly_made_file["sheet_typset_seqno"];
                    $dao->insertSheetTypsetLabelFile($conn, $label_param);
                }

                $imagik->clear();
                $imagik->destroy();
            }

            $param = array();
            $param["file_path"] = $printfile_path;
            $param["save_file_name"] = $a_file_in_output;
            $param["origin_file_name"] = $a_file_in_output;
            $param["sheet_typset_seqno"] = $newly_made_file["sheet_typset_seqno"];
            CLS_File::FileCopy($output_path . "/" . $a_file_in_output, $printfile_path . "/" . $param["save_file_name"]);
            $dao->insertSheetTypsetFile($conn, $param);

        } else {
            CLS_File::FileWrite(LOGPATH, "싸이프레스 미지원 파일 : " . $a_file_in_output,"a+");
        }
    }

    $param = array();
    $param["print_title"] = $newly_made_file["print_title"];
    $typset_info = $dao->getTypsetFormatInfo($conn, $param);

    $insert_param = array();
    $insert_param["dvs"] = $typset_info["format_name"] . "_" . $newly_made_file["dlvrboard"];
    $insert_param["ord_dvs"] = "";
    $insert_param["typset_num"] = $newly_made_file["typset_num"];
    $insert_param["paper"] = $typset_info["paper"];
    $insert_param["size"] = $typset_info["size"];
    $insert_param["print_tmpt"] = explode("_", $newly_made_file["print_title"])[2];
    $insert_param["amt"] = $newly_made_file["print_amt"];
    $insert_param["amt_unit"] = "장";
    $insert_param["specialty_items"] = $newly_made_file["specialty_items"];

    $result = $dao->insertProduceOrd($conn, $insert_param);
    if($result == true) {
        $param["sheet_typset_seqno"] = $newly_made_file["sheet_typset_seqno"];
        $dao->updateComplete($conn, $param);
    }


}

?>
