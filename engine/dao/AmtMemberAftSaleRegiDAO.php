<?
include_once(dirname(__FILE__) . '/CommonDAO.php');

class AmtMemberAftSaleRegiDAO extends CommonDAO {
    function __construct() {
    }

    /**
     * @brief 회원 정보 검색
     *
     * @param $conn  = connection identifier
     * @param $param = 조건용 파라미터
     *
     * @return 검색결과
     */
    function selectMemberInfo($conn, $param) {
        if ($this->connectionCheck($conn) === false) {
            return false;
        }

        $param = $this->parameterArrayEscape($conn, $param);

        $query  = "\n SELECT  A.member_seqno";
        $query .= "\n   FROM  member AS A";
        $query .= "\n  WHERE  A.office_nick = %s";
        $query .= "\n    AND  A.member_name = %s";

        $query  = sprintf($query, $param["office_nick"]
                                , $param["member_name"]);

        $rs = $conn->Execute($query);

        return $rs->fields["member_seqno"];
    }

    /**
     * @brief 수량 회원 할인 데이터 삭제
     *
     * @param $conn  = connection identifier
     * @param $param = 조건용 파라미터
     *
     * @return 검색결과
     */
    function deleteAmtMemberAftSale($conn, $param) {
        if (!$this->connectionCheck($conn)) return false;

        $param_count = count($param);
        $dup_check = array();
        $prk_val_arr = array();

        $loop_count = ceil(($param_count / 500));

        $j = 0;
        for ($i = 0; $i < $loop_count; $i++) {
            $k = 0;
            for ($j; $j < $param_count; $j++) {
                $temp = $param[$j];

                $member_seqno = $temp["member_seqno"];
                $after_mpcode = $temp["after_mpcode"];
                $amt          = $temp["amt"];

                $dup_key = sprintf("%s|%s|%s", $member_seqno
                                             , $after_mpcode
                                             , $amt);
                if ($dup_check[$dup_key] !== null) {
                    continue;
                }

                $dup_check[$dup_key] = true;
                $prk_val_arr[$i][$k++] = $temp;
            }
        }

        if (count($prk_val_arr) === 0) {
            return true;
        }

        unset($param);
        unset($dup_check);

        $query  = "\n DELETE FROM member_cate_after_sale";
        $query .= "\n WHERE member_seqno = %s";
        $query .= "\n   AND cate_after_mpcode = %s";

        $temp = $this->parameterArrayEscape($conn, $prk_val_arr[0][0]);

        $q = sprintf($query, $temp["member_seqno"]
                           , $temp["after_mpcode"]);

        $conn->StartTrans();
        $ret = $conn->Execute($q);
        $conn->CompleteTrans();

        if ($ret === false) {
            return false;
        }

        /*
        for ($i = 0; $i < $loop_count; $i++) {
            $prk_val = $prk_val_arr[$i];
            $prk_val_count = count($prk_val);

            $conn->StartTrans();
            for ($j = 0; $j < $prk_val_count; $j++) {
                $temp = $prk_val[$j];
                $temp = $this->parameterArrayEscape($conn, $temp);


                $q = sprintf($query, $temp["member_seqno"]
                                   , $temp["after_mpcode"]);

                $ret = $conn->Execute($q);


                if ($ret === false) {
                    return false;
                }
            }
            $conn->CompleteTrans();
            sleep(1);
        }
        */

        return true;
    }

    /**
     * @brief 수량 회원 할인 할인정보 입력
     *
     * @param $conn      = 디비 커넥션 
     * @param $param_arr = 정보 파라미터 배열
     *
     * @return 쿼리 실행결과
     */
    function insertAmtMemberAftSale($conn, $param_arr) {
        if (!$this->connectionCheck($conn)) return false;
        
        $query_base  = "\n INSERT INTO member_cate_after_sale (";
        $query_base .= "\n    member_seqno";
        $query_base .= "\n   ,cate_after_mpcode";
        $query_base .= "\n   ,amt";
        $query_base .= "\n   ,rate";
        $query_base .= "\n   ,aplc_price";
        $query_base .= "\n ) VALUES ";
        
        $param_arr_count = count($param_arr);
        $loop_count = ceil(($param_arr_count / 500));

        $values_base = "\n (%s, %s, %s, %s, %s),";

        $j = 0;
        for ($i = 0; $i < $loop_count; $i++) {
            //echo "$i / $loop_count\n";

            $query  = $query_base;
            $values = '';

            while (true) {
                //echo "while : $j\r";

                $param = $param_arr[$j++];

                if (empty($param) === true) {
                    break;
                }

                $param = $this->parameterArrayEscape($conn, $param);

                $values .= sprintf($values_base, $param["member_seqno"]
                                               , $param["after_mpcode"]
                                               , $param["amt"]
                                               , $param["rate"]
                                               , $param["aplc_price"]);

                if ($j !== 0 && $j % 500 === 0) {
                    break;
                }
            }

            $query .= substr($values, 0, -1);

            $conn->StartTrans();
            
            $ret .= $conn->Execute($query) === false ? "FAIL" : "SUCCESS";
            $ret .= '!';

            if ($ret === false) {
                $conn->FailTrans();
                $conn->RollbackTrans();
                return "FAIL";
            }

            $conn->CompleteTrans();
        }

        return $ret;
    }
}
?>
