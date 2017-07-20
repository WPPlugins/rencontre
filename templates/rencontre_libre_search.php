<?php
/*
 * Plugin : Rencontre
 * Template : Libre Search
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_libre_search.php
*/
?>

	<div id="rencSearchLibre" class="rencSearchLibre">
		<form name="rencSearch" method="get" action="">
			<input type="hidden" name="renc" value="searchLibre" />
			<?php if(!empty($rencOpt['page_id'])) { ?>
			
			<input type="hidden" name="page_id" value="<?php echo $rencOpt['page_id']; ?>" />
			<?php } ?>
			
			<p class="rencSearchBloc">
				<label><?php _e('I\'m looking for','rencontre'); ?>&nbsp;</label>
				<select name="zsex">
				<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) { ?>

					<option value="<?php echo $v; ?>"><?php echo $rencOpt['iam'][$v]; ?></option>
				<?php } ?>

				</select>
			</p>
			<?php if(!isset($rencCustom['born'])) { ?>

			<p class="rencSearchBloc">
				<label><?php _e('between','rencontre'); ?>&nbsp;</label>
				<select name="zageMin" onChange="<?php echo $onClick['zagemin']; ?>">
				<?php for($v=20;$v<91;$v+=5) { ?>
				
					<option value="<?php echo $v; ?>"<?php if($v==20) echo ' selected'; ?>><?php echo $v; ?>&nbsp;<?php _e('years','rencontre'); ?></option>
				<?php } ?>
				
				</select>
				<label>&nbsp;<?php _e('and','rencontre'); ?>&nbsp;</label>
				<select name="zageMax" onChange="<?php echo $onClick['zagemax']; ?>">
				<?php for($v=25;$v<96;$v+=5) { ?>
				
					<option value="<?php echo $v; ?>"<?php if($v==95) echo ' selected'; ?>><?php echo $v; ?>&nbsp;<?php _e('years','rencontre'); ?></option>
				<?php } ?>
				
				</select>
			</p>
			<?php } ?>
			
			<p class="rencSearchSubmit">
				<input type="submit" value="<?php _e('Search','rencontre'); ?>" />
			</p>
		</form>
	</div><!-- .rencSearchLibre -->
