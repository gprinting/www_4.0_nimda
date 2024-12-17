<?
/**
 * @file EngineSellPriceFunc.php
 *
 * @brief 판매가격 엑셀 업로드시 작업을 수행하는 클래스
 */

include_once(dirname(__FILE__) . '/common/ConnectionPool.php');
include_once(dirname(__FILE__) . '/common/EngineFuncInterface.php');
include_once(dirname(__FILE__) . '/common/EngineCommon.php');
include_once(dirname(__FILE__) . '/dao/EngineDAO.php');
include_once(dirname(__FILE__) . '/define/common.php');

class EngineSellPriceFunc implements EngineFuncInterface {

    function __construct() {
        echo "EngineSellPriceFunc이 켜졌습니다.\n";
    }

    /**
     * @brief 파라미터를 받아서 해당하는 생산 항목의 가격을 입력하는 함수
     * 
     * @param $param = 정보 파라미터
     *
     * @return 작업실행 성공여부
     */
    function execute($param) {
        echo "EngineSellPriceFunc를 실행합니다.\n";
        $ret = false;

        $param = explode('!', $param);

        $dvs = $param[0];
		
        echo "작업에 착수하였습니다 : {$dvs}\n";
		switch ($dvs) {
			case "PLY":
				$ret = $this->plyPriceFunc($param);
				break;
			case "PAPER":
				$ret = $this->paperSellPriceFunc($param);
				break;
			case "OUTPUT":
				$ret = $this->outputSellPriceFunc($param);
				break;
			case "PRINT":
				$ret = $this->printSellPriceFunc($param);
				break;
			case "AFTER":
				$ret = $this->afterSellPriceFunc($param);
				break;
			case "OPTION":
				$ret = $this->optSellPriceFunc($param);
				break;
			case "SALE_PAPER":
				$ret = $this->salePaperPriceFunc($param);
				break;
			case "SALE_MEMBER":
				$ret = $this->saleMemberPriceFunc($param);
				break;
			case "SALE_AFT":
				$ret = $this->saleMemberAftPriceFunc($param);
				break;
		}

        return $ret;
    }

    /**
     * @brief 업로드한 엑셀이 합판 판매가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명!판매채널<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function plyPriceFunc($param) {
        echo "유틸 엔진을 불러옵니다.\n";
        $util = new EngineCommon();
        echo "엔진 DAO를 불러옵니다.\n";
        $connectionPool = new ConnectionPool();
        $conn = $connectionPool->getPooledConnection();
        $engineDAO = new EngineDAO();

        echo "사용된 파라미터 { \n";
        print_r($param);
        echo "\n } 사용된 파라미터 \n";


        $base_path = dirname(__FILE__);

        if(!isset($param[3]))
            $param[3] = "";

        if(!isset($param[4]))
            $param[4] = "";

        $sell_site = $param[3];
        $etprs_dvs = $param[4];

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/ply/%s.php", $base_path
                                                          , "PlyPriceEngine");

        echo "엔진경로 : {$engine_path} \n";
        echo "엑셀경로 : {$excel_path} \n";
        $rs = $engineDAO->selectStayWork($conn);
        if(!is_file($excel_path)){
            if($rs->RecordCount() !== 0){
                $seqno = $rs->fields["engine_que_seqno"];
                $engineDAO->updateState($conn, $seqno, "SUCCESS");
                return;
            }
        }

        if($rs->RecordCount() !== 0){
            $seqno = $rs->fields["engine_que_seqno"];
            $engineDAO->updateState($conn, $seqno, "SUCCESS");
        }

        $command = sprintf("%s %s %s %s %s > %s &", $engine_path
                                                  , $excel_path
                                                  , $base_path
                                                  , $sell_site
                                                  , $etprs_dvs
                                                  , REDIR_PATH);

        echo "시스탬 커맨드 : {$command} \n";
        system($command);
        shell_exec("sh /var/www/kill.sh");

        $util->checkProcess("PlyPriceEngine");

        $fp = fopen($base_path . "/log/PlyPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 종이 판매가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function paperSellPriceFunc($param) {
        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/paper/%s.php", $base_path
                                                            , "PaperSellPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("PaperSellPriceEngine");

        $fp = fopen($base_path . "/log/PaperSellPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 출력 판매가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function outputSellPriceFunc($param) {
        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/output/%s.php", $base_path
                                                             , "OutputSellPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("OutputSellPriceEngine");

        $fp = fopen($base_path . "/log/OutputSellPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 인쇄 판매가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function printSellPriceFunc($param) {
        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/print/%s.php", $base_path
                                                            , "PrintSellPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("PrintSellPriceEngine");

        $fp = fopen($base_path . "/log/PrintSellPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 후공정 판매가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function afterSellPriceFunc($param) {
        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/after/%s.php", $base_path
                                                            , "AfterSellPriceEngine");

        echo <<<DDD
\$engine_path : $engine_path
\$excel_path : $excel_path
\$base_path : $base_path

DDD;
        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("AfterSellPriceEngine");

        $fp = fopen($base_path . "/log/AfterSellPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 옵션 판매가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function optSellPriceFunc($param) {
        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/option/%s.php", $base_path
                                                             , "OptSellPriceEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("OptSellPriceEngine");

        $fp = fopen($base_path . "/log/OptSellPrice.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 수량 종이 할인 가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명!판매채널<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function salePaperPriceFunc($param) {
        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/sale_paper/%s.php", $base_path
                                                                 , "AmtPaperSaleEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("AmtPaperSaleEngine");

        $fp = fopen($base_path . "/log/AmtPaperSaleEngine.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 수량 회원 할인 가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function saleMemberPriceFunc($param) {
        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/sale_member/%s.php", $base_path
                                                                  , "AmtMemberSaleEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("AmtMemberSaleEngine");

        $fp = fopen($base_path . "/log/AmtMemberSaleEngine.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }

    /**
     * @brief 업로드한 엑셀이 수량 회원 할인 가격일 때 실행하는 함수
     * 
     * @details 엔진으로 넘기는 파라미터로는<br/>
     * 구분!파일저장경로!파일명<br/>
     * 이다
     * 
     * @param $param = 정보 파라미터
     * 
     * @return 작업실행 성공여부
     */
    function saleMemberAftPriceFunc($param) {
        $util = new EngineCommon();

        $base_path = dirname(__FILE__);

        $excel_path = sprintf("%s/%s", $param[1], $param[2]);

        $engine_path = sprintf("%s/proc_engine/sale_member/%s.php", $base_path
                                                                  , "AmtMemberAftSaleEngine");

        $command = sprintf("%s %s %s > %s &", $engine_path
                                            , $excel_path
                                            , $base_path
                                            , REDIR_PATH);
        system($command);

        $util->checkProcess("AmtMemberSaleEngine");

        $fp = fopen($base_path . "/log/AmtMemberSaleEngine.log", "r");

        $ret_check = "";

        while (($buffer = fgets($fp, 512)) !== false) {
            $ret_check .= $buffer;
        }

        fclose($fp);

        return $util->checkFail($ret_check);
    }
}
?>
