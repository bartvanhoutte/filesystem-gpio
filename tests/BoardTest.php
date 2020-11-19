<?php


namespace Devgiants\FilesystemGPIO\Tests;


use Devgiants\FilesystemGPIO\Model\Board\Board;
use Devgiants\FilesystemGPIO\Model\GPIO\GPIO;
use Devgiants\FilesystemGPIO\Model\GPIO\Logic;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{
    /**
     * @var Board $board
     */
    private $board;

    public function setUp(): void
    {

        $log = new Logger('filesystem_gpio');
        $log->pushHandler(
            new RotatingFileHandler(
                "/tmp/filesystem_gpio.log",
                Logger::DEBUG)
        );

        // Board is the linking object carrying all GPIOs
        $this->board = Board::create($log);
        parent::setUp();
    }

    public function test()
    {

        // Numbers 203 or 200 are the Linux GPIO number given by the hardware manufaturer
        $gpo = $this->board->registerGPO(20003);

        // You can invert logic (TTL) to make 0V as the high state.
//        $gpi = $this->board->registerGPI(20003, Logic::ACTIVE_LOW);

        // After, you can manipulate
        $gpo->set(); // will output 5V on GPIO 203


//            // ReactPHP use for input interruption
//            $gpi->on( GPIO::AFTER_VALUE_CHANGE_EVENT, function () use ( $gpi, $gpo ) {
//                // As declared in inverted logic, isSet() will be true if physical voltage is 0V
//                if($gpi->isSet()) {
//                    $gpo->reset()
//        }
//            });

    }
}