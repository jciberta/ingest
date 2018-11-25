<?php

/** 
 * LibStrTest.php
 *
 * Test de la llibreria d'utilitats per a cadenes de caràcters.
 */

use PHPUnit\Framework\TestCase;
 
require_once('../src/lib/LibStr.php');

final class Cadenes extends TestCase
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
