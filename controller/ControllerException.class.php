<?php

/**
 * Custom exception class for handling controller-specific errors
 * 
 * This exception class extends the base PHP Exception class to provide
 * specific error handling for the BikeStore controller operations
 * 
 * @package BikeStore\Exceptions
 * @version 1.0
 */
class ControllerException extends Exception {
    
    /**
     * Constructor for ControllerException
     * 
     * Initializes a new controller exception with a message and HTTP status code
     * 
     * @param string $message The error message to display
     * @param int    $code    The HTTP status code (defaults to 500 Internal Server Error)
     */
    public function __construct($message, $code = 500) {
        parent::__construct($message, $code);
    }
}