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

class GPI extends GPIO {

	/**
	 * @param int $linuxNumber
	 * @param string $logic
	 *
	 * @return GPI
	 * @throws BadLogicException
	 * @throws \ReflectionException
	 * @throws ExportException
	 */
	public static function register( int $linuxNumber, string $logic ): GPI {
		GPIO::checkLogic($logic);
		$gpi = new GPI($linuxNumber, $logic);
		// Export
		$gpi->export();
		return $gpi;
	}

	/**
	 * @inheritdoc
	 */
	protected function setDirection(): void {
		file_put_contents(
			GPIO::ROOT_FILESYSTEM . GPIO::EXPORT . GPIO::DIRECTION,
			Directions::IN
		);
	}

	/**
	 * @return bool
	 * @throws DirectionMismatchException
	 */
	public function read(): bool {
		//		if ( Directions::IN !== $this->direction ) {
		//			throw new DirectionMismatchException( "This GPIO is not set as " . Directions::IN . ". You can't read anything" );
		//		}
		//
		//		return FALSE;
	}
}