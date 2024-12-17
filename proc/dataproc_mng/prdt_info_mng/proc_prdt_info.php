<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . "/com/nexmotion/common/entity/FormBean.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/dataproc_mng/set/PrdtInfoMngDAO.inc");
include_once(INC_PATH . "/com/nexmotion/job/nimda/file/FileAttachDAO.inc");
include_once(INC_PATH . "/define/nimda/common_config.inc");

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();
$conn->StartTrans();

$fb = new FormBean();
$prdtDAO = new PrdtInfoMngDAO();
$fileDAO = new FileAttachDAO();

$check = 1;

$cate_sortcode = $fb->form("cate_sortcode");

/**********************************************************************
                         * 카테고리 정보 영역 *
 **********************************************************************/

$param = array();
$param["table"] = "cate_info";
$param["col"] = "cate_info_seqno";
$param["where"]["cate_sortcode"] = $cate_sortcode;
$result = $prdtDAO->selectData($conn, $param);
if (!$result) $check = 0;

//원래 카테고리 정보가 있는지 없는지 확인
if ($result->fields["cate_info_seqno"]) {
    $info_func = "update";
    $cate_info_seqno = $result->fields["cate_info_seqno"];

} else {
    $info_func = "insert";

}

$param = array();
$param["table"] = "cate_info";
//일반상품일때
if ($fb->form("gen_y")) {
    $param["col"]["gen_yn"] = "Y";
    
} else {
    $param["col"]["gen_yn"] = "N";

}

//기획상품일때
if ($fb->form("prjt_y")) {
    $param["col"]["prjt_yn"] = "Y";
    
} else {
    $param["col"]["prjt_yn"] = "N";

}

//새상품일때
if ($fb->form("new_y")) {
    $param["col"]["new_yn"] = "Y";
    
} else {
    $param["col"]["new_yn"] = "N";

}

//추천상품일때
if ($fb->form("recomm_y")) {
    $param["col"]["recomm_yn"] = "Y";
    
} else {
    $param["col"]["recomm_yn"] = "N";

}

//품절상품일때
if ($fb->form("soldout_y")) {
    $param["col"]["soldout_yn"] = "Y";
    
} else {
    $param["col"]["soldout_yn"] = "N";
}

//인기상품일때
if ($fb->form("popular_y")) {
    $param["col"]["popular_yn"] = "Y";
    
} else {
    $param["col"]["popular_yn"] = "N";
}

//이벤트상품일때
if ($fb->form("event_y")) {
    $param["col"]["event_yn"] = "Y";
    
} else {
    $param["col"]["event_yn"] = "N";
}

//묶음상품일때
if ($fb->form("bun_yn") == "Y") {
    $param["col"]["bun_yn"] = "Y";
    
} else {
    $param["col"]["bun_yn"] = "N";
}

$param["col"]["cate_sortcode"] = $cate_sortcode;

if ($info_func == "insert") {
    //상품정보 새로 추가일때
    $result = $prdtDAO->insertData($conn, $param);
    if (!$result) $check = 0;
} else {
    //상품정보 수정일때
    $param["prk"] = "cate_info_seqno";
    $param["prkVal"] = $cate_info_seqno;
    $result = $prdtDAO->updateData($conn, $param);
    if (!$result) $check = 0;
}

/**********************************************************************
                         * 카테고리 사진 영역 *
 **********************************************************************/


//$conn->debug = 1;
//파일을 업로드 확인 후 처리
for ($i=0; $i<5; $i++) {

    //파일이 있을때
    if ($fb->form("upload_file_check" . $i)) {

        //카테고리 파일 이전에 있는지 없는지 확인
        $param = array();
        $param["table"] = "cate_photo";
        $param["col"] = "cate_photo_seqno";
        $param["where"]["cate_sortcode"] = $cate_sortcode;
        $param["where"]["seq"] = $i;
        $result = $prdtDAO->selectData($conn, $param);
        if (!$result) $check = 0;

        //원래 카테고리 정보가 있는지 없는지 확인
        if ($result->fields["cate_photo_seqno"]) {

            $file_func = "update";
            $cate_photo_seqno = $result->fields["cate_photo_seqno"];

        } else {

            $file_func = "insert";

        }

        //파일 업로드 경로
        $param =  array();
        $param["file_path"] = SITE_DEFAULT_CATE_PHOTO_FILE;
        $param["tmp_name"] = $_FILES["upload_btn" . $i]["tmp_name"];
        $param["origin_file_name"] = $_FILES["upload_btn" . $i]["name"];

        //파일을 업로드 한 후 저장된 경로를 리턴한다.
        $f_result= $fileDAO->upLoadFile($param);
        if (!$f_result) $check = 0;

        //파일 썸네일 추가
        $param = array();
        $param["fs"] = $f_result["file_path"] . '/' . $f_result["save_file_name"];
        $param["req_width"] = "75";
        $param["req_height"] = "75";

        $fileDAO->makeThumbnail($param);

        //파일 돋보기 썸네일 추가
        $param = array();
        $param["fs"] = $f_result["file_path"] . '/' . $f_result["save_file_name"];
        $param["req_width"] = "315";
        $param["req_height"] = "315";

        $fileDAO->makeThumbnail($param);

        //카테고리 사진 파일 추가
        $param = array();
        $param["table"] = "cate_photo";
        $param["col"]["cate_sortcode"] = $cate_sortcode;

        //카테고리 사진 파일
        $param["col"]["origin_file_name"] = $_FILES["upload_btn" . $i]["name"];
        $param["col"]["save_file_name"] = $f_result["save_file_name"];
        $param["col"]["file_path"] = $f_result["file_path"];
        $param["col"]["seq"] = $i;

        //파일 새로 추가일때
        if ($file_func == "insert") {

            $result = $prdtDAO->insertData($conn, $param);
            if (!$result) $check = 0;

            //파일 수정일때
        } else {

            $param["prk"] = "cate_photo_seqno";
            $param["prkVal"] = $cate_photo_seqno;
            $result = $prdtDAO->updateData($conn, $param);
            if (!$result) $check = 0;
        }
    }
}



if ($check == 1) {
    echo "1";
} else {
    echo "2";
}

$conn->CompleteTrans();
$conn->close();
?>
