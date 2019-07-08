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
		<input type="text" name="order_no" value="" placeholder="订单号">
		<div class="todo-total">
			<ul>
				<li>
					<a id="prd_sku_three_and_one" data-value="sku" href="javascript:void(0)">ERP(产品,供应商)系统 三在一中</a>
					<p></p>
				</li>
				<li>
					<a id="sale_sku_three_and_one" data-value="sku" href="javascript:void(0)">销售系统 三在一中</a>
					<p></p>
				</li>
			</ul>
		</div>
		<div class="todo-total">
			<ul>
				<li>
					<a id="tbs_sale_order_confirm" data-value="order_no" href="javascript:void(0)">TBS系统 审单</a>
					<p></p>
				</li>
				<li>
					<a id="tbs_sale_order_sync" data-value="order_no" href="javascript:void(0)">TBS系统 同步订单</a>
					<p></p>
				</li>
			</ul>
		</div>
		<div class="todo-total">
			<ul>
				<li>
					<a id="oms_sale_order_confirm" data-value="order_no" href="javascript:void(0)">OMS系统 审单</a>
					<p></p>
				</li>
				<li>
					<a id="oms_sale_order_sync" data-value="order_no" href="javascript:void(0)">OMS系统 同步订单</a>
					<p></p>
				</li>
			</ul>
		</div>
	</div>

</div>

<script>
	seajs.use(["jquery","ywj/uploader","ywj/msg","ywj/net"],function($,UP,Msg,Net){
		var $sku = $("[name=sku]");
		//三在一中
		$("a").click(function () {
			var $p = $(this).closest('li').find('p');
			$p.html('');
			var action = $(this).attr('id');
			var value_list =  $(this).data('value');
			var strs= new Array(); //定义一数组
			strs=value_list.split(","); //字符分割

			var param= {};
			param['action'] = action;
			for (var i in strs)
			{
				param[strs[i]] = $("[name="+strs[i]+"]").val();
			}

			Net.get("<?=ViewBase::getUrl("task")?>",param,function (data) {
				if(data.code){
					Msg.showError(data.message);
					return false;
				}
				var html= '';
				for(var x in data.data.out){
					html += data.data.out[x]+'<br >';
				}
				$p.html(html);
			})
		});
	});
</script>