# HelloDialog API Connector 

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/czim/hellodialog.svg?branch=master)](https://travis-ci.org/czim/hellodialog)
[![Latest Stable Version](http://img.shields.io/packagist/v/czim/hellodialog.svg)](https://packagist.org/packages/czim/hellodialog)

HelloDialog API connector / handler package.

## Install

Via Composer

``` bash
$ composer require czim/hellodialog
```

Then add the service providers in `config/app.php`:

    Czim\HelloDialog\HelloDialogServiceProvider::class,
    Czim\HelloDialog\Mail\MailServiceProvider::class,

In the same file, comment out or remove the standard Laravel MailServiceProvider:

    //Illuminate\Mail\MailServiceProvider::class,
    
Note that this step, and adding the `MailServiceProvider` is only necessary if you plan to use the `hellodialog` mail driver for use with Laravel's `Mail` facade. 
If not, only the `HelloDialogServiceProvider` is required.

Finally publish the config using the artisan command:

```bash
$ php artisan vendor:publish
```

## Configuration

Set the configuration in `config/hellodialog.php`.

## Basic Usage

Set up the configuration and instantiate the class. Then call the function.

To Do: expand on this. 


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [Coen Zimmerman][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/czim/hellodialog.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/czim/hellodialog.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/czim/hellodialog
[link-downloads]: https://packagist.org/packages/czim/hellodialog
[link-author]: https://github.com/czim
[link-contributors]: ../../contributors
