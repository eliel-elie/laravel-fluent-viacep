<?php

namespace ViaCep\Exceptions;

use Exception;

/**
 * Base exception for ViaCEP
 */
class ViaCepException extends Exception {}

/**
 * Invalid CEP format exception
 */
class InvalidCepException extends ViaCepException {}

/**
 * CEP not found in database exception
 */
class CepNotFoundException extends ViaCepException {}

/**
 * Unsupported response format exception
 */
class UnsupportedFormatException extends ViaCepException {}
