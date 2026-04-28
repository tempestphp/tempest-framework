# Tempest security policy

## Reporting a security issue

If you think you have found a security issue within Tempest, don't create a GitHub issue and don't publish a pull request with proof of concept. In the first instance, report issues using [GitHub's security advisory reporting mechanism](https://github.com/tempestphp/tempest-framework/security/advisories/new), with as much information as you can provide, ideally including steps-to-recreate. Security reports submitted on this page are forwarded to the core maintainers only.

The core maintainers will determine whether this is classified as a security issue, and address it accordingly, or whether it is classified as a regular bug, and may ask you to raise a GitHub issue instead, at this time.

## Resolution process

The core maintainers will aim to acknowledge and validate any reported security issue promptly.

Following the validation of a security issue, the core maintainers will broadly:

1. Work on a patch and commit it to the repository via GitHub following the usual processes.

2. Issue a release containing the security release.

3. Consider offering a Rector automated fix within the release, where appropriate.

4. Notify all subscribed Tempest parties via the usual channels (discord, blog, etc) that the updated is published.

## Keeping Tempest secure

Several controls are in place to ensure that Tempest code releases are kept secure.

1. All maintainers with write access to the repository use multi-factor authentication.

2. Branch protection is configured on the repository.

3. All access rights and privileges (including automated accounts, API keys) are assigned on a Principle of Least Privilege basis.

4. Every pull request requires the successful completion of code quality and static analysis checks, and is reviewed by a core maintainer.

5. Tempest actively upgrades dependencies based on deprecations and notices from upstream packages where used.