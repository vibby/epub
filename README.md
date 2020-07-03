# ePub lib for PHP

## Installation through Composer

Install composer (globaly is better)
```bash
wget http://getcomposer.org/composer.phar
mv composer.phar /usr/bin/composer
```

Add `vibby/epub` to your dependencies
```bash
composer require vibby/epub
```

## Usage

```php
$reader = new \Vibby\EPub\Reader\Reader();
$ePub = $reader->load($fileName);

printf("Title: %s\n", $epub->getMetadata()->getValue('title'));

$firstChapter = $ePub->getPackage()->getSpine()->get(0);
$content = $firstChapter->getContent();
// manipulate HTML here
$ePub->updateChapterContent($firstChapter, $content);
```

## Resources

 * http://www.hxa.name/articles/content/epub-guide_hxa7241_2007.html

## Credits

Built on the base of https://github.com/justinrainbow/epub 
