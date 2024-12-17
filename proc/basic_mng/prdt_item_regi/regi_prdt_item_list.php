<?
define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . '/com/nexmotion/common/util/ConnectionPool.inc');
include_once(INC_PATH . '/com/nexmotion/common/entity/FormBean.inc');
include_once(INC_PATH . '/com/nexmotion/job/nimda/basic_mng/prdt_mng/PrdtItemRegiDAO.inc');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fb = new FormBean();
$prdtItemRegiDAO = new PrdtItemRegiDAO();
$result = true;

//$conn->debug = 1;

$conn->StartTrans();

//카테고리 분류 코드
$cate_sortcode = $fb->form("cate_sortcode"); 

//상품종이 일련번호
$seqno = explode(',', $fb->form("seqno"));
$select_el = $fb->form("selectEl");

$table = "";

if ($select_el === "size") {
    $table = "cate_stan";
} else if ($select_el === "tmpt") {
    $table = "cate_print";
} else {
    $table = "cate_" . $select_el;
}

$i = 0;
foreach ($seqno as $key=>$value) {

    //종이
    if ($select_el === "paper") {

        $param = array();
        $param["table"] = "prdt_paper";
        $param["col"] = "sort, name, dvs, color, basisweight, basisweight_unit";
        $param["where"]["prdt_paper_seqno"] = $seqno[$i];

        $rs = $prdtItemRegiDAO->selectData($conn, $param); 

        $sort = $rs->fields["sort"];
        $name = $rs->fields["name"];
        $dvs = $rs->fields["dvs"];
        $color = $rs->fields["color"];
        $basisweight = $rs->fields["basisweight"] . $rs->fields["basisweight_unit"];

        $param = array();
        $param["table"] = "cate_paper";
        $param["col"] = "cate_paper_seqno";
        $param["where"]["sort"] = $sort;
        $param["where"]["name"] = $name;
        $param["where"]["dvs"] = $dvs;
        $param["where"]["color"] = $color;
        $param["where"]["basisweight"] = $basisweight;
        $param["where"]["cate_sortcode"] = $cate_sortcode;
        
        $rs = $prdtItemRegiDAO->selectData($conn, $param); 

        $param = array();
        $param["table"] = $table;
        $param["col"] = "MAX(mpcode) AS mpcode";
        $mpcode_rs = $prdtItemRegiDAO->selectData($conn, $param);

        $mpcode = $mpcode_rs->fields["mpcode"] + 1;

        if ($mpcode == "" || $mpcode == NULL) {
            $mpcode = 0;
        }

        if ($mpcode_rs->EOF == 1) {
            $mpcode = 0;
        }

        //중복된 값이 없으면 
        if ($rs->EOF == 1) {
            $param = array();
            $param["table"] = "cate_paper";
            $param["col"]["sort"] = $sort;
            $param["col"]["name"] = $name;
            $param["col"]["dvs"] = $dvs;
            $param["col"]["color"] = $color;
            $param["col"]["basisweight"] = $basisweight;
            $param["col"]["cate_sortcode"] = $cate_sortcode;
            $param["col"]["mpcode"] = $mpcode;

            $result = $prdtItemRegiDAO->insertData($conn, $param);
        }
   
    //사이즈
    } else if ($select_el == "size") {
 
        $param = array();
        $param["table"] = $table;
        $param["col"] = "prdt_stan_seqno";
        $param["where"]["prdt_stan_seqno"] = $seqno[$i];
        $param["where"]["cate_sortcode"] = $cate_sortcode;
        
        $rs = $prdtItemRegiDAO->selectData($conn, $param); 

        $param = array();
        $param["table"] = $table;
        $param["col"] = "MAX(mpcode) AS mpcode";
        $mpcode_rs = $prdtItemRegiDAO->selectData($conn, $param);

        $mpcode = $mpcode_rs->fields["mpcode"] + 1;

        if ($mpcode == "" || $mpcode == NULL) {
            $mpcode = 0;
        }

        if ($mpcode_rs->EOF == 1) {
            $mpcode = 0;
        }

        //중복된 값이 없으면 
        if ($rs->EOF == 1) {
            $param = array();
            $param["table"] = $table;
            $param["col"]["prdt_stan_seqno"] = $seqno[$i];
            $param["col"]["cate_sortcode"] = $cate_sortcode;
            $param["col"]["mpcode"] = $mpcode;

            $result = $prdtItemRegiDAO->insertData($conn, $param);
        }

    //인쇄도수
    } else if ($select_el == "tmpt") {

        $param = array();
        $param["table"] = $table;
        $param["col"] = "prdt_print_seqno";
        $param["where"]["prdt_print_seqno"] = $seqno[$i];
        $param["where"]["cate_sortcode"] = $cate_sortcode;
        
        $rs = $prdtItemRegiDAO->selectData($conn, $param); 

        $param = array();
        $param["table"] = $table;
        $param["col"] = "MAX(mpcode) AS mpcode";
        $mpcode_rs = $prdtItemRegiDAO->selectData($conn, $param);

        $mpcode = $mpcode_rs->fields["mpcode"] + 1;

        if ($mpcode == "" || $mpcode == NULL) {
            $mpcode = 0;
        }

        if ($mpcode_rs->EOF == 1) {
            $mpcode = 0;
        }

        //중복된 값이 없으면 
        if ($rs->EOF == 1) {
            $param = array();
            $param["table"] = $table;
            $param["col"]["prdt_print_seqno"] = $seqno[$i];
            $param["col"]["cate_sortcode"] = $cate_sortcode;
            $param["col"]["mpcode"] = $mpcode;

            $result = $prdtItemRegiDAO->insertData($conn, $param);
        }
 
    //후공정
    } else if ($select_el == "after") {
        $basic_yn     = $fb->form("basic_yn");
        $size_arr     = explode('!', substr($fb->form("size"), 0, -1));
        $crtr_unit    = $fb->form("crtr_unit");

        $param = array();
        $param["table"]            = $table;
        $param["basic_yn"]         = $basic_yn;
        $param["cate_sortcode"]    = $cate_sortcode;
        $param["prdt_after_seqno"] = $seqno[$i];

        // 마지막 mpcode 검색
        $mpcode = $prdtItemRegiDAO->selectLastMpcode($conn, $param);

        foreach ($size_arr as $size) {
            $param["affil"]     = $affil;
            $param["size"]      = $size;
            $param["crtr_unit"] = $crtr_unit;

            $ret = $prdtItemRegiDAO->selectCateAfterDupChk($conn, $param);

            if ($ret) {
                continue;
            }

            $temp = array();
            $temp["table"] = $table;
            $temp["col"]["prdt_" . $select_el . "_seqno"] = $seqno[$i];
            $temp["col"]["cate_sortcode"] = $cate_sortcode;
            $temp["col"]["mpcode"] = $mpcode++;
            $temp["col"]["basic_yn"] = $basic_yn;
            $temp["col"]["size"]     = $size;
            $temp["col"]["crtr_unit"] = $crtr_unit;

            $result = $prdtItemRegiDAO->insertData($conn, $temp);
        }


    // 옵션
    } else if ($select_el == "opt") {
        $param = array();
        $param["table"] = $table;
        $param["col"] = "prdt_" . $select_el . "_seqno";
        $param["where"]["prdt_" . $select_el . "_seqno"] = $seqno[$i];
        $param["where"]["cate_sortcode"] = $cate_sortcode;
        $param["where"]["basic_yn"] = $fb->form("basic_yn");
        
        $rs = $prdtItemRegiDAO->selectData($conn, $param); 

        $param = array();
        $param["table"] = $table;
        $param["col"] = "MAX(mpcode) AS mpcode";
        $mpcode_rs = $prdtItemRegiDAO->selectData($conn, $param);

        $mpcode = $mpcode_rs->fields["mpcode"] + 1;

        if ($mpcode == "" || $mpcode == NULL) {
            $mpcode = 0;
        }

        if ($mpcode_rs->EOF == 1) {
            $mpcode = 0;
        }

        //중복된 값이 없으면 
        if ($rs->EOF == 1) {
            $param = array();
            $param["table"] = $table;
            $param["col"]["prdt_" . $select_el . "_seqno"] = $seqno[$i];
            $param["col"]["cate_sortcode"] = $cate_sortcode;
            $param["col"]["mpcode"] = $mpcode;
            $param["col"]["basic_yn"] = $fb->form("basic_yn");

            $result = $prdtItemRegiDAO->insertData($conn, $param);
        }

    // 실사후공정
    } else if ($select_el == "ao_after") {
        $basic_yn     = $fb->form("basic_yn");
        $crtr_unit    = $fb->form("crtr_unit");

        $param = array();
        $param["table"]            = $table;
        $param["basic_yn"]         = $basic_yn;
        $param["cate_sortcode"]    = $cate_sortcode;
        $param["ao_after_seqno"] = $seqno[$i];

        // 마지막 mpcode 검색
        $mpcode = $prdtItemRegiDAO->selectLastMpcode($conn, $param);

        $temp = array();
        $temp["table"] = $table;
        $temp["col"][$select_el . "_seqno"] = $seqno[$i];
        $temp["col"]["cate_sortcode"] = $cate_sortcode;
        $temp["col"]["mpcode"] = $mpcode++;
        $temp["col"]["basic_yn"] = $basic_yn;
        $temp["col"]["crtr_unit"] = $crtr_unit;

        $result = $prdtItemRegiDAO->insertData($conn, $temp);

    // 실사 옵션
    } else if ($select_el == "ao_opt") {
        $param = array();
        $param["table"] = $table;
        $param["col"] = $select_el . "_seqno";
        $param["where"][$select_el . "_seqno"] = $seqno[$i];
        $param["where"]["cate_sortcode"] = $cate_sortcode;
        $param["where"]["basic_yn"] = $fb->form("basic_yn");
        
        $rs = $prdtItemRegiDAO->selectData($conn, $param); 

        $mpcode = $prdtItemRegiDAO->selectLastMpcode($conn, $param);

        //중복된 값이 없으면 
        if ($rs->EOF == 1) {
            $param = array();
            $param["table"] = $table;
            $param["col"][$select_el . "_seqno"] = $seqno[$i];
            $param["col"]["cate_sortcode"] = $cate_sortcode;
            $param["col"]["mpcode"] = $mpcode;
            $param["col"]["basic_yn"] = $fb->form("basic_yn");

            $result = $prdtItemRegiDAO->insertData($conn, $param);
        }
    }

    $i++;
}

echo $result;
$conn->CompleteTrans();
$conn->close();
?>
