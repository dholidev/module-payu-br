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

namespace Dholi\PayUBr\Cron;

use Dholi\Payment\Api\Data\OrderPaymentInterface;
use Dholi\PayU\Api\Data\OrderPaymentPayUInterface;
use Dholi\PayUBr\Gateway\Config\Boleto\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Payment\Repository;
use Psr\Log\LoggerInterface;

class CancelExpiredBoleto {

	private $paymentRepository;

	private $searchCriteriaBuilder;

	private $logger;

	private $config;

	public function __construct(LoggerInterface $logger,
	                            Repository $paymentRepository,
	                            SearchCriteriaBuilder $searchCriteriaBuilder,
	                            Config $config) {
		$this->logger = $logger;
		$this->paymentRepository = $paymentRepository;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->config = $config;
	}

	public function execute() {
		if ($this->config->isCancelable()) {
			$searchCriteria = $this->searchCriteriaBuilder
				->addFilter('method', \Dholi\PayUBr\Model\Ui\Boleto\ConfigProvider::CODE, 'eq')
				->addFilter(OrderPaymentPayUInterface::TRANSACTION_STATE, \Dholi\PayU\Gateway\PayU\Enumeration\PayUTransactionState::PENDING()->key(), 'eq')
				->addFilter(OrderPaymentInterface::CANCEL_AT, date('Y-m-d H:i:s', strtotime('now')), 'lt')
				->create();

			$paymentList = $this->paymentRepository->getList($searchCriteria)->getItems();
			if (count($paymentList)) {
				$processor = ObjectManager::getInstance()->get(\Dholi\PayU\Model\PaymentManagement\Processor::class);

				foreach ($paymentList as $payment) {
					try {
						$this->logger->info(sprintf("%s - Canceling boleto - Order %s", __METHOD__, $payment->getOrder()->getIncrementId()));
						$processor->cancelPayment($payment);
					} catch (\Exception $e) {
						$this->logger->critical(sprintf("%s - Exception: %s", __METHOD__, $e->getMessage()));
						//$this->logger->critical(sprintf("%s - Exception: %s", __METHOD__, $e->getTraceAsString()));
					}
				}
			}
		}
	}
}