<div class="modal-header">
	<button aria-hidden="true" data-dismiss="modal" class="close" type="button">&times;</button>
    <h4 class="modal-title"><?php echo $text_title ?></h4>
</div>

<div id="ct_form" class="tab-content">
	<?php echo $form['form_open']; ?>
	<div class="panel-body panel-body-nopadding">
		<?php
		foreach ($form['fields'] as $name => $field) {
			//Logic to calculate fileds width
			$widthclasses = "col-sm-6";
			if (is_int(stripos($field->style, 'large-field'))) {
				$widthclasses = "col-sm-7";
			} else if (is_int(stripos($field->style, 'medium-field')) || is_int(stripos($field->style, 'date'))) {
				$widthclasses = "col-sm-5";
			} else if (is_int(stripos($field->style, 'small-field')) || is_int(stripos($field->style, 'btn_switch'))) {
				$widthclasses = "col-sm-3";
			} else if (is_int(stripos($field->style, 'tiny-field'))) {
				$widthclasses = "col-sm-2";
			}
			$widthclasses .= " col-xs-12"; ?>
            <div class="form-group <?php if (!empty($error[$name])) {
                echo "has-error";
            } ?>" <?php echo($name == 'other_type' ? 'style="display: none;"' : '') ?>>
			<label class="control-label col-sm-4 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>
			<div class="input-group afield <?php echo $widthclasses; ?>"><?php echo $field; ?></div>
			<?php if (is_array($error[$name]) && !empty($error[$name])) { ?>
				<span class="help-block field_err"><?php echo $error[$name]; ?></span>
			<?php } else if (!empty($error[$name])) { ?>
				<span class="help-block field_err"><?php echo $error[$name]; ?></span>
			<?php } ?>
		</div>
	<?php } ?>
	</div>
<?php if(!$customer_transaction_id){?>
	<div class="panel-footer">
		<div class="row">
			<div class="col-sm-6 col-sm-offset-3 center">
				<button class="btn btn-primary on_save_close lock-on-click">
					<i class="fa fa-save"></i> <?php echo $button_save; ?>
				</button>
				&nbsp;
				<a class="btn btn-default" data-dismiss="modal" href="<?php echo $cancel; ?>">
                    <i class="fa fa-sync"></i> <?php echo $form['cancel']->text; ?>
				</a>

			</div>
		</div>
	</div>
<?php } ?>
	</form>
</div>

<script type="text/javascript">
	var submitSent = false;

	$('#transaction_form_transaction_type0').on('change',function(){
		if($(this).val()==''){
			$('#transaction_form_transaction_type1').parents('div.form-group').fadeIn();
			$('#transaction_form_transaction_type1').focus();
		}else{
			$('#transaction_form_transaction_type1').parents('div.form-group').fadeOut();
		}
	});

	$(document).ready(function(){
		$('#transaction_form_transaction_type0').change();
	});

	$('#tFrm').submit(function () {
		if(submitSent === true) {
			return false;
		}

		submitSent = true;

		$.ajax({
			url: '<?php echo $form['form_open']->action; ?>',
			type: 'POST',
			data: $('#tFrm').serializeArray(),
			dataType: 'json',
			beforeSend:
				function() {
					$('.alert').remove();
				},
			success: function (data) {
				if (data.result === true) {
                    <?php if(!$customer_transaction_id){?>
						if ($('#transaction_modal')) {
							$('#transaction_modal').modal('hide');
                        }
                    <?php } ?>
					location.reload();
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				submitSent = false;
			}
		});
		return false;
	});

</script>

