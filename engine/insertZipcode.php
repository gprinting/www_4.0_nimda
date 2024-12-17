#! /usr/local/php/bin/php -f
<?
/***********************************************************************************
 *** 프로 젝트 : 3.0
 *** 개발 영역 : Zipcode 데이터 삽입
 *** 개  발  자 : 조현식
 *** 개발 날짜 : 2016.08.06
 ***********************************************************************************/

/***********************************************************************************
 *** 기본 인클루드
 ***********************************************************************************/

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . '/common/EngineCommon.php');
include_once(dirname(__FILE__) . '/EngineCommonFunc.php');


/***********************************************************************************
 *** 클래스 선언
 ***********************************************************************************/

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$engineDAO = new EngineDAO();
$util = new EngineCommon();
$commonFunc = new EngineCommonFunc();


/***********************************************************************************
 *** 시작
 ***********************************************************************************/

echo "\n========================================";
echo "\n>>>>>>> 우편번호 삽입 엔진 시작 <<<<<<<";
echo "\n========================================\n\n";


/***********************************************************************************
 *** 프로세스 시작
 ***********************************************************************************/

$process_start_time = time();


/***********************************************************************************
 *** 파일불러오기
 ***********************************************************************************/

$area = array();
$area[0] = "incheon";
$area[1] = "gangwon";
$area[2] = "gyeongnam";
$area[3] = "gyeongbuk";
$area[4] = "gwangju";
$area[5] = "daegu";
$area[6] = "daejun";
$area[7] = "pusan";
$area[8] = "seoul";
$area[9] = "sejong";
$area[10] = "ulsan";
$area[11] = "junnam";
$area[12] = "junbuk";
$area[13] = "jeju";
$area[14] = "chungnam";
$area[15] = "chungbuk";
$area[16] = "gyeonggi";

for($i=0; $i < count($area) ; $i++) {
    $fh = fopen($area[$i] . ".txt", 'r');

    $param["table"] = "zipcode_" . $area[$i];
    $engineDAO->createZipcodeTable($conn, $param);

    echo $param["table"] . "테이블 추가 완료";

    $j = 0;
    while (!feof($fh)) {
        $arr_str = fgets($fh);

        $arr_str = iconv("euckr", "utf8",$arr_str);

        $arr_str = explode('|', $arr_str);
        $j++;

        $param["new_zipcode"] = $arr_str[0];
        $param["si_do_name"] = $arr_str[1];
        $param["si_do_name_eng"] = $arr_str[2];
        $param["si_gun_gu"] = $arr_str[3];
        $param["si_gun_gu_eng"] = $arr_str[4];
        $param["eup_myun"] = $arr_str[5];
        $param["eup_myun_eng"] = $arr_str[6];
        $param["street_name_code"] = $arr_str[7];
        $param["street_name"] = $arr_str[8];
        $param["street_name_eng"] = $arr_str[9];
        $param["is_underground"] = $arr_str[10];
        $param["building_num_major"] = $arr_str[11];
        $param["building_num_minor"] = $arr_str[12];
        $param["building_mng_num"] = $arr_str[13];
        $param["plural_dlvr_name"] = $arr_str[14];
        $param["si_gun_gu_building_name"] = $arr_str[15];
        $param["law_defined_dong_code"] = $arr_str[16];
        $param["law_defined_dong_name"] = $arr_str[17];
        $param["ri_name"] = $arr_str[18];
        $param["admin_dong_name"] = $arr_str[19];
        $param["is_mountain"] = $arr_str[20];
        $param["lot_num_major_num"] = $arr_str[21];
        $param["eup_myun_dong_serial_num"] = $arr_str[22];
        $param["lot_num_minor_num"] = $arr_str[23];
        $param["old_zipcode"] = $arr_str[24];
        $param["zipcode_serial_num"] = $arr_str[25];

        $engineDAO->insertZipcode($conn, $param);

        if($j % 1000 == 0) {
            echo $param["table"]. " 테이블에 " . $j . "개 삽입 완료\n";
        }

    }

    fclose($fh);

    /***********************************************************************************
     *** 끝
     ***********************************************************************************/

    echo "\n========================================";
    echo "\n>>>>>>> 우편번호 삽입 엔진 종료 <<<<<<<";
    echo "\n========================================\n\n";
}
?>
