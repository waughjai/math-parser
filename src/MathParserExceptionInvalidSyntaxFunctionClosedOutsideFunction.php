<?php

declare( strict_types = 1 );
namespace WaughJ\MathParser
{
    class MathParserExceptionInvalidSyntaxFunctionClosedOutsideFunction extends \Exception
    {
        public function __construct( string $expression )
        {
            parent::__construct( $expression );
        }
    }
}
