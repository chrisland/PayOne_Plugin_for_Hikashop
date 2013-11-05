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

<tr>
	<td class="key">
		<label for="data[payment][payment_params][url]">
			<?php echo JText::_( 'URL' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][url]" value="<?php echo @$this->element->payment_params->url; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][request]">
			<?php echo JText::_( 'REQUEST' ); ?>
		</label>
	</td>
	<td>
		<? if (!$this->element->payment_params->request) { $this->element->payment_params->request='authorization'; } ?>
		<input type="text" name="data[payment][payment_params][request]" value="<?php echo @$this->element->payment_params->request; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][portalid]">
			<?php echo JText::_( 'PORTALID' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][portalid]" value="<?php echo @$this->element->payment_params->portalid; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][aid]">
			<?php echo JText::_( 'AID' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][aid]" value="<?php echo @$this->element->payment_params->aid; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][apikey]">
			<?php echo JText::_( 'APIKEY' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][apikey]" value="<?php echo @$this->element->payment_params->apikey; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][mode]">
			<?php echo JText::_( 'MODE' ); ?>
		</label>
	</td>
	<td>
		<? if (!$this->element->payment_params->mode) { $this->element->payment_params->mode='live'; } ?>
		<input type="text" name="data[payment][payment_params][mode]" value="<?php echo @$this->element->payment_params->mode; ?>" />
	</td>
</tr>



<tr>
	<td class="key">
		<label for="data[payment][payment_params][invalid_status]">
			<?php echo JText::_( 'INVALID_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][invalid_status]",@$this->element->payment_params->invalid_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][pending_status]">
			<?php echo JText::_( 'PENDING_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][pending_status]",@$this->element->payment_params->pending_status); ?>
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][verified_status]">
			<?php echo JText::_( 'VERIFIED_STATUS' ); ?>
		</label>
	</td>
	<td>
		<?php echo $this->data['category']->display("data[payment][payment_params][verified_status]",@$this->element->payment_params->verified_status); ?>
	</td>
</tr>




<tr>
	<td class="key">
		<label for="data[payment][payment_params][successurl]">
			<?php echo JText::_( 'successurl' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][successurl]" value="<?php echo @$this->element->payment_params->successurl; ?>" />
	</td>
</tr>
<tr>
	<td class="key">
		<label for="data[payment][payment_params][errorurl]">
			<?php echo JText::_( 'errorurl' ); ?>
		</label>
	</td>
	<td>
		<input type="text" name="data[payment][payment_params][errorurl]" value="<?php echo @$this->element->payment_params->errorurl; ?>" />
	</td>
</tr>


