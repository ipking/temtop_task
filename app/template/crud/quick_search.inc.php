
<?php

use Lite\Crud\ControllerInterface;
use ttwms\ViewBase;
use function Lite\func\h;

if(!in_array(ControllerInterface::OP_QUICK_SEARCH, $operation_list ?: array())){
	return;
}

/** @var ViewBase $this */
$quick_search_defines = $this->getData('quick_search_defines');
?>
<form action="<?php echo ViewBase::getUrl($this->getControllerAbbr().'/'.$this->getAction());?>" method="get" class="quick-search-frm">
	<?php if($search['ref']):?>
	<input type="hidden" name="ref" value="<?php echo h($search['ref']);?>">
	<?php endif;?>

	<?php foreach($quick_search_defines as $field=>$def):?>
	<?php echo $this->renderSearchFormElement($search[$field], $field, $def, null, array('placeholder'=>$def['alias']));?>
	<?php endforeach;?>

	<input class="btn" type="submit" value="搜索"/>
	<a class="btn btn-weak" href="<?php echo ViewBase::getUrl($this->getControllerAbbr().'/'.$this->getAction());?>">重置</a>
</form>
