<?php
/*
 * Plugin : Rencontre
 * Template : Registration Part 3/4 and 2/3
 * Last Change : Rencontre 2.0.1
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_registration_part3.php
*/
?>

	<div class="pleineBox">
		<div class="rencBox">
			<div class="rencNouveau">
				<h3><?php _e('Hello','rencontre'); ?>&nbsp;<?php echo $current_user->user_login; ?>,&nbsp;<?php _e('welcome to the site','rencontre'); ?>&nbsp;<?php echo bloginfo('name'); ?></h3>
				<div class="rencEvol">
				<?php if(!isset($rencCustom['place'])) { ?>
					
					<div class="rencEvol75">3 / 4</div>
				<?php } else { ?>
				
					<div class="rencEvol66">2 / 3</div>
				<?php } ?>
			
					<div style="clear:left;"></div>
				</div>
				<form name="formNouveau" method='post' action=''>
					<input type='hidden' name='nouveau' value='3' />
					<input type='hidden' name='a1' value='' />
					<table>
						<tr>
							<td>
								<div class="th"><?php _e('I\'m looking for','rencontre');?></div>
							<?php if(empty($rencCustom['multiSR'])) { ?>
							
								<select name="zsex">
								<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) { ?>
								
									<option value="<?php echo $v; ?>"><?php echo $rencOpt['iam'][$v]; ?></option>
								<?php } ?>
								
								</select>
							<?php } else { ?>
								<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) { ?>
								
								<?php echo $rencOpt['iam'][$v]; ?>&nbsp;<input type="checkbox" name="zsex[]" value="<?php echo $v; ?>" />
								<?php } ?>
							<?php } ?>
							
							</td>
						<?php if(!isset($rencCustom['born'])) { ?>
						
							<td>
								<div class="th"><?php _e('Age min/max','rencontre');?></div>
								<select name="zageMin" onChange="<?php echo $onClick['agemin']; ?>">
								<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18);$v<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99);++$v) { ?>
								
									<option value="<?php echo $v; ?>"><?php echo $v; ?>&nbsp;<?php _e('years','rencontre'); ?></option>
								<?php } ?>
									
								</select>
								<select name="zageMax" onChange="<?php echo $onClick['agemax']; ?>">
								<?php for($v=(isset($rencCustom['agemin'])?intval($rencCustom['agemin']):18)+1;$v<(isset($rencCustom['agemax'])?intval($rencCustom['agemax']):99)+1;++$v) { ?>
								
									<option value="<?php echo $v; ?>"><?php echo $v; ?>&nbsp;<?php _e('years','rencontre'); ?></option>
								<?php } ?>
									
								</select>
							</td>
						</tr>
						<tr>
							<td colspan = "2">
						<?php } else { ?>
						
						</tr>
						<tr>
							<td>
						<?php } ?>
						
								<div class="th"><?php _e('For','rencontre');?></div>
							<?php if(empty($rencCustom['multiSR'])) { ?>
							
								<select name="zrelation">
								<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) { ?>
								
									<option value="<?php echo $v; ?>"><?php echo $rencOpt['for'][$v]; ?></option>
								<?php } ?>
								
								</select>
							<?php } else { ?>
								<?php for($v=(isset($rencCustom['relation'])?3:0);$v<(isset($rencCustom['relation'])?count($rencOpt['for']):3);++$v) { ?>
								
								<?php echo $rencOpt['for'][$v]; ?>&nbsp;<input type="checkbox" name="zrelation[]" value="<?php echo $v; ?>" />
								<?php } ?>
							<?php } ?>
							
							</td>
						</tr>
					</table>
					<div id="buttonPass" class="button">
						<a href="javascript:void(0)" onClick="<?php echo $onClick['save']; ?>"><?php _e('Send','rencontre');?></a>
					</div>
				</form>
				<div id="rencAlert"></div>
			</div><!-- .rencNouveau -->
		</div><!-- .rencBox -->
	</div><!-- .pleineBox -->
