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
		<div class="homepage-title">待办事项</div>
		<div class="todo-total">
			12
		</div>
	</div>
	

</div>

<script>

</script>