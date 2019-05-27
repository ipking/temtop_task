<?php
namespace ttwms;



/** @var ViewBase $this */
/** @var array $todo_receipt */
/** @var array $todo_transit */
/** @var array $status_map */

ViewBase::setPagePath(['首页']);
include ViewBase::resolveTemplate('inc/header.inc.php');
echo ViewBase::getCss('index.css?'.date('ymd'));


?>
<style>
	.breadcrumbs {display:none;}
	.index-content { margin: 30px; }
</style>
<div class="index-content">
	<div class="data-info-box">
		<div class="homepage-title">任务列表</div>
		<input type="text" name="sku" value="" placeholder="SKU">
		<div class="todo-total">
			<ul>
				<li>
					<a id="prd_sku_three_and_one" href="javascript:void(0)">tbs 三在一中</a>
					<p id="prd_sku_three_and_one_re"></p>
				</li>
			</ul>
		</div>
	</div>
	

</div>

<script>
	seajs.use(["jquery","ywj/uploader","ywj/msg","ywj/net"],function($,UP,Msg,Net){
		var $sku = $("[name=sku]");
		//三在一中
		$("#prd_sku_three_and_one").click(function () {
			var sku = $sku.val();
			
			Net.get("<?=ViewBase::getUrl("task/prd_sku_three_and_one")?>",{sku:sku},function (data) {
				if(data.code){
					Msg.showError(data.message);
					return false;
				}
				$('#prd_sku_three_and_one_re').html(data.data.out);
			})
		});
	});
</script>