<?php
/**
* 
* PayU Brasil para Magento 2
* 
* @category     Dholi
* @package      Modulo PayUBr
* @copyright    Copyright (c) 2020 dholi (https://www.dholi.dev)
* @version      1.0.0
* @license      https://opensource.org/licenses/OSL-3.0
* @license      https://opensource.org/licenses/AFL-3.0
*
*/
declare(strict_types=1);

namespace Dholi\PayUBr\Gateway\Response\Payment;

use Dholi\PayU\Api\Data\OrderPaymentPayUInterface;
use Dholi\PayU\Gateway\PayU\Enumeration\PayUTransactionState;
use Dholi\PayUBr\Gateway\Config\Boleto\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;

class BoletoAuthorisationDetailsHandler implements HandlerInterface {

	private $config;

	public function __construct(Config $config) {
		$this->config = $config;
	}

	/**
	 * @inheritdoc
	 */
	public function handle(array $handlingSubject, array $response) {
		$paymentDataObject = SubjectReader::readPayment($handlingSubject);
		$transaction = $response[0]['transaction']->transactionResponse;

		$transactionState = PayUTransactionState::memberByKey($transaction->state);

		$payment = $paymentDataObject->getPayment();
		$payment->setTransactionId($transaction->transactionId);
		$payment->setPayuTransactionState($transactionState->key());

		$payment->setLastTransId($transaction->transactionId);
		$payment->setAdditionalInformation('payuOrderId', $transaction->orderId);
		$payment->setAdditionalInformation('transactionId', $transaction->transactionId);
		$payment->setAdditionalInformation('paymentLink', $transaction->extraParameters->URL_BOLETO_BANCARIO);
		$payment->setAdditionalInformation('pdfLink', $transaction->extraParameters->URL_PAYMENT_RECEIPT_PDF);
		$payment->setAdditionalInformation('barCode', $transaction->extraParameters->BAR_CODE);

		/**
		 * Limpa dados do Cartão, se houver
		 */
		$payment->addData(
			[
				OrderPaymentPayUInterface::CC_NUMBER_ENC => null,
				OrderPaymentPayUInterface::CC_CID_ENC => null,
				OrderPaymentInterface::CC_TYPE => null,
				OrderPaymentInterface::CC_OWNER => null,
				OrderPaymentInterface::CC_LAST_4 => null,
				OrderPaymentInterface::CC_EXP_MONTH => null,
				OrderPaymentInterface::CC_EXP_YEAR => null
			]
		);

		try {
			$storeId = $payment->getOrder()->getStoreId();
			$today = new \DateTime();
			$todayFmt = $today->format('Y-m-d\TH:i:s');
			$dayOfWeek = date("w", strtotime($todayFmt));
			$incrementDays = null;

			switch ($dayOfWeek) {
				case 4:
					$incrementDays = $this->config->getCancelOnThursday($storeId);
					break;

				case 5:
					$incrementDays = $this->config->getCancelOnFriday($storeId);
					break;

				case 6:
					$incrementDays = $this->config->getCancelOnSaturday($storeId);
					break;

				default:
					$incrementDays = $this->config->getCancelOnSunday($storeId);
					break;
			}
			$totalDays = $this->config->getExpiration($storeId) + $incrementDays;
			$cancellationDate = strftime("%Y-%m-%d %H:%M:%S", strtotime("$todayFmt +$totalDays day"));
			$payment->setCancelAt($cancellationDate);
		} catch (\Exception $e) {

		}
		$payment->setIsTransactionPending(true);
		$payment->setIsTransactionClosed(false);
		$payment->setShouldCloseParentTransaction(false);

		$payment->getOrder()
			->setState(Order::STATE_NEW)
			->setStatus(Order::STATE_PENDING_PAYMENT)
			->setCanSendNewEmailFlag(true);
	}
}