<?php include($tpl_common_dir . 'action_confirm.tpl'); ?>

<div id="content" class="panel panel-default">
	<div class="panel-heading col-xs-12">
		<div class="primary_content_actions pull-left">
			<div class="btn-group mr10 toolbar">
				<a class="btn btn-primary actionitem tooltips"
				   title="<?php echo $delete_button->title; ?>"
				   href="<?php echo $delete_button->href; ?>"
				   data-confirmation="delete"
                ><i class="fa fa-trash fa-fw"></i> <?php echo $delete_button->title; ?></a>
			</div>
		</div>

		<?php include($tpl_common_dir . 'content_buttons.tpl'); ?>

	</div>
	<div class="panel-body panel-body-nopadding tab-content col-xs-12">
		<?php echo $listing_grid; ?>
	</div>
</div>