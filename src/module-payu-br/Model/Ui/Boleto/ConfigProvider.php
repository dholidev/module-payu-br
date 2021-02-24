<?php
/**
* 
* PayU Brasil para Magento 2
* 
* @category     Dholi
* @package      Modulo PayUBr
* @copyright    Copyright (c) 2021 dholi (https://www.dholi.dev)
* @version      1.1.0
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Dholi\PayUBr\Model\Ui\Boleto;

use Dholi\PayUBr\Gateway\Config\Boleto\Config as BoletoConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Session\SessionManagerInterface;

class ConfigProvider implements ConfigProviderInterface {

	const CODE = 'dholi_payments_payu_boleto';

	private $config;

	private $session;

	protected $escaper;

	public function __construct(SessionManagerInterface $session,
	                            Escaper $escaper,
	                            BoletoConfig $boletoConfig) {
		$this->session = $session;
		$this->escaper = $escaper;
		$this->config = $boletoConfig;
	}

	public function getConfig() {
		$storeId = $this->session->getStoreId();

		$payment = [];
		$isActive = $this->config->isActive($storeId);
		if ($isActive) {
			$payment = [
				self::CODE => [
					'isActive' => $isActive,
					'instructions' => $this->getInstructions($storeId)
				]
			];
		}

		return [
			'payment' => $payment
		];
	}

	protected function getInstructions($storeId): string {
		return $this->escaper->escapeHtml($this->config->getInstructions($storeId));
	}
}