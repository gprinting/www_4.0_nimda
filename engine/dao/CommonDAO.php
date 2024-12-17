<?php
class CommonDAO { 

    var $errorMessage = "";

    function __construct() {
    }

    /* * Data Update
     * $conn : DB Connection
     * $param : $param["table"] = "테이블명"
     *          $param["col"]["컬럼명"] = "수정데이터" (다중)
     *          $param["prk"] = "primary key colulm"
     *          $param["prkVal"] = "primary data"
     * return : boolean
     */ 
    function updateMultiData($conn, $param) {

        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $prkArr = str_replace(" ", "", $param["prkVal"]);
        $prkArr = str_replace("'", "", $prkArr); 
        $prkArr = explode(",", $prkArr);

        $parkVal = "";

        for ($i = 0; $i < count($prkArr); $i++) {
            $prkVal .= $conn->qstr($prkArr[$i], get_magic_quotes_gpc()) . ","; 
        }
        $prkVal = substr($prkVal, 0, -1);

        $query = "\n UPDATE " . $param["table"]  . " set";

        $i = 0;
        $col = "";
        $value = "";

        foreach ($param["col"] as $key => $val) {

            $inchr = $conn->qstr($val,get_magic_quotes_gpc());

            if ($i == 0) {
                $value  .= "\n " . $key . "=" . $inchr;
            } else {
                $value  .= "\n ," . $key . "=" . $inchr;
            }

            $i++;
        }

        $query .= $value;
        $query .= " WHERE " . $param["prk"] . " in(";
        $query .= $prkVal . ")";

        $resultSet = $conn->Execute($query);

        if ($resultSet === FALSE) {
            $errorMessage = "데이터 수정에 실패 하였습니다.";
            return false;
        } else { 
            return true;
        }

    } 

    /* * Data Update
     * $conn : DB Connection
     * $param : $param["table"] = "테이블명"
     *          $param["col"]["컬럼명"] = "수정데이터" (다중)
     *          $param["prk"] = "primary key colulm"
     *          $param["prkVal"] = "primary data"
     * return : boolean
     */ 
    function updateData($conn, $param) {

        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query = "\n UPDATE " . $param["table"]  . " set";

        $i = 0;
        $col = "";
        $value = "";

        foreach ($param["col"] as $key => $val) {

         //   $inchr = $val;
            $inchr = $conn->qstr($val, get_magic_quotes_gpc());

            if ($i == 0) {
                $value  .= "\n " . $key . "=" . $inchr;
            } else {
                $value  .= "\n ," . $key . "=" . $inchr;
            }

            $i++;
        }

        $query .= $value;
        $query .= " WHERE " . $param["prk"] . "=" . $conn->qstr($param["prkVal"], get_magic_quotes_gpc());

        $resultSet = $conn->Execute($query);

        if ($resultSet === FALSE) {
            $errorMessage = "데이터 수정에 실패 하였습니다.";
            echo $query."\n";
            return false;
        } else {
            return true;
        }

    }

    /*
     * Data Insert
     * $conn : DB Connection
     * $param : $param["table"] = "테이블명"
     *          $param["col"]["컬럼명"] = "데이터" (다중)
     * return : boolean
     */ 
    function insertData($conn, $param) {
        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query = "\n INSERT INTO " . $param["table"] . "(";

        $i = 0;
        $col = "";
        $value = "";

        foreach ($param["col"] as $key => $val) {

            $inchr = $conn->qstr($val,get_magic_quotes_gpc());

            $inchr = $val;
            if ($i == 0) {
                $col  .= "\n " . $key;
                $value  .= "\n '" . $inchr ."'";
            } else {
                $col  .= "\n ," . $key;
                $value  .= "\n ,'" . $inchr . "'";
            }

            $i++;
        }

        $query .= $col;
        $query .= "\n ) VALUES (";
        $query .= $value;
        $query .= "\n )";

        $resultSet = $conn->Execute($query);
        
        if ($resultSet === FALSE) {
            $errorMessage = "데이터 입력에 실패 하였습니다.";
            return false;
        } else {
            return true;
        }

    }
    
    /*
     * Data Insert/Update
     * $conn : DB Connection
     * $param : $param["table"] = "테이블명"
     *          $param["col"]["컬럼명"] = "데이터" (다중)
     * return : boolean
     */
    function replaceData($conn, $param) {

        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query = "\n INSERT INTO " . $param["table"] . "(";

        $i = 0;
        $col = "";
        $value = "";

        foreach ($param["col"] as $key => $val) {

            $inchr = $conn->qstr($val,get_magic_quotes_gpc()); 
            if ($i == 0) {
                $col  .= "\n " . $key;
                $value  .= "\n " . $inchr;
            } else {
                $col  .= "\n ," . $key;
                $value  .= "\n ," . $inchr;
            }

            $i++;
        }

        $query .= $col;
        $query .= "\n ) VALUES (";
        $query .= $value;
        $query .= "\n )";
        $query .= "\n ON DUPLICATE KEY UPDATE";

        $i = 0;
        $col = "";
        $value = ""; 

        reset($param["col"]);

        foreach ($param["col"] as $key => $val) {

            $inchr = $conn->qstr($val,get_magic_quotes_gpc());

            if ($i == 0) {
                $value  .= "\n " . $key . "=" . $inchr;
            } else {
                $value  .= "\n ," . $key . "=" . $inchr;
            }

            $i++;
        }
        $query .= $value;

        $resultSet = $conn->Execute($query);

        if ($resultSet === FALSE) {
            $errorMessage = "데이터 입력에 실패 하였습니다.";
            return false;
        } else {
            return true;
        } 
    }

    /*
     * Data Delete (delete only one)
     * $conn : DB Connection
     * $param["prk"] = "primary key colulm"
     * $param["prkVal"] = "primary data"
     *
     * return : boolean
     */
    function deleteData($conn, $param) {
    
        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query  = "\n DELETE ";
        $query .= "\n   FROM " . $param["table"];
        $query .= "\n  WHERE " . $param["prk"];
        $query .= "\n       =" . $this->parameterEscape($conn,
                                                        $param["prkVal"]);

        $resultSet = $conn->Execute($query);

        if ($resultSet === FALSE) {
            $errorMessage = "데이터 삭제에 실패 하였습니다.";
            return false;
        } else {
            return true;
        } 
    }

    /*
     * Data Delete (delete multi)
     * $conn : DB Connection
     * $param : $param["table"] = "테이블명"
     * $param : $param["prk"] = "primary key colulm"
     * $param : $param["prkVal"] = "primary data"
     * return : boolean
     */
    function deleteMultiData($conn, $param) {
    
        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query  = "\n DELETE ";
        $query .= "\n   FROM " . $param["table"];
        $query .= "\n  WHERE " . $param["prk"];
        $query .= "\n     IN (";

        $prkValCount = count($param["prkVal"]);
        for ($i = 0; $i < $prkValCount; $i++) {
            $val = $conn->qstr($param["prkVal"][$i], get_magic_quotes_gpc());
            $query .= $val;

            if ($i !== $prkValCount - 1) {
                $query .= ",";
            }
        }

        $query .= ")";

        $resultSet = $conn->Execute($query); 

        if ($resultSet === FALSE) {
            $errorMessage = "데이터 삭제에 실패 하였습니다.";
            return false;
        } else {
            return true;
        } 
    }

    /*
     * Data Select 
     * $conn : DB Connection
     * $param : $param["table"] = "테이블명"
     *          $param["col"] = "컬럼명,컬럼명2,컬럼명3"
     *          $param["where"]["컬럼명"] = "조건" (다중)
     *          $param["order"] = "order by 조건" 
     *          $param["group"] = "group by 조건" 
     *          $param["cache"] = "1" 캐쉬 생성
     *          $param["limit"]["start"] = 리미트 시작값
     *          $param["limit"]["end"] =  리미트 종료값
     *
     * return : resultSet 
     */ 
    function selectData($conn, $param) {

        if(!is_array($param)){
            $param = [];
        }

        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query = "\n SELECT " . $param["col"] . " FROM " . $param["table"];

        $i = 0;
        $col = "";
        $value = "";

        if (isset($param["where"])) {

            foreach ($param["where"] as $key => $val) {

                $inchr = $conn->qstr($val,get_magic_quotes_gpc());
    
                if ($i == 0) {
                        $value  .= "\n WHERE " . $key . "=" . $inchr;
                 } else {
                        $value  .= "\n   AND " . $key . "=" . $inchr;
                 }
                $i++;
            }
        }

        //임시로 만듬
        if (isset($param["like"])) {

            foreach ($param["like"] as $key => $val) {

                $inchr = $conn->qstr($val,get_magic_quotes_gpc());
    
                if ($i == 0) {
                        $value  .= "\n WHERE " . $key . " LIKE " . $inchr;
                 } else {
                        $value  .= "\n   AND " . $key . " LIKE " . $inchr;
                 }
                $i++;
            }
        }

        $query .= $value;

        if (isset($param["order"])) {
            $query .= "\n ORDER BY " . $param["order"]; 
        }
 
        if (isset($param["group"])) {
            $query .= "\n GROUP BY " . $param["group"]; 
        }

        if (isset($param["limit"])) {

            $query .= "\n LIMIT " . $param["limit"]["start"] . ",";
            $query .= $param["limit"]["end"]; 
        }

        //Query Cache 
        if (isset($param["cache"]) && $param["cache"] == 1) {
            $rs = $conn->CacheExecute(1800, $query);
        } else {
            $rs = $conn->Execute($query);
        }

        return $rs;

    } 

    /** 
     * @brief DISTINCT 데이터 검색 쿼리 함수 (공통)<br>
     *        param 배열 설명<br>
     *        $param : $param["table"] = "테이블명"<br>
     *        $param["col"] = "컬럼명"<br>
     *        $param["where"]["컬럼명"] = "조건" (다중)<br>
     * @param $conn DB Connection
     * @param $param 파라미터 인자 배열
     * @return boolean
     */ 
    function distinctData($conn, $param) {

        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }
      
        //주문배송, 회원, 주문 공통, 가상계좌, 견적
        if ($param["table"] == "member" || $param["table"] == "order_common" || 
            $param["table"] == "order_dlvr" || $param["table"] == "virt_ba_admin" ||
            $param["table"] == "esti") {
            echo "접근이 허용되지 않는 테이블 입니다.";
            return false;
        }
        
        $query = "\n SELECT DISTINCT " . $param["col"] . " FROM " . $param["table"];
        $i = 0;
        $value = "";

        if ($param["where"]) {

            foreach ($param["where"] as $key => $val) {

                $inchr = $conn->qstr($val, get_magic_quotes_gpc());
    
                if ($i == 0) {
                        $value  .= "\n WHERE " . $key . "=" . $inchr;
                 } else {
                        $value  .= "\n   AND " . $key . "=" . $inchr;
                 }
                $i++;
            }
        }

        $query .= $value;

        //Query Cache 
        if ($param["cache"] == 1) {
            $rs = $conn->CacheExecute(1800, $query);
        } else {
            $rs = $conn->Execute($query);
        }

        return $rs;
    }

    /*
     * Data Count
     * $conn : DB Connection
     * $param : $param["table"] = "테이블명"
     *          $param["col"] = "컬럼명,컬럼명2,컬럼명3"
     *          $param["where"]["컬럼명"] = "조건" (다중)

     * return : resultSet 
     */ 
    function countData($conn, $param) {

        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        $query = "\n SELECT count(*) cnt  FROM " . $param["table"];

        $i = 0;
        $col = "";
        $value = "";

        if ($param["where"]) {

            foreach ($param["where"] as $key => $val) {

                $inchr = $conn->qstr($val,get_magic_quotes_gpc());
    
                if ($i == 0) {
                        $value  .= "\n WHERE " . $key . "=" . $inchr;
                 } else {
                        $value  .= "\n   AND " . $key . "=" . $inchr;
                 }
                $i++;
            }
        }

        $query .= $value;

        $rs = $conn->Execute($query);
        return $rs;

    } 

    /**
     * 커넥션 체크
     */
    function connectionCheck($conn) {
        if (!$conn) {
            echo "master connection failed\n";
            return false;
        }

        return true;
    }

    /**
     * SQL 인젝션 방지용
     * 파라미터 이스케이프
     */
    function parameterEscape($conn, $param) {
        $ret = $conn->qstr($param, get_magic_quotes_gpc());
        return $ret;
    }

    /**
     * SQL 인젝션 방지용
     * 파라미터 배열 이스케이프
     */
    function parameterArrayEscape($conn, $param, $except_arr = null) {
        if (!is_array($param)) return false;

        $arrSize = count($param);

        foreach ($param as $key => $val) {
            if (isset($except_arr[$key]) && $except_arr[$key] === true) {
                continue;
            }

            if (is_array($val)) {
                $val = $this->parameterArrayEscape($conn, $val, $except_arr);
            } else {
                $val = $this->parameterEscape($conn, $val);
            }

            $param[$key] = $val;
        }

        return $param;
    }

    /**
     * CUD 실패시 입력된 에러메시지 반환
     */
    function getErrorMessage() {
        return $errorMessage;
    }

    /**
     * 캐쉬를 삭제하는 함수
     */
    function cacheFlush($conn) {
        $conn->CacheFlush();
    }

    /**
     * NULL 이거나 공백값('')이 아닌 파라미터만 체크
     */
    function blankParameterCheck($param, $key) {
        // 파라미터가 빈 값이 아닐경우
        if ($param !== ""
                && empty($param[$key]) !== true
                && $param[$key] !== "''" 
                && $param[$key] !== "NULL" 
                && $param[$key] !== "null") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief 파라미터 값이 공백인지 판독
     *
     * @param $val = 셀 값
     *
     * @return 공백이면 TRUE / 아니면 FALSE
     */
    function checkBlank($val) {
        if ($val !== ""
                && empty($val) !== true
                && $val !== "''" 
                && $val !== "NULL" 
                && $val !== "null") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @brief 파라미터로 넘어온 $dvs_col에 해당하는 가격을 전부 지운다
     *
     * @param $conn       = 디비 커넥션 
     * @param $table_name = 테이블명
     * @param $mpcode_col = 맵핑코드 컬럼명
     * @param $param      = 쿼리 정보배열(맵핑코드만 사용)
     *
     * @return 쿼리 실행결과
     */
    function deletePrice($conn,
                         $table_name,
                         $prk_col,
                         $param,
                         $dvs_col = "mpcode") {
        if (!$this->connectionCheck($conn)) return false;

        $param_count = count($param);
        $dup_check = array();
        $prk_val_arr = array();

        $loop_count = ceil(($param_count / 500));

        $j = 0;
        for ($i = 0; $i < $loop_count; $i++) {
            $k = 0;
            for ($j; $j < $param_count; $j++) {
                $prk_val = $param[$j][$dvs_col];

                if ($prk_val === false || isset($dup_check[$prk_val])) {
                    continue;
                }

                $dup_check[$prk_val] = $prk_val;
                $prk_val_arr[$i][$k++] = $prk_val;
            }
        }

        if (count($prk_val_arr) === 0) {
            return true;
        }

        $temp = array();
        $temp["table"] = $table_name;
        $temp["prk"]   = $prk_col;

        $prk_val_arr_count = count($prk_val_arr);

        for ($i = 0; $i < $prk_val_arr_count; $i++) {
            $temp["prkVal"] = $prk_val_arr[$i];

            $conn->StartTrans();

            $ret = $this->deleteMultiData($conn, $temp);

            $conn->CompleteTrans();

            if ($ret === false) {
                break;
            }

            sleep(1);
        }

        return $ret;
    }

    /**
     * @brief 카테고리명으로 분류코드 검색
     *
     * @param $conn      = 디비 커넥션 
     * @param $cate_name = 카테고리명
     *
     * @return 카테고리 분류코드
     */
    function selectCateSortcode($conn, $cate_name, $cate_level = 3) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $temp = array();
        $temp["col"] = "sortcode";

        $temp["table"] = "cate";

        $temp["where"]["cate_name"] = $cate_name;
        $temp["where"]["cate_level"] = $cate_level;

        $rs = $this->selectData($conn, $temp);

        return $rs->fields["sortcode"];
    }

    /**
     * @brief 판매채널명으로 판매채널 일련번호 검색
     *
     * @param $conn      = 디비 커넥션 
     * @param $sell_site = 카테고리명
     *
     * @return 판매채널 일련번호
     */
    function selectCpnAdminSeqno($conn, $sell_site) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $temp = array();
        $temp["col"] = "cpn_admin_seqno";

        $temp["table"] = "cpn_admin";

        $temp["where"]["sell_site"] = $sell_site;

        $rs = $this->selectData($conn, $temp);

        return $rs->fields["cpn_admin_seqno"];
    }

    /**
     * @brief 외부_업체 테이블에서 이름으로 일련번호 검색 
     * 
     * @detail 매입가격 입력시 사용, 외부_브랜드 검색용
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 제조사명, 매입_제품
     *
     * @return
     */
    function selectExtnlEtprsSeqno($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $temp = array();
        $temp["col"] = "extnl_etprs_seqno";

        $temp["table"] = "extnl_etprs";

        $temp["where"]["manu_name"] = $param["manu_name"];
        $temp["where"]["pur_prdt"]  = $param["pur_prdt"];
        $temp["where"]["deal_yn"]   = 'Y';

        $rs = $this->selectData($conn, $temp);

        return $rs->fields["extnl_etprs_seqno"];
    }

    /**
     * @brief 외부_브랜드 테이블에서 일련번호 검색 
     * 
     * @detail 매입가격 입력시 사용 
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 검색조건 파라미터
     *
     * @return
     */
    function selectExtnlBrandSeqno($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $temp = array();
        $temp["col"] = "extnl_brand_seqno";

        $temp["table"] = "extnl_brand";

        $temp["where"]["name"] = $param["brand"];
        $temp["where"]["extnl_etprs_seqno"] = $param["extnl_etprs_seqno"];

        $rs = $this->selectData($conn, $temp);

        return $rs->fields["extnl_brand_seqno"];
    }

    /** 
     * @brief 배열값을 IN 조건 등에 들어갈 수 있도록 문자열로 변경
     *
     * @param $conn  = DB Connection
     * @param $param = 배열값
     *
     * @return 변환 된 배열
     */ 
    function arr2paramStr($conn, $param) {
        if (empty($param) === true || count($param) === 0) {
            return '';
        }

        $ret = "";

        foreach ($param as $val) {
            if (empty($val) === true) {
                continue;
            }

            $ret .= $this->parameterEscape($conn, $val) . ','; 
        }

        return substr($ret, 0, -1);
    }

    /**
     * @brief 발행_대상_금액 있는지 검색
     *
     * @param $conn = connection identifier
     * @param $cate_sortcode = 카테고리 분류코드
     *
     * @return 카테고리명
     */
    function selectPublicObjectPrice($conn, $member_seqno) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $temp = array();
        $temp["col"]  = " public_object_price";
        $temp["col"] .= ",unissued_object_price";
        $temp["table"] = "public_object_price";
        $temp["where"]["member_seqno"] = $member_seqno;

        $rs = $this->selectData($conn, $temp);

        return $rs;
    }

    /**
     * @brief 발행_대상_금액 수정
     *
     * @param $conn = connection identifier
     * @param $cate_sortcode = 카테고리 분류코드
     *
     * @return 카테고리명
     */
    function updatePublicObjectPrice($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false; 
        }

        $temp = array();
        $temp["col"]["public_object_price"]   = $param["public_object_price"];
        $temp["col"]["unissued_object_price"] = $param["unissued_object_price"];
        $temp["table"]  = "public_object_price";
        $temp["prk"]    = "member_seqno";
        $temp["prkVal"] = $param["member_seqno"];

        $rs = $this->updateData($conn, $temp);

        return $rs;
    }

    /**
     * @brief 카테고리 분류코드, 종이, 규격, 인쇄 맵핑코드 검색
     *
     * @param $conn  = 디비 커넥션
     * @param $param = 맵핑코드를 얻어야되는 항목 정보
     *
     * @return 쿼리 실행결과
     */
    function selectPlyMpcode($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param = $this->parameterArrayEscape($conn, $param);

        // 카테고리 분류코드 검색
        $query  = "\n SELECT sortcode";
        $query .= "\n   FROM cate";
        $query .= "\n  WHERE cate_level = '3'";
        $query .= "\n    AND cate_name = %s";
        $query  = sprintf($query, $param["cate"]);

        $rs = $conn->Execute($query);
        if ($rs->EOF) return false;

        $paper_info = $param["paper_info"];
        $cate_sortcode = $rs->fields["sortcode"];

        // 종이 맵핑코드 검색
        $query  = "\n SELECT mpcode";
        $query .= "\n   FROM cate_paper";
        $query .= "\n  WHERE name          = %s";
        $query .= "\n    AND dvs           = %s";
        $query .= "\n    AND color         = %s";
        $query .= "\n    AND basisweight   = %s";
        $query .= "\n    AND cate_sortcode = %s";
        $query  = sprintf($query, $paper_info[1]
                                , $paper_info[2]
                                , $paper_info[3]
                                , $paper_info[4]
                                , $cate_sortcode);

        $rs = $conn->Execute($query);
        if ($rs->EOF) return false;

        $paper_mpcode = $rs->fields["mpcode"];

        // 규격 맵핑코드 검색
        $query  = "\n SELECT B.mpcode";
        $query .= "\n   FROM prdt_stan AS A, cate_stan AS B";
        $query .= "\n  WHERE A.prdt_stan_seqno = B.prdt_stan_seqno";
        $query .= "\n    AND A.name            = %s";
        $query .= "\n    AND A.typ             = %s";
        $query .= "\n    AND B.cate_sortcode   = %s";
        $query  = sprintf($query, $param["size"]
                                , $param["size_typ"]
                                , $cate_sortcode);

        $rs = $conn->Execute($query);
        if ($rs->EOF) return false;

        $stan_mpcode = $rs->fields["mpcode"];

        // 인쇄도수 맵핑코드 검색
        $tmpt = $param["tmpt"];

        $query  = "\n SELECT B.mpcode";
        $query .= "\n   FROM prdt_print AS A, cate_print AS B";
        $query .= "\n  WHERE A.prdt_print_seqno = B.prdt_print_seqno";
        $query .= "\n    AND A.name             = %s";
        $query .= "\n    AND B.cate_sortcode    = %s";

        // 전면
        $query  = sprintf($query, $tmpt["bef"]
                                , $cate_sortcode);
        $rs = $conn->Execute($query);
        if ($rs->EOF) return false;
        $bef_print_mpcode = $rs->fields["mpcode"];
        // 전면추가
        $bef_add_print_mpcode = '0';
        if ($tmpt["bef_add"] !== "'-'") {
            $query  = sprintf($query, $tmpt["bef_add"]
                                         , $cate_sortcode);
            $rs = $conn->Execute($query);
            if ($rs->EOF) return false;
            $bef_add_print_mpcode = $rs->fields["mpcode"];
        }
        // 후면
        $aft_print_mpcode = '0';
        if ($tmpt["aft"] !== "'-'") {
            $query  = sprintf($query, $tmpt["aft"]
                                         , $cate_sortcode);
            $rs = $conn->Execute($query);
            if ($rs->EOF) return false;
            $aft_print_mpcode = $rs->fields["mpcode"];
        }
        // 후면추가
        $aft_add_print_mpcode = '0';
        if ($tmpt["aft_add"] !== "'-'") {
            $query  = sprintf($query, $tmpt["aft_add"]
                                         , $cate_sortcode);
            $rs = $conn->Execute($query);
            if ($rs->EOF) return false;
            $aft_add_print_mpcode = $rs->fields["mpcode"];
        }

        return array("CATE"          => $cate_sortcode,
                     "PAPER"         => $paper_mpcode,
                     "STAN"          => $stan_mpcode,
                     "BEF_PRINT"     => $bef_print_mpcode,
                     "BEF_ADD_PRINT" => $bef_add_print_mpcode,
                     "AFT_PRINT"     => $aft_print_mpcode,
                     "AFT_ADD_PRINT" => $aft_add_print_mpcode);
    }
}
?>
