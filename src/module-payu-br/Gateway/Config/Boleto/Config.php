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

namespace Dholi\PayUBr\Gateway\Config\Boleto;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

class Config extends \Magento\Payment\Gateway\Config\Config {

	const KEY_ACTIVE = 'active';

	const KEY_INSTRUCTIONS = 'instructions';

	const EXPIRATION = 'expiration';

	const CANCELABLE = 'cancelable';

	const CANCEL_ON_THURSDAY = 'cancel_on_thursday';

	const CANCEL_ON_FRIDAY = 'cancel_on_friday';

	const CANCEL_ON_SATURDAY = 'cancel_on_saturday';

	const CANCEL_ON_SUNDAY = 'cancel_on_sunday';

	private $serializer;

	public function __construct(ScopeConfigInterface $scopeConfig,
	                            $methodCode = null,
	                            $pathPattern = self::DEFAULT_PATH_PATTERN,
	                            Json $serializer = null) {
		parent::__construct($scopeConfig, $methodCode, $pathPattern);
		$this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
	}

	public function isActive($storeId = null) {
		return (bool)$this->getValue(self::KEY_ACTIVE, $storeId);
	}

	public function getInstructions($storeId = null) {
		return $this->getValue(self::KEY_INSTRUCTIONS, $storeId);
	}

	public function isCancelable($storeId = null) {
		return (bool)$this->getValue(self::CANCELABLE, $storeId);
	}

	public function getCancelOnThursday($storeId = null) {
		return (int)trim($this->getValue(self::CANCEL_ON_THURSDAY, $storeId));
	}

	public function getCancelOnFriday($storeId = null) {
		return (int)trim($this->getValue(self::CANCEL_ON_FRIDAY, $storeId));
	}

	public function getCancelOnSaturday($storeId = null) {
		return (int)trim($this->getValue(self::CANCEL_ON_SATURDAY, $storeId));
	}

	public function getCancelOnSunday($storeId = null) {
		return (int)trim($this->getValue(self::CANCEL_ON_SUNDAY, $storeId));
	}

	public function getExpiration($storeId = null) {
		return (int)trim($this->getValue(self::EXPIRATION, $storeId));
	}
}