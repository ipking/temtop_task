<?php
namespace ttwms;

use function Temtop\t;

/** @var array $papers */
$current_tag = isset($current_tag) ? $current_tag : '';

/** @var ViewBase $this */
/** @var array $get */
ViewBase::setPagePath(array('打印机设置'));
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
	<li class="active">
		<a href="<?=ViewBase::getUrl('sys/config/printersetup',['ref'=>$get['ref']]);?>"><?= t('打印机设置') ?></a>
	</li>
	<li>
		<a href="<?=ViewBase::getUrl('sys/config/pagesizesetup',['ref'=>$get['ref']]);?>"><?= t('纸张大小设置') ?></a>
	</li>
</ul>
<section class="container">
<div class="global-set" style="<?= $current_tag ? 'display:none' : ''; ?>">
	<?php if (count($papers) !== 1): ?>
        <label for="g-printer-sel"><?= t("打印机统一指定为") ?></label>
        <select id="g-printer-sel" class="print_set" disabled="disabled">
            <option value="-2"><?= t('请选择打印机'); ?></option>
            <option value="-1" style="color:gray; background-color:#eee;"><?= t("不设置") ?></option>
        </select>
	<?php endif; ?>
    <ul class="setup-guide">
        <li><a href="http://help.whalepie.com/article/510.html" target="_blank">打印机设置帮助</a></li>
    </ul>
</div>
<div class="table-wrap">
    <table class="data-tbl">
        <tbody>
		<?php foreach ($papers as $key => $paper): ?>
            <tr>
                <th>
                    <div class="printer-name"><?= t($paper) ?></div>
                </th>
                <td>
                    <select class="item-print-set" data-key="<?= $key ?>" disabled="disabled">
                        <option value="-1" style="color:gray; background-color:#eee;"><?= t("不设置") ?></option>
                    </select>
                </td>
            </tr>
		<?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="operate-row">
    <input type="button" class="btn" id="save-btn" value="<?= t("保存设置") ?>" disabled="disabled">
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

            $global_sel.on('change', function () {
                if (this.value >= -2) {
                    $item_list.val(this.value);
                }
            });

            $('#save-btn').attr('disabled', false).click(function () {
                $item_list.each(function () {
                    var paper_key = $(this).data('key');
                    var val = parseInt($(this).val(), 10);
                    if (val >= 0) {
                        $.cookie(paper_key, val, {expires: 365, path: '/'});
                    } else if (val == -1) {
                        $.removeCookie(paper_key, {path: '/'});
                    }
                });

                if (CUR_TAG && $item_list.val() < 0) {
                    Msg.showInfo('<?=t('请选择打印机');?>');
                    return;
                }

                Msg.showSuccess("<?=t("设置成功!")?>");
                if (current_popup) {
                    Popup.getCurrentPopup().fire('onSuccess', $item_list.val());
                    Popup.closeCurrentPopup();
                } else {
                    deleteCurrentNav();
                }
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
