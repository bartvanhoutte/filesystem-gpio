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
		$gpi          = GPI::register( $linuxNumber, $logic );
		$this->gpis[$gpi->getLinuxNumber()] = $gpi;
		$this->gpios[$gpi->getLinuxNumber()] = $gpi;

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
		$this->gpos[$gpo->getLinuxNumber()] = $gpo;
		$this->gpios[$gpo->getLinuxNumber()] = $gpo;

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

	public function eventDetect($file) {
		list($gpioNumber) = sscanf($file, GPIO::ROOT_FILESYSTEM . GPIO::GPIO . '/%i/');

		// TODO add check on array key existence
		$gpio = $this->gpios[$gpioNumber];

		// TODO make checks on maximum speed regarding to what is done here : https://github.com/calcinai/phpi/blob/master/src/PHPi/Pin/EdgeDetector/Rubberneck.php#L65
		$gpio->toggleState();
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