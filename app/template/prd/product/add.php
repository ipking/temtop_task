<?php
namespace ttwms;

use ttwms\model\Enterprise;
use function Lite\func\h;
use function Temtop\t;

/**
 * @var \ttwms\model\PrdProduct $info
 * @var array $isActiveList
 * @var array $get
 * @var string $url
 * @var string $model
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');
$EnterpriseList = Enterprise::find("status=?",Enterprise::STATUS_ENABLED)->map("id", "code");

?>
	<style>
		.repeat-password {display:block; margin-top:10px}

		/** 只读 **/
		.readonly input.txt,
		.readonly textarea.txt {border-color:transparent; outline:none;}

		.readonly input[type=submit],
		.readonly .com-uploader-btn,
		.readonly .batch-uploader-delete-btn,
		.readonly .batch-uploader-add-new,
		.readonly .com-uploader-file,
		.readonly .small-add-btn,
		.readonly .small-delete-btn,
		.readonly button[type=submit] {display:none}

		.readonly .batch-uploader,
		.readonly.batch-uploader {background-color:transparent;}
		.readonly .batch-uploader li,
		.readonly.batch-uploader li{border-color:transparent; background-color:transparent; height:100px; margin:0; padding:0;}

		.readonly .date-txt, .readonly .date-time-txt {background-image:none;}
	</style>
<?= $this->buildBreadCrumbs(array('产品管理'=>'prd/product/index',"添加库区")); ?>
<section class="container">
	<form action="<?= ViewBase::getUrl($url,["id"=>$get['id']]); ?>" class="frm <?=$model?>" method="post" data-component="async" <?=$get['readonly']?>>
		<input type="hidden" name='id' value='<?=$info['id']?>' />
		<table class="frm-tbl">
			<caption><?=t("基本信息")?></caption>
			<tbody>
			<tr>
				<td class="col-label"><em>*</em><?=t("中文名称")?></td>
				<td><input type='text' class='txt' name="info[name]" value="<?=$info['name']?>" size="50" required="required"/></td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em><?=t("英文名称")?></td>
				<td><input type='text' class='txt' name="info[ename]" value="<?=$info['ename']?>" size="50" required="required"/></td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em>SKU</td>
				<td>
					<input type='text' class='txt' name="info[sku]" value="<?=$info['sku'];?>" size="50" maxlength="10" required="required" <?=$readonly?"readonly='readonly'":''?>/>
				</td>
			</tr>
			</tbody>
		</table>
		<table class="frm-tbl">
			<caption><?=t("基本属性")?></caption>
			<tbody>
			<tr>
				<td class="col-label"><em>*</em><?=t("客户代码")?></td>
				<td>
					<?php if($readonly):?>
						<?=$EnterpriseList[$info['enterprise_id']]?>
					<?php else:?>
						<select class="enterprise_id" name="info[enterprise_id]"  required="required" <?=$readonly?"readonly='readonly'":''?>>
							<option value=""><?=t("--请选择--")?></option>
							<?php foreach ($EnterpriseList as $id => $code):?>
								<option <?=$info['enterprise_id'] == $id ?'selected':''?> value="<?=$id?>"><?=h($code)?></option>
							<?php endforeach;?>
						</select>
					<?php endif;?>

				</td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em><?=t("长")?>*<?=t("宽")?>*<?=t("高")?>(cm)</td>
				<td><input type="number" name="info[length]" value='<?=$info['length']?:0?>' size="5" class="txt" min="0" step="0.1" required="required"/> &times;
					<input type="number" name="info[width]" value='<?=$info['width']?:0?>' size="5" class="txt" min="0" step="0.1" required="required"/> &times;
					<input type="number" name="info[height]" value='<?=$info['height']?:0?>' size="5" class="txt" min="0" step="0.1" required="required"/></td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em><?=t("净重")?>(g)</td>
				<td><input type="number" name="info[weight_net]" value='<?=$info['weight_net']?$info['weight_net']:0?>' size="7" class="txt" min="0" required="required"/></td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em><?=t("毛重")?>(g)</td>
				<td><input type="number" name="info[weight_rough]" value='<?=$info['weight_rough']?$info['weight_rough']:0?>' size="7" class="txt" min="0" required="required"/></td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em><?= t("是否自带包装") ?></td>
				<td>
					<?=ViewBase::renderElementQuick($info,"package_type","info[package_type]")?>
				</td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em><?= t("条码类型") ?></td>
				<td>
					<?=ViewBase::renderElementQuick($info,"barcode_type","info[barcode_type]")?>
				</td>
			</tr>
			</tbody>
		</table>
		<table class="frm-tbl">
			<caption><?=t("其他信息")?></caption>
			<tbody>
			<tr>
				<td class="col-label"><?=t("报关税率")?></td>
				<td><input type="number" class="txt" name="info[tax_rate]" min="0" step="0.01" value="<?=$info['tax_rate']?>" <?=$readonly?"readonly='readonly'":''?>/> %</td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em><?=t("报关名称")?></td>
				<td><input type="text" class="txt" name="info[clearance_name]" value="<?=$info['clearance_name']?>" required="required" /> </td>
			</tr>
			<tr>
				<td class="col-label"><?=t("报关")?>code</td>
				<td><input type="text" class="txt" name="info[clearance_code]" value="<?=$info['clearance_code']?>" <?=$readonly?"readonly='readonly'":''?>></td>
			</tr>
			<tr>
				<td class="col-label"><em>*</em><?=t("报关价格")?>($)</td>
				<td><input type="number" class="txt" name="info[clearance_price]" min="0" step="0.01" value="<?=$info['clearance_price']?>" required="required" /></td>
			</tr>
			<tr>
				<td class="col-label"><?=t("备注")?></td>
				<td><textarea class="txt" rows="3" cols="60" name="info[note]" <?=$readonly?"readonly='readonly'":''?>><?=$info->note?:'-'?></textarea></td>
			</tr>
			</tbody>
		</table>
		<div class="operate-row">
			<input type="submit" class="btn" value="保存">
			<?=ViewBase::getDialogCloseBtn()?>
		</div>
	</form>
</section>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>