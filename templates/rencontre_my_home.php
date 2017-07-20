<?php
/*
 * Plugin : Rencontre
 * Template : My Home
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_my_home.php
 * $u : user_id
 * $u0 : ID
*/
?>

	<div class="rencBox">
		<h3><?php _e('Featured profiles','rencontre');?></h3>
		<?php foreach($uFeatProf as $u) {
			RencontreWidget::f_miniPortrait($u->user_id,((!empty($rencOpt['onlyphoto']) && !$mephoto)?1:0));
		} ?>

		<div class="clear"></div>
	</div><!-- .rencBox -->
	
	<?php if(!empty($rencOpt['anniv']) && !isset($rencCustom['born']) && count($uBirthday)) { ?>
	
	<div class="rencBox">
		<h3><?php _e('Today\'s birthday','rencontre');?></h3>
		<?php foreach($uBirthday as $u) {
			RencontreWidget::f_miniPortrait($u->user_id,((!empty($rencOpt['onlyphoto']) && !$mephoto)?1:0));
		} ?>
				
		<div class="clear"></div>
	</div><!-- .rencBox -->
	<?php } ?>

	<?php $ho = false; if(has_filter('rencAddBox', 'f_rencAddBox')) $ho = apply_filters('rencAddBox', $u0->ID); if($ho) echo $ho; ?>

	<?php if(!empty($rencOpt['ligne']) && count($uLine)) { ?>

	<div class="rencBox">
		<h3>
		
		<?php if(!empty($rencOpt['home'])) { ?>
			<a href="javascript:void(0)" onClick="document.forms['rencLine'].submit();"><?php _e('Online now','rencontre'); ?></a>
		<?php } else { ?>
			<?php _e('Online now','rencontre'); ?>
		<?php } ?>
		</h3>
			<?php foreach($uLine as $u) {
				RencontreWidget::f_miniPortrait($u->user_id,((!empty($rencOpt['onlyphoto']) && !$mephoto)?1:0));
			} ?>
				
		<div class="clear"></div>
	</div><!-- .rencBox -->
	<?php } ?>

	<div class="rencBox">
		<h3><?php _e('New entrants','rencontre'); ?></h3>
		<?php foreach($uNew as $u) {
			RencontreWidget::f_miniPortrait($u->user_id,((!empty($rencOpt['onlyphoto']) && !$mephoto)?1:0));
		} ?>
				
		<div class="clear"></div>
	</div><!-- .rencBox -->
