<?php
/*
 * Plugin : Rencontre
 * Template : Registration Part 4/4 and 3/3
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_registration_part4.php
*/
?>

	<div class="pleineBox">
		<div class="rencBox">
			<div class="rencNouveau">
				<h3><?php _e('Hello','rencontre'); ?>&nbsp;<?php echo $current_user->user_login; ?>,&nbsp;<?php _e('welcome to the site','rencontre'); ?>&nbsp;<?php echo bloginfo('name'); ?></h3>
				<div class="rencEvol">
				<?php if(!isset($rencCustom['place'])) { ?>
					
					<div class="rencEvol100">4 / 4</div>
				<?php } else { ?>
					
					<div class="rencEvol100">3 / 3</div>
				<?php } ?>
			
					<div style="clear:left;"></div>
				</div>
				<form name="formNouveau" method='post' action=''>
					<input type='hidden' name='nouveau' value='OK' />
					<input type='hidden' name='a1' value='' />
					<table>
						<tr>
							<td colspan = "2">
								<div class="th"><?php _e('Change nickname (after, it will not be possible)','rencontre');?></div>
								<input name="pseudo" type="text" size="12" value="<?php echo $current_user->user_login; ?>" /> 
							</td>
						</tr>
						<?php if(empty($rencOpt['passw']) || isset($_SESSION['rencFB'])) { ?>
						
						<tr>
							<td>
								<div class="th"><?php _e('New password (6 min)','rencontre');?></div>
								<input name="pass1" type="password" size="12" />
							</td>
							<td>
								<div class="th"><?php _e('New password (again)','rencontre');?></div>
								<input name="pass2" type="password" size="12" />
							</td>
						</tr>
						<?php } else { ?>
						
						<input name="pass1" type="hidden" value="<?php echo $passNopass; ?>" />
						<input name="pass2" type="hidden" value="<?php echo $passNopass; ?>" />
						<?php } ?>
						
					</table>
					<div id="buttonPass" class="button">
						<a href="javascript:void(0)" onClick="<?php echo $onClick['save']; ?>"><?php _e('Send','rencontre');?></a>
					</div>
				</form>
				<div id="rencAlert"></div>
			</div><!-- .rencNouveau -->
		</div><!-- .rencBox -->
		
	</div><!-- .pleineBox -->
