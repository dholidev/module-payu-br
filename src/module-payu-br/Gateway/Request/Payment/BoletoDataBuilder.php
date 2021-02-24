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

namespace Dholi\PayUBr\Gateway\Request\Payment;

use Dholi\PayU\Gateway\PayU\Enumeration\PaymentMethod;
use Dholi\PayU\Gateway\Request\Payment\AuthorizeDataBuilder;
use Dholi\PayUBr\Gateway\Config\Boleto\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

class BoletoDataBuilder implements BuilderInterface {

	const COOKIE = 'cookie';

	const USER_AGENT = 'userAgent';

	const PAYMENT_METHOD = 'paymentMethod';

	const EXPIRATION_DATE = 'expirationDate';

	private $config;

	public function __construct(Config $config) {
		$this->config = $config;
	}

	public function build(array $buildSubject) {
		$paymentDataObject = SubjectReader::readPayment($buildSubject);
		$payment = $paymentDataObject->getPayment();
		$storeId = $payment->getOrder()->getStoreId();

		$expiration = new \DateTime('now +' . $this->config->getExpiration($storeId) . ' day');

		return [AuthorizeDataBuilder::TRANSACTION => [
			self::PAYMENT_METHOD => PaymentMethod::memberByKey('boleto')->getCode(),
			self::COOKIE => $payment->getAdditionalInformation('sessionId'),
			self::USER_AGENT => $payment->getAdditionalInformation('userAgent'),
			self::EXPIRATION_DATE => $expiration->format('Y-m-d\TH:i:s'),
			'extraParameters' => [
				'INSTALLMENTS_NUMBER' => 1
			]
		]];
	}
}