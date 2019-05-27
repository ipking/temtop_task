<?php

use Lite\DB\Model;
use ttwms\ViewBase;
use function Lite\func\h;

/**
 * @var Model $model_instance
 * @var ViewBase $this
 */
ViewBase::setPagePath(array(($model_instance->$pk? '编辑' : '新增').$model_instance->getModelDesc()));
include ViewBase::resolveTemplate('inc/header.inc.php');
$pk = $model_instance->getPrimaryKey();
$extra_params = $this->getData('extra_params');
$update_fields = $this->getData('update_fields');
?>
<form action="<?php echo ViewBase::getUrl($this->getControllerAbbr().'/update', array($pk=>$model_instance->$pk));?>" class="frm" data-component="async" method="post">
	<input style="display:none">
	<input type="password" style="display:none">

	<?php foreach($extra_params as $k=>$v):?>
	<input type="hidden" name="<?php echo h($k); ?>" value="<?php echo h($v);?>">
	<?php endforeach;?>

	<table class="frm-tbl">
		<caption><?php echo $model_instance->$pk? '编辑' : '新增'?><?php echo $model_instance->getModelDesc();?></caption>
		<tbody>
			<?php
			foreach($update_fields as $field=>$alias):
				$def = $defines[$field];
				$readonly = isset($extra_params[$field]) || $def['readonly'] ? array('readonly'=>'readonly') : array();
				if($readonly){
					continue;
				}
			?>
			<tr>
				<th><?php echo $alias;?></th>
				<td>
					<?php echo $this->renderFormElement($model_instance->$field, $field, $def, $model_instance, $readonly) ?>
					<?php if($def['description']):?>
					<span class="frm-field-desc">
						<?php echo $def['description'];?>
					</span>
					<?php endif;?>
				</td>
			</tr>
			<?php endforeach;?>
			<tr>
				<td></td>
				<td class="col-action">
					<input type="submit" value="<?php echo $model_instance->$pk? '保存修改' : '新增'?>" class="btn" />
				</td>
			</tr>
		</tbody>
	</table>
</form>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>