# Enthusiast

[Enthusiast](https://github.com/angelasabas/enthusiast), but using PDO instead of the deprecated MySQL extension. Requires at least PHP 5.4, and works with PHP 7.

## Changes

- Converted all mysql_* functions to PDO
- Replaced all instances of `TYPE=MyISAM` to `ENGINE=MyISAM`
- Removed deprecated reference operators in `enthusiast/Mail.php` and `enthusiast/PEAR.php`