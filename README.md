# Filesystem GPIO
## Description

This PHP library allow you to handle GPIO access based on [Sysfs interface](https://www.kernel.org/doc/Documentation/gpio/sysfs.txt) found on Linux systems.
Under the hood, this is using [ReactPHP](https://reactphp.org/) and my fork from excellent [calcinai/rubberneck](https://github.com/calcinai/rubberneck) in order to __capture filesystem events (write...)__.
 
## Installation

`composer req devgiants/filesystem-gpio`

__Important : install `inotify-tools` package (named like this on Debian) in order to have proper event raising system__

## Usage


```php
use Devgiants\FilesystemGPIO\Model\Board\Board;
use Devgiants\FilesystemGPIO\Model\GPIO\Logic;

try {
    // Board is the linking object carrying all GPIOs
	$board = Board::create();	

	// Numbers 203 or 200 are the Linux GPIO number given by the hardware manufaturer
	$gpo  = $board->registerGPO( 203 );

    // You can invert logic (TTL) to make 0V as the high state.				
	$gpi  = $board->registerGPI( 200, Logic::ACTIVE_LOW );	

    // After, you can manipulate
    $gpo->set(); // will output 5V on GPIO 203
    
    
    // ReactPHP use for input interruption
    $gpi->on( GPIO::AFTER_VALUE_CHANGE_EVENT, function () use ( $gpi, $gpo ) {
        // As declared in inverted logic, isSet() will be true if physical voltage is 0V
        if($gpi->isSet()) {
            $gpo->reset()
        }
    });
	
} catch ( \Exception $exception ) {
	echo "{$exception->getCode()} - {$exception->getMessage()}";
}
```

## To come
I2C, UART, SPI... And switch to Python to ensure lower level capabilities (especially for hig speed toggling)