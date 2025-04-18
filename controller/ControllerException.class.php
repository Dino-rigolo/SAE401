<?php

/**
 * Custom exception for the controller
 */
class ControllerException extends Exception {
    /**
     * Constructeur
     * 
     * @param string $message The error message
     * @param int $code The HTTP error code
     */
    public function __construct($message, $code = 500) {
        parent::__construct($message, $code);
    }
}