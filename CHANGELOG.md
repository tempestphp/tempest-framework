# Changelog

All notable changes to this project will be documented in this file.

## [3.0.1](https://github.com/tempestphp/tempest-framework/compare/v3.0.0..3.0.1)  —  2026-02-12

### 🐛 Bug fixes

- fix post-release dependency issues (#1960) ([d62c48d](https://github.com/tempestphp/tempest-framework/commit/d62c48db331a9647239695ae5a3b5da11422e676))


## [3.0.0](https://github.com/tempestphp/tempest-framework/compare/v2.13.0..v3.0.0)  —  2026-02-12

### 🚨 Breaking changes

- **console**: [**breaking**] allow `--force` to bypass `CautionMiddleware` (#1804) ([bccf92f](https://github.com/tempestphp/tempest-framework/commit/bccf92fb3e88c8ba35b1f89a8f893031a072df96))
- **core**: [**breaking**] make `Environment` its own source of truth (#1838) ([ad32ffe](https://github.com/tempestphp/tempest-framework/commit/ad32ffefe6186ebac8f9486dd28c2c185c798df1))
- **core**: [**breaking**] overhaul exception handling (#1819) ([314fb05](https://github.com/tempestphp/tempest-framework/commit/314fb05b3a3538df415393073a6a316dc2355784))
- **http**: [**breaking**] improve session management and CSRF protection (#1829) ([bd4be5e](https://github.com/tempestphp/tempest-framework/commit/bd4be5e83f19677bcef31d637a2e37476c0cc41b))
- **log**: [**breaking**] support multiple loggers (#1788) ([cfccb39](https://github.com/tempestphp/tempest-framework/commit/cfccb3921bc1e3858f0660a9559fdae3b9e16fdc))
- **mapper**: [**breaking**] move map function in `Tempest\Mapper` (#1789) ([b4b49ec](https://github.com/tempestphp/tempest-framework/commit/b4b49eceb2df32804ee6db0f69cbb6a4a8f521c8))
- **support**: [**breaking**] rename `Arr\map_iterable` to `Arr\map` (#1884) ([f97b0cf](https://github.com/tempestphp/tempest-framework/commit/f97b0cf9907ec1e6280216cf7abdc098ac76b977))
- **testing**: [**breaking**] remove deprecated testing utilities (#1849) ([ce478bf](https://github.com/tempestphp/tempest-framework/commit/ce478bf05ec916b94674e78bc633dcb4cc87370b))
- **testing**: [**breaking**] migrate view and route helpers to dedicated testers (#1870) ([e26572b](https://github.com/tempestphp/tempest-framework/commit/e26572bb3a497c5386477b53334557aa9a1febba))
- **validation**: [**breaking**] add ability to specify translation keys for specific properties (#1618) ([2537d2a](https://github.com/tempestphp/tempest-framework/commit/2537d2a1462e7a4afc51526fe539d97d0ab08c8e))
- **view**: [**breaking**] use `internal_storage_path` to build view cache ([8bb394d](https://github.com/tempestphp/tempest-framework/commit/8bb394d31481888c5b00092b7b595d026f63ab0a))
- **view**: [**breaking**] move `Tempest\view` to the `Tempest\View` namespace (#1860) ([360af87](https://github.com/tempestphp/tempest-framework/commit/360af87c06d6cdceae3662e7d48377a266069f50))
- [**breaking**] use consistent namespaces for functions (#1880) ([9621695](https://github.com/tempestphp/tempest-framework/commit/96216959a984d0c028997edc37e3764082656ef4))

### 🚀 Features

- **auth**: implement Twitch OAuth provider wrapper (#1919) ([0ad6cc7](https://github.com/tempestphp/tempest-framework/commit/0ad6cc7d68f0f3771eae1af3e2a33c8bc8a6eaf0))
- **console**: add native command completion for zsh and bash (#1851) ([1dd4946](https://github.com/tempestphp/tempest-framework/commit/1dd4946266a450c7cce22c6ce2df6d992d2dea4e))
- **console**: allow overriding internal storage in `boot` (#1904) ([64fc6c4](https://github.com/tempestphp/tempest-framework/commit/64fc6c45ca4ee19fdab7a3116150f0e586a0f73e))
- **console**: add ability to prevent built-in console commands from loading (#1906) ([4ef5a77](https://github.com/tempestphp/tempest-framework/commit/4ef5a77ef7a07f19e5ce857f31f9a4301c24d661))
- **console**: throw clear exception when sending unexpected input during console interactions (#1916) ([743e8d8](https://github.com/tempestphp/tempest-framework/commit/743e8d8b02525e9004288760c059e0835f4641a6))
- **core**: support php 8.5 (#1733) ([a1b0bcf](https://github.com/tempestphp/tempest-framework/commit/a1b0bcf88e740b0b0a72a439d3d977978f1a8cb2))
- **core**: support validating environment variables (#1836) ([f36b43b](https://github.com/tempestphp/tempest-framework/commit/f36b43b4a4859e87c20b0d7aa619cb0649bd4dd4))
- **core**: enable partial discovery by default during development (#1848) ([7f40a4e](https://github.com/tempestphp/tempest-framework/commit/7f40a4ee10a21ca8af0fc2e2f73ef91c9e60245e))
- **database**: support uuids as primary columns (#1807) ([4456541](https://github.com/tempestphp/tempest-framework/commit/44565410ff1e1a07f3381a897236714cc5cbf057))
- **database**: add pdo options to database configs (#1840) ([d75d54c](https://github.com/tempestphp/tempest-framework/commit/d75d54cf022a4273f4ebf9e2ffe376bcd8f33d62))
- **database**: add configurable migration naming strategy (#1928) ([e957640](https://github.com/tempestphp/tempest-framework/commit/e95764075c1948013a15512267698698f97b8791))
- **event-bus**: allow assertions without preventing event execution (#1841) ([ea48a8a](https://github.com/tempestphp/tempest-framework/commit/ea48a8aa1126949eb41e6a0ebc79486916d0bddf))
- **event-bus**: support enum events in `EventBus::listen()` (#1878) ([22f6cac](https://github.com/tempestphp/tempest-framework/commit/22f6cacf844d661953c1ad74d3fcfc1300d247f1))
- **mapper**: support contextual serializers and casters (#1791) ([3c0d1f3](https://github.com/tempestphp/tempest-framework/commit/3c0d1f30e096daa308b9739609f75eb6ddfa17fb))
- **mapper**: read `CastWith`/`SerializeWith` from interface definitions (#1883) ([d644091](https://github.com/tempestphp/tempest-framework/commit/d644091a31453158e0f0ebd02495fcb9477527c8))
- **reflection**: add union and intersection utils (#1798) ([378e0c0](https://github.com/tempestphp/tempest-framework/commit/378e0c0a0b97174046de011600c29fc78e49176d))
- **router**: infer constraints from route parameters (#1816) ([8d82c8c](https://github.com/tempestphp/tempest-framework/commit/8d82c8c1cc7a3d91a8f9ac892b5b4086257fac42))
- **session**: add redis session manager (#1790) ([eb7150b](https://github.com/tempestphp/tempest-framework/commit/eb7150b8c4226397feee5311b0033c5f05fd9be1))
- **support**: add `Filesystem\create_temporary_directory()` (#1901) ([1d09649](https://github.com/tempestphp/tempest-framework/commit/1d09649fc2910d285f89ef096e82889368437da9))
- **support**: add `Filesystem\copy_directory` and `Filesystem\copy` (#1909) ([9595a83](https://github.com/tempestphp/tempest-framework/commit/9595a838275a38484f381bb5d6b3d418be2e5f74))
- **testing**: support passing raw body to route test utils (#1876) ([a75fce7](https://github.com/tempestphp/tempest-framework/commit/a75fce703e952d5e04434272c82c552e201e9c48))
- **validation**: add closure-based validation (#1828) ([b6d4668](https://github.com/tempestphp/tempest-framework/commit/b6d46688837c4df64dcfac706d908986dd8db0f1))
- **view**: support expression attribute fallthrough (#1811) ([5d8bbad](https://github.com/tempestphp/tempest-framework/commit/5d8bbad9f24b83f6a4de842e628ac8fc46b2d92e))

### ⚡ Performance

- **database**: only use serializer factory when needed (#1898) ([ad7825b](https://github.com/tempestphp/tempest-framework/commit/ad7825b41981e2341b87b3ebcff8e060bed951f6))
- **mapper**: pre-resolve mapper classes (#1852) ([49fc4e2](https://github.com/tempestphp/tempest-framework/commit/49fc4e2ba35e5ed4670dcc653652d91fd60eeb84))
- **orm**: memoize mapper data to improve ORM performance (#1855) ([5ec931a](https://github.com/tempestphp/tempest-framework/commit/5ec931ac21682216a21d77f8a5c85c9cd9da15d5))

### 🐛 Bug fixes

- **auth**: properly run migrations when installing auth (#1927) ([5bb84c1](https://github.com/tempestphp/tempest-framework/commit/5bb84c182ffd93ee3e2ad7473856be4057c952b3))
- **cache**: ensure unique lock acquisition (#1757) ([0f08031](https://github.com/tempestphp/tempest-framework/commit/0f08031dce3160ebb91145c7316480b1eba11a49))
- **command-bus**: prevent crash when accessing deleted pending commands (#1926) ([40b73e4](https://github.com/tempestphp/tempest-framework/commit/40b73e4007cb7048f50a65b4d7a7f8963114eadf))
- **console**: add zsh cache cleanup instructions after uninstall (#1862) ([6f27ade](https://github.com/tempestphp/tempest-framework/commit/6f27adeb7684957902bc21bf7a0b3d6d5de0fe6c))
- **core**: correctly load symlinked config files (#1875) ([c823d63](https://github.com/tempestphp/tempest-framework/commit/c823d633d3d538556a7b2c09b8c089d5b89cada8))
- **cryptography**: add `key:generate` hint to invalid key exceptions (#1925) ([965f2ee](https://github.com/tempestphp/tempest-framework/commit/965f2ee3bc9fcad570fd4eecf10db1c4113f4a9b))
- **database**: support route binding through `IsDatabaseModel` (#1794) ([3556acb](https://github.com/tempestphp/tempest-framework/commit/3556acb1538a26cb21b33fb0e0d15180b64527c9))
- **database**: support pagination with joins and relations (#1801) ([0b52ffd](https://github.com/tempestphp/tempest-framework/commit/0b52ffd2cd18db89bb27fa3ace5b409e90f20f7e))
- **database**: update generics on query builder (#1833) ([92908e3](https://github.com/tempestphp/tempest-framework/commit/92908e369c3818f9bd6a3fa7876183d2b26ab063))
- **database**: improve raw sql serialization consistency (#1861) ([36113b7](https://github.com/tempestphp/tempest-framework/commit/36113b73aa1fc9742e934aced2a75461286880dd))
- **database**: wrap enums in quotes during raw sql serialization (#1871) ([f3d67f0](https://github.com/tempestphp/tempest-framework/commit/f3d67f0cca05dc238433529a9bca8950cb3b9305))
- **database**: use singleton serializer factory instead of creating a new one (#1877) ([7f9a2ec](https://github.com/tempestphp/tempest-framework/commit/7f9a2ec5bd6d920aee924f86ada9eb10e7849382))
- **debug**: prevent infinite recursion when debugging in `ItemsDebugged` events (#1956) ([1425014](https://github.com/tempestphp/tempest-framework/commit/142501411052ce808b122016196b1611fad0ef1f))
- **discovery**: handle the discovery test case corectlly (#1839) ([c89a15c](https://github.com/tempestphp/tempest-framework/commit/c89a15c8dd6ad22b07651bda853d0d024b72f1cb))
- **http**: gracefully recover from invalid file sessions (#1872) ([db834ab](https://github.com/tempestphp/tempest-framework/commit/db834abd0cbc67e3763ca2b9b1428fbf17a5e64d))
- **http**: prevent rendering development exception on validation errors (#1887) ([56ba5ea](https://github.com/tempestphp/tempest-framework/commit/56ba5ea92d8458038af566391e4eb3279481e02f))
- **http**: broken import in header session id resolver (#1893) ([afe65d3](https://github.com/tempestphp/tempest-framework/commit/afe65d3e4cb7d82ff57c303226b736d9e80effda))
- **http-client**: support unknown status codes (#1885) ([bd65ba8](https://github.com/tempestphp/tempest-framework/commit/bd65ba8190ad2905d46759ae9680e767256041c8))
- **http-client**: remove unused dependency on `tempest/router` (#1902) ([df69277](https://github.com/tempestphp/tempest-framework/commit/df692775e3d3e3532db6530b9a9bd1b42969eecf))
- **intl**: throw explicit exception when encountering unsupported translation file formats (#1864) ([0350d98](https://github.com/tempestphp/tempest-framework/commit/0350d980740283cf66ea31991f756b429c4a3fb4))
- **intl**: add missing dependency to ext-intl (#1866) ([516a0ac](https://github.com/tempestphp/tempest-framework/commit/516a0ac6e7a4092fd97f46f7df98fd7e97d252da))
- **kv-store**: allow Predis connection errors to be caught (#1930) ([6c1a725](https://github.com/tempestphp/tempest-framework/commit/6c1a7250985ebefa262b004457c95c6f3add19ad))
- **reflection**: correctly detect nullable types in TypeReflector (#1924) ([8947072](https://github.com/tempestphp/tempest-framework/commit/894707250f6ba542389043f5d23c6c3bb4ff1f92))
- **router**: add null checks and fix route parameter handling (#1778) ([c89c345](https://github.com/tempestphp/tempest-framework/commit/c89c345da59a97612c4ac5807cafee0b5e1b1da9))
- **router**: add missing ui composable imports (#1853) ([38128aa](https://github.com/tempestphp/tempest-framework/commit/38128aa569c18f535c0742594256ff591f08d31d))
- **router**: support rendering development exceptions with html entities (#1854) ([0e8edb4](https://github.com/tempestphp/tempest-framework/commit/0e8edb44cc93a1bba8bf48a57f30a99ccfcdd2be))
- **router**: prevent memory exhaustion when serializing exception arguments (#1859) ([7e8ec28](https://github.com/tempestphp/tempest-framework/commit/7e8ec2896038534847c487b01f0d50d9e61f9484))
- **router**: truncate exception page symbols when they are too long (#1957) ([2221da7](https://github.com/tempestphp/tempest-framework/commit/2221da71c4f25003e13aa35c1b99617749431a0d))
- **support**: prevent `Path\normalize` from removing `0` from paths (#1817) ([1af376a](https://github.com/tempestphp/tempest-framework/commit/1af376a1aefa3a69cb3386e5f3507f51540a3997))
- **support**: support deleting symlinks using `Filesystem\delete` (#1847) ([25d69dd](https://github.com/tempestphp/tempest-framework/commit/25d69dd719879f1507cb37cf90af83657988a82b))
- **view**: throw exception when parsing xml views with `short_open_tag` enabled (#1795) ([30b2a6f](https://github.com/tempestphp/tempest-framework/commit/30b2a6ff8f8d4d108b334109748c96fe56ad8bde))
- **view**: handle egde case with falsy nodes (#1825) ([37e908d](https://github.com/tempestphp/tempest-framework/commit/37e908d4d25f28fc405ceac46b759b45b92e1a58))
- **view**: fallthrough attributes with empty values (#1858) ([4332740](https://github.com/tempestphp/tempest-framework/commit/433274087c709f94ebc6442028574e3a990206d7))
- **view**: keep whitespaces during render (#1881) ([07c5aaf](https://github.com/tempestphp/tempest-framework/commit/07c5aafcb95fdf829966727790b94fdde67ac398))
- **view**: do not cache views in local environments by default (#1891) ([cfef0cc](https://github.com/tempestphp/tempest-framework/commit/cfef0cc19b17527f206dc9e883cc5deea7faea5f))


## [2.13.0](https://github.com/tempestphp/tempest-framework/compare/v2.12.0..v2.13.0)  —  2025-12-04

### 🚀 Features

- **auth**: add OAuth installer (#1674) ([9c82b71](https://github.com/tempestphp/tempest-framework/commit/9c82b715b448633a704591e9b78823da28debc98))
- **cache**: make `assertLocked` ensure that the checked lock has an expiration (#1758) ([1a2e8fb](https://github.com/tempestphp/tempest-framework/commit/1a2e8fbe90259d2bd5a8a0876d4b8fed35c5dcd7))
- **container**: make all container properties publicly readable (#1785) ([be93ec1](https://github.com/tempestphp/tempest-framework/commit/be93ec1388ec7d253637705d4335d13a78a39f00))
- **database**: add support for self-referencing relations (#1745) ([df2dcdc](https://github.com/tempestphp/tempest-framework/commit/df2dcdc231384d2dd359f8b621f0ae1f31a3e703))
- **http**: add support to mark Request properties as #[SensitiveField] (#1746) ([0000c99](https://github.com/tempestphp/tempest-framework/commit/0000c99251b31d0bfa84389fb101be1560d916c3))

### 🐛 Bug fixes

- **auth**: correctly map user in GitHub OAuth provider (#1751) ([ad2182a](https://github.com/tempestphp/tempest-framework/commit/ad2182ac40684b78752e7f7511228688f5093c1a))
- **auth**: pass scopes/options to auth URL builder (#1750) ([cbe54d7](https://github.com/tempestphp/tempest-framework/commit/cbe54d7f3f7e137fe43e9ad7f8837bd2f7103e9a))
- **auth**: update outdated authenticatable import (#1752) ([5c68b96](https://github.com/tempestphp/tempest-framework/commit/5c68b968763229dfb5a78c01a80df3b1b134e6c0))
- **cache**: support enum tags (#1756) ([678b695](https://github.com/tempestphp/tempest-framework/commit/678b69582e526e25ff545c346179bda9636f1415))
- **cache**: add descriptions to `cache:clear` arguments (#1755) ([e324f6e](https://github.com/tempestphp/tempest-framework/commit/e324f6e767b50acd6e76e8310be12422b85e782b))
- **command-bus**: extract uuid from pending commands when not provided (#1761) ([b787c16](https://github.com/tempestphp/tempest-framework/commit/b787c16e57f60de3bd7883944561f02fce3a661a))
- **console**: properly normalize boolean flag names (#1762) ([c6e6867](https://github.com/tempestphp/tempest-framework/commit/c6e6867ede678b9798386bab12e1e2afaef91bc8))
- **core**: gracefully handle missing seeders when using `db:seed` (#1759) ([450ca75](https://github.com/tempestphp/tempest-framework/commit/450ca7576c6e5a8f4f5719dd27e7d4d4a29954c9))
- **process**: properly return exit code if missing (#1776) ([9ad1587](https://github.com/tempestphp/tempest-framework/commit/9ad158747a810db490aef43a7a6c1bcfe062d900))


## [2.12.0](https://github.com/tempestphp/tempest-framework/compare/v2.11.0..v2.12.0)  —  2025-11-28

### 🚀 Features

- **eventbus**: add `#[StopsPropagation]` (#1740) ([5769ec2](https://github.com/tempestphp/tempest-framework/commit/5769ec215d96b4dbdac9a3e2fb93f97de152fca5))
- **validation**: support nullable enums (#1739) ([a9ca8c4](https://github.com/tempestphp/tempest-framework/commit/a9ca8c4d60aca0ba4e14d0c31ce056c02415c10e))

### 🐛 Bug fixes

- **core**: remove php 8.5 deprecations (#1742) ([7501c1b](https://github.com/tempestphp/tempest-framework/commit/7501c1b494a2ec2e5ee27f4a8407fbdef5e484ae))
- **support**: correct some string function oversights (#1743) ([f113128](https://github.com/tempestphp/tempest-framework/commit/f113128d71bd5fd10568f9cc07a86732f4e6a116))
- **support**: process error message after callback in box() (#1741) ([908352a](https://github.com/tempestphp/tempest-framework/commit/908352a81969be7d4652b1c62758261b4751ee35))


## [2.11.0](https://github.com/tempestphp/tempest-framework/compare/v2.10.0..v2.11.0)  —  2025-11-26

### 🚀 Features

- **core**: partial discovery loading (#1737) ([92a31c3](https://github.com/tempestphp/tempest-framework/commit/92a31c3a2ccfecbea1b0ffdf0f212af23d0b36a7))


## [2.10.0](https://github.com/tempestphp/tempest-framework/compare/v2.9.3..v2.10.0)  —  2025-11-26

### 🚀 Features

- **core**: load composer dev namespaces (#1736) ([892da0c](https://github.com/tempestphp/tempest-framework/commit/892da0ce1a2b0b0b44a4b48121c4a3d7dc3e862d))
- **database**: add ability to create a query builder from another one (#1725) ([55204a0](https://github.com/tempestphp/tempest-framework/commit/55204a05c2d69ca3376d74cb795be6fb389e28a4))
- **tests**: support paratest (#1721) ([f5b5cd3](https://github.com/tempestphp/tempest-framework/commit/f5b5cd3f61c0d49fdeb38f97d6de861893ac4cbc))
- **view**: fixes PHP 8.5 null offset deprecation warning (#1729) ([2349c71](https://github.com/tempestphp/tempest-framework/commit/2349c71f960f3638bed89f703d282c451b4536b8))


## [2.9.3](https://github.com/tempestphp/tempest-framework/compare/v2.9.2..v2.9.3)  —  2025-11-20

### 🐛 Bug fixes

- **router**: use route registry to generate uris (#1724) ([6dc51c2](https://github.com/tempestphp/tempest-framework/commit/6dc51c29e40092379f918e6f3bd47862c2e090d1))


## [2.9.2](https://github.com/tempestphp/tempest-framework/compare/v2.9.1..v2.9.2)  —  2025-11-19

### 🐛 Bug fixes

- **intl**: make pluralizer singleton (#1726) ([39b2b2d](https://github.com/tempestphp/tempest-framework/commit/39b2b2da8683a1d08b5ebabcd71f35486a85e403))


## [2.9.0](https://github.com/tempestphp/tempest-framework/compare/v2.8.0..v2.9.0)  —  2025-11-14

### 🚀 Features

- **router**: improve optional route parameter syntax (#1706) ([68f4aba](https://github.com/tempestphp/tempest-framework/commit/68f4aba41f8df225792aeaf5c94dca818079e0f8))
- **testing**: inject app config in Integration test setup (#1710) ([14a8da8](https://github.com/tempestphp/tempest-framework/commit/14a8da80880dda8eb6eb0b1222d75adf578908d5))

### 🐛 Bug fixes

- **console**: render nullable enum arguments (#1711) ([402f0e7](https://github.com/tempestphp/tempest-framework/commit/402f0e7547fef30f68c78bb0bef26607b449f7fc))
- **eventbus**: change dispatched assertion from not null to not empty (#1709) ([82318a6](https://github.com/tempestphp/tempest-framework/commit/82318a6a4362c228f6b81bb23ee752b950ed17cc))
- **view**: support relative view paths on windows (#1703) ([87b2f7b](https://github.com/tempestphp/tempest-framework/commit/87b2f7bfd43c2d277ee788ffbbe8b072f232771a))


## [2.8.0](https://github.com/tempestphp/tempest-framework/compare/v2.7.2..v2.8.0)  —  2025-11-10

### 🚨 Breaking changes

- **router**: [**breaking**] add route decorators (#1695) ([c901dfe](https://github.com/tempestphp/tempest-framework/commit/c901dfeec7c01394a4481a08ca8381988a0b03ad))


## [2.7.2](https://github.com/tempestphp/tempest-framework/compare/v2.7.1..v2.7.2)  —  2025-11-10

### 🐛 Bug fixes

- **console**: respect default value in confirm when forced (#1698) ([708c8f9](https://github.com/tempestphp/tempest-framework/commit/708c8f9ee5cef6c19d26e8ebcb069633341413ce))


## [2.7.1](https://github.com/tempestphp/tempest-framework/compare/v2.7.0..v2.7.1)  —  2025-11-09

### 🚀 Features

- **auth**: mark password property with `#[SensitiveParameter]` (#1693) ([129fdd5](https://github.com/tempestphp/tempest-framework/commit/129fdd54cd989203508b461bbbbddf85448aabb7))

### 🐛 Bug fixes

- **view**: discovery locations for view compiler (#1701) ([8604b86](https://github.com/tempestphp/tempest-framework/commit/8604b86e3118a79f8813df5a6f2315a802368a5c))


## [2.7.0](https://github.com/tempestphp/tempest-framework/compare/v2.6.3..v2.7.0)  —  2025-11-07

### 🚀 Features

- **router**: add `#[Stateless]` attribute (#1692) ([86d140d](https://github.com/tempestphp/tempest-framework/commit/86d140d8ac03b9fb1c49eaa867cc89fa448f5da8))


## [2.6.3](https://github.com/tempestphp/tempest-framework/compare/v2.6.2..v2.6.3)  —  2025-11-07

### 🐛 Bug fixes

- **database**: revert broken mysql dsn changes (#1689) ([9edc4d2](https://github.com/tempestphp/tempest-framework/commit/9edc4d2ba4e132aeb5bba6f6114a60b175f50b2a))


## [2.6.2](https://github.com/tempestphp/tempest-framework/compare/v2.6.1..v2.6.2)  —  2025-11-07

### 🐛 Bug fixes

- **http**: cleanup session without starting a new one (#1688) ([9a7dee6](https://github.com/tempestphp/tempest-framework/commit/9a7dee6491e314841fb758223370cdaeb5e88239))


## [2.6.1](https://github.com/tempestphp/tempest-framework/compare/v2.6.0..v2.6.1)  —  2025-11-07

### 🐛 Bug fixes

- **http**: gracefully recover from corrupt session retrieval (#1687) ([8c5d8cc](https://github.com/tempestphp/tempest-framework/commit/8c5d8cc501096a27aaeb96e520643727e741eec9))


## [2.6.0](https://github.com/tempestphp/tempest-framework/compare/v2.5.0..v2.6.0)  —  2025-11-07

### 🚀 Features

- **view**: standalone `TempestViewRenderer` support (#1686) ([2f5a3bc](https://github.com/tempestphp/tempest-framework/commit/2f5a3bc317f24852bdff5851a70bb8007248c0aa))


## [2.5.0](https://github.com/tempestphp/tempest-framework/compare/v2.4.0..v2.5.0)  —  2025-11-06

### 🚀 Features

- **core**: support booting in phar (#1672) ([536db47](https://github.com/tempestphp/tempest-framework/commit/536db4750fa5c2d1497e93645199be333a558132))
- **core**: make discovery cache environment variable partial by default (#1682) ([f50af80](https://github.com/tempestphp/tempest-framework/commit/f50af80f5f72ff467b827ef18341b03c2569aef0))
- **view**: add `:isset` attribte (#1675) ([1af3b23](https://github.com/tempestphp/tempest-framework/commit/1af3b23e843d7646617413a57b4fb0a8fa9ff3a4))
- **view**: support single-quote attributes (#1678) ([071993a](https://github.com/tempestphp/tempest-framework/commit/071993ab2b29e3a909812062bfbccaf482c6f2f8))

### 🐛 Bug fixes

- **database**: fix dsn format for mysql connection (#1664) ([6c3cbe2](https://github.com/tempestphp/tempest-framework/commit/6c3cbe24442353d85a6a05d9a5320fb6a01e3301))
- **events**: prevent enum event naming collisions (#1681) ([1602654](https://github.com/tempestphp/tempest-framework/commit/16026543ab6f38d4f676b95c58797d83a56c089d))
- **view**: zero-values in attributes (#1679) ([66dda2f](https://github.com/tempestphp/tempest-framework/commit/66dda2f2f57b4292e96348904901cf27fac84cb8))
- psr-discovery dependency (#1655) ([a1679a1](https://github.com/tempestphp/tempest-framework/commit/a1679a125de8eb32bafe576d78d2af6a829ee641))


## [2.4.0](https://github.com/tempestphp/tempest-framework/compare/v2.3.3..v2.4.0)  —  2025-10-22

### 🚨 Breaking changes

- **http**: [**breaking**] add `--crawl` flag to `static:generate` command (#1656) ([fee1230](https://github.com/tempestphp/tempest-framework/commit/fee1230971fed3cc7ec1836fe29c89f5d32a87b5))

### 🚀 Features

- **http**: add accepts helper method (#1638) ([b61d352](https://github.com/tempestphp/tempest-framework/commit/b61d352e9842fc5a4bcd5aef7ec93ae63bba8a6b))
- **intl**: add `current_locale` util (#1643) ([1dab1c7](https://github.com/tempestphp/tempest-framework/commit/1dab1c7e6629725e81f40e13aefd6dbcf81302ff))

### 🐛 Bug fixes

- **auth**: invalid key file arguments for Apple OAuth provider (#1640) ([bf476c0](https://github.com/tempestphp/tempest-framework/commit/bf476c00ac80e6b2ef6abbbfbd2159f180d3bb33))
- **http**: desrialize csrf token from headers (#1616) ([d1ee721](https://github.com/tempestphp/tempest-framework/commit/d1ee7211e9dd457350fc9d22819e09de22beafff))
- **reflection**: return null for method return type if not defined (#1645) ([b3acd5f](https://github.com/tempestphp/tempest-framework/commit/b3acd5f4cf6195ab9047a1b8ca21b8b50d4cddb1))
- **view**: handle boolean attribute followed by non space whitespace or self-closing tags (#1632) ([cd226a3](https://github.com/tempestphp/tempest-framework/commit/cd226a3d9ad99875cf2bc707b2e8e5820f739c0a))


## [2.3.3](https://github.com/tempestphp/tempest-framework/compare/v2.3.2..v2.3.3)  —  2025-10-09

### 🚀 Features

- **http**: support passing `JsonSerializable` to `Json` response (#1626) ([930e7ee](https://github.com/tempestphp/tempest-framework/commit/930e7eed7cecde43ebf61d9928f71fbdd0c57301))

### 🐛 Bug fixes

- **core**: optional dependency guards (#1630) ([1b23fd4](https://github.com/tempestphp/tempest-framework/commit/1b23fd4113a6f42a89b0894714ad48e86801ac33))


## [2.3.2](https://github.com/tempestphp/tempest-framework/compare/v2.3.1..v2.3.2)  —  2025-10-08

### 🐛 Bug fixes

- **installer**: set correct default port on base_uri (#1625) ([ef00d98](https://github.com/tempestphp/tempest-framework/commit/ef00d98f5996882dcac5898dc64e9f467512548f))
- make package dependencies optional where possible (#1624) ([530c226](https://github.com/tempestphp/tempest-framework/commit/530c226b51732e6e290b575e7a55cda751f05cd9))


## [2.3.1](https://github.com/tempestphp/tempest-framework/compare/v2.3.0..v2.3.1)  —  2025-10-07

### 🐛 Bug fixes

- **view**: support void tag rendering for XML files (#1621) ([a395534](https://github.com/tempestphp/tempest-framework/commit/a3955340171d2ae8eb4a35934418906e40026c34))


## [2.3.0](https://github.com/tempestphp/tempest-framework/compare/v2.2.1..v2.3.0)  —  2025-10-06

### 🚀 Features

- **console**: support variadic argument (#1572) ([b5f4185](https://github.com/tempestphp/tempest-framework/commit/b5f41858d6674efca092d71f3504323392a103f4))
- **container**: support decorators (#1537) ([2d29bd5](https://github.com/tempestphp/tempest-framework/commit/2d29bd5452e2363b4e44b5218df9a9291f1798cf))
- **http**: support database-based sessions (#1605) ([174044c](https://github.com/tempestphp/tempest-framework/commit/174044c7726d8d804465474ab2905c43cc0113df))
- **view**: parse RSS feeds with tempest/view (#1617) ([7398040](https://github.com/tempestphp/tempest-framework/commit/7398040393997d886f59fb4a8ba6d351ee56f9ac))

### 🐛 Bug fixes

- **database**: handle loading circular eager relations (#1556) ([b2e0c75](https://github.com/tempestphp/tempest-framework/commit/b2e0c75c67eb46458d4c8c32f9ae289b4a7ad930))
- **database**: multiple select fields in one statement (#1603) ([cd51bcf](https://github.com/tempestphp/tempest-framework/commit/cd51bcf9a8c9380d216295178517d4202b40561c))
- **http**: improve failed request exception messages (#1598) ([a84ce29](https://github.com/tempestphp/tempest-framework/commit/a84ce292496175de43a3685aece3210863a1fc2d))
- **http**: publish migration during database session driver installation (#1606) ([2d6fa1b](https://github.com/tempestphp/tempest-framework/commit/2d6fa1b843deaddc998f0cd59c9abbc5e03e982c))


## [2.2.1](https://github.com/tempestphp/tempest-framework/compare/v2.2.0..v2.2.1)  —  2025-10-03

### 🐛 Bug fixes

- **database**: update or create with initial values (#1597) ([d4450aa](https://github.com/tempestphp/tempest-framework/commit/d4450aa6a3b5bab0ccad38617b698ba51a5fe705))


## [2.2.0](https://github.com/tempestphp/tempest-framework/compare/v2.1.0..v2.2.0)  —  2025-10-02

### 🚀 Features

- **auth**: improve OAuth user flow (#1587) ([873fae9](https://github.com/tempestphp/tempest-framework/commit/873fae97cc30372860e0686b4da91ee7705b9cd1))

### 🐛 Bug fixes

- **oauth**: properly set state when creating the redirect URL (#1592) ([885a219](https://github.com/tempestphp/tempest-framework/commit/885a219ace57347130bd64220ce455d48675ffbc))


## [2.1.0](https://github.com/tempestphp/tempest-framework/compare/v2.0.4..v2.1.0)  —  2025-10-02

### 🚨 Breaking changes

- **http**: [**breaking**] add `query` parameter to route testing utilities (#1583) ([a819979](https://github.com/tempestphp/tempest-framework/commit/a819979a155d58fcfb5adbf51cab39cc2ee47ca6))

### 🚀 Features

- **auth**: add support for OAuth (#1577) ([742d4fb](https://github.com/tempestphp/tempest-framework/commit/742d4fbd99ca76d93af5e6ca97f99ea74f3a3909))
- **database**: add testing utilities (#1585) ([cd39b60](https://github.com/tempestphp/tempest-framework/commit/cd39b60a3998708d04838cdb7018ac030fa94692))
- **database**: support for hooked virtual properties (#1586) ([938c024](https://github.com/tempestphp/tempest-framework/commit/938c024c06a75150ee0e2cbe8aa8fe04ae396b7b))
- **support**: add `Uri` utils (#1580) ([83320ab](https://github.com/tempestphp/tempest-framework/commit/83320abb6cbd3b73451dffeb76f3319cc2b7d1a4))

### 🐛 Bug fixes

- **database**: saving nullable `BelongsTo` relations (#1584) ([a572b26](https://github.com/tempestphp/tempest-framework/commit/a572b261821e5e26e5d3ac0f61f10e6635d9dcb9))


## [2.0.4](https://github.com/tempestphp/tempest-framework/compare/v2.0.3..v2.0.4)  —  2025-09-25

### 🐛 Bug fixes

- **database**: nullable belongsto relations (#1575) ([fc77327](https://github.com/tempestphp/tempest-framework/commit/fc77327b6fba77ad972b83cf9ec1f26550208ab0))


## [2.0.2](https://github.com/tempestphp/tempest-framework/compare/v2.0.1..v2.0.2)  —  2025-09-18

### 🐛 Bug fixes

- **database**: combining chunk and with in the select querybuilder (#1567) ([62743e5](https://github.com/tempestphp/tempest-framework/commit/62743e592206d98054d40bbb2dbcb9c149e41b65))


## [2.0.1](https://github.com/tempestphp/tempest-framework/compare/v2.0.0..v2.0.1)  —  2025-09-18

### 🐛 Bug fixes

- **database**: properly serialize enum values when calling toRawSql (#1564) ([3700723](https://github.com/tempestphp/tempest-framework/commit/37007239fa7c5572e1d089a9cde11014730af4c5))


## [2.0.0](https://github.com/tempestphp/tempest-framework/compare/v1.6.0..v2.0.0)  —  2025-09-16

### 🚨 Breaking changes

- **auth**: [**breaking**] overhaul authentication and access control (#1516) ([16aacc7](https://github.com/tempestphp/tempest-framework/commit/16aacc74ab2399d9be52a03c6fee29ed563f934a))
- **commandbus**: [**breaking**] rename AsyncCommand to Async (#1507) ([9745b28](https://github.com/tempestphp/tempest-framework/commit/9745b28d5e7e28824a8256256ffa9428d712b977))
- **core**: [**breaking**] update exception processors to no longer return a throwable (#1342) ([7000028](https://github.com/tempestphp/tempest-framework/commit/70000286d5089d7d9f18ab3f60e5d3b4f297f9c0))
- **database**: [**breaking**] improve orm experience (#1458) ([c6302f3](https://github.com/tempestphp/tempest-framework/commit/c6302f347fcfa0d86308d50fdbeb8ebd56e206d7))
- **http**: [**breaking**] automatically encrypt cookies (#1447) ([6615751](https://github.com/tempestphp/tempest-framework/commit/6615751e22f84db99c80a279ad61eb41e306081c))
- **router**: [**breaking**] support signed URIs (#1520) ([9d0fc5f](https://github.com/tempestphp/tempest-framework/commit/9d0fc5f02c9bf8b1ad15f1f3a19ae26184c75831))
- **validator**: [**breaking**] add localization support for validation error messages (#1444) ([38217ee](https://github.com/tempestphp/tempest-framework/commit/38217eee3b51535de12f9347c95aba7fdb519edf))

### 🚀 Features

- **container**: allow to inject tagged singletons (#1544) ([b1459db](https://github.com/tempestphp/tempest-framework/commit/b1459dbc1cd91ac05530d5cddafb60efcfca18a3))
- **core**: add trace to exception logger (#1508) ([af512b0](https://github.com/tempestphp/tempest-framework/commit/af512b07cc1cc484dd652c209eec6f3136b6acd5))
- **core**: improve base testing class (#1509) ([830e27b](https://github.com/tempestphp/tempest-framework/commit/830e27bb28a584400528ef0d98f5b010ea123069))
- **database**: primary key improvements (#1517) ([b33204f](https://github.com/tempestphp/tempest-framework/commit/b33204fecdaa62a09d535496b5ffa4e7ffed275c))
- **database**: dissociate down migrations from up migrations (#1513) ([de2334b](https://github.com/tempestphp/tempest-framework/commit/de2334bd5eaff0d697cbbb4d498212623960cdf5))
- **database**: add `#[Hashed]` and `#[Encrypted]` attributes (#1514) ([aca1f9a](https://github.com/tempestphp/tempest-framework/commit/aca1f9a5a6c81e8a04da525998ccc8417568d581))
- **http**: improve context for failed http requests (#1484) ([01287b6](https://github.com/tempestphp/tempest-framework/commit/01287b6631259227d352d8977254f031a9d0a824))
- **mapper**: support mapping array of serialized enums (#1521) ([122d7a3](https://github.com/tempestphp/tempest-framework/commit/122d7a34e134adc5fc7f0caf3151849a7339b5ec))
- **mapper**: support converting array of objects to array (#1523) ([accab81](https://github.com/tempestphp/tempest-framework/commit/accab8183cc50f854588e988d86d49c4cffba7b6))
- **process**: introduce process component (#1326) ([70bc5f8](https://github.com/tempestphp/tempest-framework/commit/70bc5f8b388df750ec3d65617dc69003be5b5850))
- **router**: add ability to skip middleware per route (#1472) ([ba2ef8a](https://github.com/tempestphp/tempest-framework/commit/ba2ef8ab140128034e4b45798b788c275afd3883))
- **router**: add method spoofing support for HTML forms (#1536) ([fee4da0](https://github.com/tempestphp/tempest-framework/commit/fee4da0c2ce31dc705b6e439346fbefa079b4bc8))
- **upgrade**: add upgrade package (#1534) ([97ceda7](https://github.com/tempestphp/tempest-framework/commit/97ceda71847f31ebf7306eeb67c5e581695165e5))
- **upgrade**: upgrade router specific namespace changes (#1550) ([a416f94](https://github.com/tempestphp/tempest-framework/commit/a416f94c69e03b7835d765c6259e559980297588))
- **validation**: add `Exists` validation rule (#1462) ([a28c943](https://github.com/tempestphp/tempest-framework/commit/a28c94370f21ee200f70881d597d31463b7cf551))
- **vite**: mention `<x-vite-tags />` and documentation link in post-install instructions (#1473) ([477dfa9](https://github.com/tempestphp/tempest-framework/commit/477dfa9698444fbcdb0ad26ff181635990d680e2))

### 🐛 Bug fixes

- **container**: allow enums in the #[Tag] attribute (#1506) ([fd0912a](https://github.com/tempestphp/tempest-framework/commit/fd0912a5d68488e5d0af8f7ae9eaf0650dab9621))
- **core**: properly handle unserializable discovery items when caching discovery (#1503) ([e8c60ea](https://github.com/tempestphp/tempest-framework/commit/e8c60ea99f166da7eedc5ec9f1aaf19189b7bbd8))
- **core**: discover vendor directory first on all platforms (#1535) ([b7dc71e](https://github.com/tempestphp/tempest-framework/commit/b7dc71eb8c5eca9e18716c3005bd45ba5294661c))
- **database**: support inserting empty rows (#1515) ([5c2a500](https://github.com/tempestphp/tempest-framework/commit/5c2a500c62feed6c859e88da495ebd991c2b4dc8))
- **database**: ensure natural ordering for migrations (#1541) ([1d50336](https://github.com/tempestphp/tempest-framework/commit/1d50336415e4105b94b031fd0527d9c2de26fdc6))
- **http**: null for enums values in request bodies (#1498) ([0f7fc94](https://github.com/tempestphp/tempest-framework/commit/0f7fc9476a44dfd17a81e150c1a3e28808ce2d21))
- **http**: fix assertStatus mixing up expected and actual results (#1499) ([43dcf58](https://github.com/tempestphp/tempest-framework/commit/43dcf585ac5b364688437195a6d9674b3c53e1d4))
- **http**: discard unencrypted cookies (#1551) ([a061e46](https://github.com/tempestphp/tempest-framework/commit/a061e46ff5594d71781de06fc67d9a93524b1111))
- **mapper**: don't overwrite request fields (#1487) ([a280f56](https://github.com/tempestphp/tempest-framework/commit/a280f567e8026f27f7e238d376d43f2394aff0f9))


## [1.6.0](https://github.com/tempestphp/tempest-framework/compare/v1.5.1..v1.6.0)  —  2025-08-08

### 🚀 Features

- **core**: improved exceptions for unwriteable internal storage (#1468) ([948f800](https://github.com/tempestphp/tempest-framework/commit/948f8003b689488761cd3f160f6d2fdb78059a46))
- **cryptography**: introduce cryptography component (#1346) ([439b49e](https://github.com/tempestphp/tempest-framework/commit/439b49eabecdbf2e65b4a652f5e9140189bcece6))
- **database**: add `string` method on `CreateTableStatement` (#1454) ([9c78fd6](https://github.com/tempestphp/tempest-framework/commit/9c78fd645a55d5e8a309fc2b12e4968b0f1609f6))
- **datetime**: add convenience comparison and manipulation methods (#1450) ([4aab9ed](https://github.com/tempestphp/tempest-framework/commit/4aab9ed744ce0aac02a7f657acb55de76a340ca9))
- **http**: offer more control over server sent events format (#1459) ([6623850](https://github.com/tempestphp/tempest-framework/commit/6623850b1a90574fe782d5a351f0999ed07fa747))
- **mapper**: implement serialization mapping for DTOs (#1449) ([900898a](https://github.com/tempestphp/tempest-framework/commit/900898a05113d0c7629cbdb9f72896d5edfead48))
- **testing**: add assert response json assertions (#1433) ([754a657](https://github.com/tempestphp/tempest-framework/commit/754a65734a4cf1bfecebbf0cc8194d80aac66c8c))
- **vite**: support passing configuration to containerized environments (#1426) ([acace86](https://github.com/tempestphp/tempest-framework/commit/acace86ee39afefd81a4199f6c50831601d71624))

### 🐛 Bug fixes

- **router**: do not discover controller stub (#1453) ([2246f72](https://github.com/tempestphp/tempest-framework/commit/2246f7250d3e187f8f0f105aeba90badbace308c))
- **view**: set doctype in `x-base` (#1451) ([e7ea095](https://github.com/tempestphp/tempest-framework/commit/e7ea095574940fb36242a906eaa42698b016652d))
- **view**: pass scoped variables down view components (#1467) ([c911989](https://github.com/tempestphp/tempest-framework/commit/c911989bbfd04b4238fc74a742bd6c44e84d6345))
- add json-serializable to `TestResponseHelper` $body (#1455) ([3d459fd](https://github.com/tempestphp/tempest-framework/commit/3d459fdb885c46d44419cadca47760c0870fa02b))


## [1.5.1](https://github.com/tempestphp/tempest-framework/compare/v1.5.0..v1.5.1)  —  2025-07-29

### 🚀 Features

- **core**: improve exceptions related to internal storage (#1434) ([00caadf](https://github.com/tempestphp/tempest-framework/commit/00caadf2f8d37d927de8f696e035cd446ee2910d))
- **testing**: add view data assertions (#1440) ([f573fd1](https://github.com/tempestphp/tempest-framework/commit/f573fd1cceb29a2a44254422599f03f4858898a9))
- **view**: add csrf token in x-form (#1441) ([e4daa51](https://github.com/tempestphp/tempest-framework/commit/e4daa515e0bdee48179504e9365df1e34eefb019))


## [1.5.0](https://github.com/tempestphp/tempest-framework/compare/v1.4.0..v1.5.0)  —  2025-07-28

### 🚨 Breaking changes

- **http**: [**breaking**] add cross-site request forgery protection (#1411) ([2bb4fcf](https://github.com/tempestphp/tempest-framework/commit/2bb4fcf8de8fa774f779d2d77eb031e7a76a94f7))
- **view**: [**breaking**] properly handle scoped view-components (#1435) ([c6237db](https://github.com/tempestphp/tempest-framework/commit/c6237db3b01b9736b27bf22fde1a7ae60a044f05))
- **view**: [**breaking**] support overriding vendor view components (#1439) ([3483fe4](https://github.com/tempestphp/tempest-framework/commit/3483fe4f051035ee025a073ecce1515df1ec31bb))

### 🚀 Features

- **database**: add pagination support (#1417) ([07f9f4d](https://github.com/tempestphp/tempest-framework/commit/07f9f4dddc4949124a57a9ac08d0e1c71b67b1ef))
- **http**: add `csrf_token` function (#1415) ([4386578](https://github.com/tempestphp/tempest-framework/commit/43865783825e953e8eed006ef81ca92d442c2382))
- **kv-store**: introduce key-value store component (#1252) ([daee10d](https://github.com/tempestphp/tempest-framework/commit/daee10dcd19f05248e13c9d08f048fa3989470f2))
- **response**: add a new Response class for json responses (#1423) ([d25bc44](https://github.com/tempestphp/tempest-framework/commit/d25bc4470fd04027edf3b5c37e03464cb69e094f))
- **router**: json serializable as response body (#1420) ([4af4429](https://github.com/tempestphp/tempest-framework/commit/4af44299b02b17689df7a58efd672db7f2423f29))
- **view**: make default slot available as dynamic slot (#1419) ([0f6f261](https://github.com/tempestphp/tempest-framework/commit/0f6f261039e2fcc68a4040586b480587440e2942))
- **view**: add meta command for view components (#1424) ([051078b](https://github.com/tempestphp/tempest-framework/commit/051078b321928cf13a046a296ad889e59f39bba3))
- **view**: add `x-markdown` component (#1430) ([b81b9ed](https://github.com/tempestphp/tempest-framework/commit/b81b9edfbfde5c8c2b65baffb086edcc459674db))

### 🐛 Bug fixes

- **http**: don't cache csrf tokens in views (#1412) ([9db65f0](https://github.com/tempestphp/tempest-framework/commit/9db65f0f58e1724bd9db7bf9257eddd641881ae1))
- **http**: prevent CSRF token variable name collision (#1413) ([361c2fb](https://github.com/tempestphp/tempest-framework/commit/361c2fbf6a39e9a0b17b99fff7e2991a62a85daa))
- **http**: properly handle cookies lifecycle (#1416) ([1089f61](https://github.com/tempestphp/tempest-framework/commit/1089f61c4d72aa4c25b9823b1d84eabe8c77dd97))
- **http**: respect file session locks during reads (#1418) ([55cb06f](https://github.com/tempestphp/tempest-framework/commit/55cb06f6e246c0730e51fdf06f360eef41d27643))
- **mapper**: support casting `bool`, `int`, `float` and enums (#1414) ([c7292e2](https://github.com/tempestphp/tempest-framework/commit/c7292e2f0cd0ff93e1919f94e98ee60f6cdd927a))
- **vite**: prevent syntax errors on prefetching script (#1421) ([15c303e](https://github.com/tempestphp/tempest-framework/commit/15c303e99caf33bbb40322430f3140521ca1785d))


## [1.4.0](https://github.com/tempestphp/tempest-framework/compare/v1.3.1..v1.4.0)  —  2025-07-17

### 🚀 Features

- **auth**: add class-level permission support (#1405) ([1404246](https://github.com/tempestphp/tempest-framework/commit/14042463f81e6915a61212abaf468a351ca5b2b9))
- **mailer**: introduce mailer component (#1227) ([3f5f31e](https://github.com/tempestphp/tempest-framework/commit/3f5f31eacfc80a6ba8ac724f042eefd5cf8e6412))
- **support**: add json encode/decode to array and string utilities (#1396) ([978bba2](https://github.com/tempestphp/tempest-framework/commit/978bba2d3f5017c4c90920d272c2c3d143aa2485))
- **vite**: inject react refresh when needed (#1406) ([b57bf7f](https://github.com/tempestphp/tempest-framework/commit/b57bf7f9c79179f7135c1eb72894406d80d6a606))

### 🐛 Bug fixes

- **mail**: fix typos (#1410) ([0e29b0e](https://github.com/tempestphp/tempest-framework/commit/0e29b0e83be94a968d4f004d87191ad70b385f3d))
- **mailer**: small tweaks and bugfixes (#1408) ([f80536a](https://github.com/tempestphp/tempest-framework/commit/f80536a5ba5784978df9cad9cc8453be564b0110))
- **vite**: exclude `.tempest` from vite's file watcher (#1384) ([e1bdcf2](https://github.com/tempestphp/tempest-framework/commit/e1bdcf2daedcdd2076b33048de2bcd963a403dca))


## [1.3.0](https://github.com/tempestphp/tempest-framework/compare/v1.2.3..v1.3.0)  —  2025-07-10

### 🚀 Features

- **database**: run `migrate:fresh` without validation by default (#1390) ([665c825](https://github.com/tempestphp/tempest-framework/commit/665c825230a6803e0e52a64d08c28623aa267b48))

### 🐛 Bug fixes

- **commandbus**: require console as a dependency (#1397) ([e56cb6d](https://github.com/tempestphp/tempest-framework/commit/e56cb6d72678e632c413c01ba7dc317396f1e7ba))
- **router**: change the `Bindable::resolve` return type from `static` to `self` (#1391) ([3ac0e3a](https://github.com/tempestphp/tempest-framework/commit/3ac0e3aa0489ecd57e0d7dbe23c7e84ad392bfa2))
- **view**: remove multiline comments before AST parsing (#1395) ([f2c03df](https://github.com/tempestphp/tempest-framework/commit/f2c03df89977fdf0008b40c1777f31cde91108ca))


## [1.2.3](https://github.com/tempestphp/tempest-framework/compare/v1.2.2..v1.2.3)  —  2025-07-08

### 🐛 Bug fixes

- **database**: fix datetime serialization for mysql database (#1383) ([dde0e84](https://github.com/tempestphp/tempest-framework/commit/dde0e84a6bba614c4fa3932914ffaee2dd4916e7))


## [1.2.2](https://github.com/tempestphp/tempest-framework/compare/v1.2.1..v1.2.2)  —  2025-07-08

### 🚀 Features

- **vite**: make which extensions vite discovers configurable (#1375) ([de2d6d4](https://github.com/tempestphp/tempest-framework/commit/de2d6d41c2a9fedb6282f1d720279ffcd236f313))

### 🐛 Bug fixes

- **http**: allow session id cookies when on a local non-secure host (#1381) ([47e993a](https://github.com/tempestphp/tempest-framework/commit/47e993a291dcd4147ab260bf4715cde1ba2cecd8))


## [1.2.1](https://github.com/tempestphp/tempest-framework/compare/v1.2.0..v1.2.1)  —  2025-07-07

### 🐛 Bug fixes

- **http**: register request interface as singleton as soon as possible (#1379) ([b7d1d41](https://github.com/tempestphp/tempest-framework/commit/b7d1d4183f6752c51870e3a80dc9c3adbe200e07))


## [1.2.0](https://github.com/tempestphp/tempest-framework/compare/v1.1.0..v1.2.0)  —  2025-07-07

### 🚨 Breaking changes

- **database**: [**breaking**] query builder refactor (#1367) ([21ca22c](https://github.com/tempestphp/tempest-framework/commit/21ca22ca362536d38ca716d53bf793367162d6f8))
- **database**: [**breaking**] remove `ModelDefinition` in favor or `ModelInspector` (#1372) ([7e8bfc6](https://github.com/tempestphp/tempest-framework/commit/7e8bfc6d10779281212292fca759b1fc93b406e7))

### 🚀 Features

- **database**: add `having` and `groupBy` in select query builder (#1370) ([6bf5c06](https://github.com/tempestphp/tempest-framework/commit/6bf5c060d441a9ed99e2a5a401db2e029ee0277a))
- **view**: add view comments (#1356) ([c61519b](https://github.com/tempestphp/tempest-framework/commit/c61519b775b5ec220e8b5c09f9fec25d81b2aec9))

### ⚡ Performance

- **reflection**: improve class reflector memoization (#1360) ([d8c502d](https://github.com/tempestphp/tempest-framework/commit/d8c502d6cfbb639a5477fab9611724be437e02bd))

### 🐛 Bug fixes

- **database**: throw `QueryWasInvalid` on database fetch failure (#1371) ([95b660b](https://github.com/tempestphp/tempest-framework/commit/95b660b2b3ccfc4dc447119633437852ba7abca3))
- **http**: use exclusive locks for file session writes (#1366) ([c3c8d03](https://github.com/tempestphp/tempest-framework/commit/c3c8d03073330adc0390dd7c594768a7c20866ea))
- **http**: prevent mapping request data to reserved properties on request objects (#1374) ([96bd1b0](https://github.com/tempestphp/tempest-framework/commit/96bd1b06c34758a74154608558c080fc71e8ba2a))


## [1.1.0](https://github.com/tempestphp/tempest-framework/compare/v1.0.3..v1.1.0)  —  2025-07-05

### 🚀 Features

- **database**: display sqlite path in `about` command (#1353) ([9d8449c](https://github.com/tempestphp/tempest-framework/commit/9d8449c9f2ca2b972e25679e0cbeef11232fb6e4))
- **database**: add database seeder support (#1354) ([0a49e1f](https://github.com/tempestphp/tempest-framework/commit/0a49e1fd9c1af59cd4c8804d0316c8be286e3f14))
- **http**: support implicit host in serve command (#1350) ([ebde2c6](https://github.com/tempestphp/tempest-framework/commit/ebde2c6ea02d5e5d9d46402ff4fe7d44abc0a3fd))
- **session**: add reflash method to session (#1338) ([8e8d839](https://github.com/tempestphp/tempest-framework/commit/8e8d839a6fc7462584fca97415a929ed0dc1485d))
- **validation**: add HexColor validation rule (#1332) ([e9a5a17](https://github.com/tempestphp/tempest-framework/commit/e9a5a170a31216110b97125a4b6f0aaf8cb5f3a3))

### ⚡ Performance

- **core**: improve overall discovery performance (#1333) ([0199fa3](https://github.com/tempestphp/tempest-framework/commit/0199fa30c56a8e5278a278b14b426b4a352816e5))
- **core**: improve config loading performance (#1341) ([d28e896](https://github.com/tempestphp/tempest-framework/commit/d28e89613d1a6c39f367b5d421a11d66ec2b9bc2))

### 🐛 Bug fixes

- **core**: fix SkipDiscovery attributes not being detected in vendor packages (#1337) ([b049dd9](https://github.com/tempestphp/tempest-framework/commit/b049dd9eb9ec871437457407ab084f947e60eeec))
- **core**: add missing config cache initializer (#1340) ([bf4770e](https://github.com/tempestphp/tempest-framework/commit/bf4770ea55faa320a3104ce8e3f7cc1fb60c04b9))
- **icon**: enable icon cache by default (#1339) ([34e0d2d](https://github.com/tempestphp/tempest-framework/commit/34e0d2ddce5a17411d24c7ef18c6ca8febc643ba))
- **log**: fix driver resolving not accounting log level (#1343) ([e197b3c](https://github.com/tempestphp/tempest-framework/commit/e197b3c9244a405a07e11418dc1ae90cbe072490))
- **router**: support implicit HEAD requests (#1349) ([f912d6a](https://github.com/tempestphp/tempest-framework/commit/f912d6ab92108b26984aa1fb392569ca99a70f7d))


## [1.0.1](https://github.com/tempestphp/tempest-framework/compare/v1.0.0..v1.0.1)  —  2025-06-27

### 🚀 Features

- **console**: allow dynamic arguments for specific console commands (#1322) ([facdc25](https://github.com/tempestphp/tempest-framework/commit/facdc25c15df87afc844b623683f46aabc87dc55))
- **router**: handle `ConvertsToResponse` outside of exception handler (#1320) ([d4a219d](https://github.com/tempestphp/tempest-framework/commit/d4a219d24b628f8913cb588b6936fbe8d29101e7))
- **router**: allow specifying port using `host:port` syntax (#1321) ([dba57d8](https://github.com/tempestphp/tempest-framework/commit/dba57d8e5df1bfb66c3c4d8a8138cb2dd0508832))


## [1.0.0](https://github.com/tempestphp/tempest-framework/compare/v1.0.0-beta.1..v1.0.0)  —  2025-06-27

### 🚀 Features

- **cache**: separate internal and user caches (#1245) ([36edbd8](https://github.com/tempestphp/tempest-framework/commit/36edbd864b7061699719dd0c39ba862c1ccc1421))
- **cache**: support stale while revalidate (#1269) ([dde685a](https://github.com/tempestphp/tempest-framework/commit/dde685a27666a3223bc55cc7a0d241ed54b08c00))
- **console**: add inline documentation to console methods (#1232) ([6dd0cbb](https://github.com/tempestphp/tempest-framework/commit/6dd0cbb297675d6d4959dc55ac1231f3210105fd))
- **core**: add `about` command (#1226) ([25c4aff](https://github.com/tempestphp/tempest-framework/commit/25c4aff25f2f757b011ae002e3e35a45144a60e1))
- **core**: add discovery config (#1198) ([7b3cc70](https://github.com/tempestphp/tempest-framework/commit/7b3cc708f10ba722ea7479e3ad0c14358783dccc))
- **core**: support exception reporting (#1264) ([914ed58](https://github.com/tempestphp/tempest-framework/commit/914ed58b223c93d7f5193121f592181ce856e099))
- **core**: load `local` and `production` configurations last (#1266) ([0306cbd](https://github.com/tempestphp/tempest-framework/commit/0306cbdf10bbf05620f357e19f654aaacec20d11))
- **database**: add `count()` helper to `IsDatabaseModel` trait (#1181) ([f2c9e47](https://github.com/tempestphp/tempest-framework/commit/f2c9e47451d628ffae9d5d3d196e49b415c82d09))
- **database**: add insert()->then() and prevent invalid relations from being attached (#1225) ([0e78895](https://github.com/tempestphp/tempest-framework/commit/0e788958b8d61e897befab88f492b26f97a3fdad))
- **database**: support multiple databases in migrations and query builders (#1267) ([24ba164](https://github.com/tempestphp/tempest-framework/commit/24ba16413b66c616336b9131340e161160691242))
- **database**: add ShouldMigrate interface (#1273) ([d6d3e37](https://github.com/tempestphp/tempest-framework/commit/d6d3e3786d12a840c197b1166049883b6a052568))
- **database**: support dto fields (#1305) ([9b802b8](https://github.com/tempestphp/tempest-framework/commit/9b802b89f1411992df549953107b4ad9daa071e6))
- **datetime**: support tempest datetime in validator and mapper (#1257) ([5b9d8ff](https://github.com/tempestphp/tempest-framework/commit/5b9d8ffd1db15c651234db1bdab58e6a92ff2b59))
- **http**: add testing client (#1295) ([e3743ae](https://github.com/tempestphp/tempest-framework/commit/e3743ae756f588e7ce358dd64a0f3ba94840e066))
- **icon**: introduce icon component (#1313) ([cc5b0a6](https://github.com/tempestphp/tempest-framework/commit/cc5b0a610f3be3440815603f142132a575a0a2c3))
- **intl**: add localization support (#1294) ([17eeebc](https://github.com/tempestphp/tempest-framework/commit/17eeebc44cb7779800faf1643bc3a5e818f7e8f4))
- **router**: support server-sent events (#1260) ([b5420a9](https://github.com/tempestphp/tempest-framework/commit/b5420a983e237c0c53cb21f4af175ed92d17963a))
- **support**: add number formatting utils (#1268) ([c2f4e9d](https://github.com/tempestphp/tempest-framework/commit/c2f4e9db09e1a5ecfd9ed257637fa1c2312fb7f0))
- **support**: add uuid utilities (#1270) ([20c3559](https://github.com/tempestphp/tempest-framework/commit/20c35597885b35384f362cf0df7898091bdeaf22))
- **validation**: add ability to validate an array of values (#1212) ([c4a9237](https://github.com/tempestphp/tempest-framework/commit/c4a9237bac733a71210e57536ec0dbf7e9ddcb07))
- **view**: support escaped expression attributes (#1222) ([014b67f](https://github.com/tempestphp/tempest-framework/commit/014b67f019701559c82727a65c4d40996e863670))
- **view**: default slot content (#1300) ([d1a21b0](https://github.com/tempestphp/tempest-framework/commit/d1a21b0d6c9a0197aa78e860a3bc0ef2986823ca))
- **vite-plugin-tempest**: allow overriding configuration using `TEMPEST_PLUGIN_CONFIGURATION_OVERRIDE` (#1256) ([05d9942](https://github.com/tempestphp/tempest-framework/commit/05d9942bae6819fac6d65167a987ed3f49fd4a4c))

### 🐛 Bug fixes

- **cache**: allow cache clear to be forced (#1272) ([768273a](https://github.com/tempestphp/tempest-framework/commit/768273a73f17e70ed4e11bfa135e548e805b661e))
- **console**: prevent unknown console arguments (#1238) ([975b49a](https://github.com/tempestphp/tempest-framework/commit/975b49adfb279a518242e45f8a5cdf2d2be5df06))
- **core**: register `HttpExceptionHandler` only in production (#1220) ([f3a21a5](https://github.com/tempestphp/tempest-framework/commit/f3a21a545ce1d68af8ed908394d50c779b699d04))
- **core**: allow discovery:generate to run even when full caching is enabled (#1223) ([1b06332](https://github.com/tempestphp/tempest-framework/commit/1b063329bb2bbfbcacc0a20f54988dd68d6e6c7f))
- **core**: display clean version in `about` command (#1251) ([f267de2](https://github.com/tempestphp/tempest-framework/commit/f267de24c16f0e8f069bc312e9a17480fdf79c8b))
- **core**: release script fixes for next beta (#1314) ([45fe695](https://github.com/tempestphp/tempest-framework/commit/45fe69575e3696563f647a71c0bafea2c3b40770))
- **database**: prevent non-object model queries from trying to use the model class (#1239) ([c1561e0](https://github.com/tempestphp/tempest-framework/commit/c1561e068fa00531896ceac551797f84b08c1d91))
- **database**: prepend backslash when creating enum columns (#1228) ([e8705a7](https://github.com/tempestphp/tempest-framework/commit/e8705a711154533950b75e6ea0679272a5a1287a))
- **database**: properly display mysql and postgresql versions in `about` command (#1258) ([076653a](https://github.com/tempestphp/tempest-framework/commit/076653a0dc093d23734723cfd8852bea7005eb39))
- **database**: postgres support (#1259) ([f34ad57](https://github.com/tempestphp/tempest-framework/commit/f34ad57504792c474b38377c66832b38d9373b98))
- **database**: support semicolons in queries (#1262) ([b110123](https://github.com/tempestphp/tempest-framework/commit/b1101237395995ed373c72bd7f81a698ac7b7f83))
- **event-bus**: reorder `listen` parameters for consistency (#1291) ([0d6e6ee](https://github.com/tempestphp/tempest-framework/commit/0d6e6ee401b2310bfbd423891055f535e9990ed6))
- **framework**: handle reflection in config show command (#1211) ([972870f](https://github.com/tempestphp/tempest-framework/commit/972870f53d4e18b44a35454123e120a1fb6199ac))
- **intl**: remove circular dependency on datetime component (#1299) ([957f9c8](https://github.com/tempestphp/tempest-framework/commit/957f9c852bcd4b116516066f987604c750227bfc))
- **intl**: fix circular dependency (#1301) ([9e5eed6](https://github.com/tempestphp/tempest-framework/commit/9e5eed60d475c4512a35dd7d8ab1cdedffad9fcd))
- **intl**: fix circular dependency (#1302) ([6c71b06](https://github.com/tempestphp/tempest-framework/commit/6c71b06ce453afe393a5a2f35394f3f7dea7da72))
- **router**: check internal dead links without the domain (#1210) ([62f45c3](https://github.com/tempestphp/tempest-framework/commit/62f45c3423429468ed76e3d24e95b0bc6c40238b))
- **router**: require hard-coded uris to start with a slash in `Router::toUri` (#1205) ([1f3ec14](https://github.com/tempestphp/tempest-framework/commit/1f3ec141544cb3048222b4b0eef6255b4f68aad8))
- **support**: make `Arr\forget_values` and `Arr\forget_keys` mutable (#1215) ([286d9a0](https://github.com/tempestphp/tempest-framework/commit/286d9a020fb438f8e290931ad9a499e0903c5a4c))
- **support**: use `Closure` instead of `callable` when calling `preg_replace_callback` (#1231) ([ce48368](https://github.com/tempestphp/tempest-framework/commit/ce4836853468aba13da362de04a23b88902d18b3))
- **support**: support more `to_snake_case` edge cases (#1250) ([dcf926a](https://github.com/tempestphp/tempest-framework/commit/dcf926a4e44e702d0352e181eb1a83b9166ac516))
- **view**: properly unset local view component variables (#1221) ([6bdb652](https://github.com/tempestphp/tempest-framework/commit/6bdb65213f79cfdd25f4b53628fb5dfbdf0d5eb3))
- **view**: prevent infinite loop with unclosed PHP or comment tags (#1282) ([347513a](https://github.com/tempestphp/tempest-framework/commit/347513a36ad8436f7ba5068ede8d545c17f850b7))
- **view**: fix falsy boolean evaluation on comments (#1289) ([8d0d780](https://github.com/tempestphp/tempest-framework/commit/8d0d780e5f0974ab64c5b9d0a07dfec08091cb7a))
- **view**: handle icon name parsing without colon (#1298) ([e34e120](https://github.com/tempestphp/tempest-framework/commit/e34e120eecc59166e1c09ecf4a13c51c1ffc98dc))
- **view**: `InvalidClosingTag` should ignore commented out code attributes (#1288) ([3892651](https://github.com/tempestphp/tempest-framework/commit/3892651631bd9cfaaf1335eb7fc50a3225c75fcd))
- **vite**: ignore missing `.gitignore` during installation (#1275) ([a986846](https://github.com/tempestphp/tempest-framework/commit/a986846b80b8c9db16d31c4a4c0754d59729132a))
- **vite**: use npm as fallback when no package manager is detected during installation (#1297) ([b8b64d0](https://github.com/tempestphp/tempest-framework/commit/b8b64d0e54ef3e81c9fa521bfb80544e94557853))
- use correct README guideline link (#1213) ([670da14](https://github.com/tempestphp/tempest-framework/commit/670da14940accc950a3e2ea3ebdb020272ca9d58))


## [1.0.0-beta.1](https://github.com/tempestphp/tempest-framework/compare/v1.0.0-alpha.6..v1.0.0-beta.1)  —  2025-05-07

### 🚀 Features

- **console**: add `make:migration` command (#871) ([e34654a](https://github.com/tempestphp/tempest-framework/commit/e34654af84c74fe9dc5769b09b1016105d8a463a))
- **console**: add option to use terminal width to render key/values (#1148) ([0c553d4](https://github.com/tempestphp/tempest-framework/commit/0c553d46cc360a0756bf96ace943c859479a2d68))
- **container**: support lazy dependency initialization using lazy proxies (#1090) ([78273cc](https://github.com/tempestphp/tempest-framework/commit/78273ccc25e5024a9e1cd48fddbac4532a525478))
- **container**: add `container:show` command (#1118) ([80ab136](https://github.com/tempestphp/tempest-framework/commit/80ab136c06b0599b55919ccfa663a40b02818363))
- **container**: support dynamic tags using dynamic initializers (#1120) ([0980e3a](https://github.com/tempestphp/tempest-framework/commit/0980e3a49540539538ec7613c23c858f5e7afcdc))
- **core**: add middleware priority and discovery (#1109) ([da6665c](https://github.com/tempestphp/tempest-framework/commit/da6665c80af48f04b4f025aab1be975c1cffd8ea))
- **core**: display more data in default error handler (#1116) ([90e8208](https://github.com/tempestphp/tempest-framework/commit/90e82085389047f5951a8274b12abf8f1ebb4b9e))
- **core**: improve exception handling (#1203) ([9b31ecc](https://github.com/tempestphp/tempest-framework/commit/9b31ecc73150e000c0f305119f97563944e23bea))
- **database**: allow overriding table names through model class attributes (#1060) ([412c2d0](https://github.com/tempestphp/tempest-framework/commit/412c2d0a5d59e6e5df2a44e233449bdc02c0fe56))
- **database**: store default sqlite database in internal storage (#1075) ([d1704e8](https://github.com/tempestphp/tempest-framework/commit/d1704e8de46f0bcd1c6e09ef38d5b80c11a878a7))
- **database**: add migration hash checking (#1054) ([90fa20c](https://github.com/tempestphp/tempest-framework/commit/90fa20cc6f1ec1b5b461f1d3c0aabd6f1879478b))
- **database**: model validation before update, create, and save (#1131) ([58f15f9](https://github.com/tempestphp/tempest-framework/commit/58f15f9dab81eeffdedc8b655e33e8804d625b31))
- **database**: add `HasConditions` to query builders (#1154) ([619dd11](https://github.com/tempestphp/tempest-framework/commit/619dd11fc8944ac03e6aba2531a163e1c1ef6436))
- **database**: add `Count` query builder and statement (#1174) ([22dbe07](https://github.com/tempestphp/tempest-framework/commit/22dbe073792a95ee2b13ecbf314f7b7dc3829825))
- **datetime**: add datetime component (#1158) ([76d70c1](https://github.com/tempestphp/tempest-framework/commit/76d70c1530519b5c382f7cd0b28da621903dad56))
- **event-bus**: add event bus testing utilities (#1103) ([9c84c68](https://github.com/tempestphp/tempest-framework/commit/9c84c680ad5ff346686ce6d5252823e029047693))
- **router**: allow checking an action against the current route (#1059) ([a8b6ea9](https://github.com/tempestphp/tempest-framework/commit/a8b6ea970eb82aa2020cc66a6d567c09d950289e))
- **router**: support returning `string` and `array` from controllers (#1083) ([5fb1045](https://github.com/tempestphp/tempest-framework/commit/5fb1045867656f7224cf0faeb3fd5f3d6a329c7f))
- **router**: introduce response processors (#1084) ([fb8977b](https://github.com/tempestphp/tempest-framework/commit/fb8977b7ffb99ddb9613d85e37a365a3ccb2c63c))
- **router**: support getting raw body from requests (#1093) ([9d86d13](https://github.com/tempestphp/tempest-framework/commit/9d86d1363f31355600be637c19eb9791c26eed72))
- **router**: add redirect back response (#1050) ([8d43ce5](https://github.com/tempestphp/tempest-framework/commit/8d43ce50d375fe2fa4f07c815fa56ff02eecc853))
- **router**: detect dead links when generating static pages (#1192) ([453e1cb](https://github.com/tempestphp/tempest-framework/commit/453e1cb587641185b3375dcb72cdd0180e96c6b7))
- **storage**: add storage component (#1149) ([4baead1](https://github.com/tempestphp/tempest-framework/commit/4baead10f2a0a25d7b07e1f9e2993e15168d13b6))
- **storage**: support multiple storage configurations (#1187) ([5b8be8a](https://github.com/tempestphp/tempest-framework/commit/5b8be8a2264cb4e5280802534957a4667fbca299))
- **support**: support array parameters in string manipulations (#1073) ([283af0b](https://github.com/tempestphp/tempest-framework/commit/283af0b2d79b5430e70a73fb3a24928bd84731d9))
- **support**: rename `map_array` to `map_iterable` (#1071) ([1eaf65e](https://github.com/tempestphp/tempest-framework/commit/1eaf65e03b2e158dd7233bf168deeca964e37b1a))
- **support**: support `$default` on array `first` and `last` methods (#1096) ([0d93283](https://github.com/tempestphp/tempest-framework/commit/0d9328363b75e94db8c1d06f16eaa51858033903))
- **support**: add `removeValues` to array utils (#1204) ([3209379](https://github.com/tempestphp/tempest-framework/commit/320937901c137fd1288f9607fe61354cd71f27cd))
- **view**: add `view:clear` command (#1069) ([4137981](https://github.com/tempestphp/tempest-framework/commit/4137981ddab733cd6cc0985b903cbb09874021ba))
- **view**: improve boolean attributes (#1111) ([35f85e9](https://github.com/tempestphp/tempest-framework/commit/35f85e9495139cdf7bb8b533e7a1c5de41d796dd))
- **view**: attribute precedence (#1153) ([96f3149](https://github.com/tempestphp/tempest-framework/commit/96f314972271878e5af1800233b2eb8bd86ea9cd))
- **view**: dynamic view components (#1169) ([06be1af](https://github.com/tempestphp/tempest-framework/commit/06be1af98b835ded5d8da648166c2a0171d03d9c))
- **view**: prevent invalid closing tags (#1195) ([215671f](https://github.com/tempestphp/tempest-framework/commit/215671fb884b8a0cf92184e7e3baf60d90f68da9))
- **vite**: disable tag resolution in tests by default (#1072) ([71efbae](https://github.com/tempestphp/tempest-framework/commit/71efbae091838d661b62205abf80ddd6337475cc))

### ⚡ Performance

- **view**: improve view component discovery performance (#1191) ([25adb82](https://github.com/tempestphp/tempest-framework/commit/25adb8281f4fa77e373a7bf78fd49e2def15cf03))

### 🐛 Bug fixes

- **console**: keep colors in key-value lines (#1068) ([02aa357](https://github.com/tempestphp/tempest-framework/commit/02aa357dda49025ba53d16710b4bbcab5065ffd7))
- **console**: do not discover stub files (#1136) ([30f012d](https://github.com/tempestphp/tempest-framework/commit/30f012d4ecf7adb8012d3ce1a138d933bb83fd6b))
- **console**: do not discover stub files (#1138) ([3fc2a15](https://github.com/tempestphp/tempest-framework/commit/3fc2a153f32dd8bb8e45068eba006513df351a8f))
- **console**: select default option in ask component (#1139) ([797392e](https://github.com/tempestphp/tempest-framework/commit/797392e9c8d172712a2e08e42c62cb164e22042e))
- **console**: properly place cursor in multiline input (#1141) ([b079c5e](https://github.com/tempestphp/tempest-framework/commit/b079c5e3258bb5cf0c3a1136234b49f567aa9fd3))
- **core**: publish tempest binary via composer (#1207) ([03cccff](https://github.com/tempestphp/tempest-framework/commit/03cccff4ef7a71c58fd6f6105de2c5fa8f4ab695))
- **database**: improved check on missing migrations table (#1092) ([ed6f85c](https://github.com/tempestphp/tempest-framework/commit/ed6f85cc6e369d9b598dd787c6d77b1c618a69ba))
- **filesystem**: add ability to delete invalid symlinks (#1206) ([12e2b03](https://github.com/tempestphp/tempest-framework/commit/12e2b03ebc9f113c218fdcc81e54bb5af68ca7aa))
- **mapper**: properly serialize nullable properties in objects (#1107) ([0b824b6](https://github.com/tempestphp/tempest-framework/commit/0b824b6594204b9eed806745803adac30cbbf8af))
- **support**: non-dev bun dependencies installation (#1124) ([da7006f](https://github.com/tempestphp/tempest-framework/commit/da7006ff2c6868998f74f7c2b50c51689244a9de))
- **support**: fix psr-4 namespace path generation with dots and slashes in the composer path (#1166) ([ce06b52](https://github.com/tempestphp/tempest-framework/commit/ce06b5206dae772b784969ce7e513270e8e62355))
- **validation**: more lenient scalar validation (#1127) ([dcc2401](https://github.com/tempestphp/tempest-framework/commit/dcc2401d3338d8c49180d8911095281ea2b3d5bd))
- **validation**: enum request validation (#1130) ([2181ec8](https://github.com/tempestphp/tempest-framework/commit/2181ec8b0fa4f23bb2fcdbc293b61870ce9a15e4))
- **view**: lexing multiline attributes in windows (#1121) ([33085b0](https://github.com/tempestphp/tempest-framework/commit/33085b04b09bcaaf7758c7859ccc89233b96b806))
- **view**: `:` is replaced by `-` and `@` is removed (#1125) ([2f0b247](https://github.com/tempestphp/tempest-framework/commit/2f0b247aaea710f9054b0f43cb41a9df4ae5489f))
- **view**: hyphens in slot names (#1129) ([bead5a5](https://github.com/tempestphp/tempest-framework/commit/bead5a5bd2705a963c4f15298f0689e2d3b075bf))
- **view**: don't throw when using a `<table>` element (#1133) ([5a05f6d](https://github.com/tempestphp/tempest-framework/commit/5a05f6db607de8bcfae9e49588778da3b77a6cff))
- **view**: prevent `$var` from being `null`ed after passing to component (#1160) ([9aeb727](https://github.com/tempestphp/tempest-framework/commit/9aeb727a0579b50c6f9cf3ece9c8cae2235037a8))
- **view**: switch to runtime icon view component (#1165) ([6b84639](https://github.com/tempestphp/tempest-framework/commit/6b846393987ee147cff9fe1a33acf3ba8c99ad8a))
- **view**: improved attribute precedence (#1168) ([077cc7d](https://github.com/tempestphp/tempest-framework/commit/077cc7d593f821bca89a95fbf05d9ab4f33dad8e))
- **view**: fallback attributes fix with nested slots (#1172) ([5f38986](https://github.com/tempestphp/tempest-framework/commit/5f38986aca88229cd9f66d2434410529423b3f70))
- **view**: wrong matched imports in view component slots (#1173) ([6c5da00](https://github.com/tempestphp/tempest-framework/commit/6c5da00da9e518cb53514b6adc5aaf79b89fd956))
- **view**: dynamic components with slots (#1171) ([9fb3dd4](https://github.com/tempestphp/tempest-framework/commit/9fb3dd4c36bdabaf2de861b965cbbc076d1dc48d))

### Build

- fix release script adding tempest/highlight dependency ([be80673](https://github.com/tempestphp/tempest-framework/commit/be8067395057a307287aad80dba59165b369e116))


## [1.0.0-alpha.6](https://github.com/tempestphp/tempest-framework/compare/v1.0.0-alpha.5..v1.0.0-alpha.6)  —  2025-03-24

### 🚨 Breaking changes

- **support**: [**breaking**] improve architecture of support utilities (#940) ([bb75e81](https://github.com/tempestphp/tempest-framework/commit/bb75e81b355054f942081a839b40d529b3cb659f))
- **vite**: [**breaking**] automatically discover entrypoints (#1051) ([ebe3ef4](https://github.com/tempestphp/tempest-framework/commit/ebe3ef4eb43479eb03bf4f189def72e2d711b539))

### 🚀 Features

- **console**: add `make:command` command (#1048) ([13bc731](https://github.com/tempestphp/tempest-framework/commit/13bc73157d97949a882d1e7e4bd885d727f7840f))
- **console**: add `make:discovery` command (#1057) ([2bd5814](https://github.com/tempestphp/tempest-framework/commit/2bd5814d83dad8751012523f5e19daa315c0d6f5))
- **console**: add `make:generator-command` command (#1056) ([6992b70](https://github.com/tempestphp/tempest-framework/commit/6992b70cf9264a5c2bb349645ab7e3683063a5c6))
- **core**: add kernel interface (#924) ([2a2c454](https://github.com/tempestphp/tempest-framework/commit/2a2c4548e622705db24786c89f06f7887d1ec38f))
- **database**: refactor DatabaseConfig interface (#902) ([3d3a094](https://github.com/tempestphp/tempest-framework/commit/3d3a094bd4cb3456f7e41bdd7b5a4b1b059b134e))
- **database**: add a `Virtual` attribute to exclude model properties from query builder (#966) ([b6252dc](https://github.com/tempestphp/tempest-framework/commit/b6252dcf5dd03211482c9ac16e97ec9adefabd25))
- **database**: add a `findBy` method to models (#965) ([8d479bc](https://github.com/tempestphp/tempest-framework/commit/8d479bcba49d5f923a46100baf01fdead5a67192))
- **http**: empty request values are converted to null (#976) ([dc5323b](https://github.com/tempestphp/tempest-framework/commit/dc5323bae9633c7a34f7214ace13654fadf7e2f6))
- **http**: fix http header casing on retrieval (#1024) ([be2fb43](https://github.com/tempestphp/tempest-framework/commit/be2fb4332bb13145808f8f15889f3eaf125b8e2b))
- **mapper**: add two-way casters (#920) ([0748aa9](https://github.com/tempestphp/tempest-framework/commit/0748aa985764e37521226a3aa295e2851ccba296))
- **mapper**: add `MapFrom` and `MapTo` attributes (#929) ([b9a89de](https://github.com/tempestphp/tempest-framework/commit/b9a89decf4c99cf812b78b118c27585fa9dd77dd))
- **mapper**: `ObjectToArrayMapper` use `Caster::serialize` to serialize the property value (#947) ([269bfcb](https://github.com/tempestphp/tempest-framework/commit/269bfcb56715e1d446a5bfbdf871afb50bad2889))
- **mapper**: map()->with()->to() (#951) ([e6f04ee](https://github.com/tempestphp/tempest-framework/commit/e6f04eeab25d55fe7d0a9f4bcafaa308c87c6462))
- **mapper**: allow multiple fields in `#[MapFrom]` (#944) ([381c58d](https://github.com/tempestphp/tempest-framework/commit/381c58db657c65c7d974ea93c69492e8dd9c6b1e))
- **support**: add enums support (#878) ([964d55a](https://github.com/tempestphp/tempest-framework/commit/964d55ae92256119151a4639d032a44a086d68b7))
- **support**: add `basename` to string utils (#1039) ([1d4f563](https://github.com/tempestphp/tempest-framework/commit/1d4f56318c5b817f47fe0323cacc83af50ce6ac2))
- **support**: add `slug`, `ascii` and `isAscii` to string utils (#1040) ([3eb8b35](https://github.com/tempestphp/tempest-framework/commit/3eb8b352277f1b635e8d1905534e1403156e5de7))
- **support**: add `words` and `sentence` methods to string utils (#1042) ([b2dfd32](https://github.com/tempestphp/tempest-framework/commit/b2dfd324970ff18d78924ee7bfc92b5c859f9b95))
- **support**: add `groupBy` to array utils (#1047) ([d696826](https://github.com/tempestphp/tempest-framework/commit/d6968267f9df10b4c8d7e4527831824097e85100))
- **support**: add `mapFirstTo` and `mapLastTo` to array utils (#1038) ([b188609](https://github.com/tempestphp/tempest-framework/commit/b18860990d0e0c6a92ea8c086ecb6b9dc146b115))
- **validation**: allow `Stringable` objects in `IsString` rule (#1029) ([bd22988](https://github.com/tempestphp/tempest-framework/commit/bd22988d827a68d2511e64c8ed3ebbffe85c97ba))
- **view**: support dynamic `$slots` and `x-template` (#911) ([1ba1629](https://github.com/tempestphp/tempest-framework/commit/1ba1629d946878e71cb1b836e480085f9ac7d78e))
- **view**: more lenient DOM parsing (#941) ([0fe0df9](https://github.com/tempestphp/tempest-framework/commit/0fe0df917a251294682c3a094929c6ee05f91350))
- **view**: remove empty slots in production (#950) ([64b1ff0](https://github.com/tempestphp/tempest-framework/commit/64b1ff0967675d8504287ea079edb7448733f93f))
- **view**: support relative view paths (#953) ([2479148](https://github.com/tempestphp/tempest-framework/commit/247914842791e5e43bb8b56acf318621fde15377))
- **view**: access view component attributes (#1008) ([6c7dfae](https://github.com/tempestphp/tempest-framework/commit/6c7dfae87d3d47f69551f884a5b2eb44da159df7))
- **view**: add view processors (#1011) ([573d557](https://github.com/tempestphp/tempest-framework/commit/573d5575e617415f4b84567cd905465fbd258033))
- **view**: view components by file name (#1013) ([12b5503](https://github.com/tempestphp/tempest-framework/commit/12b5503e23cafb9f700d2a2d8f0694a3ccdedbce))
- **view**: fallthrough attributes (#1014) ([e1ce286](https://github.com/tempestphp/tempest-framework/commit/e1ce286dc0b0cc98711f92045f2ab17e0b032e3b))
- **view**: add icon component (#1009) ([46570eb](https://github.com/tempestphp/tempest-framework/commit/46570eb92081f949b9e25eadf7dde38172b9e286))
- **view**: support merging class attributes (#1020) ([80ff7be](https://github.com/tempestphp/tempest-framework/commit/80ff7be9f50de26b298734d265475cd608f7ca93))
- **view**: cache Blade and Twig templates in internal storage (#1061) ([1e33722](https://github.com/tempestphp/tempest-framework/commit/1e33722b776b3fbe023fe93b734b08cd8fe3cd2f))
- **vite**: add Tailwind CSS option to the installer (#926) ([cfe1564](https://github.com/tempestphp/tempest-framework/commit/cfe1564adc574c20b8f7bf2a06490f20910b5b2d))
- **vite**: add `<x-vite-tags />` component (#945) ([888f5b1](https://github.com/tempestphp/tempest-framework/commit/888f5b1465386299cb29635729c7edffa590d49e))

### 🐛 Bug fixes

- **database**: make `AlterTableStatement` produce valid SQL (#979) ([fd63ec0](https://github.com/tempestphp/tempest-framework/commit/fd63ec0c50a19977ab659c43b590650d5cfa162e))
- **discovery**: pass real paths to discovery classes (#1053) ([97bfbf2](https://github.com/tempestphp/tempest-framework/commit/97bfbf20e3d3b6639dad4156c1b6de58ed26ee40))
- **linter**: exclude cache directories (#1046) ([0b27762](https://github.com/tempestphp/tempest-framework/commit/0b277626f0649c3785096c9ab6256cfb157cb2ee))
- **mapper**: nullable datetime caster (#974) ([118eeb5](https://github.com/tempestphp/tempest-framework/commit/118eeb5dc678457e629c9feb65a8c18ae39b11c1))
- **mapper**: validate before mapping (#980) ([0688c97](https://github.com/tempestphp/tempest-framework/commit/0688c97fd0b26823983d97b5dd1e810fe66cea58))
- **router**: content-type json support when mapping psr request to tempest request (#956) ([16345b6](https://github.com/tempestphp/tempest-framework/commit/16345b637aa1bbb9f695c970503ce026725a4e75))
- **router**: use correct input stream (#1005) ([768c6fb](https://github.com/tempestphp/tempest-framework/commit/768c6fbf640a33c02a7347227d8f0b87df8470f6))
- **validation**: prevent type errors in rules using `preg_match` (#1043) ([4a00657](https://github.com/tempestphp/tempest-framework/commit/4a006575299b67125fa0da7c02944e3f18cd0a61))
- **view**: render doctype and html tags properly (#910) ([dff3884](https://github.com/tempestphp/tempest-framework/commit/dff38842dd55295015902cb500354a6e7f66696a))
- **view**: fix for compiling HTML documents that contain PHP (#922) ([f93fb3d](https://github.com/tempestphp/tempest-framework/commit/f93fb3dc30b04e7d4beb39b18a376692065af26f))
- **view**: comment out empty slots (#938) ([1b3433d](https://github.com/tempestphp/tempest-framework/commit/1b3433d839520166025c2207a98170d5c3263515))
- **view**: hardcoded boolean attributes shouldn't be parsed (#952) ([dff166b](https://github.com/tempestphp/tempest-framework/commit/dff166bc2fe2f10ec165dc3559eeef655d38bc2f))
- **view**: regex timeout (#1015) ([2e24641](https://github.com/tempestphp/tempest-framework/commit/2e24641617521e13bb663e196bb79256e7c8aeb1))
- **view**: support `<x-component>` in auto-registered components (#1018) ([371d9ea](https://github.com/tempestphp/tempest-framework/commit/371d9eaa6289ddfa113e908b039ad93d72cbf41f))
- **view**: don't allow php expressions in attributes (#1019) ([6931350](https://github.com/tempestphp/tempest-framework/commit/6931350facad78a3f252f8013b94163b8c027866))
- **view**: do not duplicate `<br />` tag (#995) ([2279402](https://github.com/tempestphp/tempest-framework/commit/2279402615c0f924214e4f4ac7163e24c32dddf7))
- **view**: prevent compiling parent elements of minified void tags (#1055) ([0b25975](https://github.com/tempestphp/tempest-framework/commit/0b2597511b02a5804a30bc5bb10a231a92f4fb72))
- **vite**: generate absolute asset urls (#1023) ([3551008](https://github.com/tempestphp/tempest-framework/commit/3551008aeff803f29b915fb95a1683a7a2205197))
- **vite**: don't discover template entrypoints (#1052) ([3d7cbbd](https://github.com/tempestphp/tempest-framework/commit/3d7cbbd67fdfd760b95a150dfaa640557e71a4cf))


## [1.0.0-alpha.5](https://github.com/tempestphp/tempest-framework/compare/v1.0.0-alpha.4..v1.0.0-alpha.5)  —  2025-02-24

### 🚀 Features

- **console**: add `make:initializer` command (#771) ([cf354b7](https://github.com/tempestphp/tempest-framework/commit/cf354b7f67ad7f3fb18185ab69dbe7f5f6cfd35c))
- **console**: add backed enum support to `ask` (#808) ([5e3d99e](https://github.com/tempestphp/tempest-framework/commit/5e3d99e272002ff0a32bb47f4535c9c7c17d4b09))
- **console**: improve rescuing enum console parameters (#809) ([7c64c7c](https://github.com/tempestphp/tempest-framework/commit/7c64c7c9b83add53ed7420c5838128bb7ca83171))
- **console**: allow calling console commands via fqcn (#824) ([a6ba3b6](https://github.com/tempestphp/tempest-framework/commit/a6ba3b62afd329e1a4b99261a93a60f86aa4452d))
- **console**: provide command suggestions when using `:` shorthands (#814) ([107f8b8](https://github.com/tempestphp/tempest-framework/commit/107f8b869e4486d24ef8a3873c7c8048acca246c))
- **console**: add installer (#837) ([90b6321](https://github.com/tempestphp/tempest-framework/commit/90b632109ad4a43e3b93dc84d6327dc58ca4a2c4))
- **console**: add `make:middleware` command (#804) ([467c664](https://github.com/tempestphp/tempest-framework/commit/467c664239c7b454bfb2b72eb069ff8c9b3a651a))
- **console**: several QOL improvements (#847) ([05dac5c](https://github.com/tempestphp/tempest-framework/commit/05dac5cca37be40f19379a082660551378c1e5ff))
- **console**: add support for printing hyperlinks (#850) ([6f457af](https://github.com/tempestphp/tempest-framework/commit/6f457aff6af3d0000188594f2fd0ce5ad159c9c8))
- **console**: add `make:config` command (#863) ([d0f3f53](https://github.com/tempestphp/tempest-framework/commit/d0f3f53f6b3a50264141129aadc7413ab422df6e))
- **console**: add `make:view` command (#864) ([a4ab813](https://github.com/tempestphp/tempest-framework/commit/a4ab813dd162acafb68dc5d8ec65f0f63e55c2a0))
- **console**: add `task` component (#857) ([d4dac18](https://github.com/tempestphp/tempest-framework/commit/d4dac18d1419a72695e81ca825135ab86c57d4db))
- **container**: add `has` and `unregister` (#840) ([09ced7a](https://github.com/tempestphp/tempest-framework/commit/09ced7a3e3e33bb37ffb42d88dc769abbb74f2b5))
- **core**: allow `defer` callbacks to receive parameters from container (#798) ([42262fa](https://github.com/tempestphp/tempest-framework/commit/42262faea3acdd3685e8f924f4aace5a78f08362))
- **core**: add `TEMPEST_START` constant (#791) ([1cabe2d](https://github.com/tempestphp/tempest-framework/commit/1cabe2d9f16078faf1076bef22dec8275addff15))
- **core**: optionally run `composer up` after installers (#839) ([6747d2c](https://github.com/tempestphp/tempest-framework/commit/6747d2cf6a5a65e8454dc5669064203308c7598e))
- **database**: improved database indexing (#851) ([82f1808](https://github.com/tempestphp/tempest-framework/commit/82f1808b9f53e9384b040b09d16e61ee1d263d98))
- **database**: alter table with only indices (#852) ([61e7abb](https://github.com/tempestphp/tempest-framework/commit/61e7abbe51d3a34ece716501cecbd0eb93d65a32))
- **database**: chunked results (#855) ([e332bbd](https://github.com/tempestphp/tempest-framework/commit/e332bbd23240460ede83ce1ee39c37b825e5b3ee))
- **database**: bindings in query methods (#859) ([49f019c](https://github.com/tempestphp/tempest-framework/commit/49f019c8e742e99212b2d2507206d5339bf480b3))
- **database**: add `raw` to `CreateTableStatement` (#868) ([60dcc28](https://github.com/tempestphp/tempest-framework/commit/60dcc28214ad7044aca0be04b97271f1982b7f34))
- **database**: add explicit relation attributes (#874) ([5e4df24](https://github.com/tempestphp/tempest-framework/commit/5e4df2421fdd165d9967e7793d9962735006e86f))
- **database**: add closable connection wrapper for PDO connection (#875) ([15f8995](https://github.com/tempestphp/tempest-framework/commit/15f899570860c40aaf560751e58cb283bef27d9c))
- **debug**: emit `ItemsDebugged` on debug (#796) ([c1be5e6](https://github.com/tempestphp/tempest-framework/commit/c1be5e67495278a51b5f7e5248e13c383c7f63f7))
- **framework**: extend http testing methods (#790) ([dd01ef1](https://github.com/tempestphp/tempest-framework/commit/dd01ef1ed9499ad9e0e1500409b39ffca247b138))
- **framework**: overhaul console interactions (#754) ([e966ecb](https://github.com/tempestphp/tempest-framework/commit/e966ecbece64870f7fad37ed5f673d8aea8a8b2b))
- **log**: emit `MessageLogged` when logs are written (#795) ([50b27c8](https://github.com/tempestphp/tempest-framework/commit/50b27c89cf6d7ad3fcd134d829991026b0f6958d))
- **log**: configure log paths through env by default (#820) ([52f200a](https://github.com/tempestphp/tempest-framework/commit/52f200adbecb55057d6132c425a5f8a898316bfe))
- **support**: improve types of `HasConditions` (#800) ([00aa6ea](https://github.com/tempestphp/tempest-framework/commit/00aa6ea96ff605416ec5a22aa337b7acc0a606dd))
- **support**: add `every` to `ArrayHelper` (#813) ([9d39e15](https://github.com/tempestphp/tempest-framework/commit/9d39e15d2a09ceecea705e65832fc88397a91813))
- **support**: add `append` and `prepend` to `ArrayHelper` (#833) ([7daf3fc](https://github.com/tempestphp/tempest-framework/commit/7daf3fcc80a0c36c3fd87cc3c897e25038656f74))
- **support**: add `HtmlString` class (#842) ([751f0d1](https://github.com/tempestphp/tempest-framework/commit/751f0d119cc7649b7310e9f3e7c39be5d77dc726))
- **view**: add twig support (#841) ([0f47a80](https://github.com/tempestphp/tempest-framework/commit/0f47a804583a9288c3d2ead43fd84b38bc1c7c2d))
- **vite**: add Vite installer (#901) ([f9f4167](https://github.com/tempestphp/tempest-framework/commit/f9f41676d53d56bc687e339c4b89ce00b34cb83d))
- add Vite support (#829) ([4099b40](https://github.com/tempestphp/tempest-framework/commit/4099b40249765c36b5fe8696e61293f9af6cbc4e))

### 🐛 Bug fixes

- **core**: do not redefine `TEMPEST_START` (#806) ([2739f4f](https://github.com/tempestphp/tempest-framework/commit/2739f4f2faa23b33618015242071be65a051bee1))
- **database**: default strong comparison check (#858) ([b6064a6](https://github.com/tempestphp/tempest-framework/commit/b6064a65efded83aee7e330e06e5dc6c76a0cba9))
- **database**: loading database relations or other objects (#884) ([0214ac3](https://github.com/tempestphp/tempest-framework/commit/0214ac33b62bc205c61893d99b83aeaeb13df301))
- **generation**: `ClassManipulator` now make replacements before simplifies classnames (#876) ([fe0f3b2](https://github.com/tempestphp/tempest-framework/commit/fe0f3b2a64dd17299894591236da3b059e24eadd))
- **http**: remove empty directories when running `static:clean` (#784) ([3f0d17b](https://github.com/tempestphp/tempest-framework/commit/3f0d17b9532f6e0a3fe7ff4c7fd655dfa3cb7fa4))
- **http**: correct HTTP 418 description and coverage (#823) ([dbad109](https://github.com/tempestphp/tempest-framework/commit/dbad1090edc3e53fbd741ba1696259c33852d837))
- **support**: support keys with dots in `ArrayHelper#get` (#832) ([8372827](https://github.com/tempestphp/tempest-framework/commit/837282712b4bdb855aa0189344092c568f7f3617))
- **tests**: update IPv6 test to align with PHP 8.4.3 behavior ([dec5c2f](https://github.com/tempestphp/tempest-framework/commit/dec5c2f05d97af5fb4069c90a10840b03443bd32))
- **view**: check the existing of the `$tagName` property (#803) ([f34c2ba](https://github.com/tempestphp/tempest-framework/commit/f34c2ba5e25cf9399e2d70b515e433c153f690fa))
- **view**: support doc comment elements (#816) ([8b95679](https://github.com/tempestphp/tempest-framework/commit/8b95679f809aa108a1740de51fd542efd2814e93))
- **view**: self-closing view component tags (#818) ([420e5d8](https://github.com/tempestphp/tempest-framework/commit/420e5d8763cdf7eaf1de6753c7371f37ba515e94))
- **view**: use bug when compiling view (#893) ([6ce796c](https://github.com/tempestphp/tempest-framework/commit/6ce796cf484560e4dce9f6f8ef80f574c270a66b))
- **vite**: fall back to global entrypoints if supplied ones are empty (#870) ([08df98c](https://github.com/tempestphp/tempest-framework/commit/08df98cc4e5c7cc49db9f2ea936e9f8e0c4a7cdf))
- **vite**: support new cors rules in Vite 6 (#890) ([d991bfd](https://github.com/tempestphp/tempest-framework/commit/d991bfd16f3920ea8a943f7b17c2db49b2bbf6b0))
- change order of scripts in composer.json (#786) ([f948184](https://github.com/tempestphp/tempest-framework/commit/f94818425bef894195575a3b0afa89798b043f06))


## [1.0.0-alpha.4](https://github.com/tempestphp/tempest-framework/compare/v1.0.0-alpha.3..v1.0.0-alpha.4)  —  2024-11-25

### 🚀 Features

- **commandbus**: async commands (#685) ([bfa1706](https://github.com/tempestphp/tempest-framework/commit/bfa170620b6ac34f97f76e072991d64387d2b522))
- **console**: support negative arguments (#660) ([1cdf158](https://github.com/tempestphp/tempest-framework/commit/1cdf158de97684b6f7b65b6361c885444109bc1f))
- **console**: support "no prompt" mode (#661) ([687e333](https://github.com/tempestphp/tempest-framework/commit/687e333d349600f6ecfe2abed64c4350e4f74039))
- **console**: add `name` parameter to `#[ConsoleArgument]` (#617) ([2a73033](https://github.com/tempestphp/tempest-framework/commit/2a730330c4ddfa85faca1170b710133c64e32852))
- **console**: ensure `tempest serve` supports routes with file extension (#704) ([6300617](https://github.com/tempestphp/tempest-framework/commit/6300617bd311cd1547f4ced49b18fdef24419b22))
- **console**: support dynamic style injections (#703) ([6847a79](https://github.com/tempestphp/tempest-framework/commit/6847a79df0446e1ed426e143cd45f7c31abc1893))
- **console**: accept `BackedEnum` as command arguments (#722) ([c21f24e](https://github.com/tempestphp/tempest-framework/commit/c21f24e7816567dbdc93ea064857b653966ac3d0))
- **console**: add `make:controller` and `make:model` commands (#647) ([0bdee91](https://github.com/tempestphp/tempest-framework/commit/0bdee919eca75ad07d8d069c5520448f66846628))
- **console**: add `make:request` command (#730) ([987eabf](https://github.com/tempestphp/tempest-framework/commit/987eabf7eaa88fa0b3e6e58fa641ddcf6d7ba346))
- **console**: infer binary path for scheduler (#758) ([25e5d81](https://github.com/tempestphp/tempest-framework/commit/25e5d816bf60274bacc6059849eae16fa3204c29))
- **console**: add `make:response` command (#760) ([e76c1f8](https://github.com/tempestphp/tempest-framework/commit/e76c1f8c27d5b27ad10739a4cd9138be5f41bcfc))
- **container**: support injecting properties using `#[Inject]` (#690) ([ab0eecd](https://github.com/tempestphp/tempest-framework/commit/ab0eecdb78e7699c28ecb4a2af383eee2b627cd9))
- **core**: install main namespace (#751) ([3f9bdde](https://github.com/tempestphp/tempest-framework/commit/3f9bddeb9dbc0d033bf9b6386e329c5686306ba3))
- **core**: partial discovery cache (#763) ([2049f6e](https://github.com/tempestphp/tempest-framework/commit/2049f6e5ad8b7c3040f3b131850d2a21453341c5))
- **database**: add json data type (#709) ([d599d50](https://github.com/tempestphp/tempest-framework/commit/d599d5047395d69d373b8ae6f8417afcf1920d76))
- **database**: add `set` data type (#725) ([f0db5c8](https://github.com/tempestphp/tempest-framework/commit/f0db5c89317e67658f5af491a406c7c504463092))
- **filesystem**: add new `Filesystem` component (#441) ([25d4a47](https://github.com/tempestphp/tempest-framework/commit/25d4a47f741f05e1ddb41a695960569d0645c206))
- **framework**: add `config:show` command (#732) ([2124577](https://github.com/tempestphp/tempest-framework/commit/21245776b4721d61fcd3f3a1c1b18e53884fa9b6))
- **http**: map uploaded files into the request properties (#702) ([a97014c](https://github.com/tempestphp/tempest-framework/commit/a97014c15653ecfc23586a0418cee30560249e69))
- **http**: add `Delete` attribute (#733) ([613b884](https://github.com/tempestphp/tempest-framework/commit/613b88460f62face589438af36c423ca44272e75))
- **http**: add `Put` and `Patch` attributes (#742) ([3621006](https://github.com/tempestphp/tempest-framework/commit/362100628cce1344dec3f08ad80768ad88486a6f))
- **log**: allow usage of multiple same log channels (#718) ([68d7e54](https://github.com/tempestphp/tempest-framework/commit/68d7e5482ff2fc69bdb648272e3dc0686797ceb8))
- **mapper**: json file to object mapper (#748) ([99933ff](https://github.com/tempestphp/tempest-framework/commit/99933ffff0759ba6a30cf26b04b0d4ec34c2b7d0))
- **routing**: add regex chunking to route regex (#714) ([3eb0c59](https://github.com/tempestphp/tempest-framework/commit/3eb0c5944ea5a8b522634ad70578c320266565e2))
- **support**: add sorting methods to `ArrayHelper` (#659) ([8f52e4a](https://github.com/tempestphp/tempest-framework/commit/8f52e4af0fc2466b854c9e7749f075f903f12d7b))
- **support**: add `wrap` and `unwrap` to `StringHelper` (#693) ([a0fffe9](https://github.com/tempestphp/tempest-framework/commit/a0fffe90873d8a00a7a726606402483d10e3f6e9))
- **support**: support not specifying a value to `ArrayHelper::pop` and `ArrayHelper::unshift` (#692) ([6a56d94](https://github.com/tempestphp/tempest-framework/commit/6a56d94c51115076d8cffb7a8eb5822051609904))
- **support**: add `start` to `StringHelper` (#713) ([f719c20](https://github.com/tempestphp/tempest-framework/commit/f719c203619c9edb39b9b9ac08bccc63e3823219))
- **support**: add methods `reduce`, `chunk` and `findKey` to `ArrayHelper` (#720) ([c8a31db](https://github.com/tempestphp/tempest-framework/commit/c8a31db07c149646263df2f72e26631f6102146d))
- **support**: add more methods to `ArrayHelper` and `StringHelper` (#721) ([bdf5efc](https://github.com/tempestphp/tempest-framework/commit/bdf5efc59f8f4ac7ec31003ae79e6dc9a7f64aee))
- **validation**: `ArrayList` rule (#745) ([ddda992](https://github.com/tempestphp/tempest-framework/commit/ddda992da505f8f61f456558d127cbd564153ba3))
- **validation**: enhance enum validation (#755) ([ca7a226](https://github.com/tempestphp/tempest-framework/commit/ca7a226f37c94535d3468d0b20c35f918b6ae240))
- **view**: add boolean attributes (#700) ([04000ac](https://github.com/tempestphp/tempest-framework/commit/04000ace54447c39bbfec261be46268745ad3fb0))
- **view**: add raw html element (#738) ([df6a418](https://github.com/tempestphp/tempest-framework/commit/df6a4189ae62d1ed3ea7fcf03f4d2cc1005c57db))
- optimize routing (#626) ([83f1dac](https://github.com/tempestphp/tempest-framework/commit/83f1dac4ee9acf17289c2accbd894be524316495))
- multiple routes per controller method (#667) ([166912d](https://github.com/tempestphp/tempest-framework/commit/166912d541e605c62ad9b6bbc9323fad71f3dbe1))
- route enum binding support (#668) ([f055fc1](https://github.com/tempestphp/tempest-framework/commit/f055fc143bd21c1dc1b23a78cd6415c4b7c73eb0))
- exception handler improvements (#670) ([0f97964](https://github.com/tempestphp/tempest-framework/commit/0f9796470e83c3091eeba975456e999abd9f436c))
- middleware callables (#672) ([d2e8a4e](https://github.com/tempestphp/tempest-framework/commit/d2e8a4ee569053e8440a7aa671103fe8d58d5061))

### ⚡ Performance

- **routing**: replace recursion in favor of iteration (#705) ([32aaff4](https://github.com/tempestphp/tempest-framework/commit/32aaff4a9bea79b98ac5e62a697667471885d5c1))

### 🐛 Bug fixes

- **commandbus**: disallow having two `#[CommandHandler]` for the same command (#706) ([f3054f9](https://github.com/tempestphp/tempest-framework/commit/f3054f95747ff27c94b44dfed2886ce501f2304f))
- **console**: handle nested `style` tags (#726) ([779973e](https://github.com/tempestphp/tempest-framework/commit/779973e5f2af6cdae75c919561c0da9139eb63fa))
- **core**: discovery location loading order (#663) ([6fc2d95](https://github.com/tempestphp/tempest-framework/commit/6fc2d9539beddab277a0483c6e1c703b8c77d0d1))
- **core**: discovery errors being silenced (#688) ([f5b848c](https://github.com/tempestphp/tempest-framework/commit/f5b848c3c5432ada48b4e4f5ad7c9bfa645e09e1))
- **core**: installers not updating docblock references (#696) ([7b7e2ca](https://github.com/tempestphp/tempest-framework/commit/7b7e2cab5a62ad9f08cebe8a49f1b78afd05e343))
- **generation**: simplify traits and method parameter attributes (#753) ([3cca6bc](https://github.com/tempestphp/tempest-framework/commit/3cca6bcf232142497f7c0b56b4df19ee03675836))
- **http**: collision between route and query params for uri generator (#687) ([e22492a](https://github.com/tempestphp/tempest-framework/commit/e22492a1b1c5ad80870bac7b80741ee5a0598848))
- **http**: use document root instead of env in `tempest serve` (#717) ([ccc1ece](https://github.com/tempestphp/tempest-framework/commit/ccc1eced75392945798173189fbe51e044fcd947))
- **http**: use default log config only if no config is provided (#719) ([fbaf866](https://github.com/tempestphp/tempest-framework/commit/fbaf866e209ece4f35ac3352c6342191011b189f))
- **support**: support calling `first` and `last` on empty `ArrayHelper` (#691) ([9021c6e](https://github.com/tempestphp/tempest-framework/commit/9021c6e08a4533a886cf2a4c915605d37b9be2e5))
- **view**: several bugfixes (#662) ([5034d0a](https://github.com/tempestphp/tempest-framework/commit/5034d0a65f9993d2aa447c348a06e522b94ab82b))
- **view**: attributes for raw elements (#734) ([f89eb5d](https://github.com/tempestphp/tempest-framework/commit/f89eb5d7bb512b181eba1e865f829e4bc6007a45))
- **view**: extra null check for node attributes (#740) ([5fa27bc](https://github.com/tempestphp/tempest-framework/commit/5fa27bcb1b152571433d266a19ae5b4647af8d0c))
- query param name collision in uri function (#679) ([59fe4fb](https://github.com/tempestphp/tempest-framework/commit/59fe4fb8122407b20ced648a751ef2d8e0730ce7))
- rector (#680) ([7fdff1d](https://github.com/tempestphp/tempest-framework/commit/7fdff1d7be48ab91fb35e1a07434ae54ef47781c))


## [1.0.0-alpha.3](https://github.com/tempestphp/tempest-framework/compare/v1.0.0-alpha.2..v1.0.0-alpha.3)  —  2024-10-31

### 🚨 Breaking changes

- [**breaking**] add support for specifying an optional port in serve command ([b8b9167](https://github.com/tempestphp/tempest-framework/commit/b8b9167b0d861e72e2d6f2c3f7fd1e2b74422617))
- [**breaking**] add the ability to use custom regex for route params ([871dda9](https://github.com/tempestphp/tempest-framework/commit/871dda97958a38b4c783390c7a3c529fc46ea687))

### 🚀 Features

- **console**: support string keys in `MultipleChoiceComponent` (#567) ([78f2794](https://github.com/tempestphp/tempest-framework/commit/78f2794b5d753251fff4f4caf7b624b310ab38bc))
- **container**: add ability to invoke arbitrary closures (#535) ([c1da5f1](https://github.com/tempestphp/tempest-framework/commit/c1da5f109ae75b52f7850e8d211f23d7892f8742))
- **core**: add root_path helper (#607) ([ccfcf94](https://github.com/tempestphp/tempest-framework/commit/ccfcf949bb860a1d5a906832422d36348ca89403))
- **event-bus**: support closure-based listeners (#540) ([0fa02bc](https://github.com/tempestphp/tempest-framework/commit/0fa02bcc31f3e43171865bfece344940d3d52615))
- **generation**: add `ClassGenerator` (#544) ([f54a0e0](https://github.com/tempestphp/tempest-framework/commit/f54a0e0f30fcc9a7ea7b041d4a38e20181161aa2))
- **support**: improve helpers (#538) ([6d60b9b](https://github.com/tempestphp/tempest-framework/commit/6d60b9b70882fe6b022d3bd62b3537e9a5a27237))
- **support**: improve array helper with additional methods (#557) ([57e6cd8](https://github.com/tempestphp/tempest-framework/commit/57e6cd836c50c3c7cd0fce93e9482d0bd8b2f664))
- **support**: refactor dd() method to use logger in string helper and add the dump() method (#563) ([3349cf1](https://github.com/tempestphp/tempest-framework/commit/3349cf16aefff2db1a4b7c0d8ee4e27d397aaf04))
- **support**: add implode()/explode() methods in string helper (#564) ([5718796](https://github.com/tempestphp/tempest-framework/commit/571879663ccb41192cf0b4525f64b16c611fec0d))
- **support**: add methods to array helper (#590) ([b16f797](https://github.com/tempestphp/tempest-framework/commit/b16f797cdb2086e47020c12ac7393e2f5ef774fe))
- **support**: add inline documentation on helper classes (#611) ([c5fdcad](https://github.com/tempestphp/tempest-framework/commit/c5fdcad5d1d276e4c129b24ae107e672b5ae4928))
- **validation**: support validating by closure (#570) ([450bc58](https://github.com/tempestphp/tempest-framework/commit/450bc5883ade43f715e3f34992ba6b6e5f6975f6))
- match all method for string helper class (#536) ([0f33a44](https://github.com/tempestphp/tempest-framework/commit/0f33a447cac5d8a167e6ba276eb844e5441e7031))
- enhance matchAll method to support flags and offset ([9da79f4](https://github.com/tempestphp/tempest-framework/commit/9da79f4dc5ea81acc42ff4ee31718d26ccdc0563))
- add named hasOne relation (#549) ([58906b7](https://github.com/tempestphp/tempest-framework/commit/58906b7220f9ff0215109f09ec2c6a9912f7961f))
- add initializer for builtin types (#541) ([bd64f5a](https://github.com/tempestphp/tempest-framework/commit/bd64f5a6d76936e825fc6372f73d08bb9838b918))
- add boolean data type (#547) (#555) ([6776fff](https://github.com/tempestphp/tempest-framework/commit/6776fff3456827d8caa9024d44ee686f67b6a656))
- add `isList()` and `isAssoc()` methods in ArrayHelper for array type checking (#566) ([f465060](https://github.com/tempestphp/tempest-framework/commit/f465060b3ed108adb37b1c1d4b9762cfaf761726))
- event bus improvements (#623) ([bf7ff15](https://github.com/tempestphp/tempest-framework/commit/bf7ff1557ec97936f98e37ee72c3f0add679b603))
- add defer helper (#624) ([15cd46e](https://github.com/tempestphp/tempest-framework/commit/15cd46e6ac46efa7dfc05d768d9d98984f299d99))
- install command (#625) ([10f3388](https://github.com/tempestphp/tempest-framework/commit/10f33888e778e3f769397b6d26642e46b43ee983))
- add inline documentation on all namespaced functions (#616) ([bef5af7](https://github.com/tempestphp/tempest-framework/commit/bef5af78df7d47491bd8b3a83ab8a992ba902419))
- publish imports (#643) ([52ca58d](https://github.com/tempestphp/tempest-framework/commit/52ca58da766cb3f7efb4d95cada8224382435862))

### 🐛 Bug fixes

- **#275**: implement weekly log rotation (#548) ([14fea7d](https://github.com/tempestphp/tempest-framework/commit/14fea7d1594a3683d0a0318dff2e2113dd7577ed))
- **container**: fix caching of autowire discovered classes (#630) ([6a5a5d5](https://github.com/tempestphp/tempest-framework/commit/6a5a5d572f8a2617484745e2d789ac2096c4b838))
- **http**: fix so referer header is resolved depending on request class in invalid response (#604) ([d463258](https://github.com/tempestphp/tempest-framework/commit/d4632581eabd29ed7a35f1daede798c6607ee007))
- **phpstan**: fix phpstan issues (#556) ([b1495b2](https://github.com/tempestphp/tempest-framework/commit/b1495b251cfb3b2a430e6587f5d1556a4e2978cd))
- **phpstan**: fix last phpstan issues (#589) ([e719dfa](https://github.com/tempestphp/tempest-framework/commit/e719dfa59b6375023e5e2fb33cfb07bfc8112ce4))
- **view**: consume dynamic attributes (#644) ([972595c](https://github.com/tempestphp/tempest-framework/commit/972595c371987c378b83cf09de414f9f571c345a))
- terminal line clearing (#576) ([ce2b6c4](https://github.com/tempestphp/tempest-framework/commit/ce2b6c438d8cc7fcde0523ad59ea9245715649ec))
- low terminal frame rate causing keystrokes to be dropped (#577) ([8f414d6](https://github.com/tempestphp/tempest-framework/commit/8f414d6cd608ed2859efcb50a46f522a1cf2c6c7))
- rector config (#581) ([83c103d](https://github.com/tempestphp/tempest-framework/commit/83c103dc1b5bbf4e39add377ea56e94508bf2075))
- view argument casing (#585) ([158b2db](https://github.com/tempestphp/tempest-framework/commit/158b2db46987fb867706dd5078613a9c9ea0bead))
- nullable properties not seen as nullable by TypeReflector (#591) ([6e7dc59](https://github.com/tempestphp/tempest-framework/commit/6e7dc59ec85a8cd19b87ba48f02ba0262a90c9d9))
- disable rector ci (#595) ([88dfdfb](https://github.com/tempestphp/tempest-framework/commit/88dfdfb4a9267df81602da946a26d2fb5ee1e7ac))
- type reflector uses wrong definition when converting to class (#592) ([94071c3](https://github.com/tempestphp/tempest-framework/commit/94071c34c9ee2be4ed57e06cae857b838de35295))
- view components with multiple attributes (#599) ([e00d0cd](https://github.com/tempestphp/tempest-framework/commit/e00d0cd35f8c07fde1cdae549c70c7605647fff8))
- console cache dependency (#603) ([2acdf9a](https://github.com/tempestphp/tempest-framework/commit/2acdf9a8c980e5e93cd67be0e4a0b185085f4fad))
- duplicate command completion (#600) ([effb684](https://github.com/tempestphp/tempest-framework/commit/effb684c91ccd579332b30a61131a58e2fb68dbb))
- publish file root namespace (#638) ([2051dd4](https://github.com/tempestphp/tempest-framework/commit/2051dd4719687502ad2c18a754726dedd91b3992))

### Build

- add release script ([e1a1107](https://github.com/tempestphp/tempest-framework/commit/e1a110750c7329c8dcfb05bfc9cc5bfa0152ca8e))


## 1.0.0-alpha.2  —  2024-10-04

### 🚀 Features

- **core**: add `Composer` util (#519) ([40c5f03](https://github.com/tempestphp/tempest-framework/commit/40c5f0339f1d3eec8d5af9f56f45ef25205606ea))
- **database**: implement table naming strategies (#453) ([519f44f](https://github.com/tempestphp/tempest-framework/commit/519f44fd38e3c41cced0a8debded91ee6f22d558))
- **discovery**: add ability to hide classes from discovery (#512) ([c09cdf4](https://github.com/tempestphp/tempest-framework/commit/c09cdf47c80f504b1cd5d7d21bf11a63771e0f4f))
- **discovery**: allow exceptions on `DoNotDiscover` classes (#521) ([1dcacae](https://github.com/tempestphp/tempest-framework/commit/1dcacae1632c34a602747f762613ab8b3e916ff0))
- **generation**: add `ClassManipulator` (#531) ([92ccb7d](https://github.com/tempestphp/tempest-framework/commit/92ccb7d43ba90d42b983e17165b24a60368a2228))
- **string-helper**: add replacement and concatenation methods (#517) ([6e3a63a](https://github.com/tempestphp/tempest-framework/commit/6e3a63add7936574e0233aaad4d02b276aea9aae))
- **support**: adds string pluralizer ([32fb3e9](https://github.com/tempestphp/tempest-framework/commit/32fb3e928a621fcdfa8d1ed8915634a54e51c8d0))
- **support**: add `StringHelper` ([ccb1e65](https://github.com/tempestphp/tempest-framework/commit/ccb1e659197f858db2bd7031b0b3e0ae0087e329))
- refactor string helper to object (#433) ([5eca329](https://github.com/tempestphp/tempest-framework/commit/5eca329f213e43944c99b87e7fb09b694b4387a9))
- add array helper (#434) ([988f28d](https://github.com/tempestphp/tempest-framework/commit/988f28d1661a0fca048b4e498374d2080812ee76))
- request::has methods (#448) ([82e6522](https://github.com/tempestphp/tempest-framework/commit/82e652291100fd0fa380cfbd20565daf7ab3ce43))
- add base uri support (#449) ([f243a1f](https://github.com/tempestphp/tempest-framework/commit/f243a1f86ffbd66c4b9279b8166ec9f0f00ab43c))
- always log errors in production handler (#454) ([0652e9c](https://github.com/tempestphp/tempest-framework/commit/0652e9caddaf74f3502b4e5e9d48ccb7f72cb19a))
- cache (#474) ([00fd2de](https://github.com/tempestphp/tempest-framework/commit/00fd2dec65a4229a050f80041adf80807c4888ec))
- add str::startswith and endswith (#484) ([3ac79d6](https://github.com/tempestphp/tempest-framework/commit/3ac79d687eb172e3762bcf2dcc86c9bc899acd49))
- cache:clear command (#487) ([c1f6cc4](https://github.com/tempestphp/tempest-framework/commit/c1f6cc4c00bfcc28b1152f698690c00f585277f1))
- use directories instead of file names to allow default server config setup (#479) ([140f9eb](https://github.com/tempestphp/tempest-framework/commit/140f9eb5a4d485ed0ed67ff5c88c83e9987ae3df))
- add output to schedule:run command (#489) ([c57b1ac](https://github.com/tempestphp/tempest-framework/commit/c57b1ac02e50547fa27bd1b9ce906b76f3e9f96d))
- allow hard-coded uris in router::touri (#490) ([72e88c8](https://github.com/tempestphp/tempest-framework/commit/72e88c8c7420744ac4c18f18d55b7c74f4a31ee7))
- add autowire/autodiscovery/auto-initialization of interfaces to classes (#501) ([1572122](https://github.com/tempestphp/tempest-framework/commit/157212204a081e188350e56995688b0d4e677c5e))
- array helper::map-to (#505) ([e4bb059](https://github.com/tempestphp/tempest-framework/commit/e4bb0593ea61072b537ad69699c12661ad49e061))
- authenticator (#493) ([5017c5f](https://github.com/tempestphp/tempest-framework/commit/5017c5f22c3d5a1b2057894df4296408408a0bb1))
- adds `enctype` within the form component (#500) ([7c4f12d](https://github.com/tempestphp/tempest-framework/commit/7c4f12d1bba68cfdf7754a425a113640ea964972))
- str match (#527) ([ce899cd](https://github.com/tempestphp/tempest-framework/commit/ce899cdefce4c558ae721c505dfe04b40db165bc))
- str regex functions (#528) ([672ea02](https://github.com/tempestphp/tempest-framework/commit/672ea02f4901eb2e16ee8951ec12b63ba209fa5f))
- allow object to define how they are mapped to array (#532) ([19d001b](https://github.com/tempestphp/tempest-framework/commit/19d001bde2fbb2b2433fb6c4aebf6b538dfb8fb0))

### 🐛 Bug fixes

- **console**: support `default` parameter on textbox component (#518) ([3c86f8e](https://github.com/tempestphp/tempest-framework/commit/3c86f8ed88ee70d59ca8b2429e42d1c2b0a8afec))
- **view**: join generic elements with an empty string ([3ad5461](https://github.com/tempestphp/tempest-framework/commit/3ad5461f7d1ba020950eb603c4a13e65f1449695))
- Missing descriptions in composer files ([5c28483](https://github.com/tempestphp/tempest-framework/commit/5c28483995d1875a6df0d48b409693dbe395b080))
- package validation reading the license from composer files ([3701b64](https://github.com/tempestphp/tempest-framework/commit/3701b64c6222a6301188c15593daa8f998ce7fd6))
- view renderer bug fixes (#439) ([d60d26f](https://github.com/tempestphp/tempest-framework/commit/d60d26f1fcb64458ce5c8db5fd821a0521400f38))
- view renderer bug fixes (#440) ([6a85ef8](https://github.com/tempestphp/tempest-framework/commit/6a85ef8b850159027d1d2f484f7a1c35529be35b))
- improved error handling for warnings and deprecations (#443) ([1e7ad4d](https://github.com/tempestphp/tempest-framework/commit/1e7ad4dde57c784163599ee355982a58c17abba2))
- response sender improvements for download and file responses (#447) ([3132bed](https://github.com/tempestphp/tempest-framework/commit/3132bed56234fb4895b1d446d0d7542ace162988))
- extra check for existing file (#455) ([f466b7d](https://github.com/tempestphp/tempest-framework/commit/f466b7d5b15fd0de7fcae788edfc90fa983e57ba))
- properly detect application (#456) ([ee84b94](https://github.com/tempestphp/tempest-framework/commit/ee84b94aa9b9ff94427d01f01fc83c2799bd0099))
- view component attribute fixes (#459) ([4622298](https://github.com/tempestphp/tempest-framework/commit/462229863b9de0dd756f65302875e5121f812b94))
- package dependencies (#461) ([e1e8470](https://github.com/tempestphp/tempest-framework/commit/e1e84704d62b9f609611ba851d5cf8ce2f43518a))
- dependency loop between kernel and event-bus (#475) ([53a9c86](https://github.com/tempestphp/tempest-framework/commit/53a9c867db649f557c6bc652491d1e9c7b262f0b))
- rename console component interfaces (#476) ([d96f98e](https://github.com/tempestphp/tempest-framework/commit/d96f98ebdf3d5ecbf4426e385f0bf8230b999d9d))
- database component tests (#477) ([02a85e1](https://github.com/tempestphp/tempest-framework/commit/02a85e1e95575885d1f4473fd5c2590467f5fa31))
- typo in readme (#473) ([df14709](https://github.com/tempestphp/tempest-framework/commit/df14709530b77d3ca37f020e6ca9f33e4dc5ad40))
- handle invalid discovery cache (#492) ([bc44eb9](https://github.com/tempestphp/tempest-framework/commit/bc44eb91e154d792d8d9cc628a0ac88152b4abd6))
- validation referer (#511) ([a3710a8](https://github.com/tempestphp/tempest-framework/commit/a3710a890bd91dc439e25919fbf9e7f5003bfe0e))
- datetime caster with datetime object (#514) ([e361f26](https://github.com/tempestphp/tempest-framework/commit/e361f26c615f0a37f7142c27f5e354857754377d))
- static generate error handling (#529) ([02b4db8](https://github.com/tempestphp/tempest-framework/commit/02b4db8a9f711f8d690412394733722d4c4f719d))

### Maintenance

- phpunit fixes (#436) ([759134f](https://github.com/tempestphp/tempest-framework/commit/759134f50f2327efb04f9334e78baa88d877a5e7))
- tag console highlighter (#437) ([b7c5332](https://github.com/tempestphp/tempest-framework/commit/b7c53325ff22905f3d09015f466391791f8f886f))
- update console readme (#444) ([7c6f1b0](https://github.com/tempestphp/tempest-framework/commit/7c6f1b083fd429d58cbde2d30b4af2143e9c58f0))


