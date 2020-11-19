<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:00
 */

namespace Devgiants\FilesystemGPIO\Model\Board;


use Devgiants\FilesystemGPIO\Exception\BadLogicException;
use Devgiants\FilesystemGPIO\Exception\ExportException;
use Devgiants\FilesystemGPIO\Model\GPIO\GPI;
use Devgiants\FilesystemGPIO\Model\GPIO\GPIO;
use Devgiants\FilesystemGPIO\Model\GPIO\GPO;
use Devgiants\FilesystemGPIO\Model\GPIO\Logic;
use Calcinai\Rubberneck\Observer;
use Monolog\Logger;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

class Board {

	/**
	 * @var array[GPIO]
	 */
	private $gpios;

	/**
	 * @var array[GPI]
	 */
	private $gpis;

	/**
	 * @var array[GPO]
	 */
	private $gpos;

	/**
	 * @var LoopInterface
	 */
	private $loop;

    /**
     * @var Logger $logger
     */
	private $logger;

	/**
	 * Board constructor.
	 */
	private function __construct() {
		$this->gpis = [];
		$this->gpos = [];
	}


    /**
     * @param Logger $logger
     * @return static
     */
	public static function create(Logger $logger): Board {
		$board       = new static();
		$board->loop = Factory::create();
		$board->logger = $logger;

		return $board;
	}

	/**
	 * @param int $linuxNumber
	 * @param string $logic
	 *
	 * @return GPI
	 * @throws BadLogicException
	 * @throws ExportException
	 * @throws \ReflectionException
	 */
	public function registerGPI( int $linuxNumber, string $logic = Logic::ACTIVE_HIGH ) {
		$gpi                                   = GPI::register( $linuxNumber, $logic );
		$this->finalize($gpi);

		return $gpi;
	}

	/**
	 * @param int $linuxNumber
	 * @param string $logic
	 *
	 * @return \Devgiants\FilesystemGPIO\Model\GPIO\GPO
	 * @throws \Devgiants\FilesystemGPIO\Exception\BadLogicException
	 * @throws \Devgiants\FilesystemGPIO\Exception\ExportException
	 * @throws \ReflectionException
	 */
	public function registerGPO( int $linuxNumber, string $logic = Logic::ACTIVE_HIGH ) {
		$gpo                                   = GPO::register( $linuxNumber, $logic );
		$this->finalize($gpo);

		return $gpo;
	}

	/**
	 * @param \Devgiants\FilesystemGPIO\Model\GPIO\GPIO $gpio
	 */
	protected function finalize(GPIO $gpio) {
		if($gpio instanceof GPI) {
			$this->gpis[ $gpio->getLinuxNumber() ]  = $gpio;
		} else {
			$this->gpos[ $gpio->getLinuxNumber() ]  = $gpio;
		}

		$this->gpios[ $gpio->getLinuxNumber() ] = $gpio;

		// Start watching
		$gpio->watch($this->loop, $this->logger);
	}

	/**
	 * @return array[GPIO]
	 */
	public function getGpios(): array {
		return $this->gpios;
	}

	/**
	 * @return array[GPI]
	 */
	public function getGpis(): array {
		return $this->gpis;
	}

	/**
	 * @return array[GPO]
	 */
	public function getGpos(): array {
		return $this->gpos;
	}

	/**
	 * @return \React\EventLoop\LoopInterface
	 */
	public function getLoop(): \React\EventLoop\LoopInterface {
		return $this->loop;
	}


	/**
	 * @param GPIO $gpio
	 *
	 * @throws \Exception
	 */
	public function deregisterGPIO( GPIO $gpio ) {
		$gpio->deregister();
	}
}