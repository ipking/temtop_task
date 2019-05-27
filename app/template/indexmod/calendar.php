<?php

use Lite\Component\Calendar;

$start = date('Y-m-01');
$end = date('Y-m-', strtotime('+1 months')).(date('t', strtotime('+1 months')));
//todo get events by date
?>
<style>
	.index-content .mod{ height: auto;  max-width: 510px; box-shadow: none; background: none; }
	.mod .bd{ max-height: none; }
</style>
<div class="calendar-wrap">
	<div class="calendar-header">
		<div class="date-now"></div>
		<div class="month-now"></div>
		<div class="year-now"></div>
	</div>
	<div class="calendar-content">
		<div><?=(new Calendar())->__toString();?></div>
		<!-- <div><?=(new Calendar(date('Y-m-d', strtotime('+1 months'))))->__toString();?></div> -->
	</div>
</div>

<script>
	var monthArr = ["JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER"];
	var dateNow = new Date();
	var yearNow = dateNow.getFullYear();
	var monthNow = monthArr[dateNow.getMonth()]; // 英文月份
	var dateNow = dateNow.getDate() < 10 ? ( '0' + dateNow.getDate() ) : dateNow.getDate();
	document.querySelector('.year-now').innerHTML = yearNow;
	document.querySelector('.month-now').innerHTML = monthNow;
	document.querySelector('.date-now').innerHTML = dateNow;
</script>
