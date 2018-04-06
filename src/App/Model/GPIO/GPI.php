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
	public static function register( int $linuxNumber, string $logic = Logic::ACTIVE_HIGH): GPI {
		GPIO::checkLogic($logic);
		$gpi = new GPI($linuxNumber, $logic);
		// Export
		$gpi->export();

		return $gpi;
	}

	/**
	 * GPI constructor.
	 *
	 * @param int $linuxNumber
	 * @param string $direction
	 * @param string $logic
	 */
	protected function __construct( int $linuxNumber, string $direction, string $logic = Logic::ACTIVE_HIGH ) {
		parent::__construct( $linuxNumber, $direction, $logic );

		// Open R
		$this->fileHandler = fopen(GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $linuxNumber . '/' . GPIO::VALUE, 'r');
	}

	/**
	 * @inheritdoc
	 */
	protected function setDirection(): void {
		file_put_contents(
			GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::DIRECTION,
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