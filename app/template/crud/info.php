<?php

use Lite\Core\Router;
use Lite\Crud\ControllerInterface;
use Lite\DB\Model;
use ttwms\ViewBase;
use function Lite\func\h;

/** @var ViewBase $this */
/** @var Model $model_instance */
include ViewBase::resolveTemplate('inc/header.inc.php');
$pk = $model_instance->getPrimaryKey();
$operation_list = $this->getData('operation_list');
$display_fields = $this->getData('display_fields');
$quick_update_fields = $this->getData('quick_update_fields');
?>
<section class="container">
	<table class="frm-tbl">
		<caption><?php echo $model_instance->getModelDesc();?>信息</caption>
		<tbody>
			<?php
			foreach($display_fields as $field=>$alias):
				$define = $defines[$field];
				$text = $model_instance->$field;
				$html = ViewBase::displayField($field, $model_instance);
				?>
			<tr>
				<th><?php echo h($alias);?></th>
				<td>
				<?php if(!in_array($field, $quick_update_fields)):?>
				<?php echo $html;?>
				<?php else:
				echo ViewBase::displayFieldQuickUpdate($field, $model_instance);?>
				<?php endif;?>
				</td>
			</tr>
			<?php endforeach;?>

			<tr>
				<td></td>
				<td class="col-action">
					<?php if(in_array(ControllerInterface::OP_UPDATE, $operation_list)):?>
					<a href="<?php echo ViewBase::getUrl($this->getControllerAbbr().'/update', array($pk=>$model_instance->$pk, 'ref'=>Router::get('ref')));?>" class="btn">修改</a>
					<?php endif;?>

					<?php if(in_array(ControllerInterface::OP_DELETE, $operation_list)):?>
					<a href="<?php echo ViewBase::getUrl($this->getControllerAbbr().'/delete', array($pk=>$model_instance->$pk));?>" data-component="async" class="btn btn-danger">删除</a>
					<?php endif;?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="operate-row">
		<?=ViewBase::getDialogCloseBtn();?>
	</div>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>