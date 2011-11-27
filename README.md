RedBulletPersistentObjectsBundle
================================

RedBulletPersistentObjectsBundle is a simple system that allows you to store
serialized versions of objects in the file system of a Symfony2 application.

Basic use case is for those times when you want to store something that you
just have one or two instances of an object and setting up entities is a bit
over the top but still need aspects of the object to be changeable via the app
and be accessed across requests. Item that makes most sense is a settings panel
for a administrator.

It's basically just a quick way to serialize an object and dump it on the file
system to be used by next request.

Installation
------------

Installation is pretty much the same as any other Symfony bundle

# Get the code

Put the code into your vendor directory: /vendors/bundles/RedBullet/PersistentObjectsBundle

For git install:

add
```
[RedBulletPersistentObjectsBundle]
    git=https://github.com/RedBullet/RedBulletPersistentObjectsBundle.git
    target=/bundles/RedBullet/PersistentObjectsBundle
```
to your deps file

and then run
 ``` bash
php bin/vendors update
```
or use submodules if that's your thing (and I doubt you need that explaining)

# Plug it into your app

in /app/AppKernel.php add
``` php
    new RedBullet\PersistentObjectsBundle\RedBulletPersistentObjectsBundle()
```
to the $bundles array

in /app/autoload.php add
``` php
    'RedBullet'        => __DIR__.'/../vendor/bundles'

to the array argument of the registerNamespaces method.

Now you're good to go, check the usage section of this for how to get going.

Usage
-----

Once installed you configure the application by adding a name and a namespaced
class path for each of the objects you want to persist.

``` yml
red_bullet_persistent_objects:
    mapping:
        name: Namespace\To\Class
        anotherName: Namespace\To\Another\Class
```

Then in your application you can interface with the objects with the object
manager

# Storing an object

``` php
$manager = $container->get('red_bullet_persistent_objects.manager');
$object = new Namespace\To\Class();
$manager->set('name', $object);
$manager->persist('name');
```

# Retrieving an object
``` php
$manager = $container->get('red_bullet_persistent_objects.manager');
$object = $manager->get('name');
```

Rules for naming an object are it must be alphanumeric with _ and dots and
although they can be mixed case they must be unique case-insensitively.

Any class can be stored so long as it can be serialized safely.


License
-------

RedBulletPersistentObjectsBundle is licensed under the [MIT License](https://github.com/RedBullet/RedBulletPersistentObjectsBundle/blob/master/Resources/meta/LICENSE).

The full license is available in Resources/Meta/LICENSE

Issues
------

Feel free to get in touch, hell it'll be interesting if there was just any users

:-)