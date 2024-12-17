<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/util/nimda/ErpCommonUtil.inc');
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/common/NimdaCommonDAO.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$util = new ErpCommonUtil();
$fb = new FormBean();
$dao = new NimdaCommonDAO();

$seqno_arr = explode(",", $fb->form("seqno"));

// 업로드 파일 디렉토리
$up_dir = INC_PATH . "/PDF/tmp_zip"; 
$zip_dir = "../PDF/tmp_zip"; 

// tmp 디렉토리 만들기 위해서 file_dir 만들기 
$tmp = microtime(); 
$tmp2 = explode(" ",$tmp); 
$file_dir = $tmp2[1].sprintf("%03d",(int)($tmp2[0]*1000)); 
$tmp=null; 
$tmp2=null; 

// tmp 디렉토리 
$tmp_up_dir = $up_dir."/".$file_dir; 
$tmp_dir = $zip_dir."/".$file_dir; 

// tmp 디렉토리 생성 
if(!mkdir ($tmp_dir, 0775)) { 
    $util->error("파일 생성시 에러가 발생했습니다." . $tmp_dir); 
    exit; 
} 

foreach ($seqno_arr as $key => $value) {

    $param = array();
    $param["table"] = "order_detail_count_file";
    $param["col"] = "file_path, save_file_name";
    $param["where"]["order_detail_seqno"] = $value;

    $rs = $dao->selectData($conn, $param);

    $file_path = $rs->fields["file_path"] . $rs->fields["save_file_name"];
    @copy($file_path, $tmp_dir."/".$rs->fields["save_file_name"]); 
}

$conn->close();

@exec("/usr/bin/zip -j " . $tmp_up_dir."/".$file_dir.".zip ".$tmp_dir."/*"); 

// 다운로드 받기 
if( $fp = @fopen( $tmp_up_dir."/".$file_dir.".zip","r")) { 
    Header("Content-type: file/unknown"); 
    Header("Content-Disposition: attachment; filename=".$file_dir.".zip"); 
    Header("Content-Description: PHP3 Generated Data"); 

    while ($data=fread($fp, filesize( $tmp_up_dir."/".$file_dir.".zip"))){ 
        print($data); 
    } 
} else { 
    $util->error('파일이 존재 하지 않습니다.');
}

if (is_dir($tmp_up_dir)) {
    echo @exec("rm -rf {$tmp_up_dir}");
    exit;
}
?>
