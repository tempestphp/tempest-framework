# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0-alpha.6](https://github.com/tempestphp/tempest-framework/compare/v1.0.0-alpha.5..1.0.0-alpha.6)  —  2025-03-24

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

### 🚜 Refactor

- **core**: remove unnecessary set hook from Kernel interface (#959) ([5949729](https://github.com/tempestphp/tempest-framework/commit/5949729119f7f1ef11ebfeefc62e9f5e9103ba1f))
- **core**: cleanup internal path helper (#960) ([92321b3](https://github.com/tempestphp/tempest-framework/commit/92321b3f145f9207ed8593b7067dc3f7483e02f5))
- **framework**: centralize internal caches (#948) ([d80bfa9](https://github.com/tempestphp/tempest-framework/commit/d80bfa9c7c263f0ddb85ab341b43be74eb4c8921))
- **mapper**: mapper improvements (#992) ([7cf0517](https://github.com/tempestphp/tempest-framework/commit/7cf0517dac4a47afd1233391df77af7a688a89ce))
- **mapper**: split caster and serializer into two (#1041) ([798d0f4](https://github.com/tempestphp/tempest-framework/commit/798d0f4ec1715eb6bc88afc6fb39a73be7721482))
- **router**: make request mapping consistent (#997) ([cb9dfc7](https://github.com/tempestphp/tempest-framework/commit/cb9dfc71495a77ea2b1342600f9a5b0d7a2563df))
- **support**: remove duplicate code (#912) ([3f8a330](https://github.com/tempestphp/tempest-framework/commit/3f8a3307d42d4e3195629ddf60d1a8fb3b4e6ed1))
- **support**: minor code cleanup (#961) ([749e97d](https://github.com/tempestphp/tempest-framework/commit/749e97dce46ae351ac56f71a5be17ff77a958c9e))
- **support**: rename `map` to `map_array` (#1045) ([51dc620](https://github.com/tempestphp/tempest-framework/commit/51dc620dd0e9bd617ae3e4a8aaeca6367136094e))
- **view**: use `is_self_closing_tag` where relevant (#1035) ([4d534fe](https://github.com/tempestphp/tempest-framework/commit/4d534fefdb6ebef8b8dfa8f20a3af315ebed5243))
- **view**: is_void_tag (#1037) ([f5e38d0](https://github.com/tempestphp/tempest-framework/commit/f5e38d062aa18a0df77874bec914c13b44dfb33e))


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

### 🚜 Refactor

- **console**: improve visual consistency (#888) ([31213bc](https://github.com/tempestphp/tempest-framework/commit/31213bce078ec23f4c02ce03b90881ad5ae0297b))
- **container**: mark container exceptions with `ContainerException` (#831) ([985be66](https://github.com/tempestphp/tempest-framework/commit/985be66623d3d5c7e9f86b1a5fbc2a58440307e1))
- **core**: call invokable classes with `invoke` (#799) ([15dd7bd](https://github.com/tempestphp/tempest-framework/commit/15dd7bd570bc57565910aeedb5b11daf62c9a101))
- **discovery**: extract into a standalone module (#861) ([58aadc9](https://github.com/tempestphp/tempest-framework/commit/58aadc97a1dad6e99c52b12e1afd6ac0ec40b46f))
- **http**: refactor http route attribute (#793) ([1bd7a67](https://github.com/tempestphp/tempest-framework/commit/1bd7a679b15a33b9209dc7c87300e1ebcd2bfb4b))
- **router**: convert route attributes to plain objects (#822) ([88bd85d](https://github.com/tempestphp/tempest-framework/commit/88bd85d404b5311ad5a76d41b08b22b84368afba))
- **view**: use PHP 8.4 DOM (#787) ([d58d028](https://github.com/tempestphp/tempest-framework/commit/d58d02854208a2cac91b948106950e4a5cf16192))
- move http router to separate package ([b7f99a8](https://github.com/tempestphp/tempest-framework/commit/b7f99a8906ea8ffd63109af24779b40458ee0f34))
- partial update to property hooks (#848) ([5abb74b](https://github.com/tempestphp/tempest-framework/commit/5abb74b21326387ee2f603fe558d9f0042efcc3e))
- general codebase upgrade to latest practices (#883) ([f880072](https://github.com/tempestphp/tempest-framework/commit/f8800722be96dc01e5402634596385812406fc61))


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

### 🚜 Refactor

- **console**: exit codes as enum + int (#741) ([b014f40](https://github.com/tempestphp/tempest-framework/commit/b014f400bcacf28587ce1937f1d52aec7a450073))
- **console**: update `make:controller` command parameters to be simpler (#761) ([9835d2e](https://github.com/tempestphp/tempest-framework/commit/9835d2e7cf567f53dbc7edffd3e998ef3ac3d79f))
- **routing**: split route construction (#666) ([32bf4d0](https://github.com/tempestphp/tempest-framework/commit/32bf4d020d811264218e41870f119114e4051b03))
- **support**: convert `PathHelper` to immutable class (#743) ([0dacd10](https://github.com/tempestphp/tempest-framework/commit/0dacd1038932d6c5df50ad9b08f5134ffa9dfbc4))
- update `ModelQueryBuilder` generics to support static analysis ([cea122e](https://github.com/tempestphp/tempest-framework/commit/cea122e293897e552aa5981e9a703faedadd74b8))
- replace `ramsey/uuid` with `symfony/uid` (#724) ([8e6b650](https://github.com/tempestphp/tempest-framework/commit/8e6b6507db1fb60b8026dfa087d997c7b902e5a6))

### ⚡ Performance

- **routing**: replace recursion in favor of iteration (#705) ([32aaff4](https://github.com/tempestphp/tempest-framework/commit/32aaff4a9bea79b98ac5e62a697667471885d5c1))


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

### 🚜 Refactor

- **http**: resolve `HttpApplication` via the container (#606) ([4680289](https://github.com/tempestphp/tempest-framework/commit/4680289923b584aace57cf7cb62ccd7758e7ae30))
- **reflection**: replace `eval` call in `TypeReflector` ([7ffe2d7](https://github.com/tempestphp/tempest-framework/commit/7ffe2d7a91ba140c6d829b8aae5fd23edf7e1285))
- **support**: move `LanguageHelper#join` to `ArrayHelper` and `StringHelper` (#612) ([75d902b](https://github.com/tempestphp/tempest-framework/commit/75d902b772429a6a90e1d92cc020637ad3da7dc0))
- **validation**: improve readability of `validateValue` (#586) ([fef15c0](https://github.com/tempestphp/tempest-framework/commit/fef15c0a771110a123169d6ce958a2c5a9c0b4e3))

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

### 🧪 Tests

- **console**: add test for install command (#432) ([d0085f7](https://github.com/tempestphp/tempest-framework/commit/d0085f773766fe7f76c6ac39a849fe4ea7ab640a))

### Maintenance

- phpunit fixes (#436) ([759134f](https://github.com/tempestphp/tempest-framework/commit/759134f50f2327efb04f9334e78baa88d877a5e7))
- tag console highlighter (#437) ([b7c5332](https://github.com/tempestphp/tempest-framework/commit/b7c53325ff22905f3d09015f466391791f8f886f))
- update console readme (#444) ([7c6f1b0](https://github.com/tempestphp/tempest-framework/commit/7c6f1b083fd429d58cbde2d30b4af2143e9c58f0))


