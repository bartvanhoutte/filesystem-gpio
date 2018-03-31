<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:00
 */

namespace App\Model\Board;


use App\Model\GPIO;
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
	 * @param string $direction
	 * @param string $logic
	 *
	 * @return GPIO
	 * @throws BadDirectionException
	 * @throws BadLogicException
	 * @throws \ReflectionException
	 */
	public function registerGPI( int $linuxNumber, string $logic ) {
//		return GPIO::register($linuxNumber, Dire, $logic);
	}

	public function registerGPO( int $linuxNumber, string $logic ) {

	}

	public function deregisterGPIO( GPIO $gpio ) {
		$gpio->deregister();
	}
}