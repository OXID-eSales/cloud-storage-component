OXID eShop Cloud Storage component
==================================

Facilitates integration of OXID eShop with cloud storage systems (e.g. AWS S3) 
over [FlySystem storage library](https://github.com/thephpleague/flysystem).

The component uses `League\Flysystem\AwsS3V3\AwsS3V3Adapter` as a default storage adapter implementation.
Component users can connect to other storage systems by switching to alternative implementation of the `FilesystemAdapter`
(see [services.yaml](services.yaml)).

**Note:** see [Flysystem documentation](https://flysystem.thephpleague.com/v2/docs/) for the list of supported adapters
and information about developing a custom one.

## Installation

1- Run the following command to install the component:

```bash
composer require oxid-esales/cloud-storage-component:*
```

2- Set the necessary adapter configuration in `<oxid-eshop-root-directory>/var/configuration/configurable_services.yaml`
```bash
# configuration for the default adapter (AWS S3):
parameters:
  aws.s3.client.configs:
    credentials:
      key: my-key
      secret: my-secret
      token: my-token
      expires: my-expires
    region: my-region
    version: my-version
  aws.s3.image.bucket: my-bucket
```

**Note:**
see AWS SDK documentation to find out more about AWS S3 configuration parameters:
https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html

## How to install component for development?

Checkout component besides OXID eShop `source` directory:

```bash
git clone https://github.com/OXID-eSales/cloud-storage-component.git
```

Run composer install command:

```bash
cd cloud-storage-component
composer install
```

Add dependency to OXID eShop `composer.json` file:

```bash
composer config repositories.oxid-esales/cloud-storage-component path cloud-storage-component
composer require --dev oxid-esales/cloud-storage-component:*
```

## How to run tests?

1- Test credentials will be the same as the real credentials 
in `<oxid-eshop-root-directory>/var/configuration/configurable_services.yaml`.

2- To run tests for the component please define OXID eShop bootstrap file:

```bash
vendor/bin/phpunit  --bootstrap ../../../source/bootstrap.php tests/
```

## How to generate image variants when using Amazon S3?

OXID eShop generates image size variants (thumbnails,small images, etc) from the "master" images on request.
This functionality is available only for the images accessed via server's storage (not via external CDN).

One of many available solutions is to configure you CDN of choice (e.g. Amazon CloudFront) 
for automatic images resizing (using AWS Lambda@Edge).

After doing this you will need to upload only main "master" images to the external storage (AWS S3),
and the rest of image variants will be generated by external servers (Amazon CloudFront) on the fly
(you will need to pass your resized image requirements (for OXID eShop they are embedded in image URL)
to the AWS by adding a simple Lambda function).
You can find more about CloudFront and Lambda@Edge in the corresponding section of
[AWS documentation](https://aws.amazon.com/de/blogs/networking-and-content-delivery/resizing-images-with-amazon-cloudfront-lambdaedge-aws-cdn-blog/)

## Bugs and Issues

If you experience any bugs or issues, please report them in the section **OXID eShop** of https://bugs.oxid-esales.com.

## License

See LICENSE file for license details.
