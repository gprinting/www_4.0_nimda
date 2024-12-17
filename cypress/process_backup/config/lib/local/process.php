<?
 /***********************************************************************************
  *** 프로 젝트 : CyPress
 *** 개발 영역 : Process
 *** 개  발  자 : 김성진
 *** 개발 날짜 : 2016.06.15
 ***********************************************************************************/

 class CLS_Process {

	 /***********************************************************************************
	 *** Process Time
	 ***********************************************************************************/

	 function ProcessStartTime() {
		return time();
	 }

	 function ProcessEndTime($startTime) {
		$endTime = time();
		$duringTime = $endTime - $startTime;

		return $this->ProcessUnit($duringTime);
	 }


	/***********************************************************************************
	*** Process Time Unit
	***********************************************************************************/

	function ProcessUnit($time) {
		$days = $hour = $min = $sec = 0;
		$sec = $time;

		if ($sec >= 60) {
			$min = floor($sec / 60);
			$sec = floor($sec % 60);
		}

		if ($min >= 60) {
			$hour = floor($min / 60);
			$min = floor($min % 60);
		}

		if ($hour >= 24) {
			$days = floor($hour / 24);
			$hour = floor($hour % 60);
		}

		if(strlen($hour) == 1) $hour = "0".$hour;
		if(strlen($min) == 1) $min = "0".$min;
		if(strlen($sec) == 1) $sec = "0".$sec;

		return "Process Time : ".$days."-".$hour.":".$min.":".$sec;
	}

 }
?>