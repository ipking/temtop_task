<?php
namespace ttwms;

use ttwms\model\WhArea;
use function Temtop\t;

/**
 * @var \ttwms\model\WhLocation[] $locations
 * @var array $isActiveList
 * @var array $get
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
	<style>
		.repeat-password {display:block; margin-top:10px}
	</style>
<?= $this->buildBreadCrumbs(array('货位管理'=>'wh/location/index',"打印条码")); ?>

<section class="container">

	<div id="col-main">
		<form action="<?=ViewBase::getUrl("wh/location/doPrint")?>" method="GET" class="frm">
			<table class="data-tbl">
				<thead>
				<tr>
					<th ><?=t("货位条码")?></th>
					<th ><?=t("数量")?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($locations as $loc):?>
					<tr>
						<td><?=$loc->code?></td>
						<td><input type="text" class="txt" value="1" name="bars[<?=$loc->id?>]"/> </td>
					</tr>
				<?php endforeach;?>
				</tbody>
			</table>
			<p style="text-align: center;margin-top:10px;">
				<a href="javascript:;" class="btn" id="btn-print" target="_blank"><?=t("打印")?></a>
				<a href="javascript:;" class="btn" id="btn-close"><?=t("关闭")?></a>
			</p>
		</form>
	</div>
	
</section>
	<script>
		seajs.use(["jquery","temtop/printer","ywj/net","ywj/popup"],function($,Printer, Net, Popup){
			$("#btn-close").click(function(){
				Popup.closeCurrentPopup();
			});

			$("#btn-print").click(function(){
				Printer.getPrinter('print_location', function(printer_index){
					var str = $(".frm:first").serialize();
					Printer.printURL_new("<?=ViewBase::getUrl("wh/location/doPrint")?>?"+str,printer_index,1,1000,400);
				});
				return false;
			});
		});
	</script>

<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>