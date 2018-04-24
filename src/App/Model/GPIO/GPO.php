<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:35
 */

namespace Devgiants\FilesystemGPIO\Model\GPIO;

use Devgiants\FilesystemGPIO\Exception\BadLogicException;
use Devgiants\FilesystemGPIO\Exception\ExportException;

class GPO extends GPIO {

	/**
	 * @var resource The filehandler used for writing GPO state
	 */
	protected $fileHandler;

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
		GPIO::checkLogic( $logic );
		$gpo = new GPO( $linuxNumber, $logic );

		// Features
		$gpo->export();
		$gpo->setDirection();
		$gpo->setLogic( $logic );

		// Reset to be sure of the state at start
		$gpo->reset();

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
		$this->fileHandler = fopen( GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::VALUE, 'r+' );
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
	 * Write the given value on GPO
	 * @param int $value the value to be written
	 */
	public function write( $value ): void {
		if ( $value !== $this->value ) {
			$this->emit( GPIO::BEFORE_VALUE_CHANGE_EVENT, [ $this ] );
			$value = boolval( $value ) ? 1 : 0;
			fwrite( $this->fileHandler, "$value", 1 );
			$this->value = $value;
			$this->emit( GPIO::AFTER_VALUE_CHANGE_EVENT, [ $this ] );
		}
	}

	/**
	 * Set the GPO (put it at LogicalValues::HIGH)
	 */
	public function set(): void {
		$this->write( $this->getHigh() );
	}

	/**
	 * Reset the GPO (put it at LogicalValues::LOW)
	 */
	public function reset(): void {
		$this->write( $this->getLow() );
	}


	/**
	 * Invert the GPO state
	 */
	public function toggleState() {
		if ( $this->isSet() ) {
			$this->write( $this->getLow() );
		} else {
			$this->write( $this->getHigh() );
		}
	}


	/**
	 *
	 */
	public function onEventDetect(): void {
		// Nothing here so far. Events are triggered on write method for better performance
	}
}