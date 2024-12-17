<?
/***********************************************************************************
 *** 프로 젝트 : CJ 송장 출력
 *** 개발 영역 : 송장 소켓 통신
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.07.25
 ***********************************************************************************/

/***********************************************************************************
 *** 인클루드
 ***********************************************************************************/

define("INC_PATH", $_SERVER["INC"]);
include_once(INC_PATH . "/define/nimda/cj_invoice_define.inc");
include_once(INC_PATH . "/com/nexmotion/common/util/ConnectionPool.inc");
include_once(INC_PATH . '/com/dprinting/InvoiceSocketDAO.inc');
include_once(INC_PATH . '/com/dprinting/CJAddressPackageDAO.inc');
include_once(INC_PATH . '/com/dprinting/file.inc');

/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$inSock = new InvoiceSocketDAO();
$CJAddr = new CJAddressPackageDAO();
$CFile = new CLS_File();


/***********************************************************************************
 *** 변수 정의
 ***********************************************************************************/

$today = date("Y.m.d");
$boxcount = "극소D";

$fileName = "/home/dprinting/nimda/logs/invoice_socket_".date("Y-m-d").".log";
$mode = "a+";
$conn->debug = 1;

/***********************************************************************************
 *** 송신 배송정보 가져오기
 ***********************************************************************************/

$strRes = $inSock->getShipInfoTransDataList($conn, array());

if ($strRes == "LOST") {
    echo "DBCON_LOST";
    exit;
}


/***********************************************************************************
*** 수신 배송정보 가져오기
***********************************************************************************/

$srvRes = $inSock->getShipInfoResvDataList($conn, array());

if ($srvRes == "LOST") {
    echo "DBCON_LOST";
    exit;
}


/***********************************************************************************
 *** Mysql Transaction Start
 ***********************************************************************************/

//$conn->StartTrans();


/***********************************************************************************
 *** 배송정보 처리
 ***********************************************************************************/

$strCount = count($strRes);
$srvCount = count($srvRes);

if ($strCount == $srvCount && $strCount > 0 &&  $srvCount > 0) {
    $json_array = array();
    for ($i = 0; $i < $strCount; $i++) {
         $invRes = $inSock->getInvoiceNumberDataValue($conn);

         if (is_array($invRes)) {
             //*** 주소 정체
             $addrRes = $CJAddr->getCJAddrPackageTransForm($_CJ_CGISDEV, $srvRes[$i]['rv_addr']);

             if (!is_array($addrRes)) {
                 echo $addrRes;
                 exit;
             }

             if ($addrRes['P_NEWADDRYN'] == "Y") {
                 $gtAddr = "[".$addrRes['P_ETCADDR']."]";
             } else {
                 $gtAddr = "";
             }

             if ($srvRes[$i]['rv_ship_div'] == "01" && $srvRes[$i]['rv_ship_way_div'] == "01") {
                 $srvRes[$i]['rv_ship_price'] = "0";
                 $srvRes[$i]['credit'] = "신용";
             } else {
                 $srvRes[$i]['credit'] = "";
             }

             $json_array["invoice"][$i] = array(
                 "invoiceNumber" =>  $invRes['in_number'],
                 "receiptDate"   =>  $today,
                 "title"         =>  $strRes[$i]['tr_title']."-".$strRes[$i]['tr_detail'],
                 "to_name"       =>  $strRes[$i]['tr_cp_name'],
                 "to_tellnum"    =>  $strRes[$i]['tr_phone'],
                 "to_cellnum"    =>  $strRes[$i]['tr_mobile'],
                 "to_address"    =>  $strRes[$i]['tr_addr'],
                 "from_name"     =>  $srvRes[$i]['rv_cp_name'],
                 "from_tellnum"  =>  $srvRes[$i]['rv_phone'],
                 "from_cellnum"  =>  $srvRes[$i]['rv_mobile'],
                 "from_address"  =>  $addrRes['P_NEWADDRESS']." ".$addrRes['P_NESADDRESSDTL']." ".$gtAddr,
                 "cost"          =>  $srvRes[$i]['rv_ship_price'],
                 "boxcount"      =>  $boxcount,
                 "calgubun"      =>  $srvRes[$i]['credit'],
                 "clsfcd"        =>  $addrRes['P_CLSFCD'],
                 "subclsfcd"     =>  $addrRes['P_SUBCLSFCD'],
                 "clsfaddr"      =>  $addrRes['P_CLSFADDR'],
                 "branshortnm"   =>  $addrRes['P_CLLDLCBRANSHORTNM'],
                 "vempnm"        =>  $addrRes['P_CLLDLVEMPNM'],
                 "vempnicknm"    =>  $addrRes['P_CLLDLVEMPNICKNM']
             );

             // 송장번호 사용현황 업뎃
             $updRes = $inSock->setInvoiceNumberDataUpdateComplete($conn, $invRes['in_number']);

             if ($invRes == "LOST") {
                 echo "DBCON_LOST";
                 exit;
             } else if ($invRes == "FAILED") {
                 echo "UPD_FAILED";
                 exit;
             }

         } else if ($invRes == "LOST") {
            echo "DBCON_LOST";
            exit;
         } else {
            echo "INV_FAILED";
            exit;
         }

        unset($invRes);
        unset($addrRes);
    }
} else {
    echo "SHIP_FAILED";
    exit;
}


/***********************************************************************************
 *** Mysql Close
 ***********************************************************************************/

$conn->close();


/***********************************************************************************
*** CJ 배송 접수
************************************************************************************/

$openSucessCount = 0;
$isDataCount = 0;

if ($strCount == $srvCount && $strCount > 0 &&  $srvCount > 0) {
    //db 연결
    $oc_conn = oci_connect(_CJ_OPEN_DB_ID, _CJ_OPEN_DB_PW, $_CJ_OPENDBTEST, 'UTF8');

    if (!$oc_conn) {
        return "ORC_NOT_CONN";
        exit;
    }

    for ($i = 0; $i < $strCount; $i++) {
         $chkRes = $CJAddr->getCJShipReceiveRequestValueCheck($oc_conn, $strRes[$i]);

         if ($chkRes == "SUCCESS") {
             $opeRes = $CJAddr->setCJShipReceiveRequestInsertComplete($oc_conn, $strRes[$i], $srvRes[$i], $json_array["invoice"][$i]);
             if ($opeRes == "SUCCESS") {
                 $openSucessCount++;
             } else {
                 echo $opeRes;
                 exit;
             }
         } else {
             $isDataCount++;
         }
    }

    if ($strCount == $openSucessCount) {
        // 트랜젝션 커밋
        oci_commit($oc_conn);
    } else if ($strCount == $isDataCount) {
        oci_close($oc_conn);

        echo "OC_NONE_PRINT";
        exit;
    } else {
        // 트랜젝션 롤백
        oci_rollback($oc_conn);
        oci_close($oc_conn);

        echo "OC_TRAC_FAILED";
        exit;
    }

    // db 종료
    oci_close($oc_conn);
}


/***********************************************************************************
 *** 전송데이터 인코딩
 ***********************************************************************************/

$json = json_encode($json_array)."@";


/***********************************************************************************
 *** 소켓통신 데이터 전송
 ***********************************************************************************/

$sock = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

if ($sock == false) {
    echo "SK_CRT_FAILED";
    exit;
}

$result = @socket_connect($sock, _CSHAP_ENZIN_IP, _CSHAP_ENZIN_PORT);

if ($result == false) {
    @socket_close($sock);
    echo "SK_CON_FAILED";
    exit;
}

@socket_write($sock, $json);
$sMsg  = @socket_read($sock, 4096);

@socket_close($sock);


/***********************************************************************************
 *** 처리결과 값 리턴
 ***********************************************************************************/

if ($sMsg == 1) {
    echo "SUCESS";
} else {
    echo "FAILED";
}

?>
