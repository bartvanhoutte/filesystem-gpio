<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 30/03/18
 * Time: 16:28
 */

namespace Devgiants\FilesystemGPIO\Model\GPIO;


use Devgiants\FilesystemGPIO\Exception\BadLogicException;
use Devgiants\FilesystemGPIO\Exception\ExportException;
use Calcinai\Rubberneck\Observer;
use Evenement\EventEmitterTrait;
use React\EventLoop\LoopInterface;
use ReactFilesystemMonitor\INotifyProcessMonitor;

abstract class GPIO implements GPIOInterface {

	use EventEmitterTrait;

	/**
	 * Root filesystem to use for handling GPIO
	 */
	const ROOT_FILESYSTEM = '/sys/class/gpio/';

	const EXPORT = 'export';

	const UNEXPORT = 'unexport';

	const DIRECTION = 'direction';

	const EDGE = 'edge';

	const GPIO = 'gpio';

	const VALUE = 'value';

	const ACTIVE_LOW = 'active_low';

	const BEFORE_VALUE_CHANGE_EVENT = 'before_value_change_event';

	const AFTER_VALUE_CHANGE_EVENT = 'after_value_change_event';

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
	 * @var int
	 */
	protected $value;


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
	 * @param $logic
	 *
	 * @throws BadLogicException
	 * @throws \ReflectionException
	 */
	public static function checkLogic( $logic ): void {
		$logicClass = new \ReflectionClass( Logic::class );
		// Check logic is valid
		if ( ! ( in_array( $logic, $logicClass->getConstants() ) ) ) {
			throw new BadLogicException( "Logic can only be : " . implode( ', ', $logicClass->getConstants() ) );
		}
	}

	/**
	 * @throws \Exception
	 */
	public function deregister(): void {

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
	 *
	 * @return void
	 */
	abstract protected function setDirection(): void;

	public function watch( LoopInterface $loop ) {
		$observer = new Observer( $loop );
		$observer->on( Observer::EVENT_MODIFY, [ $this, 'onEventDetect' ] );
		$observer->watch( GPIO::ROOT_FILESYSTEM . GPIO::GPIO . "{$this->linuxNumber}/" . GPIO::VALUE );
	}

	/**
	 * @return string
	 */
	public function getLogic(): string {
		return $this->logic;
	}

	/**
	 * Set logic for HIGH level
	 *
	 * @param string $logic
	 */
	protected function setLogic( string $logic ): void {
		file_put_contents(
			GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::ACTIVE_LOW,
			GPIO::ACTIVE_LOW === $logic ? 1 : 0
		);
	}

	/**
	 * @return int
	 */
	public function getValue(): int {
		return $this->value;
	}


	abstract public function onEventDetect();



	/**
	 * Export GPIO to make it accessible in program userspace
	 *
	 * @throws ExportException
	 */
	protected function export(): void {

		// Unexport first
		$this->unexport();

		// TODO review like PHPi
		file_put_contents(
			static::ROOT_FILESYSTEM . static::EXPORT,
			"{$this->linuxNumber}"
		);

		// Check
		if ( ! file_exists( static::ROOT_FILESYSTEM . static::GPIO . $this->linuxNumber ) ) {
			throw new ExportException( "Problem with export. GPIO {$this->linuxNumber} not found" );
		}
		$this->isExported = TRUE;
	}

	/**
	 * Unexport GPIO to remove it from userspace
	 */
	protected function unexport(): void {
		file_put_contents(
			static::ROOT_FILESYSTEM . static::UNEXPORT,
			"{$this->linuxNumber}"
		);
	}

	/**
	 * @return int
	 */
	protected function getHigh(): int {
		return (($this->logic === static::ACTIVE_LOW) ? LogicalValues::LOW : LogicalValues::HIGH);
	}

	/**
	 * @return int
	 */
	protected function getLow(): int {
		return (($this->logic === static::ACTIVE_LOW) ? LogicalValues::HIGH : LogicalValues::LOW);
	}

	/**
	 * @return bool
	 */
	public function isSet(): bool {
		return ($this->value === $this->getHigh());
	}

	/**
	 * @return bool
	 */
	public function isReset(): bool {
		return ($this->value === $this->getLow());
	}
}