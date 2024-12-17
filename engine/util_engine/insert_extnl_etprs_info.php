#! /usr/local/bin/php -f
<?
/**
 * @file insert_extnl_etprs_info.php
 *
 * @brief 외부업체 데이터 입력
 *
 * @detail csv 셀 순서는 아래와 같다
 * # 매입업체 기본정보(extnl_etprs)
 * [0] 매입업체명 => 아이쿱스(퀵)
 * [1] 매입품목 => 기타
 * [2] 거래여부 => N
 * [3] 회사이름 => (주)아이쿱스(icoops Corp.)
 * [4] 전화번호 => 1544-3165
 * [5] 팩스번호 =>
 * [6] 홈페이지 =>
 * [7] 우편번호 =>
 * [8] 주소 => 서울특별시 중구 마른내로 74(인현동2가, 106호)
 * [9] 주소상세 =>
 * # 매입업체 사업자등록증 정보(extnl_etprs_bls_info)
 * [10] 회사이름 => (주)아이쿱스(icoops Corp.)
 * [11] 대표자명 => 박성순
 * [12] 사업자등록번호 => 201-86-42266
 * [13] 업태 => 서비스
 * [14] 업종 => 소프트웨어개발및공급업
 * [15] 우편번호 =>
 * [16] 주소 => 서울특별시 중구 마른내로 74(인현동2가, 106호)
 * [17] 주소상세 =>
 * [18] 거래은행=>
 * [19] 계좌번호 =>
 * [20] 참고사항 => icoopsnet@naver.com
 * # 매입업체 담당자 정보(extnl_mng)
 * [21] 전화번호 =>
 * [22] 이메일 =>
 * [23] 핸드폰 =>
 * [24] 내선번호 =>
 * [25] 구분 =>
 * [26] 부서 =>
 * [27] 직책 =>
 * [28] 이름 =>
 * # 매입업체 브랜드 정보(extnl_brand)
 * [29] 브랜드명 =>
 */
include_once(dirname(__FILE__) . '/ConnectionPool.php');
include_once(dirname(__FILE__) . '/CommonDAO.php');

$connectionPool = new ConnectionPool();
$conn = $connectionPool->getPooledConnection();

$fd = fopen(dirname(__FILE__) . "/csv/extnl_etprs_info.csv", 'r');

if ($fd === false) {
    echo "fopen ERR\n";
    exit;
}

$arr = array();

$j = 0;
while (($data = fgetcsv($fd)) !== false) {
    $arr[$j++] = $data;
}
fclose($fd);

$arr_count = count($arr);

// 외부업체 기본 중복체크
$ee_dup_chk = array();
// 외부업체 사업자 등록증 중복 체크
$ei_dup_chk = array();
// 외부업체 담당자 중복 체크
$em_dup_chk = array();
// 외부업체 브랜드 중복 체크
$eb_dup_chk = array();

$conn->debug = 1;
for ($i = 0; $i < $arr_count; $i++) {
    $temp = $arr[$i];

    $ee_key = sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s|%s", $temp[0]
                                                     , $temp[1]
                                                     , $temp[2]
                                                     , $temp[3]
                                                     , $temp[4]
                                                     , $temp[5]
                                                     , $temp[6]
                                                     , $temp[7]
                                                     , $temp[8]
                                                     , $temp[9]);
    $ei_key = sprintf("%s|%s|%s|%s|%s|%s|%s|%s|%s|%s|%s", $temp[10]
                                                        , $temp[11]
                                                        , $temp[12]
                                                        , $temp[13]
                                                        , $temp[14]
                                                        , $temp[15]
                                                        , $temp[16]
                                                        , $temp[17]
                                                        , $temp[18]
                                                        , $temp[19]
                                                        , $temp[20]);
    $em_key = sprintf("%s|%s|%s|%s|%s|%s|%s|%s", $temp[21]
                                               , $temp[22]
                                               , $temp[23]
                                               , $temp[24]
                                               , $temp[25]
                                               , $temp[26]
                                               , $temp[27]
                                               , $temp[28]);
    $eb_key = sprintf("|%s|%s", $ee_key
                              , $temp[29]);

    $ee_seqno = null;

    if ($ee_dup_chk[$ee_key] === null) {
        $query  = "\n INSERT INTO extnl_etprs (";
        $query .= "\n      manu_name";
        $query .= "\n     ,pur_prdt";
        $query .= "\n     ,deal_yn";
        $query .= "\n     ,cpn_name";
        $query .= "\n     ,tel_num";
        $query .= "\n     ,fax";
        $query .= "\n     ,hp";
        $query .= "\n     ,zipcode";
        $query .= "\n     ,addr";
        $query .= "\n     ,addr_detail";
        $query .= "\n     ,mail";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query  = sprintf($query, $temp[0]
                                , $temp[1]
                                , $temp[2]
                                , $temp[3]
                                , $temp[4]
                                , $temp[5]
                                , $temp[6]
                                , $temp[7]
                                , $temp[8]
                                , $temp[9]
                                , '');

        $conn->Execute($query);

        $ee_seqno = $conn->Insert_ID();

        $ee_dup_chk[$ee_key] = $ee_seqno;
    } else {
        $ee_seqno = $ee_dup_chk[$ee_key];
    }

    if (!empty($temp[10]) && $ei_dup_chk[$ei_key] === null) {
        $ei_dup_chk[$ei_key] = true;

        $query  = "\n INSERT INTO extnl_etprs_bls_info (";
        $query .= "\n      cpn_name";
        $query .= "\n     ,repre_name";
        $query .= "\n     ,crn";
        $query .= "\n     ,bc";
        $query .= "\n     ,tob";
        $query .= "\n     ,zipcode";
        $query .= "\n     ,addr";
        $query .= "\n     ,addr_detail";
        $query .= "\n     ,bank_name";
        $query .= "\n     ,ba_num";
        $query .= "\n     ,add_items";
        $query .= "\n     ,extnl_etprs_seqno";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query  = sprintf($query, $temp[10]
                                , $temp[11]
                                , $temp[12]
                                , $temp[13]
                                , $temp[14]
                                , $temp[14]
                                , $temp[16]
                                , $temp[17]
                                , $temp[18]
                                , $temp[19]
                                , $temp[20]
                                , $ee_seqno);

        $conn->Execute($query);
    }

    if ($em_dup_chk[$em_key] === null) {
        $em_dup_chk[$em_key] = true;

        $query  = "\n INSERT INTO extnl_mng (";
        $query .= "\n      tel_num";
        $query .= "\n     ,mail";
        $query .= "\n     ,cell_num";
        $query .= "\n     ,exten_num";
        $query .= "\n     ,dvs";
        $query .= "\n     ,depar";
        $query .= "\n     ,job";
        $query .= "\n     ,name";
        $query .= "\n     ,extnl_etprs_seqno";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query  = sprintf($query, $temp[21]
                                , $temp[22]
                                , $temp[23]
                                , $temp[24]
                                , $temp[24]
                                , $temp[26]
                                , $temp[27]
                                , $temp[28]
                                , $ee_seqno);

        $conn->Execute($query);
    }

    if ($eb_dup_chk[$eb_key] === null) {
        $eb_dup_chk[$eb_key] = true;

        $query  = "\n INSERT INTO extnl_brand (";
        $query .= "\n      name";
        $query .= "\n     ,extnl_etprs_seqno";
        $query .= "\n ) VALUES (";
        $query .= "\n      '%s'";
        $query .= "\n     ,'%s'";
        $query .= "\n )";

        $query  = sprintf($query, $temp[29]
                                , $ee_seqno);

        $conn->Execute($query);
    }
}
?>
