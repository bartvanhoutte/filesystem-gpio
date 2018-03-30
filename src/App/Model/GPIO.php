<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 30/03/18
 * Time: 16:28
 */

namespace App\Model;


use App\Exception\BadDirectionException;
use App\Exception\BadLogicException;
use App\Exception\DirectionMismatchException;
use Symfony\Component\Filesystem\Filesystem;

class GPIO {

	/**
	 * Root filesystem to use for handling GPIO
	 */
	const ROOT_FILESYSTEM = '/sys/class/gpio/';

	const EXPORT = "export";

	const GPIO = "gpio";

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
	 * @var Filesystem $filesystem Filesystem object needed for access/set Linux Filesys
	 */
	protected $filesystem;

	/**
	 * GPIO constructor.
	 *
	 * @param int $linuxNumber
	 * @param string $direction
	 * @param string $logic
	 */
	private function __construct( int $linuxNumber, string $direction, string $logic = Logic::ACTIVE_HIGH ) {
		$this->linuxNumber = $linuxNumber;
		$this->direction   = $direction;
		$this->logic       = $logic;
		$this->filesystem  = new Filesystem();
	}

	/**
	 * @param int $linuxNumber
	 * @param string $direction
	 * @param string $logic
	 *
	 * @return GPIO
	 * @throws BadDirectionException
	 * @throws BadLogicException
	 * @throws \ReflectionException
	 */
	public static function register( int $linuxNumber, string $direction, string $logic ) {
		$directionClass = new \ReflectionClass( Directions::class );
		$logicClass     = new \ReflectionClass( Logic::class );
		// Check direction is valid
		if ( ! ( in_array( $direction, $directionClass->getConstants() ) ) ) {
			throw new BadDirectionException( "Direction can only be :" . implode( ', ', $directionClass->getConstants() ) );
		}

		// Check logic is valid
		if ( ! ( in_array( $logic, $logicClass->getConstants() ) ) ) {
			throw new BadDirectionException( "Logic can only be : " . implode( ', ', $logicClass->getConstants() ) );
		}

		return new self( $linuxNumber, $direction );
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
	 * @return bool
	 */
	public function read(): bool {
		if ( Directions::IN !== $this->direction ) {
			throw new DirectionMismatchException( "This GPIO is not set as " . Directions::IN . ". You can't read anything" );
		}
		// Export
		$this->export();

		return false;
	}

	/**
	 * @param bool $value
	 *
	 * @return bool
	 */
	public function write( $value ): bool {
		if ( Directions::OUT !== $this->direction ) {
			throw new DirectionMismatchException( "This GPIO is not set as " . Directions::OUT . ". You can't output anything" );
		}

		// Export
		$this->export();

		// Write value
		$this->filesystem->dumpFile(
			static::ROOT_FILESYSTEM . static::GPIO . $this->linuxNumber,
			(bool) $value
		);

		return true;
	}

	/**
	 * @return bool
	 */
	private function export(): bool {
		$exportedValues = file_get_contents( static::ROOT_FILESYSTEM . static::EXPORT );
		if ( ! in_array( $this->linuxNumber, explode( "\n", $exportedValues ) ) ) {
			$this->filesystem->appendToFile(
				static::ROOT_FILESYSTEM . static::EXPORT,
				"{$this->linuxNumber}\n"
			);
		}

		return true;
	}
}