# 회원 등급 별 지급/사용 포인트 통계 
/var/www/html/nimda/engine/insertMemberGradePoint.php  1>/dev/null 2>/dev/null &

# 포인트 통계
/var/www/html/nimda/engine/insertPointStats.php 1>/dev/null 2>/dev/null &

# 가격 엑셀 입력 엔진
/var/www/html/nimda/engine/price_engine.php 1>/dev/null 2>/dev/null &

# 오특이 이벤트 종료시간 체크 데몬
/var/www/html/nimda/engine/oevent_update.php 1>/dev/null 2>/dev/null &


