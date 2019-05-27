<?php
namespace ttwms;

use Lite\Core\Config;
use ttwms\model\PurchaseReceipt;
use function Temtop\t;

/**
 * @var $list
 * @var string $model
 * @var PurchaseReceipt $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$type = Config::get('type');
?>
<?= $this->buildBreadCrumbs(array('退回')); ?>
	<section class="container">
	<form action="<?=ViewBase::getUrl('receipt/purchaseReceipt/back',['id'=>$info->id]);?>" class="frm model_<?=$model?>" method="POST" data-component="async">
		<table class="frm-tbl">
			<tbody>
				   <tr>
					   <td><?=t('选择原因')?></td>
					   <td>
                           <select id="reason_chose">
                               <option value="">--<?=t("请选择")?>--</option>
	                           <?php foreach ($type['order']['back_reason'] as $id=>$name):?>
		                           <option value="<?=$id?>"><?=$name?></option>
	                           <?php endforeach;?>
                           </select>
					   </td>
				   </tr>
	               <tr class="">
	                   <td  class="col-label"><em>*</em><?=t('退回原因')?></td>
	                   <td  > <textarea cols="60" rows="5" name="back_note"   class="txt"></textarea></td>
	               </tr>
				    <tr>
	                    <td></td>
	                    <td>
					        <input type="submit" value="<?=t('确定')?>" class="btn big-btn btn_submit"/>&nbsp;&nbsp;
		                    <?=ViewBase::getDialogCloseBtn()?>
	                    </td>
	                </tr>
			</tbody>
		</table>
	</form>

<script>
	seajs.use(["jquery","ywj/msg"],function($,Msg){
		$('#reason_chose').change(function(){
			$("textarea").val($(this).find("option:selected").text());
		})
	})
</script>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>