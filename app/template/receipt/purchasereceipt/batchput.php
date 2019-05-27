<?php
namespace ttwms;

use ttwms\business\Form;
use ttwms\model\PurchaseReceipt;
use ttwms\model\WhLocation;
use ttwms\model\WhProductLocationMapping;
use function Temtop\t;

/**
 * @var $list
 * @var string $mode
 * @var array $param
 * @var PurchaseReceipt $info
 * @var \Lite\Component\Paginate $paginate
 */

include ViewBase::resolveTemplate('inc/header.inc.php');

?>
<?= $this->buildBreadCrumbs(array('批量上架')); ?>
    <style>
        #codeinput{
            padding-left: 50px;
            font-size: 20px;
        }
        #codeinput input{
            font-size: 20px;
        }
        .autofill { text-align: right;}
        .td_limit{max-width:200px !important;-ms-word-break: break-all;word-break: break-all;}
    </style>
	<section class="container">
        <div class="frm">
			<div class="autofill">
                <input type="text" class="choose-location-code txt" id="quick_fill" name="" >
                <a href="<?=ViewBase::getUrl("wh/location/simpleview")?>"  data-onsuccess="chooseLocation" data-component="popup" data-popup-height="400" data-popup-width="800" ><?=t("选择货位")?></a>

				<a class="btn" href="javascript:;" id="autofill_btn"><?=t("快速填充")?></a>
			</div>
        </div>
        <h2 style="text-align:center;">SKU:<?=$param['sku']?></h2>
        <h2 style="text-align:center;"><?=t("总数")?>:<?=$param['totalQty']?></h2>
        <form action="<?=ViewBase::getUrl('receipt/purchaseReceipt/batchput',['id'=>$info->id]);?>>" class="frm <?=$mode?'':'readonly'?>" method="POST" data-component="async" id="receiveForm">
            <input type="hidden" name="id" id="box_id" value="<?=$info->id?>">
            <table class="data-tbl" id="receive_list">
                <tr>
                    <th ><?=t("箱号")?></th>
                    <th ><?=t("产品名称")?></th>
                    <th ><?=t("数量")?></th>
                    <th ><?=t("收货")?><?=t("数量")?></th>
                    <th ><?=t("上架数量")?></th>
                    <th><?=t("推荐存放货架")?></th>
                    <th width="230"><?=t("实际存放货位")?></th>
                    <th width="40"><?=t("上架数量")?></th>
                    <th width="80"><?=t("操作")?></th>
                </tr>
                <?php foreach ($list as $item):?>
                    <tr class="data-tr" id="<?=$item->product->sku?>">
                        <td >
                            <?=$item->box->no?>
                            <input type="hidden" class="item_id" name="items[<?=$item->id?>][item_id]" value="<?=$item->id?>"/>
                            <input type="hidden" class="product_id" name="items[<?=$item->id?>][product_id]" value="<?=$item->product->id?>">
                        </td>
                        <td class="td_limit">
                            <div class="ch-name"><?=$item->product->name?></div>
                            <div class="en-name"><?=$item->product->ename?></div>
                        </td>
                        <td class="expectNum"><?=$item->qty?></td>
                        <td><input type="number" class="txt receive_qty" name="items[<?=$item->id?>][receive_qty]" min="0" step="1" value="<?=$item->receive_qty?>"></td>
                        <td align="center">
                            <input type="hidden" name="items[<?=$item->id?>][expectNum]" value="<?=$item->qty?>">
                             <?=$item->putCount_tmp?>
                        </td>
                        <td align="center" class="location">
                            <span class="rettwmsend_code"><?=$item->location->code?></span>
                            <input type="hidden" class="rettwmsend_id" value="<?=$item->location->id?>">
                        </td>
                        <td colspan="3">
                            <table id="<?=$item->id?>" width="100%">
                                <tr></tr>
                                <?php
                                $put = WhProductLocationMapping::find('ref_id=? and product_id=? and ref_type=?',$item->id,$item->product_id,Form::TYPE_RO_IN)->all();
                                foreach($put as $row):
                                    $location = WhLocation::find('id=?',$row['location_id'])->one();
                                ?>
                                    <tr>
                                    <td align="center" width="230">
                                        <input type="text" class="choose-location-code txt" value="<?=$location->code?>" readonly name="items[<?=$item->id?>][location_code][]" >
                                        <a href="<?=ViewBase::getUrl("wh/location/simpleview")?>"  data-onsuccess="chooseLocation" data-component="popup" data-popup-height="400" data-popup-width="800" ><?=t("选择货位")?></a>
                                    </td>
                                    <td width="40"><input type='number' class="txt real_qty" name="items[<?=$item->id?>][real_qty][]" value="<?=$row->qty?>"></td>
                                    <td width="80"><span class="small-btn small-delete-btn" rel="row-delete-btn" data-allow-empty="true"><?=t("移除")?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><span class="small-btn small-add-btn " rel="row-append-btn" data-tpl="row-template"><?=t("新增一行")?></span></td>
                                </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                <?php endforeach;?>
                <tfoot>
                <tr>
                    <td colspan="10" align="center">
                        <input type="submit" class="btn" value="<?=t("保存所有")?>" id="receiveSubmit">
	                    <?=ViewBase::getDialogCloseBtn()?>
                    </td>
                </tr>
                </tfoot>
            </table>
        </form>
	</section>

    <script type="text/template" id="row-template">
        <tr>
            <td align="center" width="230">
                <input type="text" class="choose-location-code txt" name=""  >
                <a href="<?=ViewBase::getUrl("wh/location/simpleview")?>"  data-onsuccess="chooseLocation" data-component="popup" data-popup-height="400" data-popup-width="800" ><?=t("选择货位")?></a>
            </td>
            <td width="40"><input type='number' class="txt real_qty" name=""></td>
            <td width="80"><span class="small-btn small-delete-btn" rel="row-delete-btn" data-allow-empty="true"><?=t("删除")?></span></td>
        </tr>
    </script>
    <script>
        <?php if($mode==false){?>
            $("#codeinput").hide();
            $("#autofill_btn").hide();
            $("a[data-component='popup']").hide();
        <?php } ?>

        seajs.use("jquery",function($){
            $("#code").focus();

            //快速填充
            $("#autofill_btn").click(function(){
                var quick_code=$("#quick_fill").val();
                $("#receive_list .data-tr").each(function(){
                    var qty=$(this).find(".expectNum").text();
                    $(this).find(".put").val(qty);
                    $(this).find('span[rel="row-delete-btn"]').click();
                    $(this).find('span[rel="row-append-btn"]').click();
                    $(this).find('.receive_qty').val(qty);
                   var inter = window.setInterval(function(){
                        if($('.choose-location-code').length){
                            $('.choose-location-code').each(function(i,k){
                                var qty = $(this).closest('.data-tr').find('.expectNum').text();

                                if(quick_code==""){
                                    var code = $(this).closest('.data-tr').find('.rettwmsend_code').text();
                                }else{
                                    var code = quick_code;
                                }
                                var id = $(this).closest('.data-tr').find('.rettwmsend_id').val();
                                $(this).val(code);
                                $(this).closest('tr').find('.choose-location-id').val(id);
                                $(this).closest('tr').find('.real_qty').val(qty);
                                window.setName($(this));
                            });
                            window.clearInterval(inter);
                        }
                    },100);

                    //隐藏单条的和sku条码
                    $("#codeinput").hide();
                    $(".saveReceive").hide();
                });
            });

            //表单保存
            $("#receiveSubmit").click(function(){
                if(confirm("<?=t("是否确定保存")?>")){
                    var isfillfalse=false;
                    $("#receive_list .data-tr").each(function(){
                        //值检查未隐藏的
                        if(! $(this).is(":hidden")){
                            var putNum=$(this).find(".put").val();
                            var expectNum=$(this).find(".expectNum").text();
                            var reg=/^\-?\d+$/;
                            if(!reg.test(expectNum) || !reg.test(expectNum || putNum>expectNum)){
                                alert("SKU:"+($(this).attr("id"))+"  <?=t("数量错误")?>");
                                isfillfalse=true;
                                return false;
                            }
                        }
                    });
                    if(isfillfalse){return false;}
                }else{
                    return false;
                }
            });

            $('.choose-location-code').live('focus',function(){
                window.setName($(this));
            });

            //选择货位
            window.chooseLocation=function(data){
                window.setName($(this).parent().find(".choose-location-code"));
                $(this).parent().find(".choose-location-code").val(data.code);
            };

            window.setName=function(obj){
            	debugger
                var item_id =
                var code = "items["+item_id+"][location_code][]";
                obj.closest('tr').find('.real_qty').attr('name','items['+item_id+'][real_qty][]');
                obj.closest('tr').find(".choose-location-code").attr('name',code);
            };
        });
    </script>
	
	<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>