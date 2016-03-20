# WP Easy Translate

    composer require sourcerer-mike/wp-easy-translate

This tool helps you with your tranlations.
Real developer use their console ;)

    wp-easy-translate themes

And then the magic happens:

- All translations with their text-domain will be fetched
- Your source files (po, php or json) will be updates.
- The binaries (MO-Files) will be updated to.

Translate with ease!

## Formats

Run `wp-easy-translate themes --format php` to get a **PHP-Array**:

    <?php return array (
      'some_textdomain' => 
      array (
        '' => 
        array (
          'domain' => 'some_textdomain',
          'lang' => 'de',
          'plural-forms' => 'nplurals=2; plural=(n != 1);',
        ),
        'General' => 
        array (
          0 => '',
          1 => 'Allgemeines', // THIS IS VERY THE TRANSLATION HAPPENS
        ),
        'Author' =>
         
        ...

My favourite! Or `wp-easy-translate themes --format json` to have a nice
and clean list:

    {
        "General": "Allgemein",
        "Author": "Autor",
        "Steak": "Schnitzel",
        "Tank": "Panzerwagen",
    }

Formats like **YAML** are planned.
Even **CSV** will come so that your customer can contribut with translations made in Excel.
I guess they don't like to edit JSON or YAML files ;)

## Updates every time

Work and run `wp-easy-translate themes` again to update your PO-Files:

- Obsolete translations will be removed
- New translations will be added
- MO-Files are updated every time.


## Copy and Translate

Everytime `wp-easy-translate themes` runs there will be a "empty.po" (or "empty.php", "empty.json") file in the languages
folder of every theme. Just copy it to "de_DE.po" or "en_GB.po" (or "php", "json") and add the translations.

With every run of `wp-easy-translate themes` the according MO-File ("de_DE.mo", "en_GB.mo") will be updates.