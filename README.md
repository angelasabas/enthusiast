# Enthusiast

[Enthusiast](https://github.com/angelasabas/enthusiast), but using PDO instead of the deprecated MySQL extension. Requires at least PHP 5.4, and compatible with PHP 7.

## Changes

- Converted all mysql_* functions to PDO
- Replaced all instances of `TYPE=MyISAM` with `ENGINE=MyISAM`
- Replaced `ereg()` with `preg_match()`
- Updated [PEAR](https://pear.php.net/package/PEAR/) to v1.10.5
- Updated [PEAR/Mail](https://pear.php.net/package/Mail/) to v1.4.1

## Upgrading

If you are using [this version](https://github.com/angelasabas/enthusiast) of Enthusiast:

1. **Back up all your current Enthusiast configurations, files, and databases first.**
2. Take note of your database information in all your `config.php` files.
3. Download an [archive of this repository](https://github.com/Lysianthus/enthusiast/archive/master.zip). Extract the archive.
4. Replace your current `enthusiast/` files with the `enthusiast/` files from this repository.
5. In every fanlisting folder, paste the `config.sample.php` file. Edit your database information and listing ID variable accordingly, and save it as `config.php` to overwrite your old one.