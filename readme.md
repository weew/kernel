# PHP Kernel

[![Build Status](https://travis-ci.org/weew/php-kernel.svg?branch=master)](https://travis-ci.org/weew/php-kernel)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/weew/php-kernel/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/weew/php-kernel/?branch=master)
[![Coverage Status](https://coveralls.io/repos/weew/php-kernel/badge.svg?branch=master&service=github)](https://coveralls.io/github/weew/php-kernel?branch=master)
[![License](https://poser.pugx.org/weew/php-kernel/license)](https://packagist.org/packages/weew/php-kernel)

## Table of contents

- [Introduction](#introduction)
- [Usage](#usage)
    - [Creating a provider](#creating-a-provider)
    - [Registering providers](#registering-providers)
    - [Initialization](#initialization)
    - [Booting](#booting)
- [Extension](#extension)
    - [Sharing data between providers](#sharing-data-between-providers)
    - [Custom container support](#custom-container-support)
- [Related projects](#related-projects)

## Introduction

Kernel is responsible for the bootstrap process of service providers. It offers you a easy and intuitive way to register your own providers. The boot process consists of three steps - `instantiation`, `initialization` and `booting`. There is also an additional step - `shutdown`. This gives your providers a lot of flexibility on when to do what.

## Usage

### Creating a provider

Any class can be used as a provider. If the provider has any of these methods `initialize`, `boot`, `shutdown`, the container will invoke them accordingly. It does not require a specific interface. This is by choice, I'll explain why I chose this solution in one of the future readme updates.

```php
class MyServiceProvider {}
// or
class MyServiceProvider {
    public function initialize() {}
    public function boot() {}
    public function shutdown() {}
}
```

### Registering providers

It is fairly easy to create a kernel and register your own providers.

```php
$kernel = new Kernel();
$kernel->addProviders([
    MyServiceProvider::class,
    AnotherServiceProvider::class,
]);
```

### Initialization

When you initialize the kernel, all of its service providers get instantiated and initialized.

```php
$kernel->initialize();
```

### Booting

On boot, all service providers will be booted. This is a good place to setup your provider and do some work.

```php
$kernel->boot();
```

### Shutdown

This will shutdown the kernel and all of its providers.

```php
$kernel->shutdown();
```

## Extension

The kernel comes without a container. Out of the box the service providers will be very limited since they have no way to share anything. There are several workarounds for this.

### Sharing data between providers

The easiest way to share data between providers is to use kernel's shared arguments.

```php
class MyProvider {
    public function boot(IDictionary $shared) {
        $shared->get('container')['foo'] = 'bar';
    }
}

$kernel = new Kernel();
$container = [];
$kernel->getSharedArguments()->set('container', $container);
$kernel->addProvider(MyProvider::class);
```

### Custom container support

A better way to enable container access for your providers is to replace the default implementation of the `IProviderInvoker` with your own. In this example I'll be using this powerful [container](https://github.com/weew/php-container).

```php
class ContainerProviderInvoker implements IProviderInvoker {
    private $container;

    public function __construct(IContainer $container) {
        $this->container = $container;
    }

    public function create($providerClass, IDictionary $shared) {
        $this->container->get($providerClass, ['shared' => $shared]);
    }

    public function initialize(IProvider $provider, IDictionary $shared) {
        $this->container->callMethod($provider, 'initialize', ['shared' => $shared]);
    }

    public function boot(IProvider $provider, IDictionary $shared) {
        $this->container->callMethod($provider, 'boot', ['shared' => $shared]);
    }

    public function shutdown(IProvider $provider, IDictionary $shared) {
        $this->container->callMethod($provider, 'shutdown', ['shared' => $shared]);
    }
}

$container = new Container();
$invoker = new ContainerProviderInvoker($container);
$kernel = new Kernel($invoker);
// or
$kernel->setProviderInvoker($invoker);
```

From now on all providers will benefit from constructor and method injection and will be able to share anything in the container. Depending on which container package you use the `IProviderInvoker` implementation may vary, but the idea stays the same.

## Related projects

[PHP Container](https://github.com/weew/php-container) works very well together with this package.
