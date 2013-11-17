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
<?php
class plgHikashoppaymentPayone extends JPlugin
{
	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
		'SEK','DKK','PLN','NOK','HUF','CZK','MXN','BRL','MYR','PHP',
		'TWD','THB','ILS','TRY'
	);
	var $apikey = '';
	var $debugData = array();
	function onPaymentDisplay(&$order,&$methods,&$usable_methods){
		if(!empty($methods)){
			foreach($methods as $method){
			if($method->payment_type!='payone'  || !$method->enabled  || !$method->published){
				continue;
			}
			if(!empty($method->payment_zone_namekey)){
				$zoneClass=hikashop_get('class.zone');
					$zones = $zoneClass->getOrderZones($order);
				if(!in_array($method->payment_zone_namekey,$zones)){
					return true;
				}
			}
			$currencyClass = hikashop_get('class.currency');
			$null=null;
			if(!empty($order->total)){
				$currency_id = intval(@$order->total->prices[0]->price_currency_id);
				$currency = $currencyClass->getCurrencies($currency_id,$null);
				if(!empty($currency) && !in_array(@$currency[$currency_id]->currency_code,$this->accepted_currencies)){
					return true;
				}
			}
			$usable_methods[$method->ordering]=$method;
			}
		}
		return true;
	}
	function onPaymentSave(&$cart,&$rates,&$payment_id){
		$usable = array();
		$this->onPaymentDisplay($cart,$rates,$usable);
		$payment_id = (int) $payment_id;
		foreach($usable as $usable_method){
			if($usable_method->payment_id==$payment_id){
				return $usable_method;
			}
		}
		return false;
	}
	function onAfterOrderConfirm(&$order,&$methods,$method_id){
		return true;
	}
	function onPaymentNotification(&$statuses){
	
		include_once("payone_helper.php");
		
		$method->payment_params->apikey = '12345';
	
		if (!$method->payment_params->apikey) {
			echo "ERR=No Api-Key";
			return false;
			exit;
		}
		
		$TS = new PayOneTransactionStatus($method->payment_params->apikey);
		$TS->ImportDataFromRequest();
		
		if ($TS->AuthorizeTransaction() == true) {
			// transaction was authorized
			
			$order_id = $TS->getReference();
			if(empty($order_id)) { echo 'error!'; return false; exit; }
			
			if ($TS->IsAppointed() == true) {
				echo "TSOK"; exit;
			}
			if ($TS->IsPaid() == true) {
				$clearingtype = $TS->getClearingtype();
				$bill_output["code"] = 1;    // mark as paid
		        $pluginsClass = hikashop_get('class.plugins');
	    		$elements = $pluginsClass->getMethods('payment','payone_'.$clearingtype);
	    		if(empty($elements)) return false;
	    		$element = reset($elements);
	    		
				$orderClass = hikashop_get('class.order');
	    		$dbOrder = $orderClass->get((int)$order_id);
	    		if(empty($dbOrder)){
	    			//echo "Could not load any order for your notification ".$invoice;
	    			return false;
	    			exit;
	    		}
	    		
	    		$order = null;
	    		$order->order_id = $dbOrder->order_id;
				$order->history->history_reason=JText::sprintf('AUTOMATIC_PAYMENT_NOTIFICATION');
	    		$order->history->history_notified=0;
	    		$order->history->history_amount= $invoice;
	    		$order->history->history_payment_id = $element->payment_id;
	    		$order->history->history_payment_method =$element->payment_type;
	    		$order->history->history_data = ob_get_clean();
	    		$order->history->history_type = 'payment';
	    		$order->order_status = $element->payment_params->verified_status;
	    		$order->history->history_notified = 1;
	    		$orderClass->save($order);
				echo "TSOK";
	    		exit;
		    } else {
		        $bill_output["code"] = 3;    // mark as queued
		    }
		} else {
			// transaction was not authorized (wrong key)
			$tmp = "transaction not authorized, wrong key";
			$bill_output["code"] = 2;        // payment not accepted
			$bill_output["message"] = $tmp;
			echo "ERR=" . $tmp;
			return false;
		}	
	}
	function onPaymentConfiguration(&$element){
			$subtask = JRequest::getCmd('subtask','');
			if($subtask=='ips'){
				exit;
			}else{
				$this->payone = JRequest::getCmd('name','payone');
				if(empty($element)){
					$element = null;
					$element->payment_name='PayOne';
					$element->payment_description='';
					$element->payment_images='';
					$element->payment_type=$this->payone;
					$element->payment_params=null;
					$element->payment_params->url='https://secure.pay1.de/frontend/';
					$element->payment_params->notification=1;
					$list=null;
					$element->payment_params->details=0;
					$element->payment_params->invalid_status='cancelled';
					$element->payment_params->pending_status='created';
					$element->payment_params->verified_status='confirmed';
					$element->payment_params->address_override=1;
					$element = array($element);
				}
				$obj = reset($element);
				$bar = & JToolBar::getInstance('toolbar');
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				JToolBarHelper::divider();
				$bar->appendButton( 'Pophelp','payment-payone-form');
				hikashop_setTitle('Payone','plugin','plugins&plugin_type=payment&task=edit&name='.$this->payone);
				$app =& JFactory::getApplication();
				$app->setUserState( HIKASHOP_COMPONENT.'.payment_plugin_type', $this->payone);
				$this->address = hikashop_get('type.address');
				$this->category = hikashop_get('type.categorysub');
				$this->category->type = 'status';
			}
		}
	function onPaymentConfigurationSave(&$element){
		return true;
	}
}
