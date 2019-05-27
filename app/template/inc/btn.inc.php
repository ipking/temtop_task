<div class="submit-row">
	<button data-save_form="com-save-form" id="com-save-btn" class="btn iconfont icon-save">保存</button>
	<button class="btn-weak iconfont icon-cancel close-current-win-btn">关闭</button>
</div>
<script>
	seajs.use(['jquery'], function ($) {
		$('#com-save-btn').click(function(){
			$("#com-save-form").submit();
		});
	});
</script>