<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:00
 */

namespace App\Model\Board;


use App\Model\GPIO\GPI;
use App\Model\GPIO\GPIO;
use App\Model\GPIO\GPO;
use App\Model\GPIO\Logic;
use Calcinai\Rubberneck\Observer;
use React\EventLoop\Factory;

class Board {

	/**
	 * @var array[GPIO]
	 */
	protected $gpios;

	/**
	 * @var array[GPI]
	 */
	protected $gpis;

	/**
	 * @var array[GPO]
	 */
	protected $gpos;

	/**
	 * @var \React\EventLoop\LoopInterface
	 */
	protected $loop;

	/**
	 * Board constructor.
	 */
	private function __construct() {
		$this->gpis = [];
		$this->gpos = [];
	}


	/**
	 * @return static
	 */
	public static function create(): Board {
		$board       = new static();
		$board->loop = Factory::create();

		return $board;
	}

	/**
	 * @param int $linuxNumber
	 * @param string $logic
	 *
	 * @return \App\Model\GPIO\GPI
	 * @throws \App\Exception\BadLogicException
	 * @throws \App\Exception\ExportException
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
	 * @return \App\Model\GPIO\GPO
	 * @throws \App\Exception\BadLogicException
	 * @throws \App\Exception\ExportException
	 * @throws \ReflectionException
	 */
	public function registerGPO( int $linuxNumber, string $logic = Logic::ACTIVE_HIGH ) {
		$gpo                                   = GPO::register( $linuxNumber, $logic );
		$this->finalize($gpo);

		return $gpo;
	}

	/**
	 * @param \App\Model\GPIO\GPIO $gpio
	 */
	protected function finalize(GPIO $gpio) {
		if($gpio instanceof GPI) {
			$this->gpis[ $gpio->getLinuxNumber() ]  = $gpio;
		} else {
			$this->gpos[ $gpio->getLinuxNumber() ]  = $gpio;
		}

		$this->gpios[ $gpio->getLinuxNumber() ] = $gpio;

		// Start watching
		$gpio->watch($this->loop);
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