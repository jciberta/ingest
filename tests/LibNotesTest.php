<?php

/** 
 * LibNotesTest.php
 *
 * Test de la llibreria d'utilitats per a les notes.
 */

 use PHPUnit\Framework\TestCase;
 
 require_once('../src/lib/LibNotes.php');

 final class Notes extends TestCase
{
    public function testEsNotaValida()
    {
        $this->assertTrue(EsNotaValida('1'));
        $this->assertTrue(EsNotaValida('2'));
        $this->assertTrue(EsNotaValida('3'));
        $this->assertTrue(EsNotaValida('4'));
        $this->assertTrue(EsNotaValida('5'));
        $this->assertTrue(EsNotaValida('6'));
        $this->assertTrue(EsNotaValida('7'));
        $this->assertTrue(EsNotaValida('8'));
        $this->assertTrue(EsNotaValida('9'));
        $this->assertTrue(EsNotaValida('10'));
        $this->assertTrue(EsNotaValida('NP'));
        $this->assertTrue(EsNotaValida('A'));
        $this->assertTrue(EsNotaValida('NA'));

        $this->assertFalse(EsNotaValida('0'));
        $this->assertFalse(EsNotaValida('-1'));
        $this->assertFalse(EsNotaValida('11'));
        $this->assertFalse(EsNotaValida('B'));
        $this->assertFalse(EsNotaValida('C'));
    }
}
  
 ?>