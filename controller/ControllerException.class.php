<?php

/**
 * Exception personnalisée pour le contrôleur
 */
class ControllerException extends Exception {
    /**
     * Constructeur
     * 
     * @param string $message Le message d'erreur
     * @param int $code Le code HTTP d'erreur
     */
    public function __construct($message, $code = 500) {
        parent::__construct($message, $code);
    }
}