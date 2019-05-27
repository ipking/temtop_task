<?php
namespace ttwms;

use ttwms\model\PurchaseReceipt;
use function Temtop\t;

/**
 * @var $info
 * @var string $model
 * @var PurchaseReceipt $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('预计到达时间')); ?>
	<section class="container">
        <form action="<?=ViewBase::getUrl("receipt/purchaseReceipt/arrivaldateeditanddescription",['id'=>$info->id])?>" class="frm model_<?=$model?>" method="POST" data-component="async">
            <table class="frm-tbl">
                <thead></thead>
                <tbody>
                <tr class="">
                    <td  class="col-label"><?=t("预计到达日期")?>:</td>
                    <td> <input name="arrival_date" type="text"  class="date-txt txt" value="<?=$info->arrival_date?>"/></td>
                </tr>
                <tr class="">
                    <td  class="col-label"><?=t("备注")?>：</td>
                    <td  >

                        <textarea cols="40" rows="7" name="description"   class="txt"><?=$info->description?></textarea>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <input name="id" type="hidden"  class="txt"   value="<?=$info->id?>"/>
                        <input type="submit" value="<?=t('确定')?>" class="btn big-btn btn_submit"/>&nbsp;&nbsp;
	                    <?=ViewBase::getDialogCloseBtn()?>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>