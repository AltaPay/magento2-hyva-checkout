# AltaPay Support for Hyva Checkout

AltaPay integration with the Hyva Checkout, allowing merchants to accept secure payments easily.

## Module Dependencies
This module relies on the following components:
- SDM_Altapay plugin
- The Hyva default theme
- Hyva_Checkout plugin

## Installation
Follow these steps to install and configure AltaPay for Hyva Checkout in your Magento store:

1. Run the following command in the Magento 2 root folder to install the module:
```bash
composer require altapay/magento2-hyva-checkout
``` 
2. Enable the module:
```bash
php bin/magento module:enable Altapay_HyvaCheckout
``` 
3. Upgrade the setup:
```bash
php bin/magento setup:upgrade
```
4. Generate Hyva configuration:
```bash
php bin/magento hyva:config:generate
```
5. Build Tailwind CSS for Hyva themes:
```bash
npm --prefix vendor/hyva-themes/magento2-default-theme/web/tailwind/ run ci
npm --prefix vendor/hyva-themes/magento2-default-theme/web/tailwind/ run build-prod
```
Or from your custom theme:
```bash
npm --prefix app/design/frontend/<Vendor>/<Theme>/web/tailwind run ci
npm --prefix app/design/frontend/<Vendor>/<Theme>/web/tailwind run build-prod
```
## Configuration Overview

The setup process requires configuring AltaPay Payment. This module does not introduce any custom configuration options. Instead, the AltaPay Payment configuration follows the standard setup process, just as it would for any default scenario (e.g., Luma-based checkout).

Next, the setup requires configuring the Hyvä theme and Hyvä checkout for the specified store.

- Go to **Content > Design > Configuration** in the admin panel and set the **hyva/default** theme for the desired store view.  
- Navigate to **Stores > Configuration > Hyvä Themes > Checkout > General**, then enable **Hyva Default** (or **Hyva One Page**) for the selected store view.

## Changelog

See [Changelog](CHANGELOG.md) for all the release notes.

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

## Documentation

For more details please see [docs](https://github.com/AltaPay/plugin-magento2-community/wiki)