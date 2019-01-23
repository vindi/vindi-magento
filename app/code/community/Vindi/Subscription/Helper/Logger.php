<?php

class Vindi_Subscription_Helper_Logger
{
	/**
	 * Grava o histórico de Webhooks recebidos
	 *
	 * @param string   $message, int|null $level
	 */
	public function log($message, $level = null)
	{
		Mage::log($message, $level, 'vindi_webhooks.log');

		switch ($level) {
		case 4:
			http_response_code(422);
			return false;
			break;
		case 5:
			return false;
			break;
		default:
			return true;
			break;
		}
	}
}
