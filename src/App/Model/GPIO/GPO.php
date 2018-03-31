<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:35
 */

namespace App\Model\GPIO;

use App\Exception\DirectionMismatchException;
use App\Exception\BadLogicException;
use App\Exception\ExportException;
use App\Model\GPIO\GPI;

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
	public static function register( int $linuxNumber, string $logic ): GPO {
		GPIO::checkLogic($logic);
		$gpo = new GPO($linuxNumber, $logic);
		// Export
		$gpo->export();

		// Set direction
		$gpo->setDirection();

		return $gpo;
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
		file_put_contents(
			GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . GPIO::VALUE,
					(bool) $value
		);
	}
}