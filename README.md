# AltaPay for Hyvä Checkout

AltaPay integration with the Hyvä Checkout, allowing merchants to accept secure payments easily.

## Module Dependencies
This module relies on the following components:
- [AltaPay Module](https://github.com/AltaPay/plugin-magento2-community)
- Hyvä default theme
- Hyva checkout plugin

## Installation
Follow these steps to install and configure AltaPay for Hyvä Checkout in your Magento store:

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
4. Generate Hyvä configuration:
```bash
php bin/magento hyva:config:generate
```
5. Build Tailwind CSS for Hyvä themes:
```bash
npm --prefix vendor/hyva-themes/magento2-default-theme/web/tailwind/ run ci
npm --prefix vendor/hyva-themes/magento2-default-theme/web/tailwind/ run build-prod
```
Or from your custom theme:
```bash
npm --prefix app/design/frontend/<Vendor>/<Theme>/web/tailwind run ci
npm --prefix app/design/frontend/<Vendor>/<Theme>/web/tailwind run build-prod
```

## Changelog

See [Changelog](CHANGELOG.md) for all the release notes.

## License

Distributed under the MIT License. See [LICENSE](LICENSE) for more information.

## Documentation

For more details please see [docs](https://github.com/AltaPay/magento2-hyva-checkout/wiki)