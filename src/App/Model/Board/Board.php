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
		$gpi          = GPI::register( $linuxNumber, $logic );
		$this->gpis[] = $gpi;

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
		$gpo          = GPO::register( $linuxNumber, $logic );
		$this->gpos[] = $gpo;

		return $gpo;
	}

	/**
	 * @return array
	 */
	public function getGpis(): array {
		return $this->gpis;
	}

	/**
	 * @return array
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
	 *
	 */
	public function watch() {
		$observer = new Observer($this->loop);
		$observer->on(Observer::EVENT_MODIFY, [$this, 'eventDetect']);
		$observer->watch(GPIO::ROOT_FILESYSTEM . GPIO::GPIO . '*/' . GPIO::VALUE);
	}

	protected function eventDetect($file) {
		echo "change on $file";
	}

//	/**
//	 * @param \App\Model\Board\GPIO $gpio
//	 */
//	public function deregisterGPIO( GPIO $gpio ) {
//		$gpio->deregister();
//	}
}