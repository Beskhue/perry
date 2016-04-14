========
Overview
========

Requirements
============

#. PHP 5.4+
#. Guzzle 6.2,
#. Guzzle promises 1.x,
#. Guzzle rate limiter 0.3.x

.. _installation:

Installation
============

The recommended way to install Perry is with `Composer <http://getcomposer.org>`_.
Composer manages package dependencies for PHP and installs them into your project.

To install Perry, run the following in your project's root directory:

.. code-block:: bash

    php composer.phar require beskhue/perry:~3.0

Alternatively, you can add Perry to your composer.json file:

.. code-block:: js

    {
      "require": {
         "beskhue/perry": "~3.0"
      }
   }
   
and run: 

.. code-block:: bash

    php composer.phar update
    
If you do not yet use Composer in your project, you need to require Composer's autoloader:

.. code-block:: php

    require 'vendor/autoload.php';

License
=======

Licensed using the `MIT license <http://opensource.org/licenses/MIT>`_.

    Copyright (c) 2016 Peter Petermann, Thomas Churchman <https://github.com/beskhue>

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
