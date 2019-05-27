<?php

use ttwms\controller\IndexModController;
use ttwms\ViewBase;

echo ViewBase::getCss('clock.css');
$time_config = IndexModController::getClockSetting();
?>
<div class="clocks <?=$time_config['mode'] == 'digit' ? 'clocks-list':'';?>">
	<?php foreach($time_config['list'] as list($city, $tz)):?>
	<div class="clock-wrap">
		<article class="clock ios7 linear" data-timezone="<?=$tz;?>">
			<div class="hours-container">
				<div class="hours"></div>
			</div>
			<div class="minutes-container">
				<div class="minutes"></div>
			</div>
			<div class="seconds-container">
				<div class="seconds"></div>
			</div>
		</article>
		<label><?=$city;?></label>
		<span class="tm"></span>
	</div>
	<?php endforeach;?>
</div>

<a href="<?=ViewBase::getUrl('indexMod/clockSetting');?>" data-component="popup" class="clock-set-dialog-link">设置</a>

<script>
	seajs.use(['jquery', 'ywj/popup', 'jquery/cookie'], function($, Pop){
		var DATE_MAP = {
			0: 'MON',
			1: 'TUE',
			2: 'WED',
			3: 'THU',
			4: 'FRI',
			5: 'SAT',
			6: 'SUN'
		};
		
		var utc_now = function(){
			var d = new Date();
			return d.getTime() + d.getTimezoneOffset()*60000;
		};
		
		var pl = function(str){
			return (str+'').length < 2 ? ('0'+str) : str;
		};
		
		/**
		 * convert timezone to seconds
		 */
		var convert_time_zone = function(timezone){
			var tmp = timezone.substr(1).split(':');
			var h = parseInt(tmp[0], 10);
			var m = parseInt(tmp[1], 10);
			var s = h*3600 + m * 60;
			return timezone.substr(0, 1) === '-' ? -s : s;
		};
		
		/**
		 * Starts any clocks using the user's local time
		 * From: cssanimation.rocks/clocks
		 */
		var initLocalClocks = function($clock) {
			var timezone_sec = convert_time_zone($clock.data('timezone'));
			var date = new Date(utc_now() + timezone_sec*1000);
			var seconds = date.getSeconds();
			var minutes = date.getMinutes();
			var hours = date.getHours();

			update_time($clock.parent().find('.tm'), timezone_sec);

			// Create an object with each hand and it's angle in degrees
			var hands = [
				{
					hand: 'hours',
					angle: (hours * 30) + (minutes / 2)
				},
				{
					hand: 'minutes',
					angle: (minutes * 6)
				},
				{
					hand: 'seconds',
					angle: (seconds * 6)
				}
			];
			// Loop through each of these hands to set their angle
			for(var j = 0; j < hands.length; j++){
				var el = $clock.find('.' + hands[j].hand)[0];
				el.style.webkitTransform = 'rotateZ(' + hands[j].angle + 'deg)';
				el.style.transform = 'rotateZ(' + hands[j].angle + 'deg)';
				// If this is a minute hand, note the seconds position (to calculate minute position later)
				if(hands[j].hand === 'minutes'){
					el.parentNode.setAttribute('data-second-angle', hands[j + 1].angle);
				}
			}
		};
		
		var update_time = function($container, timezone_sec){
			var date = new Date(utc_now() + timezone_sec*1000);
			var seconds = date.getSeconds();
			var minutes = date.getMinutes();
			var hours = date.getHours();
			var str = '<span class="tm-h">'+pl(hours)+'</span>:<span class="tm-m">'+pl(minutes)
				+'</span>:<span class="tm-s">'+pl(seconds)+ '</span> <span class="tm-wk">' + DATE_MAP[date.getDay()==0?6:date.getDay()-1]+'</span>';
			$container.html(str);
			setTimeout(function(){
				update_time($container, timezone_sec);
			}, 1000);
		};
		
		$('.clock[data-timezone]').each(function(){
			initLocalClocks($(this));
		});
	});
</script>