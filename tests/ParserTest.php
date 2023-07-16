<?php /** @noinspection DuplicatedCode */

namespace Tests;

use Exception;
use Nemorize\Fexpr\Parser;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    /**
     * @var array<array{input: string, expectException: bool, expectJson: string}>
     */
    private array $testCases = [
        [ '> 1', true, [] ],
        [ 'a >', true, [] ],
        [ 'a > >', true, [] ],
        [ 'a > %', true, [] ],
        [ 'a ! 1', true, [] ],
        [ 'a + 1', true, [] ],
        [ '1 - 1', true, [] ],
        [ '1 + 1', true, [] ],
        [ '> a 1', true, [] ],
        [ 'a || 1', true, [] ],
        [ 'a && 1', true, [] ],
        [ 'test > 1 &&', true, [] ],
        [ '|| test = 1', true, [] ],
        [ 'test = 1 && ||', true, [] ],
        [ 'test = 1 && a', true, [] ],
        [ 'test = 1 && "a"', true, [] ],
        [ 'test = 1 a', true, [] ],
        [ 'test = 1 "a"', true, [] ],
        [ 'test = 1@test', true, [] ],
        [ 'test = .@test', true, [] ],

        [ 'test = "demo\'', true, [] ],
        [ 'test = \'demo"', true, [] ],
        [ 'test = \'demo\'"', true, [] ],
        [ 'test = \'demo\'\'', true, [] ],
        [ 'test = "demo"\'', true, [] ],
        [ 'test = "demo""', true, [] ],
        [ 'test = ""demo""', true, [] ],
        [ 'test = \'\'demo\'\'', true, [] ],
        [ 'test = `demo`', true, [] ],

        [ '(a=1', true, [] ],
        [ 'a=1)', true, [] ],
        [ '((a=1)', true, [] ],
        [ '{a=1}', true, [] ],
        [ '[a=1]', true, [] ],
        [ '((a=1 || a=2) && c=1))', true, [] ],
        [ '()', true, [] ],

        [ '1=12', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'number', 'literal' => '1' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '12' ] ]
            ]
        ] ],
        [ '   1   =   12   ', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'number', 'literal' => '1' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '12' ] ]
            ]
        ] ],
        [ '"demo" != test', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'text', 'literal' => 'demo' ], 'operation' => [ 'type' => 'sign', 'literal' => '!=' ], 'right' => [ 'type' => 'identifier', 'literal' => 'test' ] ]
            ]
        ] ],
        [ 'a~1', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '~' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
            ]
        ] ],
        [ 'a !~ 1', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '!~' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
            ]
        ] ],
        [ 'test>12', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'test' ], 'operation' => [ 'type' => 'sign', 'literal' => '>' ], 'right' => [ 'type' => 'number', 'literal' => '12' ] ]
            ]
        ] ],
        [ 'test > 12', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'test' ], 'operation' => [ 'type' => 'sign', 'literal' => '>' ], 'right' => [ 'type' => 'number', 'literal' => '12' ] ]
            ]
        ] ],
        [ 'test >= "test"', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'test' ], 'operation' => [ 'type' => 'sign', 'literal' => '>=' ], 'right' => [ 'type' => 'text', 'literal' => 'test' ] ]
            ]
        ] ],
        [ 'test<@demo.test2', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'test' ], 'operation' => [ 'type' => 'sign', 'literal' => '<' ], 'right' => [ 'type' => 'identifier', 'literal' => '@demo.test2' ] ]
            ]
        ] ],
        [ '1<="test"', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'number', 'literal' => '1' ], 'operation' => [ 'type' => 'sign', 'literal' => '<=' ], 'right' => [ 'type' => 'text', 'literal' => 'test' ] ]
            ]
        ] ],
        [ '1<="te st"', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'number', 'literal' => '1' ], 'operation' => [ 'type' => 'sign', 'literal' => '<=' ], 'right' => [ 'type' => 'text', 'literal' => 'te st' ] ]
            ]
        ] ],
        [ 'demo=\'te\\\'st\'', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'demo' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'text', 'literal' => 'te\'st' ] ]
            ]
        ] ],
        [ 'demo="te\\\'st"', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'demo' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'text', 'literal' => 'te\\\'st' ] ]
            ]
        ] ],
        [ 'demo="te\"st"', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'demo' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'text', 'literal' => 'te"st' ] ]
            ]
        ] ],
        [ 'demo="test0"', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'demo' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'text', 'literal' => 'test0' ] ]
            ]
        ] ],
        [ '(a=1)', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [
                    [
                        'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                        'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
                    ]
                ]
            ]
        ] ],
        [ '(a="test(")', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [
                    [
                        'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                        'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'text', 'literal' => 'test(' ] ]
                    ]
                ]
            ]
        ] ],
        [ '(a="test)")', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [
                    [
                        'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                        'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'text', 'literal' => 'test)' ] ]
                    ]
                ]
            ]
        ] ],
        [ '((a=1))', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [
                    [
                        'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                        'item' => [
                            [
                                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
                            ]
                        ]
                    ]
                ]
            ]
        ] ],
        [ 'a=1 || 2!=3', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
            ],
            [
                'operation' => [ 'type' => 'join', 'literal' => '||' ],
                'item' => [ 'left' => [ 'type' => 'number', 'literal' => '2' ], 'operation' => [ 'type' => 'sign', 'literal' => '!=' ], 'right' => [ 'type' => 'number', 'literal' => '3' ]]
            ]
        ] ],
        [ 'a=1 && 2!=3', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
            ],
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'number', 'literal' => '2' ], 'operation' => [ 'type' => 'sign', 'literal' => '!=' ], 'right' => [ 'type' => 'number', 'literal' => '3' ]]
            ]
        ] ],
        [ 'a=1 && 2!=3 || "b"=a', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
            ],
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [ 'left' => [ 'type' => 'number', 'literal' => '2' ], 'operation' => [ 'type' => 'sign', 'literal' => '!=' ], 'right' => [ 'type' => 'number', 'literal' => '3' ]]
            ],
            [
                'operation' => [ 'type' => 'join', 'literal' => '||' ],
                'item' => [ 'left' => [ 'type' => 'text', 'literal' => 'b' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'identifier', 'literal' => 'a' ]]
            ]
        ] ],
        [ '(a=1 && 2!=3) || "b"=a', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [
                    [
                        'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                        'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
                    ],
                    [
                        'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                        'item' => [ 'left' => [ 'type' => 'number', 'literal' => '2' ], 'operation' => [ 'type' => 'sign', 'literal' => '!=' ], 'right' => [ 'type' => 'number', 'literal' => '3' ]]
                    ]
                ]
            ],
            [
                'operation' => [ 'type' => 'join', 'literal' => '||' ],
                'item' => [ 'left' => [ 'type' => 'text', 'literal' => 'b' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'identifier', 'literal' => 'a' ]]
            ]
        ] ],
        [ '((a=1 || a=2) && (c=1))', false, [
            [
                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                'item' => [
                    [
                        'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                        'item' => [
                            [
                                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
                            ],
                            [
                                'operation' => [ 'type' => 'join', 'literal' => '||' ],
                                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'a' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '2' ] ]
                            ]
                        ]
                    ],
                    [
                        'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                        'item' => [
                            [
                                'operation' => [ 'type' => 'join', 'literal' => '&&' ],
                                'item' => [ 'left' => [ 'type' => 'identifier', 'literal' => 'c' ], 'operation' => [ 'type' => 'sign', 'literal' => '=' ], 'right' => [ 'type' => 'number', 'literal' => '1' ] ]
                            ]
                        ]
                    ]
                ]
            ]
        ] ],
    ];

    public function testParser()
    {
        $parser = new Parser();
        foreach ($this->testCases as $testCase) {
            if ($testCase[1] === true) {
                try {
                    $parser->parse($testCase[0]);
                }
                catch (Exception $e) {
                    $this->assertInstanceOf(Exception::class, $e);
                    continue;
                }
                $this->fail();
            }
            else {
                /** @noinspection PhpUnhandledExceptionInspection */
                $this->assertEquals(json_encode($testCase[2]), json_encode($parser->parse($testCase[0])));
            }
        }
    }
}
