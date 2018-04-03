<?php

namespace AdrianBaez\Bundle\EasySfBundle\Tests\Utils;

use AdrianBaez\Bundle\EasySfBundle\Utils\Utils;
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    /**
     * @dataProvider providerGetClassFromString
     * @param  string $string
     * @param  string $fqcn
     */
    public function testGetClassFromString($string, $fqcn)
    {
        $this->assertEquals($fqcn, Utils::getClassFromString($string));
    }
    
    /**
     * @dataProvider providerGetClassFromFile
     * @param  string $file
     * @param  string $fqcn
     */
    public function testGetClassFromFile($file, $fqcn)
    {
        $this->assertEquals($fqcn, Utils::getClassFromFile($file));
    }
    
    /**
     * @return \Iterator
     */
    public function providerGetClassFromString()
    {
        $empty = '';
        yield [$empty, ''];
        
        $noPHP = <<<EOF
Lorem ipsum dolor sit amet, consectetur adipisicing elit,
sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
Ut enim ad minim veniam,
quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore
eu fugiat nulla pariatur.
Excepteur sint occaecat cupidatat non proident,
sunt in culpa qui officia deserunt mollit anim id est laborum.
EOF;
        yield [$noPHP, ''];
        
        $noPHPClass = <<<EOF
<?php

function foo() {
    
}
EOF;
        yield [$noPHPClass, ''];
        
        $mixedPHP = <<<EOF
Some text with <?php echo 'PHP';?> inside.
EOF;
        yield [$mixedPHP, ''];
        
        $incompletePHPClass = <<<EOF
<?php

class Foo{
    protected
EOF;
        yield [$incompletePHPClass, 'Foo'];
        
        $incompletePHPClass = <<<EOF
<?php
namespace Foo;
class Bar{
    protected
EOF;
        yield [$incompletePHPClass, 'Foo\Bar'];
        
        $incompletePHPClass = <<<EOF
<?php

namespace Foo;

/**
 * Some description
 */
class Bar extends Baz{
    protected
EOF;
        yield [$incompletePHPClass, 'Foo\Bar'];
    }
    
    /**
     * @return \Iterator
     */
    public function providerGetClassFromFile()
    {
        yield ['inexistent', ''];
        yield [__FILE__, static::class];
    }
}
