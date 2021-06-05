# Consumer Edge Assessment

Coding challenge from Consumer Edge

## Description

My response to the Software Engineer assessment from Consumer Edge

### Usage

* First setup a local database and either enter the connection info within the `init_db()` function in `helpers.php` or pass them as arguments to the call to `init_db()` in `carvana.php`
* To fetch and store a single page run `carvana.php` giving it the page number (eg: `php carvana.php 3`)
* To fetch and store all pages pass 'all' to the script (eg: `php carvana.php all`)
  - There are over 620 pages of results using default pagination so this will either take a while or possibly time out before completing
