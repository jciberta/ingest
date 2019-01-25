<?php

/** 
 * LibDBTest.php
 *
 * Test de la llibreria d'utilitats per a DB.
 *
 * @author Josep Ciberta
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License version 3
 */

use PHPUnit\Framework\TestCase;
 
require_once('../src/Config.php');
require_once(ROOT.'/lib/LibDB.php');

final class DBTest extends TestCase
{
    public function testComprovaFortalesaPassword()
    {
		$errors = [];
        $this->assertFalse(ComprovaFortalesaPassword('', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('a', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('1', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('aa', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('11', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('a1', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('12', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('123', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('1234', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('12345', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('123456', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('1234567', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('1234567', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('12345678', $errors));
        $this->assertTrue(ComprovaFortalesaPassword('12345678A', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('aaa', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('aaaa', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('aaaaa', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('aaaaaa', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('aaaaaaa', $errors));
        $this->assertFalse(ComprovaFortalesaPassword('aaaaaaaa', $errors));
        $this->assertTrue(ComprovaFortalesaPassword('aaaaaaaa1', $errors));
    }
}
  
?>
