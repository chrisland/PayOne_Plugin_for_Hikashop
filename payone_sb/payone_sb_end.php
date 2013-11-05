<?php
/**
 * @package		Hikashop Payone Payment Plugin for Joomla!
 * @version		1.1.0
 * @author		http://www.chrisland.de
 * @copyright	(C) Christian Marienfeld. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="hikashop_payone_end" id="hikashop_payone_end">
	<span id="hikashop_payone_end_message" class="hikashop_payone_end_message">
		<?php echo JText::sprintf('PLEASE_WAIT_BEFORE_REDIRECTION_TO_X',$method->payment_name).'<br/>'. JText::_('CLICK_ON_BUTTON_IF_NOT_REDIRECTED');?>
	</span>
	<span id="hikashop_payone_end_spinner" class="hikashop_payone_end_spinner">
		<img src="<?php echo HIKASHOP_IMAGES.'spinner.gif';?>" />
	</span>
	<br/>
	<form id="hikashop_payone_form" name="hikashop_payone_form" action="<?php echo $method->payment_params->url;?>" method="post">
		<div id="hikashop_payone_end_image" class="hikashop_payone_end_image">
			<input id="hikashop_payone_button" type="submit" value="<?php echo JText::_('PAY_NOW');?>" name="" alt="<?php echo JText::_('PAY_NOW');?>" />
		</div>
		<?php
			foreach( $vars as $name => $value ) {
				echo '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars((string)$value).'" />';
			}
			JRequest::setVar('noform',1); ?>
	</form>
	<script type="text/javascript">
		document.getElementById('hikashop_payone_form').submit();
	</script>
</div>