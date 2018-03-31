<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:00
 */

namespace App\Model\Board;


use App\Model\GPIO\GPI;
use App\Model\GPIO\GPO;
use App\Exception\BadDirectionException;
use App\Exception\BadLogicException;

class Board {

	/**
	 * Board constructor.
	 */
	private function __construct() {

	}

	/**
	 *
	 */
	public static function create() {
		return new static();
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
		return GPI::register($linuxNumber, $logic);
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
		return GPO::register($linuxNumber, $logic);
	}

//	/**
//	 * @param \App\Model\Board\GPIO $gpio
//	 */
//	public function deregisterGPIO( GPIO $gpio ) {
//		$gpio->deregister();
//	}
}