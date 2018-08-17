# Enthusiast

[Enthusiast](https://github.com/angelasabas/enthusiast), but using PDO instead of the deprecated MySQL extension. Requires at least PHP 5.4, and compatible with PHP 7.

## Changes

- Converted all mysql_* functions to PDO
- Replaced all instances of `TYPE=MyISAM` with `ENGINE=MyISAM`
- Replaced `ereg()` with `preg_match()`
- Updated [PEAR](https://pear.php.net/package/PEAR/) to v1.10.5
- Updated [PEAR/Mail](https://pear.php.net/package/Mail/) to v1.4.1