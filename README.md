# zbateson/php-dummy-sendmail

sendmail dummy for testing written in PHP

PHP based 'sendmail' dummy for writing emails to a directory for testing.

* Predictable and configurable file-naming
* Configurable output directory and file extension

## Installation

Currently, installing involves either creating the phar archive from the repo,
or running it directly from the repo.

Dependencies must be downloaded and installed with composer - to do so:

```bash
composer install
```

> This depends on how composer is installed, if that fails please ensure
> composer is installed and review composer's documentation at: 
> https://getcomposer.org/doc/00-intro.md

To create the phar archive, after cloning, use clue/phar-composer and run
phar-composer build to create php-dummy-sendmail.phar.  Once created, optionally
move it to /usr/local/bin or configure sendmail with php
path/to/php-dummy-sendmail.phar.

```bash
mv php-dummy-sendmail.phar /usr/local/bin/php-sendmail
```

> clue/phar-composer will automatically set execute permissions.
> you may need to run this with sudo

To run directly without creating a phar archive, the main project file sendmail,
is an executable file in bash and can be run with:

```bash
php sendmail
```

In Windows php-dummy-sendmail can be run with:

```
php sendmail
```

### Configure php.ini

Change the sendmail configuration in php.ini:

```
sendmail = /usr/local/bin/php-sendmail --directory /path/to/output-dir
```

## Usage

php-sendmail [--directory[="..."]] [--timestamp[="..."]] [--increment-file[="..."]]
[--input-file[="..."]] [--file-extension[="..."]] [--print] [to]

Example:
php-sendmail user@example.com --directory /path/to/output/dir --timestamp "Y-m-d H:i:s:u" --file-extension txt

> php-sendmail reads from STDIN by default, so the above example run on the
> command line would block.

### Command-line Options

--directory - specifies the default directory to read/write to (useful if not
    the current dir)

--timestamp - PHP date() timestamp format (with 'u' support), used to format
    the output file name.  Defaults to 'Y-m-d H:i:s:u'

--increment-file - Specifies a file to save an index number for auto-increment
    functionality.  File names are numbered with this option set.

--file-extension - Sets the file extension to use for saved files.

--input-file - Specify an input file (useful for debugging)

--print - Simply prints the output
