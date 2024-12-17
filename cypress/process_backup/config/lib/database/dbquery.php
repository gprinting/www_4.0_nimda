<?
 /***********************************************************************************
 *** 프로 젝트 : CyPress
 *** 개발 영역 : DB Query
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.15
 ***********************************************************************************/

class CLS_DBQuery {

	var $sql;
	var $rs;
	var $conn;
	var $time;
	var $error;
	var $num_rows;
	var $data;


	/***********************************************************************************
	*** 변수 초기화
	***********************************************************************************/

	function Init() {
		$this->sql			= null;
		$this->rs			= null;
		$this->conn		= null;
		$this->time			= null;
		$this->error			= null;
		$this->num_rows = null;
		$this->data			= array();
	}


	/***********************************************************************************
	*** DB 쿼리 실행
	***********************************************************************************/

	function CLS_DBQuery($sql, &$db) {
		$this->Init();
		$this->conn = $db->conn;

		if ($sql != "") {
			$this->sql = trim($sql);
			$this->Query();
		}
	}

	function Query($sql = "") {

		if($sql != "") {
			$this->sql = $sql;
		}

		if ($this->sql != "") {
			$isFetch = null;

			$this->rs = mysqli_query($this->conn, $this->sql);

			if (!$this->conn) {
				$this->error = mysqli_error($this->conn);
			}

			if ($this->error != "") {
				//echo $this->error."--".var_dump(debug_backtrace());
				//echo "\n";
				echo "Error : Please Try Again or Contact Your Administrator!! \n";
				exit;

				return false;
			}

			if (!$this->rs) {
				//echo $this->rs."--".var_dump(debug_backtrace());
				//echo "\n";
				echo $this->sql;
				echo "\n";
				echo "Error : Mysql Query Error!! \n";
				exit;

				return false;
			}

			$queryPos = stripos($this->sql, "select");

			if ($queryPos !== false && $queryPos < 8) {
				$isFetch = 1;
			} else {
				$queryPos = stripos($this->sql, "show");

				if ($queryPos !== false && $queryPos < 8) {
					$isFetch = 1;
				}
			}

			if($isFetch == 1) {
				$this->num_rows = @mysqli_num_rows($this->rs);

				for($i = 0; $i < $this->num_rows; $i++) {
					$this->data[$i] = mysqli_fetch_array($this->rs);
				}
			}
		}

	}

}
?>