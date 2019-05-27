<?php
namespace ttwms;

use ttwms\model\PurchaseReceiptBox;
use ttwms\model\PurchaseReceiptBoxItem;
use function Temtop\t;

/**
 * @var $list
 * @var string $model
 * @var string $mode
 * @var string $url
 * @var PurchaseReceiptBox $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('收货')); ?>
	
    <style>
        #codeInput{ font-size: 20px;}
        #codeInput input{ font-size: 20px;}
        .autoFill {float:right;}
        h2 { font-weight:normal; font-size:22px; padding-bottom:0.5em;}
        .td_limit{max-width:200px !important;-ms-word-break: break-all;word-break: break-all;}
    </style>
	<section class="container">
        <h2 style="text-align:center;"><?=t("箱号")?>:<?=$info->no?></h2>
        <form action="<?=ViewBase::getUrl($url)?>" class="frm <?=$mode?'':'readonly'?>" method="POST" data-component="async" id="receiveForm">
            <input type="hidden" name="id" id="box_id" value="<?=$info->id?>">
            <input type="hidden" id="enterpriseCode" value="<?=$info->receipt->code?>">
            <table class="data-tbl" id="receive_list">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th ><?=t("产品名称")?></th>
                        <th ><?=t("数量")?></th>
                        <th ><?=t("收货")?><?=t("数量")?></th>
                    </tr>
                </thead>
                <tbody>
                <?php /** @var PurchaseReceiptBoxItem[] $list */
                foreach ($list as $item):?>
                    <tr class="data-tr" id="<?=$item->product->sku?>">
                        <td><?=$item->product->sku;?>
                            <input type="hidden" class="item_id" name="items[<?=$item->id?>][item_id]" value="<?=$item->id?>"/>
                            <input type="hidden" class="product_id" name="items[<?=$item->id?>][product_id]" value="<?=$item->product->id?>">
                        </td>
                        <td class="td_limit">
                            <div class="ch-name"><?=$item->product->name?></div>
                            <div class="en-name"><?=$item->product->ename?></div>
                        </td>
                        <td class="expectNum"><?=$item->qty?></td>
                        <td><input type="number" class="txt receive_qty" name="items[<?=$item->id?>][receive_qty]" required='required' min="0" step="1" value="<?=$item->receive_qty?>"  title=""/></td>
                    </tr>
                <?php endforeach;?>
                </tbody>
            </table>
	        <div class="operate-row">
		        <?=ViewBase::getDialogCloseBtn()?>
	        </div>
        </form>
	</section>


	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>