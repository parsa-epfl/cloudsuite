# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 3.4.0 - 2018-12-22

### Added

- [#275](https://github.com/zendframework/zend-servicemanager/pull/275) Enables
  plugin managers to accept as a creation context PSR Containers not
  implementing Interop interface

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#268](https://github.com/zendframework/zend-servicemanager/pull/268) Fixes
  ReflectionBasedAbstractFactory trying to instantiate classes with private
  constructors

## 3.3.2 - 2018-01-29

### Added

- Nothing.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#243](https://github.com/zendframework/zend-servicemanager/pull/243) provides
  a fix to the `ReflectionBasedAbstractFactory` to resolve type-hinted arguments
  with default values to their default values if no matching type is found in
  the container.

- [#233](https://github.com/zendframework/zend-servicemanager/pull/233) fixes a
  number of parameter annotations to reflect the actual types used.

## 3.3.1 - 2017-11-27

### Added

- [#201](https://github.com/zendframework/zend-servicemanager/pull/201) and
  [#202](https://github.com/zendframework/zend-servicemanager/pull/202) add
  support for PHP versions 7.1 and 7.2.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#206](https://github.com/zendframework/zend-servicemanager/pull/206) fixes an
  issue where by callables in `Class::method` notation were not being honored
  under PHP 5.6.

## 3.3.0 - 2017-03-01

### Added

- [#180](https://github.com/zendframework/zend-servicemanager/pull/180) adds
  explicit support for PSR-11 (ContainerInterface) by requiring
  container-interop at a minimum version of 1.2.0, and adding a requirement on
  psr/container 1.0. `Zend\ServiceManager\ServiceLocatorInterface` now
  explicitly extends the `ContainerInterface` from both projects.

  Factory interfaces still typehint against the container-interop variant, as
  changing the typehint would break backwards compatibility. Users can
  duck-type most of these interfaces, however, by creating callables or
  invokables that typehint against psr/container instead.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.2.1 - 2017-02-15

### Added

- [#176](https://github.com/zendframework/zend-servicemanager/pull/176) adds
  the options `-i` or `--ignore-unresolved` to the shipped
  `generate-deps-for-config-factory` command. This flag allows it to build
  configuration for classes resolved by the `ConfigAbstractFactory` that
  typehint on interfaces, which was previously unsupported.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#174](https://github.com/zendframework/zend-servicemanager/pull/174) updates
  the `ConfigAbstractFactory` to allow the `config` service to be either an
  `array` or an `ArrayObject`; previously, only `array` was supported.

## 3.2.0 - 2016-12-19

### Added

- [#146](https://github.com/zendframework/zend-servicemanager/pull/146) adds
  `Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory`, which enables a
  configuration-based approach to providing class dependencies when all
  dependencies are services known to the `ServiceManager`. Please see
  [the documentation](docs/book/config-abstract-factory.md) for details.
- [#154](https://github.com/zendframework/zend-servicemanager/pull/154) adds
  `Zend\ServiceManager\Tool\ConfigDumper`, which will introspect a given class
  to determine dependencies, and then create configuration for
  `Zend\ServiceManager\AbstractFactory\ConfigAbstractFactory`, merging it with
  the provided configuration file. It also adds a vendor binary,
  `generate-deps-for-config-factory`, for generating these from the command
  line.
- [#154](https://github.com/zendframework/zend-servicemanager/pull/154) adds
  `Zend\ServiceManager\Tool\FactoryCreator`, which will introspect a given class
  and generate a factory for it. It also adds a vendor binary,
  `generate-factory-for-class`, for generating these from the command line.
- [#153](https://github.com/zendframework/zend-servicemanager/pull/153) adds
  `Zend\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory`. This
  class may be used as either a mapped factory or an abstract factory, and will
  use reflection in order to determine which dependencies to use from the
  container when instantiating the requested service, with the following rules:
  - Scalar values are not allowed, unless they have default values associated.
  - Values named `$config` type-hinted against `array` will be injected with the
    `config` service, if present.
  - All other array values will be provided an empty array.
  - Class/interface typehints will be pulled from the container.
- [#150](https://github.com/zendframework/zend-servicemanager/pull/150) adds
  a "cookbook" section to the documentation, with an initial document detailing
  the pros and cons of abstract factory usage.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#106](https://github.com/zendframework/zend-servicemanager/pull/106) adds
  detection of multiple attempts to register the same instance or named abstract
  factory, using a previous instance when detected. You may still use multiple
  discrete instances, however.

## 3.1.2 - 2016-12-19

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#167](https://github.com/zendframework/zend-servicemanager/pull/167) fixes
  how exception codes are provided to ServiceNotCreatedException. Previously,
  the code was provided as-is. However, some PHP internal exception classes,
  notably PDOException, can sometimes return other values (such as strings),
  which can lead to fatal errors when instantiating the new exception. The
  patch provided casts exception codes to integers to prevent these errors.

## 3.1.1 - 2016-07-15

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#136](https://github.com/zendframework/zend-servicemanager/pull/136) removes
  several imports to classes in subnamespaces within the `ServiceManager`
  classfile, removing potential name resolution conflicts that occurred in edge
  cases when testing.

## 3.1.0 - 2016-06-01

### Added

- [#103](https://github.com/zendframework/zend-servicemanager/pull/103) Allowing
  installation of `ocramius/proxy-manager` `^2.0` together with
  `zendframework/zend-servicemanager`.
- [#103](https://github.com/zendframework/zend-servicemanager/pull/103) Disallowing
  test failures when running tests against PHP `7.0.*`.
- [#113](https://github.com/zendframework/zend-servicemanager/pull/113) Improved performance
  when dealing with registering aliases and factories via `ServiceManager#setFactory()` and
  `ServiceManager#setAlias()`
- [#120](https://github.com/zendframework/zend-servicemanager/pull/120) The
  `zendframework/zend-servicemanager` component now provides a
  `container-interop/container-interop-implementation` implementation

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#97](https://github.com/zendframework/zend-servicemanager/pull/97) Typo corrections
  in the delegator factories documentation.
- [#98](https://github.com/zendframework/zend-servicemanager/pull/98) Using coveralls ^1.0
  for tracking test code coverage changes.

## 3.0.4 - TBD

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 3.0.3 - 2016-02-02

### Added

- [#89](https://github.com/zendframework/zend-servicemanager/pull/89) adds
  cyclic alias detection to the `ServiceManager`; it now raises a
  `Zend\ServiceManager\Exception\CyclicAliasException` when one is detected,
  detailing the cycle detected.
- [#95](https://github.com/zendframework/zend-servicemanager/pull/95) adds
  GitHub Pages publication automation, and moves the documentation to
  https://zendframework.github.io/zend-servicemanager/
- [#93](https://github.com/zendframework/zend-servicemanager/pull/93) adds
  `Zend\ServiceManager\Test\CommonPluginManagerTrait`, which can be used to
  validate that a plugin manager instance is ready for version 3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#90](https://github.com/zendframework/zend-servicemanager/pull/90) fixes
  several examples in the configuration chapter of the documentation, ensuring
  that the signatures are correct.
- [#92](https://github.com/zendframework/zend-servicemanager/pull/92) ensures
  that alias resolution is skipped during configuration if no aliases are
  present, and forward-ports the test from [#81](https://github.com/zendframework/zend-servicemanager/pull/81)
  to validate v2/v3 compatibility for plugin managers.

## 3.0.2 - 2016-01-24

### Added

- [#64](https://github.com/zendframework/zend-servicemanager/pull/64) performance optimizations
  when dealing with alias resolution during service manager instantiation

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#62](https://github.com/zendframework/zend-servicemanager/pull/62)
  [#64](https://github.com/zendframework/zend-servicemanager/pull/64) corrected benchmark assets signature
- [#72](https://github.com/zendframework/zend-servicemanager/pull/72) corrected link to the Proxy Pattern Wikipedia
  page in the documentation
- [#78](https://github.com/zendframework/zend-servicemanager/issues/78)
  [#79](https://github.com/zendframework/zend-servicemanager/pull/79) creation context was not being correctly passed
  to abstract factories when using plugin managers
- [#82](https://github.com/zendframework/zend-servicemanager/pull/82) corrected migration guide in the DocBlock of
  the `InitializerInterface`

## 3.0.1 - 2016-01-19

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- [#68](https://github.com/zendframework/zend-servicemanager/pull/68) removes
  the dependency on zend-stdlib by inlining the `ArrayUtils::merge()` routine
  as a private method of `Zend\ServiceManager\Config`.

### Fixed

- Nothing.

## 3.0.0 - 2016-01-11

First stable release of version 3 of zend-servicemanager.

Documentation is now available at http://zend-servicemanager.rtfd.org

### Added

- You can now map multiple key names to the same factory. It was previously
  possible in ZF2 but it was not enforced by the `FactoryInterface` interface.
  Now the interface receives the `$requestedName` as the *second* parameter
  (previously, it was the third).

  Example:

  ```php
  $sm = new \Zend\ServiceManager\ServiceManager([
      'factories'  => [
          MyClassA::class => MyFactory::class,
          MyClassB::class => MyFactory::class,
          'MyClassC'      => 'MyFactory' // This is equivalent as using ::class
      ],
  ]);

  $sm->get(MyClassA::class); // MyFactory will receive MyClassA::class as second parameter
  ```

- Writing a plugin manager has been simplified. If you have simple needs, you no
  longer need to implement the complete `validate` method.

  In versions 2.x, if your plugin manager only allows creating instances that
  implement `Zend\Validator\ValidatorInterface`, you needed to write the
  following code:

  ```php
  class MyPluginManager extends AbstractPluginManager
  {
    public function validate($instance)
    {
        if ($instance instanceof \Zend\Validator\ValidatorInterface) {
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin manager "%s" expected an instance of type "%s", but "%s" was received',
             __CLASS__,
             \Zend\Validator\ValidatorInterface::class,
             is_object($instance) ? get_class($instance) : gettype($instance)
        ));
    }
  }
  ```

  In version 3, this becomes:

  ```php
  use Zend\ServiceManager\AbstractPluginManager;
  use Zend\Validator\ValidatorInterface;

  class MyPluginManager extends AbstractPluginManager
  {
      protected $instanceOf = ValidatorInterface::class;
  }
  ```

  Of course, you can still override the `validate` method if your logic is more
  complex.

  To aid migration, `validate()` will check for a `validatePlugin()` method (which
  was required in v2), and proxy to it if found, after emitting an
  `E_USER_DEPRECATED` notice prompting you to rename the method.

- A new method, `configure()`, was added, allowing full configuration of the
  `ServiceManager` instance at once. Each of the various configuration methods —
  `setAlias()`, `setInvokableClass()`, etc. — now proxy to this method.

- A new method, `mapLazyService($name, $class = null)`, was added, to allow
  mapping a lazy service, and as an analog to the other various service
  definition methods.

### Deprecated

- Nothing

### Removed

- Peering has been removed. It was a complex and rarely used feature that was
  misunderstood most of the time.

- Integration with `Zend\Di` has been removed. It may be re-integrated later.

- `MutableCreationOptionsInterface` has been removed, as options can now be
  passed directly through factories.

- `ServiceLocatorAwareInterface` and its associated trait has been removed. It
  was an anti-pattern, and you are encouraged to inject your dependencies in
  factories instead of injecting the whole service locator.

### Changed/Fixed

v3 of the ServiceManager component is a completely rewritten, more efficient
implementation of the service locator pattern. It includes a number of breaking
changes, outlined in this section.

- You no longer need a `Zend\ServiceManager\Config` object to configure the
  service manager; you can pass the configuration array directly instead.

  In version 2.x:

  ```php
  $config = new \Zend\ServiceManager\Config([
      'factories'  => [...]
  ]);

  $sm = new \Zend\ServiceManager\ServiceManager($config);
  ```

  In ZF 3.x:

  ```php
  $sm = new \Zend\ServiceManager\ServiceManager([
      'factories'  => [...]
  ]);
  ```

  `Config` and `ConfigInterface` still exist, however, but primarily for the
  purposes of codifying and aggregating configuration to use.

- `ConfigInterface` has two important changes:
  - `configureServiceManager()` now **must** return the updated service manager
    instance.
  - A new method, `toArray()`, was added, to allow pulling the configuration in
    order to pass to a ServiceManager or plugin manager's constructor or
    `configure()` method.

- Interfaces for `FactoryInterface`, `DelegatorFactoryInterface` and
  `AbstractFactoryInterface` have changed. All are now directly invokable. This
  allows a number of performance optimization internally.

  Additionally, all signatures that accepted a "canonical name" argument now
  remove it.

  Most of the time, rewriting a factory to match the new interface implies
  replacing the method name by `__invoke`, and removing the canonical name
  argument if present.

  For instance, here is a simple version 2.x factory:

  ```php
  class MyFactory implements FactoryInterface
  {
      function createService(ServiceLocatorInterface $sl)
      {
          // ...
      }
  }
  ```

  The equivalent version 3 factory:

  ```php
  class MyFactory implements FactoryInterface
  {
      function __invoke(ServiceLocatorInterface $sl, $requestedName)
      {
          // ...
      }
  }
  ```

  Note another change in the above: factories also receive a second parameter,
  enforced through the interface, that allows you to easily map multiple service
  names to the same factory.

  To provide forwards compatibility, the original interfaces have been retained,
  but extend the new interfaces (which are under new namespaces). You can implement
  the new methods in your existing v2 factories in order to make them forwards
  compatible with v3.

- The for `AbstractFactoryInterface` interface renames the method `canCreateServiceWithName()`
  to `canCreate()`, and merges the `$name` and `$requestedName` arguments.

- Plugin managers will now receive the parent service locator instead of itself
  in factories. In version 2.x, you needed to call the method
  `getServiceLocator()` to retrieve the parent (application) service locator.
  This was confusing, and not IDE friendly as this method was not enforced
  through the interface.

  In version 2.x, if a factory was set to a service name defined in a plugin manager:

  ```php
  class MyFactory implements FactoryInterface
  {
      function createService(ServiceLocatorInterface $sl)
      {
          // $sl is actually a plugin manager

          $parentLocator = $sl->getServiceLocator();

          // ...
      }
  }
  ```

  In version 3:

  ```php
  class MyFactory implements FactoryInterface
  {
      function __invoke(ServiceLocatorInterface $sl, $requestedName)
      {
          // $sl is already the main, parent service locator. If you need to
          // retrieve the plugin manager again, you can retrieve it through the
          // servicelocator:
          $pluginManager = $sl->get(MyPluginManager::class);
          // ...
      }
  }
  ```

  In practice, this should reduce code, as dependencies often come from the main
  service locator, and not the plugin manager itself.

  To assist in migration, the method `getServiceLocator()` was added to `ServiceManager`
  to ensure that existing factories continue to work; the method emits an `E_USER_DEPRECATED`
  message to signal developers to update their factories.

- `PluginManager` now enforces the need for the main service locator in its
  constructor. In v2.x, people often forgot to set the parent locator, which led
  to bugs in factories trying to fetch dependencies from the parent locator.
  Additionally, plugin managers now pull dependencies from the parent locator by
  default; if you need to pull a peer plugin, your factories will now need to
  pull the corresponding plugin manager first.

  If you omit passing a service locator to the constructor, your plugin manager
  will continue to work, but will emit a deprecation notice indicatin you
  should update your initialization code.

- It's so fast now that your app will fly!

## 2.7.0 - 2016-01-11

### Added

- [#60](https://github.com/zendframework/zend-servicemanager/pull/60) adds
  forward compatibility features for `AbstractPluingManager` and introduces
  `InvokableFactory` to help forward migration to version 3.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#46](https://github.com/zendframework/zend-servicemanager/pull/46) updates
  the exception hierarchy to inherit from the container-interop exceptions.
  This ensures that all exceptions thrown by the component follow the
  recommendations of that project.
- [#52](https://github.com/zendframework/zend-servicemanager/pull/52) fixes
  the exception message thrown by `ServiceManager::setFactory()` to remove
  references to abstract factories.

## 2.6.0 - 2015-07-23

### Added

- [#4](https://github.com/zendframework/zend-servicemanager/pull/4) updates the
    `ServiceManager` to [implement the container-interop interface](https://github.com/container-interop/container-interop),
    allowing interoperability with applications that consume that interface.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#3](https://github.com/zendframework/zend-servicemanager/pull/3) properly updates the
  codebase to PHP 5.5, by taking advantage of the default closure binding
  (`$this` in a closure is the invoking object when created within a method). It
  also removes several `@requires PHP 5.4.0` annotations.
