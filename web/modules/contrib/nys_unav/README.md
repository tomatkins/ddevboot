# NYS Universal Navigation Integration #

All state of New York websites are required to have the state Universal Navigation bar at the top and bottom of the site, surrounding any other content.  This module makes it easy to integrate them on a Drupal site.

## Table of contents

- Installation and Configuration
- Restrictions
- Credits
- Use
- Non-automatic option
  - Blocks
  - Templates
- Updating uNav Embed Code

## Installation and Configuration
- Install as usual, see [https://www.drupal.org/documentation/install/modules-themes/modules-8](https://www.drupal.org/documentation/install/modules-themes/modules-8) for further information.
- Enable the module.
- Go to Configuration >> User Interface >> NYS Universal Navigation (/admin/config/user-interface/nys-unav) to configure the module. You can also reach the configuration page from the Configure link on the module page.
  - The configuration page has one option for the unav:
    - Whether to use the default search
    - *Note that if you don't configure the module, it will default to automatic insertion*
  - The configuration page has three options for the tranlation bar
    - Enable the header
    - Enable the footer
    - Strip www from the urls
    - *Note that your site will need to be registered with the translation service in order to make use of the translation feature. Please submit an RITM in order to access this functionality.*
- Set the *Administer the NYS uNav module* permission for those roles that should be able to administer the configuration of this module at People >> Permissions (/admin/people/permissions#module-nys_unav). You can also reach the permission from the Permissions link on the module page.
- The module will automatically insert the Universal Navigation at the top and the Universal footer at the bottom of your website's page; outside of any page HTML.

Note that another, hidden configuration exists: `nys_unav.nys_unav_auto`. This is set to `1` by default. You can set it programatically to `0` in order to place the blocks wherever you want. See the "Non-automatic option" section below for more information on this process.


## Restrictions
This Drupal module was developed for use by New York State agencies and entities for official New York State websites to be compliant per ITS mandate policy NYS-S05-001 ([https://www.its.ny.gov/document/state-common-web-banner](https://www.its.ny.gov/document/state-common-web-banner)).

For use on other sites, please contact New York State Office of Information Services WebNY team at webnysupport@its.ny.gov for guidance and authorization for use. The static html `NY State Universal Navigation`, that is integrated using this module, can be found at [https://github.com/ny/universal-navigation](https://github.com/ny/universal-navigation).

## Credits
This project was sponsored by the [New York State Office of Information Technology Services WebNY department](https://www.drupal.org/webny-new-york-state-office-of-information-technology-services).

## Use
Enabling the module on your site will, by default, insert the Universal Navigation at the top and the Universal footer at the bottom of your website's page; outside of any page HTML.

The Universal Navigation will **not** display on any site administration pages, for most sites administration pages.

Ideally if you enable the module after your other modules it will adapt to most situations.

## Non-automatic option

If you find the automatic option doesn't work on your particular site, or you want to use the additional flexibility offered via either blocks you can position in your theme or functions you can use in a template, you can disable the automatic addition of the header and footer via an addition to your site's `docroot/sites/settings/global.settings.php` file which is a feature of the `acquia/drupal-recommended-settings` composer package.

```
  $config['nys_unav.settings']['nys_unav']['nys_unav_auto'] = 0;
```

Don't forget to run `drush cr` after making this change.

If you don't have the recommended settings package installed, you can add it to `settings.php` or whatever site-specific settings file you are using instead.

Once you have disabled the automatic addition of the Universal Navigation, you have two options:  use blocks in your theme or functions in your theme templates.

### Blocks

If your theme has header and footer regions (they aren't necessarily named that way) that are full page width, and don't have anything above them (header) or below them (footer), using the module is as simple as enabling it and using your block placement method (the structure >> blocks page in Drupal or using context, panels, etc if you are using one of those contributed modules.).

If your theme doesn't have appropriate regions, you could add new regions in your theme, using the information at [https://www.drupal.org/node/171224](https://www.drupal.org/node/171224) or following the tutorial at [https://www.ostraining.com/blog/drupal/block-region-drupal-theme/](https://www.ostraining.com/blog/drupal/block-region-drupal-theme/), then place the blocks as described.  Adding new regions will requiring modifying your page.tpl.php file, if you also have custom page templates (e.g. page--front.tpl.php, etc), remember to modify them also.

### Templates

Alternatively, you can modify your theme template(s) to insert the uNav HTML
during output.

For the header:
```
  {{ drupal_block('nys_unav_header_block') }}
```

For the footer:
```
  {{ drupal_block('nys_unav_footer_block') }}
```

Don't forget to run `drush cr` after adding these blocks. You may also need to run that command when you make a change to the configuration.

## Updating uNav Embed Code
The uNav embed code(s) are contained in the template files `nys-unav-header.html.twig`. Should the State of New York require changes to the embed code, update the module to the latest version of this module.
