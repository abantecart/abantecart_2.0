<h1 class="heading1">
  <span class="maintext"><i class="fa fa-search"></i> <?php echo $heading_title; ?></span>
  <span class="subtext"></span>
</h1>

<div class="contentpanel">

	<h4 class="heading4"><?php echo $text_criteria; ?></h4>
	<div class="form-inline">
			<div class="form-group">
				<div class="input-group">
				    <?php echo $keyword; ?>&nbsp;
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
				    <?php echo $category; ?>&nbsp;
				</div>
			</div>
			<div class="form-group">
				    <?php echo $description; ?>&nbsp;
			</div>
			<div class="form-group">
				    <?php echo $model; ?>&nbsp;
			</div>
			<div class="form-group">
				<div class="input-group">
				    <?php echo $submit; ?>
				</div>
			</div>
	</div>

	<h4 class="heading4"><?php echo $text_search; ?></h4>
	<?php if ($products) { ?>
	<div class="sorting well">
	  <form class=" form-inline pull-left">
	    <?php echo $text_sort; ?>&nbsp;&nbsp;<?php echo $sorting; ?>
	  </form>
	  <div class="btn-group pull-right">
	    <button class="btn" id="list"><i class="fa fa-th-list"></i>
	    </button>
	    <button class="btn btn-orange" id="grid"><i class="fa fa-th"></i></button>
	  </div>
	</div>

	<?php include( $this->templateResource('pages/product/product_listing.tpl', 'file') ) ?>

	<div class="sorting well">
		<?php echo $pagination_bootstrap; ?>
		<div class="btn-group pull-right">
		</div>
	</div>

<?php } else { ?>
		<div>
			<?php echo $text_empty; ?>
		</div>
<?php } ?>

</div>

<script type="text/javascript">
$('#keyword').keydown(function (e) {
	if (e.keyCode == 13) {
		contentSearch();
	}
});
$('#search_button').click(function (e) {
	contentSearch();
});

$('#sort').change(function () {
	contentSearch();
});

function contentSearch() {
	url = '<?php echo $this->html->getURL('product/search','&limit='.$limit); ?>';

    var keyword = $('#keyword').val();

	if (keyword) {
		url += '&keyword=' + encodeURIComponent(keyword);
	}

    var category_id = $('#category_id').val();

	if (category_id) {
		url += '&category_id=' + encodeURIComponent(category_id);
	}

	if ($('#description').is(':checked')) {
		url += '&description=1';
	}

	if ($('#model').is(':checked')) {
		url += '&model=1';
	}
	if($('#sort').val()) {
		url += '&sort=' + $('#sort').val();
	}

	location = url;
}
</script>
