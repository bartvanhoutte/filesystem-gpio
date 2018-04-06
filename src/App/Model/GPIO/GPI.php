<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:35
 */

namespace App\Model\GPIO;


use App\Exception\BadLogicException;
use App\Exception\ExportException;

class GPI extends GPIO {

	/**
	 * @param int $linuxNumber
	 * @param string $logic
	 * @param string $edge
	 *
	 * @return GPI
	 * @throws BadLogicException
	 * @throws \ReflectionException
	 * @throws ExportException
	 */
	public static function register( int $linuxNumber, string $logic = Logic::ACTIVE_HIGH, string $edge = Edge::BOTH): GPI {
		GPIO::checkLogic($logic);
		$gpi = new GPI($linuxNumber, $logic);


		$gpi->export();
		$gpi->setDirection();
		$gpi->setEdge($edge);
		$gpi->setLogic($logic);

		return $gpi;
	}

	/**
	 * @inheritdoc
	 */
	protected function setDirection(): void {
		file_put_contents(
			GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::DIRECTION,
			Directions::IN
		);
	}

	/**
	 * @param string $edge
	 */
	protected function setEdge(string $edge): void {
		file_put_contents(
			GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::EDGE,
			$edge
		);
	}

	/**
	 * @return bool
	 * @throws DirectionMismatchException
	 */
	public function read(): bool {
		return (bool)file_get_contents(GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::VALUE);
	}
}