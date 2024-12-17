<?
/**
 *
 * ConnectionPool.php
 * author        : Kim, Kwan Woo
 * coded by      : Kim, Kwan Woo
 * version       : 1.1
 * Last modified : 2004.06.03
 *
 */ 

/*
   지능적 트랜잭션
   ADOdb의 예전 버전에서 사용한 트랜잭션은 다음과 같다. 
   $conn->BeginTrans();
   $ok = $conn->Execute($sql);
   if ($ok) $ok = $conn->Execute($sql2);
   if (!$ok) $conn->RollbackTrans();
   else $conn->CommitTrans();

   이 것은 대형 프로젝트의 경우 아주 복잡하다. 왜냐하면 오류 상태를 계속 추적해야 하기때문이다. 지능적 트랜잭션은 훨씬 간단하다. StartTrans()를 호출함으로서 트랜잭션을 시작한다: 
   $conn->StartTrans();
   $conn->Execute($sql);
   $conn->Execute($Sql2);
   $conn->CompleteTrans();

   CompleteTrans()은 SQL 오류의 발생 여부를 검출하고 오류 발생 여부에따라 적당히 롤백/커밋한다. 오류가 발생하지않아도 강제로 롤백하려면 FailTrans()를 사용한다. 주의: 롤백은 FailTrans()에서가 아니라 CompleteTrans()에서 수행된다. 
   $conn->StartTrans();
   $conn->Execute($sql);
   if (!CheckRecords()) $conn->FailTrans();
   $conn->Execute($Sql2);
   $conn->CompleteTrans();

   HasFailedTrans()를 사용해서 트랙잭션의 실패여부를 검사할 수 있다. HasFailedTrans()은 FailTrans()가 호출된 경우나 SQL 실행에 오류가 있는 경우 참을 리턴한다. 

   마지막으로 StartTrans/CompleteTrans는 중첩될 수 있지만 최외곽 블럭만 실행된다. 반대로 BeginTrans/CommitTrans/RollbackTrans는 중첩될 수 없다. 

   $conn->StartTrans();
   $conn->Execute($sql);
   $conn->StartTrans();    # 무시
   if (!CheckRecords()) $conn->FailTrans();
   $conn->CompleteTrans(); # 무시
   $conn->Execute($Sql2);
   $conn->CompleteTrans();

   주의: Savepoints는 현재 지원되지 않는다.
 */
 
include_once(dirname(__FILE__) . '/db/adodb.inc.php');

class ConnectionPool {

    var $connectIdentifier; //connect identifier
    var $dbType; //database type => mysql : mysql, oracle : oci8
    var $host; //host
    var $user; //user
    var $password; //password
    var $databaseName; //database name
    
    function __construct (
        $dbType = "mysqli",

        $host = "211.110.168.85",
        $user = "dpuser01",
        $password = "gpdb2021",

        $databaseName = "gprinting"
    ) {
    	
        $this->dbType = $dbType; //oracle -> oci8        
        $this->connectIdentifier = ADONewConnection($this->dbType);

        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->databaseName = $databaseName;
        
        $this->connectIdentifier->Connect($this->host, $this->user, $this->password, $this->databaseName);
            	
    }

    function reConnectDB() {
        unset($this->connectIdentifier);
        $this->connectIdentifier = ADONewConnection($this->dbType);
        $this->connectIdentifier->Connect($this->host, $this->user, $this->password, $this->databaseName);
    }
        
    //return Pooled Connection
    function &getPooledConnection() {
        
        return $this->connectIdentifier;
        
    }
}
?>
