<?php

declare(strict_types=1);

/*
 * This file is part of the humbug/php-scoper package.
 *
 * Copyright (c) 2017 Théo FIDRY <theo.fidry@gmail.com>,
 *                    Pádraic Brady <padraic.brady@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Humbug\PhpScoper\Patcher;

use PHPUnit\Framework\TestCase;
use function func_get_args;
use function implode;
use function Safe\sprintf;
use function str_replace;

/**
 * @covers \Humbug\PhpScoper\Patcher\PatcherChain
 */
class PatcherChainTest extends TestCase
{
    public function test_applies_all_the_inner_patchers(): void
    {
        $filePath = '/path/to/file.php';
        $contents = 'OriginalContent';
        $prefix = 'Humbug';

        $patcher = new PatcherChain([
            self::createPatcher(0),
            self::createPatcher(1),
            self::createPatcher(2),
        ]);

        $expected = <<<'EOF'
        patcher#2{
            /path/to/file.php,
            Humbug,
            patcher#1{
                /path/to/file.php,
                Humbug,
                patcher#0{
                    /path/to/file.php,
                    Humbug,
                    OriginalContent
                }
            }
        }
        EOF;

        // The line returns are purely for readability
        $expected = str_replace([' ', "\n"], ['', ''], $expected);

        $actual = $patcher($filePath, $prefix, $contents);

        self::assertSame($expected, $actual);
    }

    private static function createPatcher(int $id): callable
    {
        return static fn (string $filePath, string $prefix, string $contents) => sprintf(
            'patcher#%s{%s}',
            $id,
            implode(',', func_get_args()),
        );
    }
}
