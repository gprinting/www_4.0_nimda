<?
define("INC_PATH", $_SERVER["INC"]);
//ini_set('display_errors', 1);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/manufacture/storage_mng/StorageMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");
include_once(INC_PATH . "/define/nimda/common_config.inc");
include_once(INC_PATH . '/com/nexmotion/common/excel/PHPExcel.php');
include_once(INC_PATH . '/define/nimda/excel_define.inc');
include_once($_SERVER["DOCUMENT_ROOT"] . '/engine/common/DeliveryExcelUtil.php');
include_once(INC_PATH . '/com/dprinting/MoamoaDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$dao = new StorageMngDAO();
$fileDAO = new FileAttachDAO();
$MoamoaDAO = new MoamoaDAO();
$excelUtil = new DeliveryExcelUtil();

$filename = uniqid();

$param =  array();
$param["file_path"] = SITE_DEFAULT_DELIVERY_FILE;
$param["tmp_name"] = $_FILES["preview_file"]["tmp_name"];
$param["origin_file_name"] = $_FILES["preview_file"]["name"];

//파일을 업로드 한 후 저장된 경로를 리턴한다.
$f_result= $fileDAO->upLoadFile($param);
$file = $_SERVER["DOCUMENT_ROOT"] . $f_result["file_path"] . $f_result["save_file_name"];

$excelUtil->initExcelFileReadInfo($file, 0, 0, 0);
$excelUtil->insertDeliveryInfo($conn, $MoamoaDAO, $fb->getSession()["id"]);

echo "1";

$conn->close();
?>
