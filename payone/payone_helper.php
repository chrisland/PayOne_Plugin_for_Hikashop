<?php
/**
 * @package		Hikashop Payone Payment Plugin for Joomla!
 * @version		1.1.0
 * @author		http://www.chrisland.de
 * @copyright	(C) Christian Marienfeld. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */


class PayOneTransactionStatus {
	var $_key;
	var $_txaction;
	var $_portalid;
	var $_productid;
	var $_aid;
	var $_clearingtype;
	var $_txid;
	var $_reference;
	var $_sequencenumber;
	var $_txtime;
	var $_currency;
	var $_receivable;
	var $_balance;
	var $_customerid;
	var $_reminderlevel;
	var $_failedcause;
	var $_accessid;
	var $_param;
	var $_complete_postdata;
	var $_xcart_action;
	var $_Config_Key;
	var $errortype = "message"; 
	
	function PayOneTransactionStatus($key) {
		$this->_Config_Key = $key;
	}
	function getReference() {
		return $this->_reference;
	}
	function _ThrowError($msg) {
		if ($errortype == "message") {
			echo "ERR=" . $msg;
			exit;
		} else {
			trigger_error(PAYONE_ERRORIDENT . $msg);
		}
	}
	function ImportDataFromRequest() {
		if ($_SERVER['REQUEST_METHOD'] == "POST") {
			$data = $_POST;
		} else {
			$data = $_GET;
		}
		$this->_complete_postdata = print_r($data, true);
		$this->_key				= $data["key"];
		$this->_txaction		= $data["txaction"];
		$this->_portalid		= $data["portalid"];
		$this->_productid		= $data["productid"];
		$this->_aid				= $data["aid"];
		$this->_clearingtype	= $data["clearingtype"];
		$this->_txid			= $data["txid"];
		$this->_reference		= $data["reference"];
		$this->_sequencenumber	= $data["sequencenumber"];
		$this->_txtime			= $data["txtime"];
		$this->_currency		= $data["currency"];
		$this->_receivable		= $data["receivable"];
		$this->_balance			= $data["balance"];
		$this->_customerid		= $data["customerid"];
		$this->_reminderlevel	= $data["reminderlevel"];
		$this->_failedcause		= $data["failedcause"];
		$this->_accessid		= $data["accessid"];
		$this->_param			= $data["param"];
		
		$this->_CheckData();
	}	
	
	function _CheckData() {
		$possible_txaction = array("appointed", "capture", "paid", "underpaid", "cancelation", "refund", "debit", "reminder");
		if (!in_array($this->_txaction, $possible_txaction)) {
			$this->_ThrowError("txaction unknown");
		}
		
		$this->_portalid = intval($this->_portalid);
		$this->_productid = intval($this->_productid);
		$this->_aid = intval($this->_aid);
		
		$possible_clearingtype = array("elv", "cc", "vor", "rec", "sb", "wlt");
		if (!in_array($this->_clearingtype, $possible_clearingtype)) {
			$this->_ThrowError("clearingtype unknown");
		}
				
		$this->_txid = floatval($this->_txid);
		$this->_receivable = floatval($this->_receivable);
		$this->_balance = floatval($this->_balance);
		$this->_userid = intval($this->_userid);
	}
	function AuthorizeTransaction() {
		if (md5($this->_Config_Key) == $this->_key) {
			return true;
		} else {
			return false;
		}
	}
	function IsPaid() {
	    if ($this->_txaction == "paid") {
	        return true;
	    } else {
	        return false;
	    }
	}
	function IsAppointed() {
        if ($this->_txaction == "appointed") {
            return true;
        } else {
            return false;
        }
    }
    function getClearingtype() { 
    	if ($this->_clearingtype) {
    		return $this->_clearingtype;
    	}
    }
}
?>