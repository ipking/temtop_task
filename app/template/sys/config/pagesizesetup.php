<?php
namespace ttwms;

use function Temtop\t;

/** @var array $papers */
$current_tag = isset($current_tag) ? $current_tag : '';

/** @var ViewBase $this */
/** @var array $get */
ViewBase::setPagePath(array('纸张大小设置'));
include ViewBase::resolveTemplate('inc/header.inc.php');
?>
<style>
    select {
        width: 250px;
        max-width: inherit;
    }

    .table-wrap {
        min-height: 75px;
    }

    .data-tbl {
        margin-top: 5px;
    }

    .data-tbl tbody th {
        width: 180px;
        white-space: nowrap;
        font-weight: bold;
    }

    .printer-name {
        color: green;
    }

    .printer-name {
        color: gray;
    }

    .global-set {
        border: 1px solid #eee;
        padding: 10px;
        background-color: #efefef;
        overflow: hidden;
    }

    #cancel-btn {
        display: none;
    }

    .page-iframe #cancel-btn {
        display: inline-block;
    }

    .setup-guide {
        float: right;
    }

    .setup-guide li {
        float: left;
        padding: 0 0.5em;
    }

    .setup-guide li:after {
        content: "|";
        color: #ccc;
        display: inline-block;
        margin-left: 10px;
    }

    .setup-guide li:last-child:after {
        display: none;
    }
</style>
<ul class="tab">
	<li>
		<a href="<?=ViewBase::getUrl('sys/config/printersetup',['ref'=>$get['ref']]);?>"><?= t('打印机设置') ?></a>
	</li>
	<li class="active">
		<a href="<?=ViewBase::getUrl('sys/config/pagesizesetup',['ref'=>$get['ref']]);?>"><?= t('纸张大小设置') ?></a>
	</li>
</ul>
<section class="container">

<div class="table-wrap">
	<table class="data-tbl">
		<tbody>
		<tr>
			<th></th>
			<td><?=t('宽')?> * <?=t('高')?>(mm)</td>
		</tr>
		<?php foreach ($papers as $key=>$paper):?>
			<tr class="print-size" data-key="<?=$key?>">
				<th>
					<?=$paper?><br/>
				</th>
				<td>
					<input type="number" class="txt width" value="" min="0" step="1"/> *
					<input type="number" class="txt height" value="" min="0" step="1"/>
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>
</div>
	<div class="operate-row">
		<input type="button" class="btn" id="save-size-btn" value="<?=t("保存设置")?>">
		<input type="button" class="btn btn-weak" id="cancel-btn" value="<?= t('关闭'); ?>">
	</div>
</section>
<script>
    //打印
    seajs.use(["jquery", "temtop/printer", "ywj/popup", "ywj/msg"], function ($, Printer, Popup, Msg) {
        var CUR_TAG = '<?=$current_tag;?>';
        Msg.showLoading('正在加载打印机列表', 10);
        Printer.init(function (lodop) {
            Msg.hide();
            var current_popup = Popup.getCurrentPopup ? Popup.getCurrentPopup() : null;
            var $global_sel = $('#g-printer-sel');
            var $item_list = $('.item-print-set');
	        var $print_size = $('.print-size');

            //init printer list
            var printer_list = [];
            var printer_count = lodop.GET_PRINTER_COUNT();
            for (var i = 0; i < printer_count; i++) {
                printer_list[i] = lodop.GET_PRINTER_NAME(i);
            }
            $('select').each(function () {
                for (var i = 0; i < printer_list.length; i++) {
                    $('<option value="' + i + '">' + printer_list[i] + '</option>').appendTo($(this));
                }
                $(this).attr('disabled', false);
            });

            $item_list.each(function () {
                var paper_key = $(this).data('key');
                var printer_index = $.cookie(paper_key);
                if (printer_index !== '' && printer_index !== null) {
                    $(this).val(printer_index);
                }
            });
	        $print_size.each(function(){
		        var paper_key = $(this).data('key');
		        var $tr = $(this).closest('tr');
		        var printer_index = $.cookie(paper_key);
		        if(printer_index != '' && printer_index != null && printer_index!='undefined'){
			        var arr = printer_index.split("*");
			        $tr.find('.width').val(arr[0]);
			        $tr.find('.height').val(arr[1]);
		        }
	        });
            $global_sel.on('change', function () {
                if (this.value >= -2) {
                    $item_list.val(this.value);
                }
            });

	        $('#save-size-btn').attr('disabled',false).click(function(e){
		        var errorMessage = '';
		        $.each($('input'), function(){
			        if ( !this.checkValidity() ) {
				        this.focus();
				        errorMessage = this.validationMessage;
				        return false;
			        }
		        });
		        if (errorMessage.length > 0) {
			        Msg.showError(errorMessage);
			        return;
		        }
		        $print_size.each(function () {
			        var paper_key = $(this).data('key');
			        var $tr = $(this).closest('tr');
			        var width = $tr.find('.width').val();
			        var height = $tr.find('.height').val();
			        var val = width+'*'+height;
			        $.cookie(paper_key, val, {expires: 365, path: '/'});
		        });
		        Msg.showSuccess("<?=t("设置成功!")?>");

	        });

            $('#cancel-btn').click(function () {
                if (current_popup) {
                    Popup.closeCurrentPopup();
                }
            })
        }, function () {
            Printer.showInstall();
        });
    });
</script>
<?php include ViewBase::resolveTemplate('inc/footer.inc.php'); ?>
