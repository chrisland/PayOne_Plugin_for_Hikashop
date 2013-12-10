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
class plgHikashoppaymentPayone_sb extends JPlugin
{
	var $accepted_currencies = array(
		'AUD','CAD','EUR','GBP','JPY','USD','NZD','CHF','HKD','SGD',
		'SEK','DKK','PLN','NOK','HUF','CZK','MXN','BRL','MYR','PHP',
		'TWD','THB','ILS','TRY'
	);
	var $debugData = array();
	function onPaymentDisplay(&$order,&$methods,&$usable_methods){
		if(!empty($methods)){
			foreach($methods as $method){
				if($method->payment_type!='payone_sb'  || ( !$method->enabled && !$method->published )){
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
				if ($method->enabled == 0) {
					return false;
				} else {
					$usable_methods[$method->ordering] = $method;
				}
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
		$method =& $methods[$method_id];
		$tax_total = '';
		$discount_total = '';
		$currencyClass = hikashop_get('class.currency');
		$currencies=null;
		$currencies = $currencyClass->getCurrencies($order->order_currency_id,$currencies);
		$currency=$currencies[$order->order_currency_id];
		hikashop_loadUser(true,true); //reset user data in case the emails were changed in the email code
		$user = hikashop_loadUser(true);
		$lang = &JFactory::getLanguage();
		$locale=strtolower(substr($lang->get('tag'),0,2));
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid)){
			$url_itemid='&Itemid='.$Itemid;
		}
		$clearingtype = "sb";
		$reference	=	$order->order_id; // Händer-ReferenzNr.
		$vars = array();
		$i = 1;
		$tax = 0;
		$amount = 0;
		$attr_de = $attr_id = $attr_pr = $attr_no = $attr_va = '';
		$coupon_price = (round($order->order_discount_price,(int)$currency->currency_locale['int_frac_digits']) * 100);
		$coupon = false;
		foreach($order->cart->products as $product){
			$price = (round($product->order_product_price,(int)$currency->currency_locale['int_frac_digits']) *100);
			if (!$coupon) {
				if(!empty($order->order_discount_price)){
				
					if ($price >= $coupon_price) {
						$price -= $coupon_price;
						$coupon = true;
					}
				}
			}
			$vars["de[".$i."]"] = substr(strip_tags($product->order_product_name),0,127);
			$attr_de .= substr(strip_tags($product->order_product_name),0,127);
			$vars["id[".$i."]"] = $product->order_product_code;
			$attr_id .= $product->order_product_code;
			$vars["pr[".$i."]"] = $price;
			$attr_pr .= $price;
			$vars["no[".$i."]"] = $product->order_product_quantity;
			$attr_no .= $product->order_product_quantity;
			$tax += round($product->order_product_tax,(int)$currency->currency_locale['int_frac_digits'])*$product->order_product_quantity;
			$amount += $price * $product->order_product_quantity;
			$i++;
		}
		if(!empty($order->order_shipping_price) && bccomp($order->order_shipping_price,0,5)){
			$vars["de[".$i."]"] = JText::_('HIKASHOP_SHIPPING');
			$attr_de .= JText::_('HIKASHOP_SHIPPING');
			$vars["id[".$i."]"] = 'shipping';
			$attr_id .= 'shipping';
			$vars["pr[".$i."]"] = round($order->order_shipping_price-$order->order_shipping_tax,(int)$currency->currency_locale['int_frac_digits'])*100;
			$attr_pr .= round($order->order_shipping_price-$order->order_shipping_tax,(int)$currency->currency_locale['int_frac_digits'])*100;
			$vars["no[".$i."]"] = 1;
			$attr_no .= '1';
			$tax += round($order->order_shipping_tax,(int)$currency->currency_locale['int_frac_digits']);
			$amount += round($order->order_shipping_price-$order->order_shipping_tax,(int)$currency->currency_locale['int_frac_digits'])*100;
			$i++;
		}
		if(bccomp($tax,0,5)){
			$vars["de[".$i."]"] = 'Tax';
			$attr_de .= 'Tax';
			$vars["id[".$i."]"] = 'tax';
			$attr_id .= 'tax';
			$vars["pr[".$i."]"] = $tax*100;
			$attr_pr .= $tax*100;
			$vars["no[".$i."]"] = 1;
			$attr_no .= '1';
			$amount	+= $tax*100;
			$i++;
		}
		$address1 = '';
		$address2 = '';
		if(!empty($order->cart->billing_address->address_street2)){
			$address2 = substr($order->cart->billing_address->address_street2,0,99);
		}
		if(!empty($order->cart->billing_address->address_street)){
			if(strlen($order->cart->billing_address->address_street)>100){
				$address1 = substr($order->cart->billing_address->address_street,0,99);
				if(empty($address2)) $address2 = substr($order->cart->billing_address->address_street,99,199);
			}else{
				$address1 = $order->cart->billing_address->address_street;
			}
		}
		$vars["notif_payment"] 				= 'payone_sb';
		$vars["successurl"] 				= $method->payment_params->successurl;
		$vars["errorurl"] 					= $method->payment_params->errorurl;
		$vars["aid"] 						= $method->payment_params->aid;
		$vars["amount"] 					= $amount;
		$vars["clearingtype"] 				= $clearingtype;
		$vars["currency"] 					= $currency->currency_code;
		$vars["customerid"] 				= $method->payment_params->customerid;
		$vars["mode"] 						= $method->payment_params->mode;
		$vars["portalid"] 					= $method->payment_params->portalid;
		$vars["reference"] 					= $reference;
		$vars["request"] 					= $method->payment_params->request;
		$vars["firstname"]					= $order->cart->billing_address->address_firstname;
		$vars["lastname"]					= $order->cart->billing_address->address_lastname;
		$vars["company"] 					= $order->cart->billing_address->address_company;
		$vars["street"] 					= $order->cart->billing_address->address_street;
		$vars["zip"] 						= $order->cart->billing_address->address_post_code;
		$vars["city"] 						= $order->cart->billing_address->address_city;	
		$vars["country"] 					= $order->cart->billing_address->address_country->zone_code_2;
		$vars["email"] 						= $user->user_email;
		$vars["telephonenumber"] 			= $order->cart->billing_address->address_telephone;
		$vars["shipping_firstname"]			= $order->cart->shipping_address->address_firstname;
		$vars["shipping_lastname"] 			= $order->cart->shipping_address->address_lastname;
		$vars["shipping_company"] 			= $order->cart->shipping_address->address_company;
		$vars["shipping_street"] 			= $order->cart->shipping_address->address_street;
		$vars["shipping_zip"] 				= $order->cart->shipping_address->address_post_code;
		$vars["shipping_city"]				= $order->cart->shipping_address->address_city;
		$vars["shipping_country"] 			= $order->cart->shipping_address->address_country->zone_code_2;
		$vars["shipping_email"] 			= $user->user_email;
		$vars["shipping_telephonenumber"] 	= $order->cart->shipping_address->address_telephone;
		$hash		=	md5(	$vars["aid"] . 
								$vars["amount"] . 
								$vars["clearingtype"] . 
								$vars["currency"] . 
								$vars["customerid"] .
								$attr_de . 
								$vars["errorurl"] .
								$attr_id . 
								$vars["mode"] . 
								$attr_no . 
								$vars["portalid"] . 
								$attr_pr .
								$vars["reference"] . 
								$vars["request"] . 
								$vars["successurl"] .
								$attr_va .
								$method->payment_params->apikey
							); // Parameter in sortierter Reihenfolge + Key
		$vars["hash"] = $hash;
		JHTML::_('behavior.mootools');
		$app =& JFactory::getApplication();
		$name = $method->payment_type.'_end.php';
		$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashoppayment'.DS.$name;
		if(!file_exists($path)){
			if(version_compare(JVERSION,'1.6','<')){
				$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$name;
			}else{
				$path = JPATH_PLUGINS .DS.'hikashoppayment'.DS.$method->payment_type.DS.$name;
			}
			if(!file_exists($path)){
				return true;
			}
		}
		require($path);
		return true;
	}
	function onPaymentNotification(&$statuses){
		return false;
	}
	function onPaymentConfiguration(&$element){
		$subtask = JRequest::getCmd('subtask','');
		if($subtask=='ips'){
			$ips = null;
			//echo implode(',',$this->_getIPList($ips));
			exit;
		}else{
			$this->payone = JRequest::getCmd('name','payone_sb');
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
		if(!empty($element->payment_params->ips)){
			$element->payment_params->ips=explode(',',$element->payment_params->ips);
		}
		return true;
	}
}
