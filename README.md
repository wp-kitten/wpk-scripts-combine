# WpkScriptsCombine

The **WpkScriptsCombine** class provides an easy way to load and combine (and minify using the JShrink class) 
multiple scripts or stylesheets into a single file that will be automatically included in the page header/footer of 
your website. This class provides a great way for theme developers, and not only, to limit the number of http requests
 in order to load a bunch of scripts and stylesheets required by a theme.

## Information

<table>
  <tr>
    <td>Class</td>
    <td>WpkScriptsCombine</td>
  </tr>
  <tr>
    <td>Version</td>
    <td>1.0.0</td>
  </tr>
  <tr>
    <td>WordPress Version</td>
    <td>>= 4.1</td>
  </tr>
  <tr>
    <td>PHP</td>
    <td>>= 5.3.0</td>
  </tr>
</table>


### Usage

Upload the **wpk-scripts-combine** directory into your theme. Open the **functions.php** file and add the following: 
```php
/*
 * Load the class
 */
require( dirname( __FILE__ ) . '/wpk-scripts-combine/wpk-scripts-combine.php' );

/*
 * Instantiate the class
 */
$wpksc = new WpkScriptsCombine();
```

Afterwards, you can use the instance of the class, now stored in the **$wpksc** variable to combine and enqueue scripts:

```php
// @file header.php

// JavaScript
$wpksc->combineScripts(array(
    // These paths MUST be relative to baseDir path specified below
    'res/b/b.js',
    'res/c/c.js',
    'res/a.js',
), array('jquery'), trailingslashit(get_template_directory()), false);

/*
 * The above configuration would match a directory tree like this:
 *
 * [theme-dir] => retrieved using: trailingslashit(get_template_directory())
 *      [res]
 *          a.js
 *          [b]
 *              b.js
 *          [c]
 *              c.js
 */

wp_head();
```

To combine stylesheets:

```php
// @file header.php

// JavaScript
$wpksc->combineStylesheets(array(
    // These paths MUST be relative to baseDir path specified below
    'res/b/b.css',
    'res/c/c.css',
    'res/a.css',
), array(), trailingslashit(get_template_directory()), false);

/*
 * The above configuration would match a directory tree like this:
 *
 * [theme-dir] => retrieved using: trailingslashit(get_template_directory())
 *      [res]
 *          a.css
 *          [b]
 *              b.css
 *          [c]
 *              c.css
 */

wp_head();
```

## Methods

### __construct()
<table>
  <tr>
   <td colspan="3" align="center"><strong>Parameters</strong></td>
  </tr>
  <tr>
   <th>Name</th>
   <th>Description</th>
   <th>Default</th>
  </tr>
  <tr>
    <td>$minify</td>
    <td>Whether or not to minify the output</td>
    <td>true</td>
  </tr>
</table>

For scripts compression, I have used the minifier script **JShrink** provided by [tedious](https://github.com/tedious/JShrink)



### combineScripts()

<table>
  <tr>
   <td colspan="3" align="center"><strong>Parameters</strong></td>
  </tr>
  <tr>
   <th>Name</th>
   <th>Description</th>
   <th>Default</th>
  </tr>
  <tr>
    <td>$files</td>
    <td>The list of files to combine</td>
    <td></td>
  </tr>
  <tr>
    <td>$dependencies</td>
    <td>The list of dependencies. Ex: array('jquery', 'jquery-ui');</td>
    <td>array()</td>
  </tr>
  <tr>
    <td>$baseDirPath</td>
    <td>The base directory path where to search for scripts</td>
    <td>null</td>
  </tr>
  <tr>
    <td>$inFooter</td>
    <td>Whether or not to include the output file in the page footer.</td>
    <td>false</td>
  </tr>
</table>

### combineStylesheets()

<table>
  <tr>
   <td colspan="3" align="center"><strong>Parameters</strong></td>
  </tr>
  <tr>
   <th>Name</th>
   <th>Description</th>
   <th>Default</th>
  </tr>
  <tr>
    <td>$files</td>
    <td>The list of files to combine</td>
    <td>&mdash;</td>
  </tr>
  <tr>
    <td>$dependencies</td>
    <td>The list of dependencies. Ex: array('layout_css', 'template_css');</td>
    <td>array()</td>
  </tr>
  <tr>
    <td>$baseDirPath</td>
    <td>The base directory path where to search for stylesheets.</td>
    <td>null</td>
  </tr>
</table>

### clearCache()

Use this method to delete all cached files from the **cache** directory.
