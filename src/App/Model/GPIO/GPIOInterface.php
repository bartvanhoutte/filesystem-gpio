<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 18:29
 */

namespace App\Model\GPIO;


interface GPIOInterface {
	static function register(int $linuxNumber, string $logic);
	function deregister();
}