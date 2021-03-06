# PHP Kernel

[![Build Status](https://img.shields.io/travis/weew/kernel.svg)](https://travis-ci.org/weew/kernel)
[![Code Quality](https://img.shields.io/scrutinizer/g/weew/kernel.svg)](https://scrutinizer-ci.com/g/weew/kernel)
[![Test Coverage](https://img.shields.io/coveralls/weew/kernel.svg)](https://coveralls.io/github/weew/kernel)
[![Version](https://img.shields.io/packagist/v/weew/kernel.svg)](https://packagist.org/packages/weew/kernel)
[![Licence](https://img.shields.io/packagist/l/weew/kernel.svg)](https://packagist.org/packages/weew/kernel)

## Table of contents

- [Installation](#installation)
- [Introduction](#introduction)
- [Usage](#usage)
    - [Creating a provider](#creating-a-provider)
    - [Registering providers](#registering-providers)
    - [Initialization](#initialization)
    - [Booting](#booting)
- [Extension](#extension)
    - [Sharing data between providers](#sharing-data-between-providers)
    - [Custom container support](#custom-container-support)
- [Existing container integrations](#existing-container-integrations)
- [Related projects](#related-projects)

## Installation

`composer require weew/kernel`

## Introduction

Kernel is responsible for the bootstrap process of service providers. It offers you a easy and intuitive way to register your own providers. The boot process consists of three steps - `instantiation`, `initialization` and `booting`. There is also an additional step - `shutdown`. This gives your providers a lot of flexibility on when to do what.

## Usage

### Creating a provider

Any class can be used as a provider. If the provider has any of these methods `configure`, `initialize`, `boot`, `shutdown`, the container will invoke them accordingly. It does not require a specific interface. This is by choice, I'll explain why I chose this solution in one of the future readme updates.

```php
class MyServiceProvider {}
// or
class MyServiceProvider {
    public function configure() {}
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

### Configuration

When you configure the kernel, all of its service providers get instantiated and configured.

```php
$kernel->configure();
```

### Initialization

When you initialize the kernel, all of its service providers get initialized.

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

A better way to enable container access for your providers is to replace the default implementation of the `IProviderInvoker` with your own. In this example I'll be using this powerful [container](https://github.com/weew/container).

```php
class ContainerProviderInvoker implements IProviderInvoker {
    private $container;

    public function __construct(IContainer $container) {
        $this->container = $container;
    }

    public function create($providerClass, IDictionary $shared) {
        $this->container->get($providerClass, ['shared' => $shared]);
    }

    public function configure($provider, IDictionary $shared) {
        $this->container->callMethod($provider, 'configure', ['shared' => $shared]);
    }

    public function initialize($provider, IDictionary $shared) {
        $this->container->callMethod($provider, 'initialize', ['shared' => $shared]);
    }

    public function boot($provider, IDictionary $shared) {
        $this->container->callMethod($provider, 'boot', ['shared' => $shared]);
    }

    public function shutdown($provider, IDictionary $shared) {
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

## Existing container integrations

There is an integration available for the [weew/container](https://github.com/weew/container) container. See [weew/kernel-container-aware](https://github.com/weew/kernel-container-aware).

## Related projects

- [PHP Container](https://github.com/weew/container) works very well together with this package.
