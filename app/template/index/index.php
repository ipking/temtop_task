<?php
namespace ttwms;

use ttwms\model\PurchaseReceipt;
use ttwms\model\TransitDeliveryOrder;

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
		<div class="homepage-title">待办事项</div>
		<div class="todo-total">
			<ul>
				<?php foreach($status_map as $key=>$name):?>
				<li class="todo-item" data-url="<?=ViewBase::getUrl('receipt/purchasereceipt/index')?>">
					<p class="todo-number"><a href="<?=ViewBase::getUrl('receipt/purchasereceipt/index',['status'=>$key])?>"><?=$todo_receipt[$key]?:0?></a></p>
					<p><?=$name?></p>
				</li>
				<?php endforeach;?>
				<li class="todo-item" data-url="<?=ViewBase::getUrl('order/transitdeliveryorder/index')?>">
					<p class="todo-number"><a href="<?=ViewBase::getUrl('order/transitdeliveryorder/index',['status'=>TransitDeliveryOrder::STATUS_NEW])?>"><?=$todo_transit[TransitDeliveryOrder::STATUS_NEW]?:0?></a></p>
					<p>待出库</p>
				</li>
				
			</ul>
		</div>
	</div>
	<div class="mod-wrap">
		<div class="mod">
			<div class="homepage-title">日历</div>
			<div class="bd">
				<!-- 日历 -->
				<?php include ViewBase::resolveTemplate('indexmod/calendar.php');?>
			</div>
		</div>
	</div>

</div>

<script>

</script>
<?php
include ViewBase::resolveTemplate('inc/footer.inc.php');?>