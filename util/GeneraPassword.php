<?php
/**
 * We just want to hash our password using the current DEFAULT algorithm.
 * This is presently BCRYPT, and will produce a 60 character result.
 *
 * Beware that DEFAULT may change over time, so you would want to prepare
 * By allowing your storage to expand past 60 characters (255 would be good)
 */

$pwdHash = password_hash("1234", PASSWORD_DEFAULT);
echo $pwdHash.'\n';

if (password_verify("1234", $pwdHash))
    echo 'Ok!\n';
else
    echo 'NO!\n';
?>