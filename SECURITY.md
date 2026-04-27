# TempestPHP Security Policy

## Reporting a Security Issue

If you think you have found a Security Issue within one or more of the TempestPHP repositories, don't use the Issues and don't publish a PR with proof of concept. In the first instance, report issues using [GitHub's security advisory reporting mechanism](https://github.com/tempestphp/tempest-framework/security/advisories/new), with as much information as you can provide, ideally including steps-to-recreate. Security reports submitted on this page are forwarded to the core maintainers, only.

The core maintainers will determine whether this is classified as a Security Issue, and address it accordingly, or whether it is classified as a regular bug, and may ask you to raise a GitHub Issue instead, at this time.

## Resolution Process

The core maintainers will aim to acknowledge and validate any reported Security Issue promptly.

Following the validation of a Security Issue, the core maintainers will broadly:

1. Work on a patch and commit it to the repository via GitHub following the usual processes.

2. Issue a release containing the security release.

3. Consider offering a Rector automated fix within the release, where appropriate.

4. Notify all subscribed TempestPHP parties via the usual channels (discord, blog, etc) that the updated is published.

## Keeping TempestPHP Secure

Several controls are in place to ensure that TempestPHP code releases are kept secure.

1. All maintainers with write access to the repository (currently, just core maintainers) utilise Multi-Factor Authentication.

2. Branch protection is configured on the repository.

3. All access rights and privileges (including automated accounts, API keys) are assigned on a Principle of Least Privilege basis.

4. Every Pull Request requires the successful completion of code quality and static analysis checks, and is reviewed by a core maintainer.

5. TempestPHP actively upgrades dependencies based on deprecations and notices from upstream packages where used.