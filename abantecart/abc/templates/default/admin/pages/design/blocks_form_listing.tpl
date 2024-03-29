<?php include($tpl_common_dir . 'action_confirm.tpl');
echo $tabs;
?>

<div id="content" class="panel panel-default">

	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
		</div>
		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<label class="h4 heading"><?php echo $form_title; ?></label>
			<?php foreach ($form['fields'] as $name => $field) {
				//Logic to calculate fields width
				$widthcasses = "col-sm-7";
				if ( is_int(stripos($field->style, 'large-field')) ) {
					$widthcasses = "col-sm-7";
				} else if ( is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date')) ) {
					$widthcasses = "col-sm-5";
				} else if ( is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch')) ) {
					$widthcasses = "col-sm-3";
				} else if ( is_int(stripos($field->style, 'tiny-field')) ) {
					$widthcasses = "col-sm-2";
				}
				$widthcasses .= " col-xs-12";
			?>
        <div class="form-group <?php if (!empty($error[$name])) {
            echo "has-error";
        } ?>">
            <label class="control-label col-sm-3 col-xs-12"
                   for="<?php echo $field->element_id; ?>"><?php echo $form['text'][$name]; ?></label>
			<div class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'description' ? 'ml_ckeditor' : '')?>">
				<?php echo $field; ?>
			</div>
		    <?php if (!empty($error[$name])) { ?>
		    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
		    <?php } ?>
		</div>
			<?php }  ?><!-- <div class="fieldset"> -->
		<div><?php echo $entry_list_type; ?></div>
		<div class="ml_field"><?php echo $list_type; ?></div>

		<div id="subformcontent"></div>
	</div>

	<div class="panel-footer col-xs-12">
		<div class="text-center">
			<button class="btn btn-primary lock-on-click">
			<i class="fa fa-save fa-fw"></i> <?php echo $form['submit']->text; ?>
			</button>
			<button class="btn btn-default" type="reset">
                <i class="fa fa-sync fa-fw"></i> <?php echo $button_reset; ?>
			</button>
			<a class="btn btn-default" href="<?php echo $cancel; ?>">
			<i class="fa fa-arrow-left fa-fw"></i> <?php echo $form['cancel']->text; ?>
			</a>
		</div>
	</div>
	</form>

</div><!-- <div class="tab-content"> -->

<script type="text/javascript">
    function load_subform(postdata) {
        $('#subformcontent').html('');
        if (postdata['listing_source'] == '') {
            return null;
        }
        $.ajax({
            url:'<?php echo $subform_url; ?>',
            type:'POST',
            dataType:'html',
            data:jQuery.param(postdata, true),
            success:function (data) {
                if (data) {
                    $('#subformcontent').append(data);
                    $('#subformwrapper').fadeIn('slow');
                    $(data).find('input, select, textarea').each(function () {
                        var field = $(this).attr('id');
                        $('#' + field).aform({ showButtons:false });
                    });
                }
            }
        });
    }

    $('#BlockFrm_listing_datasource').change(function () {
        load_subform({'listing_datasource':$(this).val()});
        $('#BlockFrm_popup_buffer').html('{}');
        $('#BlockFrm_popup_selected').html('{}');
        $('#BlockFrm_popup_item_count').html(0);

    });
    <?php echo $autoload;?>
</script>
