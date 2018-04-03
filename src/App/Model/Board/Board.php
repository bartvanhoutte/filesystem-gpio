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

	}

	/**
	 *
	 */
	public static function create() {
		$board = new static();
		$board->loop = Factory::create();
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
	public function registerGPI( int $linuxNumber, string $logic ) {
		$gpi = GPI::register($linuxNumber, $logic);
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
	public function registerGPO( int $linuxNumber, string $logic ) {
		$gpo =  GPO::register($linuxNumber, $logic);
		$this->gpos[] = $gpo;
		return $gpo;
	}

	/**
	 *
	 */
	public function monitor() {
		array_map(function(GPIO $gpio){
			$gpio->monitor($this->loop);
		}, array_merge($this->gpis, $this->gpos));
	}

//	/**
//	 * @param \App\Model\Board\GPIO $gpio
//	 */
//	public function deregisterGPIO( GPIO $gpio ) {
//		$gpio->deregister();
//	}
}