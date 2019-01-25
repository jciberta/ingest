<?php

/** 
 * LibDateTest.php
 *
 * Test de la llibreria d'utilitats per a dates.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

use PHPUnit\Framework\TestCase;
 
require_once('../src/Config.php');
require_once(ROOT.'/lib/LibDate.php');

final class DateTest extends TestCase
{
    public function testComprovaData()
    {
		$this->assertTrue(ComprovaData('28/02/2012')); 
		$this->assertTrue(ComprovaData('2012-02-28', 'Y-m-d')); 
		$this->assertTrue(ComprovaData('28/02/2012', 'd/m/Y')); 
		$this->assertTrue(ComprovaData('14:50', 'H:i')); 
		$this->assertTrue(ComprovaData(14, 'H')); 
		$this->assertTrue(ComprovaData('14', 'H')); 
		$this->assertTrue(ComprovaData('2012-02-28T12:12:12+02:00', 'Y-m-d\TH:i:sP')); 
		$this->assertTrue(ComprovaData('2012-02-28T12:12:12+02:00', DateTime::ATOM)); 
		$this->assertTrue(ComprovaData('Tue, 28 Feb 2012 12:12:12 +0200', 'D, d M Y H:i:s O')); 
		$this->assertTrue(ComprovaData('Tue, 28 Feb 2012 12:12:12 +0200', DateTime::RSS)); 
		$this->assertFalse(ComprovaData('14:77', 'H:i')); 
		$this->assertFalse(ComprovaData('2012-02-30 12:12:12')); 
		$this->assertFalse(ComprovaData('30/02/2012', 'd/m/Y')); 
		$this->assertFalse(ComprovaData('Tue, 27 Feb 2012 12:12:12 +0200', DateTime::RSS)); 
    }

    public function testDataAMySQL()
    {
		$this->assertEquals(DataAMySQL('28/02/2012'), "'2012-02-28'"); 
		$this->assertEquals(DataAMySQL(''), 'NULL'); 
	}

    public function testMySQLAData()
    {
		$this->assertEquals(MySQLAData('2012-02-28'), '28/02/2012'); 
		$this->assertEquals(MySQLAData(''), ''); 
	}
}
 
?>
