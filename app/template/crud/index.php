<?php

use Lite\Crud\ControllerInterface;
use Lite\DB\Model;
use ttwms\ViewBase;
use function Lite\func\h;

$operation_list = $this->getData('operation_list');
$search = $this->getData('search');
$display_fields = $this->getData('display_fields');
$data_list = $this->getData('data_list');
$defines = $this->getData('defines');
$paginate = $this->getData('paginate');

$export_link = $this->getData('export_link');
$export_format = $this->getData('export_format');
$order_fields = $this->getData('order_fields');
$quick_update_fields = $this->getData('quick_update_fields');

/** @var Model $model_instance */
$pk = $model_instance->getPrimaryKey();
include ViewBase::resolveTemplate('inc/header.inc.php');
$_in_new_page = count($defines) > 15 || in_array('rich_text', array_column($defines, 'type'));
?>
<?php include ViewBase::resolveTemplate('crud/quick_search.inc.php');?>
<?php if(in_array(ControllerInterface::OP_UPDATE, $this->getData('operation_list'))):?>
	<div class="operate-bar">
		<a href="<?php echo ViewBase::getUrl(ViewBase::getControllerAbbr().'/update');?>" <?php if(!$_in_new_page):?>data-component="popup"<?php endif;?> class="btn">
			新增<?php echo $model_instance->getModelDesc();?>
		</a>
		<?php if($export_link):?>
			<a href="<?php echo $export_link;?>" class="table-export-btn" target="_blank">另存为<?php echo $export_format;?></a>
		<?php endif;?>
	</div>
<?php endif;?>

<?php
$operation_link_list_count = count($this->getData('operation_link_list'));
if(in_array(ControllerInterface::OP_DELETE, $operation_list)){
	$operation_link_list_count++;
}
if(in_array(ControllerInterface::OP_UPDATE, $operation_list)){
	$operation_link_list_count++;
}
if(in_array(ControllerInterface::OP_INFO, $operation_list)){
	$operation_link_list_count++;
}
?>
<table class="data-tbl" data-empty-fill="1" data-component="ywj/fixedhead">
	<thead>
	<tr>
		<?php foreach($display_fields as $field=>$alias):?>
		<th><?php echo in_array($field, $order_fields) ? ViewBase::getOrderLink($alias, $field) : h($alias);?></th>
		<?php endforeach;?>
		<?php if($operation_link_list_count):?>
		<th class="col-op">操作</th>
		<?php endif;?>
	</tr>
	</thead>
	<tbody>
		<?php
		foreach($data_list ?: array() as $item):
			/** @var Model $item */
			$operation_link_list = $this->getData('operation_link_list') ?: array();
			if(in_array(ControllerInterface::OP_DELETE, $operation_list)){
				array_unshift($operation_link_list, '<a href="'.ViewBase::getUrl(ViewBase::getControllerAbbr().'/delete', array($pk=>$item->$pk)).'" data-component="async">删除</a>');
			}
			if(in_array(ControllerInterface::OP_UPDATE, $operation_list)){
				array_unshift($operation_link_list, '<a href="'.ViewBase::getUrl(ViewBase::getControllerAbbr().'/update', array($pk=>$item->$pk)).'" ' .(!$_in_new_page ? 'data-component="popup"':''). ' >编辑</a>');
			}
			if(in_array(ControllerInterface::OP_INFO, $operation_list)){
				array_unshift($operation_link_list, '<a href="'.ViewBase::getUrl(ViewBase::getControllerAbbr().'/info', array($pk=>$item->$pk)).'" '.(!$_in_new_page ? 'data-component="popup"':'').'>详情</a>');
			}
		?>
		<tr <?php if($item['status']):?>class="row-status-<?=strtolower($item['status'])?>"<?php endif;?>>
			<?php
			foreach($display_fields as $field=>$alias):
				$define = $defines[$field];
				$text = $item->$field;
				$html = ViewBase::displayField($field, $item);
			?>
			<?php if(!in_array($field, $quick_update_fields)):?>
			<td>
				<?php echo $html;?>
			</td>
			<?php else:?>
			<td style="width:100px">
				<?=ViewBase::displayFieldQuickUpdate($field, $item);?>
			</td>
			<?php endif;?>
			<?php endforeach;?>

			<?php if($operation_link_list):?>
			<td class="col-op">
				<?php
				if(count($operation_link_list) == 1){
					echo $operation_link_list[0];
				}
				else if($operation_link_list){?>
				<dl class="drop-list drop-list-left">
					<dt>
						<?php
						$op = array_shift($operation_link_list);
						echo is_callable($op) ? call_user_func($op, $item) : $op;?>
					</dt>
					<dd>
						<?php
						foreach($operation_link_list as $op){
							echo is_callable($op) ? call_user_func($op, $item) : $op;
						}?>
					</dd>
				</dl>
				<?php }?>
			</td>
			<?php endif;?>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<?php echo $paginate;?>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php');?>