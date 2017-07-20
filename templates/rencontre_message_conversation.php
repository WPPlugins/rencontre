<?php
/*
 * Plugin : Rencontre
 * Template : Message Conversation
 * Last Change : Rencontre 2.0.2
 * Custom This File ? : wp-content/themes/name-of-my-theme/templates/rencontre_message_conversation.php
 * $u : ID, user_login, login, i_photo, photo
 * $u0 : user_login, login
*/
?>

	<?php if($hoAns!==99) { // display buttons and photo ?>

	<a class="msgProfil" href="javascript:void(0)" onClick="<?php echo $onClick['profile']; ?>">
		<span style="margin:10px"><?php echo $u->login; ?></span>
		<?php if($u->i_photo!=0) { ?>
		
		<img src="<?php echo $u->photo; ?>" alt="<?php echo $u->user_login; ?>" title="<?php echo $u->user_login; ?>" />
		<?php } else { ?>
		
		<img src="<?php echo plugins_url('rencontre/images/no-photo60.jpg'); ?>" alt="<?php echo $u->user_login; ?>" title="<?php echo $u->user_login; ?>" />
		<?php } ?>
	</a>
	<div class="clear"></div>
	<div class="button" style="float:left;">
		<a href="javascript:void(0)" onClick="<?php echo $onClick['inbox']; ?>"><?php _e('Inbox','rencontre');?></a>
	</div>
	<?php if(!$hoAns){ ?>
	
	<div class="button">
		<a href="javascript:void(0)" onClick="<?php echo $onClick['write']; ?>"><?php _e('Answer','rencontre');?></a>
	</div>
	<?php } else { ?>
	
	<div class="button rencLiOff">
		<a href="javascript:void(0)"><?php _e('Answer','rencontre'); ?></a>
	</div>
	<?php } ?>
	
	<div class="button">
		<a href="javascript:void(0)" onClick="if(confirm('<?php _e('Delete the conversation','rencontre'); ?>')){<?php echo $onClick['del']; ?>}"><?php _e('Delete the conversation','rencontre'); ?></a>
	</div>
	<?php } ?>
	<?php foreach($conversation as $m) { ?>
	
	<div class="<?php echo ($m->sender==$u0->user_login?'to':'fm'); ?>">
		<div class="msgDate"><?php echo $m->date; ?></div>
		<div class="msgContent"><?php echo stripslashes(nl2br($m->content)); ?></div>
	</div>
	<?php } ?>
		
	<div class="clear"></div>
	<?php if(!isset($rencCustom['emot']) || !$rencCustom['emot']) { ?>
	
	<script type="text/javascript">jQuery(document).ready(function(){f_msgEmotContent(document.getElementsByClassName("msgContent"))})</script>
	<?php } ?>
