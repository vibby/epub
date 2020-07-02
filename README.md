# ePub lib for PHP - ![project status](http://stillmaintained.com/justinrainbow/epub.png) - [![Build Status](https://secure.travis-ci.org/justinrainbow/epub.png)](http://travis-ci.org/justinrainbow/epub)

## Installation through Composer

Add `vibby/epub` to your `composer.json` file.

```bash
composer require vibby/epub
```

Then just run the `composer.phar install` (or `composer.phar update` if
you added this to an existing `composer.json` file).

```bash
wget http://getcomposer.org/composer.phar
php composer.phar install
```

## Usage

```php
$reader = new \Vibby\EPub\Reader\Reader();
$ePub = $reader->load($fileName);

printf("Title: %s\n", $epub->getMetadata()->get('title'));

$firstChapter = $ePub->getPackage()->getSpine()->get(0);
$content = $firstChapter->getContent();
// manipulate HTML here
$ePub->updateChapterContent($firstChapter, $content);
```

## Resources

 * http://www.hxa.name/articles/content/epub-guide_hxa7241_2007.html
