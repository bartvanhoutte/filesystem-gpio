<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:35
 */

namespace App\Model\GPIO;

use App\Exception\BadLogicException;
use App\Exception\ExportException;

class GPO extends GPIO {

	/**
	 * @param int $linuxNumber
	 * @param string $logic
	 *
	 * @return GPO
	 * @throws BadLogicException
	 * @throws ExportException
	 * @throws \ReflectionException
	 */
	public static function register( int $linuxNumber, string $logic = Logic::ACTIVE_HIGH ): GPO {
		GPIO::checkLogic($logic);
		$gpo = new GPO($linuxNumber, $logic);

		// Features
		$gpo->export();
		$gpo->setDirection();
		$gpo->setLogic($logic);

		return $gpo;
	}

	/**
	 * GPO constructor.
	 *
	 * @param int $linuxNumber
	 * @param string $direction
	 * @param string $logic
	 */
	protected function __construct( int $linuxNumber, string $direction, string $logic = Logic::ACTIVE_HIGH ) {
		parent::__construct( $linuxNumber, $direction, $logic );
	}


	/**
	 * @inheritdoc
	 */
	protected function export(): void {
		parent::export();
		// Open RW
		$this->fileHandler = fopen(GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::VALUE, 'r+');
	}

	/**
	 * @inheritdoc
	 */
	protected function setDirection(): void {
		file_put_contents(
			GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::DIRECTION,
			Directions::OUT
		);
	}

	/**
	 * @param $value
	 */
	public function write( $value ): void {
		if($value !== $this->value) {
			$this->emit(GPIO::BEFORE_VALUE_CHANGE_EVENT, [$this]);
			$value = boolval($value) ? 1 : 0;
			fwrite($this->fileHandler, "$value", 1);
			$this->value = $value;
			$this->emit(GPIO::BEFORE_VALUE_CHANGE_EVENT, [$this]);
		}
	}

	/**
	 *
	 */
	public function onEventDetect() {
//		$this->emit(GPIO::AFTER_VALUE_CHANGE_EVENT);
	}
}