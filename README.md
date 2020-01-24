Brief description
============
This bundle provides image upload and optimization functionality out of the box, for embedded image collections.   

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require iiirxs/image-upload-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require iiirxs/image-upload-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    IIIRxs\ImageUploadBundle\IIIRxsImageUploadBundle::class => ['all' => true],
];
```

Usage
============
The bundle provides all needed functionalities for image uploading  out of the box as well as extensibility through configuration, service override and class inheritance. At this point it only supports MongoDB database, through Doctrine ODM Bundle.

Basic usage
----------------------------------
### Step 1: Add image embedded collection
Embed a collection of images inside a document. The target document class **must** extend `IIIRxs\ImageUploadBundle\Document\AbstractImage`:

AbstractImage class provides two basic mapped fields: `path` and `rank` but one can add any other mapped fields in the extended class.
```php
// src/Document/ImageContainer.php

/**
 * @MongoDB\EmbedMany(targetDocument=Image::class, strategy="atomicSetArray")
 */
protected $images;
```
```php
// src/Document/Image.php
use IIIRxs\ImageUploadBundle\Document\AbstractImage;

/**
 * @MongoDB\EmbeddedDocument
 */ 
class Image extends AbstractImage
```

### Step 2: Set an uploader
In order for the images to be uploaded in the filesystem, your application should contain at least one service implementing `IIIRxs\ImageUploadBundle\Uploader\ImageUploaderInterface`, added to the `IIIRxs\ImageUploadBundle\Uploader\ChainUploader`.

The simplest way to achieve this is by simply configuring the full path to the directory where images should be uploaded to. You can do this, just by configuring `default_image_upload_dir` value inside bundle's configuration file:

```yaml
// config/packages/iiirxs_image_upload.yaml
iiirxs_image_upload:
    default_image_upload_dir: '%kernel.project_dir%/public/images'
```
The bundle also provides the capability of creating an optimized version and a thumbnail for each uploaded image instead of simply moving the uploaded file to the target directory. To use this feature just provide an array with `optimized` and `thumbnails` keys instead of a single path:

```yaml
// config/packages/iiirxs_image_upload.yaml
iiirxs_image_upload:
    default_image_upload_dir:
        optimized: '%kernel.project_dir%/public/images/optimized'
        thumbnails: '%kernel.project_dir%/public/images/thumbnails'
```

Now a default uploader is registered inside the chain uploader and will be used to upload images for any image collection inside your application that is configured to be processed by the bundle.

### Step 3: Post image files
As all-needed functionality is already implemented inside `IIIRxs\ImageUploadBundle\Controller\ImageController`, all you need to do is the make the appropriate requests to `uploadImages` controller action.
### Image collection creation

###### All-together upload

To upload a new collection of images along with their ranks in a single request, you should make a post request to `iiirxs_image_upload` route in your javascript code with `Content-Type` header set to `multipart/form-data`. The payload  of the request should be a `FormData` object.

#### Example: Add collection of images to pre-existing image container object
```javascript
let formData = new FormData();
// name of the property containing the embedded collection 
let formField = 'images';
let imageInput = document.querySelector('#multiple_image_file_input');

formData.append(formField + '[0][file]', imageInput.files[0]);
formData.append(formField + '[0][rank]', 1);

formData.append(formField + '[1][file]', imageInput.files[1]);
formData.append(formField + '[1][rank]', 2);

// the object id to which images are being added
let id = 1;

// name of the class with the embedded collection
// route path: /{className}/{fieldName}/upload/{id}
let route = '/image-container-class/images/upload/' + id;

axios.post(route, formData);
```  
###### One-by-one upload
To upload a new image collection with n images, one-by-one, you should make n+1 post requests to iiirxs_image_upload route; one for each image, plus one for the image ranks.

#### Example: Add collection of images (multiple requests)
```javascript
let formField = 'images';
// the object id to which images are being added
let id = 1;

// name of the class with the embedded collection
// route path: /{className}/{fieldName}/upload/{id}
let route = '/image-container-class/images/upload/' + id;

let formData = new FormData();
// name of the property containing the embedded collection 
let imageInput = document.querySelector('#multiple_image_file_input');

formData.append(formField + '[0][file]', imageInput.files[0]);
formData.append(formField + '[1][file]', null);
let request1 = axios.post(route, formData);

formData = new FormData();
formData.append(formField + '[0][file]', null);
formData.append(formField + '[1][file]', imageInput.files[1]);
let request2 = axios.post(route, formData);

axios.all([request1, request2]).then(() => {
    let rankData = new FormData();
    rankData.append(formField + '[0][rank]', 1);
    rankData.append(formField + '[1][rank]', 2);

    axios.post(route, rankData);
})
```
### Addition to image collection

Adding a new image to an existing collection is very similar to the image collection creation with multiple requests. Please note that during such requests, you should always add a field with null value to the `FormData` object, for every existing image in your database if the corresponding rank field is not set in your `FormData` object, elsewise the image will be removed from the database:

#### Example: Add new image to existing collection
```javascript
let formData = new FormData();
// name of the property containing the embedded collection 
let formField = 'images';
let imageInput = document.querySelector('#image_file_input');

// A third image is being added
formData.append(formField + '[2][file]', imageInput.files[0]);

formData.append(formField + '[0][rank]', 1);
formData.append(formField + '[1][rank]', 2);
formData.append(formField + '[2][rank]', 3);

// the object id to which images are being added
let id = 1;

// name of the class with the embedded collection
// route path: /{className}/{fieldName}/upload/{id}
let route = '/image-container-class/images/upload/' + id;

axios.post(route, formData);
```  


#### Example: Add new image to existing collection (without ranks)
```javascript
let formData = new FormData();
// name of the property containing the embedded collection 
let formField = 'images';
let imageInput = document.querySelector('#image_file_input');

// A third image is being added
formData.append(formField + '[0][file]', null);
formData.append(formField + '[1][file]', null);
formData.append(formField + '[2][file]', imageInput.files[0]);

// the object id to which images are being added
let id = 1;

// name of the class with the embedded collection
// route path: /{className}/{fieldName}/upload/{id}
let route = '/image-container-class/images/upload/' + id;

axios.post(route, formData);
```

### Deletion from image collection

In order to delete an image from a collection you can just omit its key from the `FormData` object sent:
#### Example: Add new image to existing collection (without ranks)
```javascript
let formData = new FormData();
// name of the property containing the embedded collection 
let formField = 'images';

// Second image is being deleted
formData.append(formField + '[0][file]', null);
formData.append(formField + '[2][file]', null);

// the object id to which images are being added
let id = 1;

// name of the class with the embedded collection
// route path: /{className}/{fieldName}/upload/{id}
let route = '/image-container-class/images/upload/' + id;

axios.post(route, formData);
```

### Step 4 (optional): Post additional image details
In case you have additional fields added inside your Image class (e.g. description) you can post these details to `postImageDetails` controller action.

However, in order to do that, at first you should create your own form type class that extends `IIIRxs\ImageUploadBundle\Form\Type\ImageType` class:


```php
class CustomImageType extends ImageType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('description', TextType::class)
        ;
    }
}
```

Then you have to add the appropriate mapping to the bundle's configuration, so that the correct form type will be used for this specific embedded collection:

```yaml
// config/packages/iiirxs_image_upload.yaml

iiirxs_image_upload:
    mappings:
        App\Document\ImageContainerClass:
            fields:
                # name of the property containing the image collection
                images:
                    class: App\Document\CustomImage
                    form_type: App\Form\Type\CustomImageType
```

Now you can post image details by making a simple post request to the `iiirxs_image_upload_details_post` route. The payload of the request should be a json object with the structure of the example. 

#### Example: Post additional details to existing image collection

```javascript
// name of the property containing the embedded collection 
let formField = 'images';
let details = {
    [formField]: {
        0: { rank: 1, description: 'Lorem ipsum' },
        1: { rank: 2, description: 'dolor sit amet' },
    }
}

// the object id to which images are being added
let id = 1;

// name of the class with the embedded collection
// route path: /{className}/{fieldName}/details/{id}
let route = '/image-container-class/details/' + id;

axios.post(route, details);
```

You are **done**!! Your images will be stored inside the embedded collection in your MongoDB database and the corresponding files will be moved to the appropriate location in your filesystem.

Advanced usage
----------------------------------

Form
----------------------------------------
The bundle provides two basic form types:

- IIIRxs\ImageUploadBundle\Form\Type\ImageCollectionType
- IIIRxs\ImageUploadBundle\Form\Type\ImageType

It also provides an `ImageFormService` service  that creates `ImageCollectionType` forms based on Doctrine ODM mappings or explicit configuration.

`ImageCollectionType` provides a collection field (field name should be explicitly provided). Entry type of this collection field is by default `ImageType` class but you can override it through bundle or by passing form options appropriately.
  
`ImageType` only supports objects of that extend `AbstractImage` class.

If Doctrine ODM mappings inside your project is correctly defined or correct mapping exists inside bundle configuration all you need to do to create a `ImageCollectionType` form for a specific class is to provide an instance of this class and a valid field name (corresponding to an image collection) to ImageFormService:
```php
$object = new ImageContainer();

$form = $imageFormService->createForm($object, 'images');
```

To explicitly define mappings for specific classes and their respective form types, you can use bundle's configuration like this:
```yaml
// config/packages/iiirxs_image_upload.yaml

iiirxs_image_upload:
    mappings:
        App\Document\ImageContainer:
            fields:
                images:
                    class: App\Document\Image
                    form_type: App\Form\Type\YourOwnImageType
                anotherImageCollection:
                    class: App\Document\AnotherImageClass
                    form_type: App\Form\Type\AnotherImageType
```

Uploader
----------------------------------------
The bundle provides a `ChainUploader` class, an `AbstractUploader` class and `ImageUploaderInterface`. `ImageUploaderInterface` exposes two methods: `supports` and `upload`.

```yaml
// config/services.yaml
App\Service\ImageUploader:
    tags: ['image.uploader']
    arguments:
        imagesDir: '%kernel.project_dir%/public/images'
        # in order to save both optimized and thumbnail images for each upload:     
        # imagesDir:
        #     optimized: '%kernel.project_dir%/public/images/optimized'
        #     thumbnails: '%kernel.project_dir%/public/images/thumbnails'   
```

The easiest way to create an ImageUploader class is to extend `IIIRxs\ImageUploadBundle\Uploader\AbstractUploader` class that provides all needed functionality out of the box.

When extending `AbstractUploader` class, your `ImageUploader` service should receive at least two arguments it its constructor.
 
##### Constructor arguments
- `$imagesDir` which corresponds to a full path to the directory where images 
should be stored. It can be a string or an array, in case both optimized and 
thumbnail images should be generated. In the latter case the array must 
contain `optimized` and `thumbnails` keys with full paths as string values.
- `$maxThumbnailDimension` is used when thumbnails need to be created and it defines the maximum dimension in pixels for a thumbnail. By default it is set to 600px.
 
```php
// App\Service\ImageUploader

class ImageUploader extends AbstractUploader
{
	
	function __construct(string $imagesDir, int $maxThumbnailDimension)
	{
		parent::__construct($imagesDir, $maxThumbnailDimension);
	}

	public function supports($document): bool
    {
        // return true to support every document or "$document instanceof ExampleClass"// 
        // to support upload only for specific class.
        // Note that ExampleClass is a class containing the image file, typically a// 
        // subclass of AbstractImage class  
        return true;
    }

}
```


You can easily set the maximum thumbnail dimension inside bundle configuration:
```yaml
// config/packages/iiirxs_image_upload.yaml
iiirxs_image_upload:
    max_thumbnail_dimension: 400
```

The bundle registers all services tagged with 'image.uploader' tag inside the `ChainUploader` class. In order to use the `ChainUploader` inside a service, all you need to do is to inject it, and call the `selectUploader` and `upload` methods:
```php

class Service 
{
    public function __construct(ChainUploader $uploader)
    {
        $this->uploader = $uploader;
    }
    
    public function callUploader()
    {
        $this->uploader->selectUploader($image);
        $filename = $this->uploader->upload($image->getFile());
    }
}
```
Events
----------------------------------------
The bundle provides three events:

- `ImageDetailsPostEvent`
- `ImagesDeleteEvent`
- `ImagesUploadEvent`

and an event subscriber that listens to the `ImagesDeleteEvent` and `ImagesUploadEvent` events. The `ImageListener` uses `ChainUploader` service to upload files appropriately and also deletes image files when `ImagesDeleteEvent`
 is correctly dispatched.
 
You can set up your own functionality for this event subscriber by overriding `iiirxs_image_upload.event_listener.image_listener` service in your project's configuration.

Controllers
----------------------------------------
This bundle ultimately provides controller actions that handle everything regarding image uploading for you.

As mentioned before, if you want to use bundle's controller actions, the only needed thing is to register at least one service that implements `ImageUploaderInterface`, is tagged with 'image.uploader' tag and supports the uploading cases that you want.

The bundle provides two different actions: `uploadImages` for file uploading and `postImageDetails` for updating information related to images like image rank and image description.

Currently `uploadImages` action only supports file uploading through form submission with multipart/form-data content-type. 