<?php
/*
 * Plugin : Rencontre
 * Template : Registration Part 1/4 and 1/3
 * Last Change : Rencontre 2.0.1
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_registration_part1.php
 * Filter : do_action('rencontre_registration', $f, $g) - see below
*/
?>

	<div class="pleineBox">
		<div class="rencBox">
			<div class="rencNouveau">
				<h3><?php _e('Hello','rencontre'); ?>&nbsp;<?php echo $current_user->user_login; ?>,&nbsp;<?php _e('welcome to the site','rencontre'); ?>&nbsp;<?php echo bloginfo('name'); ?></h3>
				<p>
			<?php if(isset($rencCustom['new']) && !empty($rencCustom['newText'])) { ?>
				<?php echo stripslashes($rencCustom['newText']); ?>
			<?php } else { ?>
				<?php _e('You will access all the possibilities offered by the site in few minutes.','rencontre'); ?>
				<?php _e('Before that, you need to provide some information requested below.','rencontre'); ?>
				
				</p>
				<p>
				<?php _e('We would like to inform you that we do not use your personal data outside of this site.','rencontre'); ?>
				<?php _e('Deleting your account on your part or ours, causes the deletion of all your data.','rencontre'); ?>
				<?php _e('This also applies to messages that you have sent to other members as well as those they have sent to you.','rencontre'); ?>
				
				</p>
				<p>
				<?php _e('We wish you nice encounters.','rencontre'); ?>
			<?php } ?>
			
				</p>
				<div class="rencEvol">
				<?php if(!isset($rencCustom['place'])) { ?>
					
					<div class="rencEvol25">1 / 4</div>
				<?php } else { ?>
					
					<div class="rencEvol33">1 / 3</div>
				<?php } ?>
			
					<div style="clear:left;"></div>
				</div>
				<form name="formNouveau" method='post' action=''>
					<input type='hidden' name='nouveau' value='1' />
					<input type='hidden' name='a1' value='' />
					<table>
						<tr>
							<td>
								<div class="th"><?php _e('I am','rencontre');?></div>
								<select name="sex">
								<?php for($v=(isset($rencCustom['sex'])?2:0);$v<(isset($rencCustom['sex'])?count($rencOpt['iam']):2);++$v) { ?>
								
									<option value="<?php echo $v; ?>"><?php echo $rencOpt['iam'][$v]; ?></option>
								<?php } ?>
								
								</select>
							</td>
							<td>
							<?php if(!isset($rencCustom['born'])) { ?>
							
								<div class="th"><?php _e('Born','rencontre'); ?></div>
								<select name="jour">
									<?php for($v=1;$v<32;++$v) { ?>
									
									<option value="<?php echo $v; ?>"><?php echo $v; ?></option>
									<?php } ?>
									
								</select>
								<select name="mois">
									<?php for($v=1;$v<13;++$v) { ?>
									
									<option value="<?php echo $v; ?>"><?php echo $v; ?></option>
									<?php } ?>
									
								</select>
								<select name="annee">
									<?php for($v=$oldmax;$v<$oldmin;++$v) { ?>
									
									<option value="<?php echo $v; ?>"><?php echo $v; ?></option>
									<?php } ?>
									
								</select>
							<?php } ?>

							</td>
						</tr>
						<tr>
							<td>
							<?php if(!isset($rencCustom['size'])) { ?>
							
								<div class="th"><?php _e('My size','rencontre'); ?></div>
								<select name="taille">
								<?php for($v=140;$v<221;++$v) { ?>
									<?php if(empty($rencCustom['sizeu'])) { ?>
									
									<option value="<?php echo $v; ?>"><?php echo $v; ?>&nbsp;<?php _e('cm','rencontre'); ?></option>
									<?php } else { ?>
									
									<option value="<?php echo $v; ?>"><?php echo (floor($v/24-1.708)); ?>&nbsp;<?php _e('ft','rencontre'); ?>&nbsp;<?php echo (round(((($v/24-1.708)-floor($v/24-1.708))*12),1)); ?>&nbsp;<?php _e('in','rencontre'); ?></option>
									<?php } ?>
								<?php } ?>
									
								</select>
							<?php } ?>

							</td>
							<td>
							<?php if(!isset($rencCustom['weight'])) { ?>
							
								<div class="th"><?php _e('My weight','rencontre'); ?></div>
								<select name="poids">
								<?php for($v=40;$v<140;++$v) { ?>
									<?php if(empty($rencCustom['weightu'])) { ?>
									
									<option value="<?php echo $v; ?>"><?php echo $v; ?>&nbsp;<?php _e('kg','rencontre'); ?></option>
									<?php } else { ?>
									
									<option value="<?php echo $v; ?>"><?php echo ($v*2+10); ?>&nbsp;<?php _e('lbs','rencontre'); ?></option>
									<?php } ?>
								<?php } ?>
									
								</select>
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

<?php
/*
 * Data from this Template (POST) are treated in rencontre_widget.php, static function f_registerMember($f,$g).
 * You can replace this function by adding this example in the functions.php file of your theme :
 * 
 *		add_action('rencontre_registration','my-registration',10,2);
 *		function my-registration($f,$g)
 *			{
 *			// $f : my user_id
 *			// $g : $_POST['nouveau'] (1, 2, 3, 4)
 *			global $wpdb; global $rencOpt;
 *			... see static function f_registerMember($f,$g) for your code creation.
 *			...
 *			}
*/
?>
