<?php

/** 
 * LibSQLTest.php
 *
 * Test de la llibreria d'utilitats per a SQL.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

use PHPUnit\Framework\TestCase;
 
require_once('../src/lib/LibSQL.php');

final class SQLTest extends TestCase
{
    public function testSQL1()
    {
		$SQL = new SQL('SELECT * FROM Taula');
        $this->assertEquals($SQL->Select, '*');
        $this->assertEquals($SQL->From, 'Taula');
        $this->assertEquals($SQL->Where, '');
    }

    public function testSQL2()
    {
		$SQL = new SQL('SELECT * FROM Taula WHERE a=3');
        $this->assertEquals($SQL->Select, '*');
        $this->assertEquals($SQL->From, 'Taula');
        $this->assertEquals($SQL->Where, 'a=3');
    }

    public function testSQL3()
    {
		$SQL = new SQL('SELECT a, b, c FROM Taula WHERE a=3 AND b=2');
        $this->assertEquals($SQL->Select, 'a, b, c');
        $this->assertEquals($SQL->From, 'Taula');
        $this->assertEquals($SQL->Where, 'a=3 AND b=2');
    }

    public function testSQL4()
    {
		$SQL = new SQL('SELECT COUNT(*) FROM Taula T WHERE a=3 AND b=2');
        $this->assertEquals($SQL->Select, 'COUNT(*)');
        $this->assertEquals($SQL->From, 'Taula T');
        $this->assertEquals($SQL->Where, 'a=3 AND b=2');
    }

    public function testSQL5()
    {
		$SQL = new SQL('select count(*) from Taula T where a=3 and b=2');
        $this->assertEquals($SQL->Select, 'count(*)');
        $this->assertEquals($SQL->From, 'Taula T');
        $this->assertEquals($SQL->Where, 'a=3 and b=2');
    }

    public function testSQL6()
    {
		$SQL = new SQL('select 1, 2, 3');
        $this->assertEquals($SQL->Select, '1, 2, 3');
        $this->assertEquals($SQL->From, '');
        $this->assertEquals($SQL->Where, '');
    }

    public function testSQL7()
    {
		$SQL = new SQL('SELECT T1.a AS A, T2.b AS B, T1.* FROM Taula1 T1, Taula2 T2 WHERE T1.a=3 AND T2.b=2');
        $this->assertEquals($SQL->Select, 'T1.a AS A, T2.b AS B, T1.*');
        $this->assertEquals($SQL->From, 'Taula1 T1, Taula2 T2');
        $this->assertEquals($SQL->Where, 'T1.a=3 AND T2.b=2');
        $this->assertEquals($SQL->CampAlies['A'], 'T1.a');
        $this->assertEquals($SQL->CampAlies['B'], 'T2.b');
    }
}
  
?>
