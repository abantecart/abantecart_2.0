<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<?php if ($tabs) {
    echo $tabs;
} ?>

<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
			<?php if (!empty ($list_url)) { ?>
			<div class="btn-group">
				<a class="btn btn-white tooltips" href="<?php echo $list_url; ?>" data-toggle="tooltip" data-original-title="<?php echo $text_back_to_list; ?>">
					<i class="fa fa-arrow-left fa-lg"></i>
				</a>
			</div>
			<?php } ?>

			<div class="actionitem btn-group mr10 toolbar">
                <?php if($insert){ ?>
                    <a class="btn btn-primary lock-on-click tooltips" href="<?php echo $insert; ?>"
                       title="<?php echo $button_add; ?>">
                        <i class="fa fa-plus"></i>
                    </a>
                <?php }
                if ($auditLog) { ?>
                    <a data-toggle="modal"
                       class="btn btn-white tooltips"
                       data-target="#viewport_modal"
                       href="<?php echo $auditLog->vhref; ?>"
                       data-fullmode-href="<?php echo $auditLog->href; ?>"
                       rel="audit_log"
                       title="<?php echo $auditLog->text; ?>">
                        <i class="fa fa-history "></i></a>
                    <?php
				}
				?>
			</div>
		</div>

		<div class="primary_content_actions pull-left">
		</div>
		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>
	</div>

	<?php echo $form['form_open']; ?>
    <div class="panel-body panel-body-nopadding tab-content col-xs-12">

	<div class="col-md-9 mb10">
		<?php foreach ($form['fields'] as $section => $fields) { ?>
		<label class="h4 heading" id="<?php echo $section;?>"><?php echo ${'tab_' . $section}; ?></label>
			<?php foreach ($fields as $name => $field) { ?>
			<?php
				//Logic to calculate fields width
				$widthcasses = "col-sm-9";
				if ( is_int(stripos($field->style, 'large-field')) ) {
					$widthcasses = "col-sm-9";
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
			<label class="control-label col-sm-3 col-xs-12" for="<?php echo $field->element_id; ?>"><?php echo ${'entry_' . $name}; ?></label>
			<div class="input-group afield <?php echo $widthcasses; ?> <?php echo ($name == 'description' ? 'ml_ckeditor' : '')?>">
				<?php if($name == 'keyword') { ?>
                    <span class="input-group-btn">
					<?php echo $keyword_button; ?>
				</span>
				<?php } ?>
				<?php echo $field; ?>
			</div>
		    <?php if (!empty($error[$name])) { ?>
		    <span class="help-block field_err"><?php echo $error[$name]; ?></span>
		    <?php } ?>
		</div>
			<?php }  ?><!-- <div class="fieldset"> -->
		<?php }  ?>
	</div>

	<div class="col-md-3 mb10">
		<div id="image">
		   <?php if ( !empty($update) ) {
			echo $resources_html;
			echo $resources_scripts;
			} ?>
		</div>
	</div>
	</div>

	<div class="panel-footer col-xs-12">
		<div class="text-center">
            <?php if($form['submit']){ ?>
			<button class="btn btn-primary lock-on-click">
			<i class="fa fa-save fa-fw"></i> <?php echo $form['submit']->text; ?>
			</button>
			<button class="btn btn-default" type="reset">
                <i class="fa fa-sync fa-fw"></i> <?php echo $button_reset; ?>
			</button>
			<a class="btn btn-default" href="<?php echo $cancel; ?>">
			<i class="fa fa-arrow-left fa-fw"></i> <?php echo $form['cancel']->text; ?>
			</a>
            <?php } ?>
		</div>
	</div>
	</form>

</div>

<script type="text/javascript">
	$('#editFrm_generate_seo_keyword').click(function(){
		var seo_name = $('#editFrm_name').val().replace('%','');
		$.get('<?php echo $generate_seo_url;?>&seo_name='+seo_name, function(data){
			$('#editFrm_keyword').val(data).change();
		});
	});
</script>

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