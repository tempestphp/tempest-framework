# Versions & Support

Tempest follows a straightforward versioning and support policy. We focus our maintenance efforts on the latest stable major version, embrace modern PHP features, and provide automated upgrade paths to help projects stay up to date.

## Supported versions

Only the latest stable major version of Tempest is officially supported.

| Version | Status | Support |
|---|---|---|
| 4.x | Upcoming | Not yet released |
| 3.x | Current | Supported |
| 2.x and older | End of life | Unsupported |

When a new major version is released, the previous major version reaches its end of life and no longer receives official support.

This means that when Tempest 4.x is released, Tempest 3.x will no longer be supported.

## Long-term support

Tempest does not offer long-term support (LTS) releases.

Instead of maintaining multiple major versions simultaneously, we focus our efforts on improving and maintaining the latest version of the framework.

This allows us to move the framework forward without the maintenance burden of supporting older versions indefinitely.

We encourage developers to stay up to date with the latest stable major version.

## PHP version support

Tempest embraces modern PHP and aims to make full use of the language's latest features and improvements.

**The latest major version of Tempest targets the latest stable version of PHP.** We prioritize moving the framework forward rather than maintaining compatibility with older PHP versions.

As a result, upgrading to a new major version of Tempest may also require upgrading your PHP version.

We believe staying current with PHP allows us to build a simpler, more expressive, and more maintainable framework.

You can find the exact PHP requirements for your version of Tempest in the installation documentation.

## What does support include?

Supported versions may receive:

- Bug fixes
- Security fixes
- Compatibility updates
- General maintenance and improvements

Unsupported versions no longer receive official maintenance, including bug fixes and security updates.

We do not guarantee specific support periods or security response times.

## Upgrading between versions

We understand that upgrading between major versions can require changes to your application.

To make upgrades easier, **Tempest provides [Rector](https://getrector.com/) suites** that automate many of the changes required when moving between major versions.

Our goal is to make upgrading as straightforward as possible, allowing developers to adopt new versions without having to manually address every breaking change.

While Rector can automate many changes, some upgrades may still require manual adjustments. We recommend reviewing the changes and running your application's test suite after upgrading.

## Which version should I use?

For new projects, we always recommend using the latest stable major version of Tempest alongside the latest stable version of PHP.

For existing projects, we recommend keeping both Tempest and PHP up to date to benefit from security fixes, performance improvements, and new language features.