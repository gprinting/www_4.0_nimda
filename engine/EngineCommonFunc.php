<?php
/**
 * @file EngineCommonFunc.php
 *
 * @brief 엔진에서 넘긴 작업을 실제로 분배하는 클래스
 */

include_once(dirname(__FILE__) . '/common/EngineCommon.php');

class EngineCommonFunc {
    /**
     * @var const array CLASS_NAME
     *
     * @brief 작업구분에 따른 실제 동작 파일명을 저장하는 배열<br/>
     * 엔진이 추가될 경우 작업명과 파일명 추가요망
     */
    const CLASS_NAME = array(
        "SELL_PRICE" => "EngineSellPriceFunc",
        "PUR_PRICE"  => "EnginePurPriceFunc",
        "CALC_PRICE" => "EngineCalcPriceFunc"
    );

    var $err_msg = "";

    function __construct() {
    }

    /**
     * @brief 엔진에서 넘겨받은 파라미터 정보를 이용해서<br/>
     * 실제 작업을 분배하는 함수
     * 
     * @param $dvs   = 엔진 작업구분
     * @param $param = 정보 파라미터
     *
     * @return 작업실행 성공여부
     */
    function execute($dvs, $param) {
        $util = new EngineCommon();

        $class_name = self::CLASS_NAME[$dvs];

        if ($util->checkBlank($class_name)) {
            $this->setErrMsg("[ERR] Class Name is NOT EXIST");
            return false;
        }

        $class_path = sprintf("%s/%s.php", dirname(__FILE__)
                                         , $class_name);

        include_once($class_path);

        /* 자바형식 리플렉션 사용예시
        $obj = new ReflectionClass($class_name);
        $class = $obj->newInstance();
        */

        $class = new $class_name();

        echo "ret 실행됨\n";
        $ret = $class->execute($param);

        return $ret;
    }

    /**
     * @brief 에러메세지를 세팅하는 함수
     * 
     * @param $msg = 반환할 에러메세지
     */
    function setErrMsg($msg) {
        $this->err_msg = $msg;
    }

    /**
     * @brief 에러메세지를 반환하고 초기화 하는 함수
     * 
     * @return 설정되어있는 에러메세지
     */
    function getErrMsg() {
        $temp = $this->err_msg;
        $this->err_msg = "";
        return $temp;
    }
}
?>
