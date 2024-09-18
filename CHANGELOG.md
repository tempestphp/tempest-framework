# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Add `LanguageHelper::pluralize` and `LanguageHelper::singularize`
- Unit tests for the CommandBus `dispatch` method.
- Replaced Ignition with Whoops.
- Properly detect environment from `.env` when present.
- `discovery:cache` command that will generate the discovery cache
- Separate CI actions for isolated tests
- Separate CI actions for integration tests
- Separate CI actions for code conventions

### Fixed
- Missing descriptions in composer files