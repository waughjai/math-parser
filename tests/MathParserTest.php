<?php

use PHPUnit\Framework\TestCase;
use WaughJ\MathParser\MathParser;

class MathParserTest extends TestCase
{
	public function testAddition()
	{
		$math = new MathParser();
		$this->assertEquals( 2+2, $math->parse( '(+ 2 2)' ) );
		$this->assertEquals( 1+2, $math->parse( '(+ 1 2)' ) );
		$this->assertEquals( 1+2+4+6, $math->parse( '(+ 1 2 4 6)' ) );
	}

	public function testSubtraction()
	{
		$math = new MathParser();
		$this->assertEquals( 4-2, $math->parse( '(- 4 2)' ) );
		$this->assertEquals( 6-3-7, $math->parse( '(- 6 3 7)' ) );
	}

	public function testMultiplication()
	{
		$math = new MathParser();
		$this->assertEquals( 4*2, $math->parse( '(* 4 2)' ) );
		$this->assertEquals( 6*3*7, $math->parse( '(* 6 3 7)' ) );
	}

	public function testDivision()
	{
		$math = new MathParser();
		$this->assertEquals( 4/2, $math->parse( '(/ 4 2)' ) );
		$this->assertEquals( 6/3/7, $math->parse( '(/ 6 3 7)' ) );
	}

	public function testRemainder()
	{
		$math = new MathParser();
		$this->assertEquals( 6 % 5, $math->parse( '(% 6 5)' ) );
	}

	public function testFunctionCreation()
	{
		$math = new MathParser();
		$math->addFunction
		(
			'double',
			function( array $args )
			{
				return $args[ 0 ] * 2;
			}
		);
		$this->assertEquals( 2*2, $math->parse( '(double 2)' ) );
	}

	public function testCeiling()
	{
		$math = new MathParser();
		$this->assertEquals( ceil( 6 / 5 ), $math->parse( '(ceil (/ 6 5 ))' ) );
	}

	public function testQuote()
	{
		$math = new MathParser();
		$this->assertEquals( "¡BAM! ¡LOOK @ THAT BACON SIZZLE!", $math->parse( '(" ¡BAM! ¡LOOK @ THAT BACON SIZZLE!)' ));
	}

	public function testLayers()
	{
		$math = new MathParser();
		$this->assertEquals( 4+(8-3)+(2*2), $math->parse( '(+ 4 (- 8 3) (* 2 2))' ) );
	}

	public function testEquality()
	{
		$math = new MathParser();
		$this->assertTrue( $math->parse( '(= 2 (+ 1 1))' ) );
		$this->assertTrue( $math->parse( '(#= 2 (+ 1 1))' ) );
		$this->assertFalse( $math->parse( '(!= 2 (+ 1 1))' ) );
		$this->assertFalse( $math->parse( '(!#= 2 (+ 1 1))' ) );
	}

	public function testIf()
	{
		$math = new MathParser();
		$this->assertEquals
		(
			(
				( 2 == 2 )
				? "equal!"
				: "not equal..."
			),
			$math->parse('(if ( #= 2 2 ) (" equal!) (" not equal...))')
		);
	}

	public function testIfWithJust1Argument()
	{
		$math = new MathParser();
		$this->assertTrue( $math->parse('(if ( #= 2 2 ))') );
		$this->assertFalse( $math->parse('(if ( #= 2 3 ))') );
	}

	public function testIfWith2Arguments()
	{
		$math = new MathParser();
		$this->assertEquals( null, $math->parse('(if (false) (" true!))') );
		$this->assertEquals( "true!", $math->parse('(if (true) (" true!))') );
	}

	public function testIfWithMoreThan3Arguments()
	{
		$math = new MathParser();
		$this->assertEquals
		(
			(
				( 2 == 2 )
				? "equal!"
				: "not equal..."
			),
			$math->parse('(if ( #= 2 2 ) (" equal!) (" not equal...) 2 (" bleeugh))')
		);
	}

	public function testOr()
	{
		$math = new MathParser();
		$this->assertTrue( $math->parse( '(or (true) (true))' ));
		$this->assertTrue( $math->parse( '(or (true) (false) (false))' ));
		$this->assertFalse( $math->parse( '(or (false) (false) (false))' ));
	}

	public function testAnd()
	{
		$math = new MathParser();
		$this->assertTrue( $math->parse( '(& (true) (true))' ));
		$this->assertFalse( $math->parse( '(& (true) (false) (false))' ));
		$this->assertFalse( $math->parse( '(& (false) (false) (false))' ));
		$this->assertTrue( $math->parse( '(and (true) (true))' ));
		$this->assertFalse( $math->parse( '(and (true) (false) (false))' ));
		$this->assertFalse( $math->parse( '(and (false) (false) (false))' ));
	}

	public function testComplexIf()
	{
		$math = new MathParser();
		$n = 9;
		$z = 5.60;
		$math->addFunction
		(
			'n',
			function( array $args ) use ( $n )
			{
				return $n;
			}
		);
		$math->addFunction
		(
			'z',
			function( array $args ) use ( $z )
			{
				return $z;
			}
		);

		$this->assertEquals( ceil( $n / 6 ) * $z, $math->parse('(* (ceil (/ (n) 6)) (z))') );

		$this->assertEquals
		(
			(
				( $n % 6 === 1 || $n % 6 === 2 )
				? ( ceil( $n / 6 ) - 1 ) * $z
				: ceil( $n / 6 ) * $z
			),
			$math->parse
			('
				(
					if
					(
						or
						(
							#=
							( % (n) 6 ) 1)
							(
								#=
								( % (n) 6 )
								2
							)
						)
					(
						*
						(
							-
							( ceil ( / (n) 6 ) )
							1
						)
						(z)
					)
					(
						*
						(
							ceil
							( / (n) 6 )
						)
						(z)
					)
				)
			')
		);
	}

	public function testDifferentDividers()
	{
		$math = new MathParser();
		$math->resetDividers( '|' );
		$this->assertEquals( 4, $math->parse( '(+|2|2)' ) );
		$math->addDivider( ',' );
		$this->assertEquals( 2, $math->parse( '(/,8|4)' ) );
	}

	public function testQuoteWithDifferentDividers()
	{
		$math = new MathParser();
		$this->assertEquals( "¡BAM! ¡LOOK @ THAT BACON SIZZLE!", $math->parse( '(" ¡BAM! ¡LOOK @ THAT BACON SIZZLE!)' ));
		$math->resetDividers( ',' );
		$this->assertEquals( "¡BAM! ¡LOOK @ THAT BACON SIZZLE!", $math->parse( '(", ¡BAM! ¡LOOK @ THAT BACON SIZZLE!)' ));
		$this->assertEquals( "¡BAM! ¡LOOK @ THAT BACON SIZZLE!", $math->parse( '(", ¡BAM!, ¡LOOK, @, THAT, BACON, SIZZLE!)' ));
		// TODO: ¿Do I want (", ¡BAM!, ¡LOOK, @, THAT, BACON, SIZZLE!) for parser with "," divider to keep commas or no?
	}

	public function testEqualOr()
	{
		$math = new MathParser();
		$this->assertTrue( $math->parse( '(=or 1 1 2 3 4)' ) );
		$this->assertTrue( $math->parse( '(=or 2 1 2 3 4)' ) );
		$this->assertTrue( $math->parse( '(=or 3 1 2 3 4)' ) );
		$this->assertTrue( $math->parse( '(=or 4 1 2 3 4)' ) );
		$this->assertFalse( $math->parse( '(=or 5 1 2 3 4)' ) );
		$this->assertTrue( $math->parse( '(=or (/ 12 4) 1 2 3 4)' ) );
		$this->assertTrue( $math->parse( '(=or (/ 12 4) 1 2 (+ 1 2) 4)' ) );
	}

	public function testEqualAnd()
	{
		$math = new MathParser();
		$this->assertTrue( $math->parse( '(=& 2 2 (/ 4 2) (- 12 4 5 1))' ) );
		$this->assertFalse( $math->parse( '(=& 2 2 (/ 4 2) (- 12 4 5 1) 5)' ) );
		$this->assertTrue( $math->parse( '(=and 2 2 (/ 4 2) (- 12 4 5 1))' ) );
		$this->assertFalse( $math->parse( '(=and 2 2 (/ 4 2) (- 12 4 5 1) 5)' ) );
	}
}
