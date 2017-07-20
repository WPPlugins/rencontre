<?php
/*
 * Plugin : Rencontre
 * Template : Message Inbox
 * Last Change : Rencontre 2.0.2
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_message_inbox.php
 * $u0 : user_login
*/
?>

	<div class="rencBox">
		<form name="formEcrire" method='post' action=''>
		<?php if(isset($rencOpt['page_id'])) { ?>
		
			<input type="hidden" name="page_id" value="<?php echo $rencOpt['page_id']; ?>" />
		<?php } ?>
		
			<input type='hidden' name='renc' value='' />
			<input type='hidden' name='id' value='' />
			<input type='hidden' name='msg' value='' />
			<div id="rencMsg" class="rencMsg">
				<table>
					<tr>
						<th style="width:16px;"></th>
						<th><?php _e('Member','rencontre');?></th>
						<th style="width:40%;"><?php _e('Date','rencontre');?></th>
						<th style="width:20px;"></th>
					</tr>
				<?php foreach($inbox as $m) { ?>
					<?php if($m->read==1) { ?>
					
					<tr class="<?php echo $m->type; ?>">
						<td></td>
					<?php } else { ?>
				
					<tr class="unread <?php echo $m->type; ?>">
						<td></td>
					<?php } ?>
				
						<td onClick="id=<?php echo $m->id.';'.$onClick['look']; ?>"><?php echo $m->member; ?></td>
						<td onClick="id=<?php echo $m->id.';'.$onClick['look']; ?>"><?php echo $m->date; ?></td>
						<td>
							<a class="rencSupp" href="javascript:void(0)" onClick="id=<?php echo $m->ID.';'.$onClick['del']; ?>">&nbsp;</a>
						</td>
					</tr>
				<?php } ?>
				
				</table>
			</div>
		</form>
	</div><!-- .rencBox -->
