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

			public function __construct()
			{
				$this->dividers = self::DEFAULT_DIVIDERS;

				if ( empty( $this->functions ) )
				{
					$this->functions =
					[
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
							assert( count( $args ) === 3 );
							return ( $this->eval( $args[ 2 ] ) === true ) ? $this->eval( $args[ 1 ] ) : $this->eval( $args[ 0 ] );
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
							return $this->$functions[ '&' ]( $args );
						},
						'"' => function( array $args )
						{
							return implode( ' ', array_reverse( $args ) );
						}
					];
				}
			}

			public function parse( string $expression )
			{
				$data = null;
				$current_arg = '';
				$stack = [];
				$chars = str_split( trim( $expression ) );
				foreach ( $chars as $c )
				{
					if ( $c === '(' )
					{
						if ( !is_array( $data ) )
						{
							$data = [];
							$stack[] = &$data;
						}
						else
						{
							$stack[ count( $stack ) - 1 ][] = [];
							$stack[] = &$stack[ count( $stack ) - 1 ][ count( $stack[ count( $stack ) - 1 ] ) - 1 ];
						}
					}
					else if ( $c === ')' )
					{
						if ( !is_array( $data ) )
						{
							// ERROR
						}
						else
						{
							$current_arg = trim( $current_arg );
							if ( !empty( $current_arg ) )
							{
								$stack[ count( $stack ) - 1 ][] = $current_arg;
								$current_arg = '';
							}
							array_pop( $stack );
						}
					}

					else if ( in_array( $c, $this->dividers ) )
					{
						if ( !is_array( $data ) )
						{
							// ERROR
						}
						else if ( empty( $current_arg ) )
						{
							// ERROR
						}
						else
						{
							$stack[ count( $stack ) - 1 ][] = trim( $current_arg );
							$current_arg = '';
						}
					}
					else
					{
						if ( !is_array( $data ) )
						{
							// ERROR
						}
						else
						{
							$current_arg .= $c;
						}
					}
				}
				return $this->eval( $data );
			}

			public function addFunction( string $name, callable $function ) : void
			{
				$this->functions[ $name ] = $function;
			}

			public function resetDividers( $dividers ) : void
			{
				if ( is_array( $dividers ) )
				{
					$this->dividers = $dividers;
				}
				else if ( is_string( $dividers ) )
				{
					$this->dividers = [ $dividers ];
				}
				else
				{
					// Error
				}
			}

			public function addDivider( string $divider ) : void
			{
				$this->dividers[] = $divider;
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
					if ( array_key_exists( $function, $this->functions ) )
					{
						return $this->functions[ $function ]( $data );
					}
					else
					{
						// ERROR
					}
				}
				return $data;
			}

			private function doForEach( array $args, callable $function )
			{
				$answer = array_pop( $args );
				if ( is_array( $answer ) )
				{
					$answer = $this->eval( $answer );
				}
				while ( !empty( $args ) )
				{
					$arg = array_pop( $args );
					if ( is_array( $arg ) )
					{
						$arg = $this->eval( $arg );
					}
					$answer = $function( $answer, $arg );
				}
				return $answer;
			}

			private $functions;
			private $divider;

			private const DEFAULT_DIVIDERS = [ ' ', "\t", "\n" ];
	}
}
