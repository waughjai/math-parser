<?php

declare( strict_types = 1 );
namespace WaughJ\MathParser
{
	class MathParser
	{

		//
		//  PUBLIC
		//
		/////////////////////////////////////////////////////////

			public function __construct( ?StringParser $parser = null )
			{
				$this->parser = ( $parser !== null ) ? $parser : new LisphpParser();
				$this->functions = $this->generateBuildInFunctionsList();
			}

			public function parse( string $expression )
			{
				return $this->eval( $this->parser->parse( $expression ) );
			}

			public function addFunction( string $name, callable $function ) : void
			{
				$this->functions[ $name ] = $function;
			}

			public function getParser() : StringParser
			{
				return $this->parser;
			}

			public function changeParser( StringParser $parser ) : void
			{
				$this->parser = $parser;
			}



		//
		//  PRIVATE
		//
		/////////////////////////////////////////////////////////

			private function eval( $data )
			{
				if ( is_array( $data ) )
				{
					$data = array_reverse( $data );
					$function = array_pop( $data );
					var_dump( json_encode( $function ) );
					$function = $this->eval( $function );

					if ( is_string( $function ) )
					{
						if ( array_key_exists( $function, $this->functions ) )
						{
							return $this->functions[ $function ]( $data );
						}
						else
						{
							throw new MathParserExceptionNonExistentFunctionCall( $function );
						}
					}
				}
				return $data;
			}

			private function doForEach( array $args, callable $function )
			{
				$answer = $this->eval( array_pop( $args ) );
				while ( !empty( $args ) )
				{
					$arg = $this->eval( array_pop( $args ) );
					$answer = $function( $answer, $arg );
				}
				return $answer;
			}

			private function generateBuildInFunctionsList() : array
			{
				return [
					'lambda' => function( array $args )
					{
						return '(" BIPPO)';
						if ( empty( $args ) )
						{
							throw new MathParserExceptionInvalidFunctionCall( "Call to function “lambda” given no arguments. Needs at least 2." );
						}
						$argument_names = array_pop( $args );
						if ( empty( $args ) )
						{
							throw new MathParserExceptionInvalidFunctionCall( "Call to function “lambda” given only 1 argument. Needs at least 2." );
						}
						$function_body = array_pop( $args );
						return function( array $args )
						{
							return $this->eval( $function_body );
						};
					},
					'+' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return floatval( $orig ) + floatval( $arg ); } );
					},
					'-' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return floatval( $orig ) - floatval( $arg ); } );
					},
					'*' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return floatval( $orig ) * floatval( $arg ); } );
					},
					'/' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return floatval( $orig ) / floatval( $arg ); } );
					},
					'%' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return floatval( $orig ) % floatval( $arg ); } );
					},
					'=' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return $orig == $arg; } );
					},
					'#=' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return floatval( $orig ) === floatval( $arg ); } );
					},
					'!=' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return $orig != $arg; } );
					},
					'!#=' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return floatval( $orig ) !== floatval( $arg ); } );
					},
					'true' => function( array $args )
					{
						return true;
					},
					'false' => function( array $args )
					{
						return false;
					},
					'ceil' => function( array $args )
					{
						return ceil( floatval( $this->eval( array_pop( $args ) ) ) );
					},
					'if' => function( array $args )
					{
						$arg_count = count( $args );
						switch ( $arg_count )
						{
							case ( 0 ):
							case ( 1 ):
							{
								throw new MathParserExceptionInvalidFunctionCall( "Call to function “if” given only {$arg_count} arguments. Needs at least 2." );
							}
							break;

							case ( 2 ):
							{
								return ( $this->eval( $args[ 1 ] ) === true ) ? $this->eval( $args[ 0 ] ) : null;
							}
							break;

							default: // 3 or greater
							{
								$condition = array_pop( $args );
								$do_on_yes = array_pop( $args );
								$do_on_no = array_pop( $args );
								return ( $this->eval( $condition ) === true ) ? $this->eval( $do_on_yes ) : $this->eval( $do_on_no );
							}
							break;
						}
					},
					'or' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return $orig || $arg; } );
					},
					'&' => function( array $args )
					{
						return $this->doForEach( $args, function( $orig, $arg ) { return $orig && $arg; } );
					},
					'and' => function( array $args )
					{
						return $this->functions[ '&' ]( $args );
					},
					'=or' => function( array $args )
					{
						$comparison = floatval( $this->eval( array_pop( $args ) ) );
						foreach ( $args as $arg )
						{
							if ( $comparison === floatval( $this->eval( $arg ) ) )
							{
								return true;
							}
						}
						return false;
					},
					'=&' => function( array $args )
					{
						$comparison = floatval( $this->eval( array_pop( $args ) ) );
						foreach ( $args as $arg )
						{
							if ( $comparison !== floatval( $this->eval( $arg ) ) )
							{
								return false;
							}
						}
						return true;
					},
					'=and' => function( array $args )
					{
						return $this->functions[ '=&' ]( $args );
					},
					'"' => function( array $args )
					{
						return implode( ' ', array_reverse( $args ) );
					},
					'rand' => function( array $args )
					{
						if ( empty( $args ) )
						{
							return rand();
						}
						$min = array_pop( $args );
						if ( empty( $args ) )
						{
							return rand( $min );
						}
						$max = array_pop( $args );
						return rand( $min, $max );
					},
					'cmp' => function( array $args )
					{
						if ( empty( $args ) )
						{
							return true;
						}
						$function = array_pop( $args );
						if ( empty( $args ) )
						{
							return true;
						}
						$target = array_pop( $args );
						if ( empty( $args ) )
						{
							return true;
						}

						while ( !empty( $args ) )
						{
							if ( array_pop( $args ) >= $target )
							{
								return false;
							}
						}
						return true;
					},
					'>' => function( array $args )
					{
						if ( empty( $args ) )
						{
							return true;
						}

						$target = array_pop( $args );

						if ( empty( $args ) )
						{
							return true;
						}

						while ( !empty( $args ) )
						{
							if ( array_pop( $args ) >= $target )
							{
								return false;
							}
						}
						return true;
					},
					'gt' => function( array $args )
					{
						return $this->functions[ '>' ]( $args );
					},
					'>#' => function( array $args )
					{
						return $this->functions[ '>' ]( array_map( function( $arg ) { return floatval( $arg ); }, $args ) );
					},
					'gtf' => function( array $args )
					{
						return $this->functions[ '>#' ]( $args );
					}
				];
			}

			private $parser;
			private $functions;
	}
}
