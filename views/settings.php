<div class="wrap">
	<h2><?php echo __('WEN Image Cleaner', 'wen-image-cleaner'); ?></h2>

	<?php $req_page = (isset($_GET['req']) && $_GET['req'] == 'cleaner' ? $_GET['req'] : 'settings'); ?>

	<div class="wp-filter">
		<ul class="filter-links">
			<li><a href="?page=wen-image-cleaner&req=settings" <?php echo ($req_page == 'settings' ? 'class="current"' : ''); ?>><?php echo __('Configure Settings', 'wen-image-cleaner'); ?></a></li>
			<li><a href="?page=wen-image-cleaner&req=cleaner" <?php echo ($req_page == 'cleaner' ? 'class="current"' : ''); ?>><?php echo __('Run Cleaner', 'wen-image-cleaner'); ?></a></li>
		</ul>
	</div>

	<?php if ( $req_page == 'cleaner' ) { ?>

	<?php $selectedMonth = (isset($_GET['dm']) ? $_GET['dm'] : date('m')); ?>
	<?php $selectedYear = (isset($_GET['dy']) ? $_GET['dy'] : date('Y')); ?>
	<p><?php echo __('Use this tool to remove leftover images. To begin, just press the button below.', 'wen-image-cleaner'); ?></p>

	<!--<p><strong><?php //echo __('Unused Attachments:', 'wen-image-cleaner'); ?></strong> <span id="unwanted-attachments-count">0</span></p>-->
	<p><strong><?php echo __('Leftover Files:', 'wen-image-cleaner'); ?></strong> <span id="missing-files-count">0</span></p><br/>

	<!--<div>
		<label>
			<?php //echo __('Select Year:', 'wen-image-cleaner'); ?> 
			<select name="dy" id="wen-ic-year-dropdown" class="wen-ic-dropdown"></select>
		</label>
		<label>
			<?php //echo __('Select Month:', 'wen-image-cleaner'); ?> 
			<select name="dm" id="wen-ic-month-dropdown" class="wen-ic-dropdown"></select>
		</label>
		<br/><br/>
	</div>

	<p><em><?php //echo __('Note: WEN Cleaner will not be able to detect the attachments used with sliders or stored on post metadata', 'wen-image-cleaner'); ?></em></p>-->

	<button class="button button-primary wen-ic-button" id="wen-remove-missings"><?php echo __('Remove Leftovers', 'wen-image-cleaner'); ?></button> 
	<!--<button class="button wen-ic-button" id="wen-remove-unused-attachments"><?php echo __('Remove Unused Attachments', 'wen-image-cleaner'); ?></button>
	<button class="button wen-ic-button" id="wen-remove-both"><?php echo __('Remove Both', 'wen-image-cleaner'); ?></button>-->
	<button class="button wen-ic-button" id="wen-refresh-info"><?php echo __('Refresh Informations', 'wen-image-cleaner'); ?></button>

	<div id="wen-ic-progress"></div>
	<div id="wen-ic-progress-status"></div>

	<script>
	var mediaMonth = <?php echo $selectedMonth; ?>;
	var mediaYear = <?php echo $selectedYear; ?>;
	</script>

	<?php } else { ?>

	<?php settings_errors(); ?>
	<form method="post" action="<?php echo admin_url('options.php'); ?>">
		<?php settings_fields( 'wen-image-cleaner' ); ?>
        <?php do_settings_sections( 'wen-image-cleaner' ); ?>  
		<?php submit_button(); ?>
	</form>

	<?php } ?>
</div>