# twfy-php
An up-to-date but simple wrapper to the TheyWorkForYouAPI written in PHP.

It can be easily installed using Composer with the following command:

`composer require odinshat/twfy-php`

After that you can use it in you project by simply including it with:

`use OdinsHat\Twfy`

Then here's a few basic examples:

```php
$twfyapi = new TWFYAPI('DpPSWnGj7XPRGePtfMGWvGqQ');

$mps = $twfyapi->query('getMPs', array('output' => 'xml', 'party' => 'labour'));

header('Content-type: application/xml');
echo $mps;
```
