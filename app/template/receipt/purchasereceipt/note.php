<?php
namespace ttwms;

use ttwms\model\PurchaseReceiptBox;
use function Temtop\t;

/**
 * @var $list
 * @var string $mode
 * @var array $param
 * @var PurchaseReceiptBox $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?php
echo $this->buildBreadCrumbs(array("备注"));
?>
<style>
.frm-tbl td.col-label{width:80px;}
</style>
	<section class="container">
	
	<form action="<?=ViewBase::getUrl("receipt/purchaseReceipt/note",['id' => $info->id])?>" class="frm" method="POST" data-component="async">
		<table class="frm-tbl">
			<caption><?=t("备注")?></caption>
            <tbody>
	            <tr>
		            <td class="col-label"><?=t("收货说明")?></td>
		            <td>
			            <textarea class="txt" rows="5" cols="50" name="note"><?=$info->note?></textarea>
		            </td>
	            </tr>
	            <tr>
		            <td></td>
		            <td align="center">
			            <input type="submit" value="<?=t("保存修改")?>" class="btn"/>
			            <?=ViewBase::getDialogCloseBtn()?>
		            </td>
	            </tr>
            </tbody>
		</table>
	</form>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>