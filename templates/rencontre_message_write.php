<?php
/*
 * Plugin : Rencontre
 * Template : Message Write
 * Last Change : Rencontre 2.0
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_message_write.php
 * $u : user_id, user_login, i_photo, photo
 * $u0 : user_login
*/
?>

	<div class="rencBox">
		<h3><?php _e('Send a message to','rencontre'); ?>
			<a href="javascript:void(0)" onClick="<?php echo $onClick['profile']; ?>">
			<?php echo '&nbsp;'.$u->user_login; ?>
			</a>
		</h3>
		<div id="rencMsg" class="rencMsg">
		<form name="formEcrire" method='post' action=''>
		<?php if(isset($rencOpt['page_id'])) { ?>
		
			<input type="hidden" name="page_id" value="<?php echo $rencOpt['page_id']; ?>" />
		<?php } ?>
			<input type='hidden' name='renc' value='' />
			<input type='hidden' name='id' value='' />
			<input type='hidden' name='msg' value='' />
			<a href="javascript:void(0)" onClick="<?php echo $onClick['profile']; ?>">
			<?php if($u->i_photo!=0) { ?>
			
				<img class="tete" src="<?php echo $u->photo; ?>" alt="<?php echo $u->user_login; ?>" />
			<?php } else { ?>
			
				<img class="tete" src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php echo $u->user_login; ?>" />
			<?php } ?>
			
			</a>
			<label><?php _e('Message','rencontre');?>&nbsp;:</label>
			<textarea name="contenu" rows="8"></textarea>
			<br />
		<?php if(!isset($rencCustom['emot']) || !$rencCustom['emot']) { ?>
			
			<div id="msgEmot" class="msgEmot"></div>
			<script type="text/javascript">jQuery(document).ready(function(){f_msgEmot(document.getElementById("msgEmot"))})</script>
		<?php } ?>
		
			<div class="button">
				<a href="javascript:void(0)" onClick="<?php echo $onClick['send']; ?>"><?php _e('Send','rencontre');?></a>
			</div>
			<div class="clear"></div>
		<?php RencontreWidget::f_conversation($u0->user_login,$u->user_id,$u->user_login,$u->i_photo,99); ?>
		
		</form>
		</div>
	</div><!-- .rencBox -->
