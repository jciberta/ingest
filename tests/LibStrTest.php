<?php

/** 
 * LibStrTest.php
 *
 * Test de la llibreria d'utilitats per a cadenes de caràcters.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

use PHPUnit\Framework\TestCase;
 
require_once('../src/Config.php');
require_once(ROOT.'/lib/LibStr.php');

final class StrTest extends TestCase
{
    public function testTrimX()
    {
        $this->assertEquals(TrimX(' test '), 'test');
        $this->assertEquals(TrimX(' test test '), 'test test');
        $this->assertEquals(TrimX(' test  test '), 'test test');
        $this->assertEquals(TrimX(' test   test '), 'test test');
        $this->assertEquals(TrimX('  test   test  '), 'test test');
    }

    public function testTrimXX()
    {
        $this->assertEquals(TrimXX(' test '), 'test');
        $this->assertEquals(TrimXX(' test test '), 'testtest');
        $this->assertEquals(TrimXX(' test  test '), 'testtest');
        $this->assertEquals(TrimXX(' test   test '), 'testtest');
        $this->assertEquals(TrimXX('  test   test  '), 'testtest');
    }
}
  
?>
