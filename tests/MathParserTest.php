<?php

use PHPUnit\Framework\TestCase;
use WaughJ\MathParser\MathParser;
use WaughJ\MathParser\MathParserExceptionInvalidFunction;
use WaughJ\MathParser\MathParserExceptionInvalidSyntaxFunctionClosedOutsideFunction;
use WaughJ\MathParser\MathParserExceptionInvalidSyntaxContentOutsideOfFunction;
use WaughJ\MathParser\MathParserExceptionNonExistentFunctionCall;
use WaughJ\MathParser\MathParserExceptionInvalidDividersType;
use WaughJ\MathParser\MathParserExceptionInvalidFunctionCall;
use WaughJ\MathParser\LisphpParser;

class MathParserTest extends TestCase
{
	public function testLambda()
	{
		$math = new MathParser();
		$this->assertEquals( $math->parse( '((lambda (a b) (+ a b)) 1 2)' ), 3 );
	}

}
