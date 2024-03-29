<div class="panel panel-default">
	<div class="panel-heading">
		<div class="panel-btns">
			<a class="panel-close" href="">×</a>
			<a class="minimize" href="">−</a>
		</div>
		<h4 class="panel-title"><?php echo $text_product_summary; ?></h4>
	</div>
	<div class="panel-body panel-body-nopadding table-responsive" style="display: block;">

        <div class="row">
            <div class="media col-3 col-sm-4">
                <div class="media-left">
                    <a title="<?php abc_echo_html2view($text_view); ?>" href="<?php echo $product['preview']; ?>"
                       target="_blank">
                        <?php echo $product['image']['thumb_html']; ?>
                    </a>
                </div>
                <div class="media-body">
                    <a title="<?php abc_echo_html2view($text_view); ?>"
                       href="<?php echo $product['preview']; ?>"
                       class="btn btn-small btn-default mt10 summary-button"
                       target="_blank">
                        <i class="fa fa-eye"></i> <?php echo $text_view; ?></a>
                    <?php if ($auditLog) { ?>
                        <br><a data-toggle="modal"
                               class="btn btn-white tooltips summary-button mt10"
                               data-target="#viewport_modal"
                               href="<?php echo $auditLog->vhref; ?>"
                               data-fullmode-href="<?php echo $auditLog->href; ?>"
                               rel="audit_log"
                               title="<?php echo $auditLog->text; ?>">
                            <i class="fa fa-history "></i> <?php echo $auditLog->text; ?></a>
                        <?php
                    }
                    echo $this->getHookVar('product_summary_buttons_hookvar');
                    ?>
                </div>
            </div>
            <div class="col-8">
                <table id="summary" class="table summary">
                    <tr>
                        <td class="summary_label"><?php echo $entry_name; ?></td>
                        <td><?php echo $product['description']['name']; ?></td>
                        <td class="summary_label"><?php echo $entry_product_id; ?></td>
                        <td class="summary_value">#<?php echo $product['product_id']; ?></td>
                    </tr>
                    <tr>
                        <td class="summary_label"><?php echo $entry_model; ?></td>
                        <td class="summary_value"><?php echo $product['model']; ?></td>
                        <td class="summary_label"><?php echo $entry_price; ?></td>
                        <td class="summary_value"><?php echo $product['format_price']; ?></td>
                    </tr>
                    <tr>
                        <td class="summary_label"><?php echo $text_product_condition; ?></td>
                        <td class="summary_value"><?php
                            if ($product['condition']) {
                                echo '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle fa-2x"></i> <b>' . implode('</p><p>', $product['condition']) . '</b></div>';
                            } else {
                                echo $text_product_available;
                            } ?></td>
                        <td class="summary_label"><?php echo $text_total_orders; ?></td>
                        <td class="summary_value"> <?php echo $product['orders']; ?>
                            <?php if ($product['orders'] > 0) { ?>
                                &nbsp;&nbsp;<a href="<?php echo $product['orders_url']; ?>"
                                               class="btn btn-small btn-default"
                                               target="_new"><i
                                            class="fa fa-external-link-alt"></i> <?php echo $text_view; ?></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php echo $this->getHookVar('product_summary_hookvar'); ?>
                </table>
            </div>
        </div>
	</div>
</div>

<?php
	//load quick view port modal
	echo $this->html->buildElement(
array(
'type' => 'modal',
'id' => 'viewport_modal',
'modal_type' => 'lg',
'data_source' =>'ajax',
'js_onload' => "
var url = $(this).data('bs.modal').options.fullmodeHref;
$('#viewport_modal .modal-header a.btn').attr('href',url);
",
'js_onclose' => "$('#".$data['table_id']."').trigger('reloadGrid',[{current:true}]);"
)
);
?>
