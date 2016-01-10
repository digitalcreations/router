<?php

namespace DC\Router\Exceptions;

use Exception;

class FileNotFoundException extends \Exception {
    public function __construct($file)
    {
        parent::__construct("File not found: " . $file);
    }

}