OXID eShop AWS S3 component
===========================

This component is developed to make a communication with AWS S3, such as putting an object, 
removing it, and other operations.

One of the features that we have provided in this component is the ability to store and manage 
the shop images in AWS S3 buckets.
In fact, after installing this component, shop images (products, promotions, vendors, and manufacturers) will 
be stored and managed in cloud storage.

## Installation

1- Run the following command to install the component:

```bash
composer require oxid-esales/aws-s3-component:*
```

2- Set the AWS credentials in `<oxid-eshop-root-directory>/var/configuration/configurable_services.yaml`.

```bash
parameters:
  aws.s3.key: sample-key
  aws.s3.secret: sample-secret
  aws.s3.region: sample-region
  aws.s3.version: sample-version
  aws.s3.image.bucket: sample-image.bucket
  aws.s3.image.bucket.acl: sample-image.bucket.acl
```

## How to install component for development?

Checkout component besides OXID eShop `source` directory:

```bash
git clone https://github.com/OXID-eSales/aws-s3-component.git
```

Run composer install command:

```bash
cd aws-s3-component
composer install
```

Add dependency to OXID eShop `composer.json` file:

```bash
composer config repositories.oxid-esales/aws-s3-component path aws-s3-component
composer require --dev oxid-esales/aws-s3-component:*
```

## How to run tests?

1- Test credentials will be the same as the real credentials 
in `<oxid-eshop-root-directory>/var/configuration/configurable_services.yaml`.

2- To run tests for the component please define OXID eShop bootstrap file:

```bash
vendor/bin/phpunit --bootstrap=../source/bootstrap.php tests/
```

## Bugs and Issues

If you experience any bugs or issues, please report them in the section **OXID eShop** of https://bugs.oxid-esales.com.

## License

See LICENSE file for license details.