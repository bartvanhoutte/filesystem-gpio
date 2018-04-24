<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 31/03/18
 * Time: 15:35
 */

namespace Devgiants\FilesystemGPIO\Model\GPIO;


use Devgiants\FilesystemGPIO\Exception\BadLogicException;
use Devgiants\FilesystemGPIO\Exception\ExportException;

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
	 * @return int
	 */
	public function read() {
		return ($this->value = (int)file_get_contents(GPIO::ROOT_FILESYSTEM . GPIO::GPIO . $this->linuxNumber . '/' . GPIO::VALUE));
	}

	/**
	 *
	 */
	public function onEventDetect() {
		// Read value
		$this->read();
		$this->emit(GPIO::AFTER_VALUE_CHANGE_EVENT);
	}

}