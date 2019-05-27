seajs.use(['jquery', 'ywj/msg', 'ywj/net', 'ywj/popup', 'ywj/tip', 'ywj/util'], function($, Msg, Net, Pop, Tip, Util){
	Pop.autoResizeCurrentPopup();
	var $body = $('body');

	//form element disabled
	$('.readonly input, .readonly select, .readonly textarea').attr('disabled','disabled').attr('placeholder', '');
	$('.readonly input[type=radio][disabled]').each(function(){
		var $radio = $(this);
		if(!$radio.attr('checked')){
			var $parent = $radio.parent();
			if($parent[0].tagName === 'LABEL'){
				$parent.hide();
			}
		} else {
			$radio.hide();
		}
	});

	//page size
	$('[rel=page-size-trigger]').click(function(){
		var sets = [10, 20, 40, 100];
		var html = '<div class="page-size-setup">';
		var url_mode = $(this).data('url-mode');
		for(var i=0; i<sets.length; i++){
			var url = url_mode.replace('__PS__', sets[i]);
			html += '<a href="'+url+'">'+sets[i]+'</a>';
		}
		html += '</div>';
		var tip = new Tip(html, this, {dir:6});
		tip.show();
	});

	//require 表单标记
	$(':input[required]').closest('tr').each(function(){
		$(this).addClass('row-required');
		$(this).find('[required]:not([disabled]):not([readonly])').each(function(){
			var $th = $(this).closest('td').prev();
			var tagName = $th.prop("tagName");
			if(tagName === 'TH'){
			}else{
				$th = $(this).closest('td');
			}
			if($th.data('mark')){
				return;
			}
			var $mark = $('<span class="required-mark">*</span>');
			$mark.prependTo($th);
			$th.attr('data-mark',1);
		});
	});

	//sku列表效果
	$('.tt-sku-list').each(function(){
		var OFFSET = 5;
		var $list = $(this);
		var list_top = $list.offset().top;
		var h = $list.outerHeight();
		var overflow = false;
		$list.children().each(function(){
			if($(this).offset().top - list_top > (h-OFFSET)){
				overflow = true;
				return false;
			}
		});
		if(overflow){
			var $more = $('<span class="tt-sku-list-more" title="查看更多"></span>').insertAfter($list);
			var $panel;
			var panel_tm;
			var show_panel = function(){
				clearTimeout(panel_tm);
				if(!$panel){
					$panel = $('<div class="tt-sku-list-more-panel"></div>').appendTo('body');
					var html = '<table class="data-tbl"><tbody><tr>';
					var k = 0;
					$list.find('li').each(function(){
						if(!(k % 2) && k){
							html += '</tr><tr>';
						}
						html += '<td>'+$(this).html()+'</td>';
						k++;
					});
					for(var i=0; i<(2-(k%2)) && k%2; i++){
						html += '<td></td>';
					}
					html += '</tr></tbody></table>';
					$panel.html(html);
					$panel.find('.tt-sku-list').addClass('tt-sku-list-all');
					$panel.hover(show_panel, hide_panel);
				}
				$panel.css({
					left: $list.offset().left,
					top:$list.offset().top,
					opacity:0
				}).stop().show().animate({
					opacity:1
				});
			};

			var hide_panel = function(){
				if($panel){
					panel_tm = setTimeout(function(){
						$panel.stop().animate({
							opacity:0
						}, function(){
							$panel.hide();
						});
					},100);
				}
			};
			$list.hover(show_panel, hide_panel);
			$more.hover(show_panel, hide_panel);
		}
	});

	 //drop-list dl>dt&dd>a,dd>span
	(function(){
		$("dl.drop-list").each(function(){
			var $dl = $(this);
			if($dl.find('dd').length === 0){
				$dl.find('dt').addClass('single');
				return;
			}
			var num = 0;
			$dl.find('dd>a,dd>span').each(function(){
				if($(this).css('visibility') == 'hidden' || $(this).css('display') == 'none'){
					num++;
				}
			});
			if($dl.find('dd>a,dd>span').length == num){
				$dl.find('dt').addClass('single');
				$dl.find('dd').remove();
			}
		});

		$('.drop-list').each(function(){
			if($(this).find('dd').length==0){
				$(this).children('dt').addClass('info-after')
			}
		});
	})();

	/**
	 * tag组件
	 * @param $tags_input
	 */
	(function(){
		var update_tags_input = function($tags_input){
			var value = [];
			$tags_input.find('li>span[class!="del-tag"]').each(function(){
				value.push($(this).text());
			});
			var $val = $tags_input.find('input[type=hidden]');
			$val.val(value.join(','));
		};

		$body.delegate('.tags-input .del-tag', 'click', function(){
			var $ti = $(this).closest('.tags-input');
			$(this).parent().remove();
			update_tags_input($ti);
		});

		$body.delegate('.tags-input input[type=text]', 'keydown', function(e){
			var val = $.trim(this.value);
			if(val && e.keyCode == 13){
				var vs = val.replace('，',',').split(',');
				for(var i = 0; i < vs.length; i++){
					$('<li><span class="del-tag" title="删除">&times;</span><span>' + vs[i] + '</span></li>').appendTo($(this).closest('.tags-input').find('ul'));
				}
				this.value = '';
				update_tags_input($(this).closest('.tags-input'));
			}

			if(e.keyCode == 13){
				return false;
			}
		});

		$('.tags-input input[type=hidden]').each(function(){
			var val = $.trim(this.value);
			if(!val){
				return;
			}
			var vs = val.replace('，',',').split(',');
			for(var i = 0; i < vs.length; i++){
				$('<li><span class="del-tag" title="删除">&times;</span><span>' + vs[i] + '</span></li>').appendTo($(this).closest('.tags-input').find('ul'));
			}
		});
	})();

	//关闭当前页
	(function(){
		$body.delegate('.close-current-win-btn', 'click', function(e){
			if (Pop.getCurrentPopup()) {
				Pop.getCurrentPopup().close();
			}else{
				window.history.back();
			}
		});
	})();

	//SKU input
	(function(){
		var ERR_CLASS = 'sku-code-error';
		var $sku_inputs = $('input[rel=sku-input]');
		var pattern = /^\w{4,11}$/;
		$sku_inputs.attr('pattern',"^\\w{4,11}$");
		var _check_sku_code = function($inp, $err){
			$err.hide();
			$inp.removeClass(ERR_CLASS);
			var val = $.trim($inp.val());
			if(!val){
				return;
			}
			var reg = new RegExp(pattern);
			if(!reg.test(val)){
				$err.show().html('请输入正确格式的SKU编码');
				$inp.addClass(ERR_CLASS);
			}
		};
		$sku_inputs.each(function(){
			var $inp = $(this);
			if($inp.attr('readonly') || $inp.attr('disabled')){
				return;
			}
			var $tip = $('<div class="sku-input-tip">SKU格式：大写英文字母或数字，长度4至11位，如：ABC123，A012，ABCD1234，1234A</div>').insertAfter($inp);
			var $err = $('<span class="sku-code-err">').insertAfter($tip);
			$inp.on('click mouseup keyup blur', function(){_check_sku_code($inp, $err)});
			$inp.on('blur keyup',function(e){
				if(e.keyCode === Util.KEYS.ENTER || e.type === 'blur'){
					var val = $.trim(this.value) || '';
					$(this).val(val.toUpperCase());
				}
			});
		});
	})();
});