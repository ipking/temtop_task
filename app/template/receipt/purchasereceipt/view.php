<?php
namespace ttwms;

use ttwms\model\PrdProduct;
use ttwms\model\PurchaseReceipt;
use function Temtop\t;

/**
 * @var PrdProduct[] $list
 * @var PurchaseReceipt $info
 * @var array $roleList
 * @var array $search
 * @var array $supplyStatusList
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

$sku_predict_qty_map = $info->sku_predict_qty_map;
$sku_receipt_qty_map = $info->sku_receipt_qty_map;
$sku_qt_good_qty_map = $info->sku_qt_good_qty_map;
$sku_qt_bad_qty_map = $info->sku_qt_bad_qty_map;
$sku_put_good_qty_map = $info->sku_put_good_qty_map;
$sku_put_bad_qty_map = $info->sku_put_bad_qty_map;
?>
<?= $this->buildBreadCrumbs(array('入库明细')); ?>
	<section class="container">
        <table class="data-tbl scroll-tbl" data-empty-fill="1">
            <thead>
            <tr>
                <th ><?=t('SKU')?></th>
                <th ><?=t('产品')?></th>
                <th ><?=t('预报数量')?></th>
                <th ><?=t('已收货数量')?></th>
                <th ><?=t('质检良品数量')?></th>
                <th ><?=t('质检不良品数量')?></th>
                <th ><?=t('良品上架数量')?></th>
                <th ><?=t('不良品上架数量')?></th>
            </tr>
            </thead>
            <tbody>
                <?php
                /**
                 * @var PrdProduct $prd
                 */
                foreach ($list as $prd):?>
                    <tr>
                        <td><?=$prd->sku?></td>
                        <td><?=$prd->name?></td>
                        <td><?=$sku_predict_qty_map[$prd->id]?></td>
                        <td><?=$sku_receipt_qty_map[$prd->id]?></td>
                        <td><?=$sku_qt_good_qty_map[$prd->id]?></td>
                        <td><?=$sku_qt_bad_qty_map[$prd->id]?></td>
                        <td><?=$sku_put_good_qty_map[$prd->id]?></td>
                        <td><?=$sku_put_bad_qty_map[$prd->id]?></td>
                    </tr>
                <?php endforeach;?>
            </tbody>
        </table>
        <div class="operate-row">
	        <?=ViewBase::getDialogCloseBtn()?>
        </div>
	</section>
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>