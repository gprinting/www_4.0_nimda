<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : DB Connect
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.15
 ***********************************************************************************/

class CLS_DBConect {

	var $conn;
    var $host;
    var $user;
    var $password;
    var $database;
	var $alias;
	var $charset;
	var $collate;


	/***********************************************************************************
	*** 변수 초기화
	***********************************************************************************/

	function Init() {
		$this->conn        = null;
        $this->host         = null;
        $this->user         = null;
        $this->password  = null;
        $this->database  = null;
		$this->alias		= null;
		$this->charset		= null;
		$this->collate		= null;
	}


	/***********************************************************************************
	*** DB 연결
	***********************************************************************************/

	function CLS_DBConect($DbHost, $DbUser, $DbPass, $dataBase, $aLias = "", $charSet = "", $colLate = "") {
        $this->Init();

        if ($DbHost != "" && $DbUser != "" && $DbPass != "") {
	        $this->Connect($DbHost, $DbUser, $DbPass, $aLias);
        } else {
        	echo "$DbHost = DB Info Error!!";
        	exit;
        }

        if ($dataBase != "") {
	        $this->SelectDB($dataBase, $charSet, $colLate);
        }
    }

	function Connect($DbHost, $DbUser, $DbPass, $aLias = "") {
        if (isset($this->conn) == false) {
			$this->conn = mysqli_connect($DbHost, $DbUser, $DbPass);

            if (!$this->conn) {
			    $this->error = mysqli_connect_errno();

           	    die("Connect Failed : DB ".$this->error."Check The Connection Information!!");
			    exit;
            }

            $this->host     = $DbHost;
            $this->user     = $DbUser;
            $this->password = $DbPass;
            $this->alias 	= $aLias;
        }
    }

    function SelectDB($dataBase, $charSet = "", $colLate = "") {
        if (isset($this->conn) == true) {
			if (!mysqli_select_db($this->conn, $dataBase)) {
				$this->error = mysqli_connect_errno();
				die('DB Select Failed!!');
			}

            $this->database = $dataBase;

            if ($charSet != "") {
            	mysqli_query($this->conn, 'set names '.$charSet);
            	$this->charset = $charSet;
        	}

        	if ($colLate != "") {
            	$this->collate = $colLate;
        	}
        }
    }


	/***********************************************************************************
	*** DB 연결 종료
	***********************************************************************************/

	function DBClose() {
        mysqli_close($this->conn);
        $this->Init();
    }

}
?>