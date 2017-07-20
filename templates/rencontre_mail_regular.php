<?php
/*
 * Plugin : Rencontre
 * Template : Mail Regular
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_mail_regular.php
 * $u : ID, user_login, d_naissance, c_pays, c_ville, t_titre, name, age, title, link
*/
?>

<td>
	<div style='display:block;width:265px;padding:3px;margin:4px;border-radius:3px;border:1px solid #e3d2a6;background-color:#fbf8f3;'>
		<table style='width:100%;'>
			<tr>
				<td style='vertical-align:top;'>
					<div style='padding:1px;border-radius:3px;border:1px solid #e3d2a6;background-color:#e8e5ce;text-align:center;'><?php echo $u->name; ?></div>
				</td>
				<td rowspan=2 style='vertical-align:top;'>
					<div style='margin:3px 0;font-size:11px;'>
					<?php if($u->age) { ?>
					
						<span style='font-weight:bold;'><?php _e('Age','rencontre'); ?> : </span><?php echo $u->age; ?>
					<?php } ?>
					<?php if($rencDrap) { ?>
					
						<img style='float:right;border-radius:3px;margin-top:-3px' src='<?php echo plugins_url('rencontre/images/drapeaux/').$rencDrap[$u->c_pays]; ?>' />
					<?php } ?>
					
					</div>
				<?php if(!isset($rencCustom['place'])) { ?>
				
					<div style='margin:7px 0;font-size:11px;line-height:1em;'>
						<span style='font-weight:bold;'><?php _e('City','rencontre'); ?> : </span><?php echo $u->c_ville; ?>
					</div>
				<?php } ?>
				
					<div style='margin:10px 0 7px;font-size:11px;line-height:1em;'><?php echo $u->title; ?></div>
				</td>
			</tr>
			<tr>
				<td style='vertical-align:top;width:136px;'>
					<img style='background-color:#ffffff;padding:3px;border-radius:3px;border:1px solid #e3d2a6;' src='<?php echo $u->photoUrl; ?>' alt='<?php echo $u->name; ?>' width='141' height='108' />
				</td>
			</tr>
			<tr>
				<td style='vertical-align:top;'>
					<a style='<?php echo $buttonCSS; ?>' href='<?php echo $u->link->contact; ?>' target='_blank'>
						&nbsp;<?php _e('Ask for a contact','rencontre'); ?>
					</a>
				</td>
				<td style='vertical-align:top;'>
					<a style='<?php echo $buttonCSS; ?>' href='<?php echo $u->link->smile; ?>' target='_blank'>&nbsp;
						<?php if(!empty($rencCustom['smiw']) && !empty($rencCustom['smiw1'])) echo stripslashes($rencCustom['smiw1']);
						else _e('Smile','rencontre'); ?>
					</a>
				</td>
			</tr>
			<tr>
				<td style='vertical-align:top;'>
					<a style='<?php echo $buttonCSS; ?>' href='<?php echo $u->link->message; ?>' target='_blank'>
						&nbsp;<?php _e('Send a message','rencontre'); ?>
					</a>
				</td>
				<td style='vertical-align:top;'>
					<a style='<?php echo $buttonCSS; ?>' href='<?php echo $u->link->profile; ?>' target='_blank'>
						&nbsp;<?php _e('Profile','rencontre'); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
</td>
