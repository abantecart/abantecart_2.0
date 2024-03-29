<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" xml:lang="<?php echo $lang; ?>" <?php echo $this->getHookVar('hk_html_attribute'); ?>>
<head><?php	echo $head; ?></head>
<body class="<?php echo $page_css_class; ?>">
<div class="container-fixed" style="max-width: <?php echo $layout_width; ?>">

<?php if($maintenance_warning){ ?>
	<div class="alert alert-warning">
	 	<button type="button" class="close" data-dismiss="alert">&times;</button>
 		<strong><?php echo $maintenance_warning;?></strong>
 	</div>
<?php
}
echo ${$header}; ?>

<?php if ( !empty( ${$header_bottom} ) ) { ?>
<!-- header_bottom blocks placeholder -->
	<?php echo ${$header_bottom}; ?>
<!-- header_bottom blocks placeholder -->
<?php } ?>

<div id="maincontainer">

<?php
	//check layout dynamicaly
	$present_columns = 1;
	$center_padding = '';
	if ( !empty(${$column_left}) ) {
		$present_columns++;
		$center_padding .= 'ct_padding_left';
	}
	if ( !empty(${$column_right}) ) {
		$present_columns++;
		$center_padding .= ' ct_padding_right';
	}
?>

	<div class="container-fluid">
		<?php if ( !empty(${$column_left} ) ) { ?>
		<div class="column_left col-md-3 col-xs-12">
		<?php echo ${$column_left}; ?>
		</div>
		<?php } ?>

		<?php $span = 12 - 3 * ($present_columns -1); ?>
		<div class="col-md-<?php echo $span ?> col-xs-12 mt20">
		<?php if ( !empty( ${$content_top} ) ) { ?>
		<!-- content top blocks placeholder -->
		<?php echo ${$content_top}; ?>
		<!-- content top blocks placeholder (EOF) -->
		<?php } ?>

		<div class="<?php echo $center_padding; ?>">
		<?php echo $content; ?>
		</div>

		<?php if ( !empty( ${$content_bottom} ) ) { ?>
		<!-- content bottom blocks placeholder -->
		<?php echo ${$content_bottom}; ?>
		<!-- content bottom blocks placeholder (EOF) -->
		<?php } ?>
		</div>

		<?php if ( !empty(${$column_right} ) ) { ?>
		<div class="column_right col-md-3 col-xs-12 mt20">
		<?php echo ${$column_right}; ?>
		</div>
		<?php } ?>
	</div>

</div>

<?php if ( !empty( ${$footer_top} ) ) { ?>
<!-- footer top blocks placeholder -->
	<div class="container-fluid">
		<div class="col-md-12">
	    <?php echo ${$footer_top}; ?>
	  	</div>
	</div>
<!-- footer top blocks placeholder -->
<?php } ?>

<!-- footer blocks placeholder -->
<div id="footer">
	<?php echo ${$footer}; ?>
</div>

</div>

<!--
AbanteCart is open source software and you are free to remove the Powered By AbanteCart if you want, but its generally accepted practise to make a small donation.
Please donate http://www.abantecart.com/donate
//-->

<?php
/*
	Placed at the end of the document so the pages load faster

	For better rendering minify all JavaScripts and merge all JavaScript files in to one singe file
	Example: <script type="text/javascript" src=".../javascript/footer.all.min.js" defer async></script>

Check Dan Riti's blog for more fine tunning suggestion:
https://www.appneta.com/blog/bootstrap-pagespeed/
		*/
?>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/bootstrap.min.js'); ?>" defer></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/common.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/respond.min.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery.flexslider.min.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/easyzoom.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery.validate.min.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery.carouFredSel.min.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery.mousewheel.min.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery.touchSwipe.min.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery.ba-throttle-debounce.min.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/jquery.onebyone.min.js'); ?>" defer async></script>
<script type="text/javascript" src="<?php echo $this->templateResource('assets/js/custom.js'); ?>" defer async></script>
<?php
if ($scripts_bottom && is_array($scripts_bottom)) {
    foreach ($scripts_bottom as $script) {
        ?>
        <script type="text/javascript" src="<?php echo $script; ?>" defer></script>
        <?php
    }
} ?>

<?php if (trim($this->config->get('config_google_tag_manager_id'))) {
    //get ecommerce tracking data from checkout page
    /**
     * @see ControllerPagesCheckoutSuccess::_google_analytics()
     */
    $registry = \abc\core\engine\Registry::getInstance();
    $ga_data = $registry->get('google_analytics_data');
    if ($ga_data) { ?>
        <script>
            dataLayer.push({ecommerce: null});
            dataLayer.push({
                event: "purchase",
                ecommerce: {
                    transaction_id: <?php abc_js_echo($ga_data['transaction_id']);?>,
                    affiliation: <?php abc_js_echo($ga_data['store_name']);?>,
                    value: <?php abc_js_echo($ga_data['total']); ?>,
                    tax: <?php abc_js_echo($ga_data['tax']); ?>,
                    shipping: <?php abc_js_echo($ga_data['shipping']); ?>,
                    currency: <?php abc_js_echo($ga_data['currency_code']); ?>,
                    coupon: <?php abc_js_echo($ga_data['coupon']); ?>,
                    city: <?php abc_js_echo($ga_data['city']); ?>,
                    state: <?php abc_js_echo($ga_data['state']);?>,
                    country: <?php abc_js_echo($ga_data['country']);?>
                    <?php if ($ga_data['items']) { ?>
                    , items: <?php abc_js_echo($ga_data['items']); ?>
                    <?php } ?>
                }
            });
        </script>
    <?php }
} ?>

</body>
</html>