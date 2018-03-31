<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 30/03/18
 * Time: 16:28
 */

namespace App\Model\GPIO;


use App\Exception\BadDirectionException;
use App\Exception\BadLogicException;
use App\Exception\DirectionMismatchException;
use App\Exception\ExportException;

abstract class GPIO implements GPIOInterface {

	/**
	 * Root filesystem to use for handling GPIO
	 */
	const ROOT_FILESYSTEM = '/sys/class/gpio/';

	const EXPORT = 'export';
	const UNEXPORT = 'unexport';

	const DIRECTION = 'direction';
	const GPIO = 'gpio';
	const VALUE = 'value';
	const ACTIVE_LOW = 'active_low';

	/**
	 * @var int $linuxNumber the GPIO number given by linux
	 */
	protected $linuxNumber;

	/**
	 * @var string $direction the GPIO direction (in or out)
	 */
	protected $direction;

	/**
	 * @var string $logic wether GPIO is active high or low regarding to value
	 */
	protected $logic;

	/**
	 * @var boolean $isExported
	 */
	protected $isExported = FALSE;

	/**
	 * GPIO constructor.
	 *
	 * @param int $linuxNumber
	 * @param string $direction
	 * @param string $logic
	 */
	protected function __construct( int $linuxNumber, string $direction, string $logic = Logic::ACTIVE_HIGH ) {
		$this->linuxNumber = $linuxNumber;
		$this->direction   = $direction;
		$this->logic       = $logic;
	}

	/**
	 * @throws \Exception
	 */
	public function deregister(): void {
		throw new \Exception( 'To implement' );
	}

	/**
	 * @return int
	 */
	public function getLinuxNumber(): int {
		return $this->linuxNumber;
	}

	/**
	 * @return string
	 */
	public function getDirection(): string {
		return $this->direction;
	}

	/**
	 * Set direction for this GPIO
	 * @return void
	 */
	abstract protected function setDirection(): void;

	/**
	 * Set logic for HIGH level
	 * @param string $logic
	 */
	protected function setLogic( string $logic): void {
		file_put_contents(
			GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::ACTIVE_LOW,
			GPIO::ACTIVE_LOW === $logic ? 1 : 0
		);
	}

	/**
	 * @throws ExportException
	 */
	protected function export(): void {

		if(!file_exists(static::ROOT_FILESYSTEM . static::GPIO . $this->linuxNumber)) {
			file_put_contents(
				static::ROOT_FILESYSTEM . static::EXPORT,
				"{$this->linuxNumber}"
			);
		}

		// Check
		if(!file_exists(static::ROOT_FILESYSTEM . static::GPIO . $this->linuxNumber)) {
			throw new ExportException("Problem with export. GPIO {$this->linuxNumber} not found");
		}
		$this->isExported = true;
	}

	/**
	 * @param $logic
	 *
	 * @throws BadLogicException
	 * @throws \ReflectionException
	 */
	public static function checkLogic($logic): void {
		$logicClass     = new \ReflectionClass( Logic::class );
		// Check logic is valid
		if ( ! ( in_array( $logic, $logicClass->getConstants() ) ) ) {
			throw new BadLogicException( "Logic can only be : " . implode( ', ', $logicClass->getConstants() ) );
		}
	}
}