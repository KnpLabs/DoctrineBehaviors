# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

PRs and issues are linked, so you can find more about it. Thanks to [ChangelogLinker](https://github.com/Symplify/ChangelogLinker).

<!-- changelog-linker -->

## [v2.0.0-beta1]

### Added

- [#470] Add `CHANGELOG.md`

### Changed

- [#475] [Tree] Include name "tree" in naming
- [#478] Improve docs, handle timestampable field type for the user
- [#474] split NodeTrait to NodeMethodsTrait and NodePropertiesTrait

### Fixed

- [#477] Fix slug uniqueness check function, Thanks to [@StanislavUngr]
- [#472] Fix slug generation if the getRegenerateSlugOnUpdate method return false, Thanks to [@hermann8u]

### Removed

- [#473] [Sortable] Drop, it never worked

## [v2.0.0-alpha4]

### Added

- [#469] [Rector] Add Upgrade set for id property on translations

## [v2.0.0-alpha3]

### Changed

- [#468] Use symfony/strings instead of transliterator

### Removed

- [#467] [Geocodable] Drop for very wide interface and limited usage
- [#464] Remove scheduleExtraUpdate calls

## [v2.0.0-alpha2]

### Added

- [#448] [Translation] add abstract class support
- [#460] add setTranslations()
- [#452] Add missing dependency symplify/package-builder, Thanks to [@webda2l]
- [#447] add default location provider

### Changed

- [#458] Use PHP 7.4 instead of a snapshot on Travis, Thanks to [@andreybolonin]
- [#459] composer: use the symfony/security for symfony 4.4
- [#461] Various updates
- [#449] make slug unique optionally [closes [#236]]
- [#453] use entity list instead of explicit
- [#450] [Uuidable] init
- [#451] [tests] move entities and repositories to own namespaces

### Removed

- [#463] drop filterable, way to opinionated and limited, use custom implementation
- [#457] remove repository traits, use custom methods in own repository instead

## [v2.0.0-alpha1]

### Added

- [#435] [CI] Add + Apply Coding Standards: PSR12, PHP 7.0, PHP 7.1
- [#445] [CI] Add Rector
- [#436] Add static code analysis and PSR-4 for tests
- [#443] Add code of conduct
- [#425] Explicitly add maintainers in the README, Thanks to [@alexpozzi]

### Changed

- [#423] Do not specify version constraint - let Composer do this, Thanks to [@bocharsky-bw]
- [#433] Travis: bump to min PHP 7.2, test stable doctrine/orm
- [#389] Shrink locale columns to 5 chars, Thanks to [@NiR-]
- [#442] Refactoring tests to dependency injection container based + use interfaces over traits for detection
- [#390] Document master and v1 branches, Thanks to [@NiR-]
- [#438] [cs] apply common set - unite MIT license to single file
- [#444] [cs] use trait suffix for traits to prevent opening
- [#439] [cs] apply symplify set
- [#441] [tests] strict types for subscribers, various PR cherry-pick

### Fixed

- [#440] [Translatable] Fix property access on twig
- [#411] Fix config deprecation, Thanks to [@martinprihoda]

## [1.6.0] - 2018-11-13

### Added

- [#358] [Tree] Add possibility to pass extra parameters in getTree, Thanks to [@Einenlum]

### Changed

- [#382] [Translatable] Do not persist new translations if empty, Thanks to [@giuliapellegrini]
- [#392] Only set locales on entities managed by knp translations, Thanks to [@jordisala1991]

## [1.5.0] - 2017-09-27

### Added

- [#361] Add nullable setchildof, Thanks to [@Einenlum]

### Changed

- [#363] Looking for maintainers, Thanks to [@Einenlum]

### Fixed

- [#365] Fix drop php < 7, Thanks to [@Einenlum]

## [1.4.1] - 2017-09-19

### Changed

- [#338] Update branch alias in composer.json, Thanks to [@nykopol]
- [#326] Use svg image for Travis badge and show status of master branch, Thanks to [@bocharsky-bw]
- [#328] Run PHPUnit in normal mode instead of --testdox, Thanks to [@bocharsky-bw]

### Fixed

- [#304] Minor fix: Tweak docblocks, Thanks to [@bocharsky-bw]
- [#256] fix typo, Thanks to [@shieldo]
- [#332] Fix: isEmpty() return true if it's empty, Thanks to [@corentinheadoo]
- [#360] Fix doctrine dependency and drop PHP < 7, Thanks to [@Einenlum]
- [#350] Fix disabling softdeletable, Thanks to [@ossinkine]
- [#353] Markdown syntax fix, Thanks to [@Nyholm]

## [1.4.0] - 2016-09-30

- [#317] Fix interaction between translations & joined inheritance, Thanks to [@lemoinem]
- [#316] Fixes for Symfony 3.1, Thanks to [@tarlepp]

## Previous Versions

### Added

- [#98] [Geocodable] Add a function to compute distances in meters., Thanks to [@kimlai]
- [#262] Timestampable - add db field type parameter, Thanks to [@lopsided]
- [#1] Added filterable repository behavior, Thanks to [@l3l0]
- [#253] Add documentation to override the default naming strategy for translatable, Thanks to [@ksom]
- [#76] Add missing setSlug method, Thanks to [@EmmanuelVella]
- [#10] Added sluggable trait, Thanks to [@Lusitanian]
- [#20] Add a post delete feature to the SoftDeletable trait, Thanks to [@PedroTroller]
- [#25] Add a method to test the removal of the object in the future, Thanks to [@PedroTroller]
- [#27] Add preRemove hook to Blamable trait and listener, Thanks to [@PedroTroller]
- [#57] Add a creation message, Thanks to [@PedroTroller]
- [#62] add recursive trait parameter to orm services, Thanks to [@docteurklein]
- [#112] Adds a parameter for the fetch method used by doctrine for the translations, Thanks to [@bobvandevijver]
- [#220] Add documentation about restore() method in Softdeleteable, Thanks to [@akovalyov]
- [#138] add several missing subscribers, Thanks to [@greg0ire]
- [#148] travis - PHP 5.6 added, linter added
- [#160] Return $this where it was not added., Thanks to [@kuczek]
- [#179] Improved Entity Managers configs + added doc for testing from local env, Thanks to [@hanovruslan]
- [#189] added customizable tree identifier, Thanks to [@digitalkaoz]
- [#191] Add Callable function to override default language, Thanks to [@jerome-fix]
- [#206] travis: PHP 7.0 nightly added
- [#243] Add missing annotations, Thanks to [@bocharsky-bw]
- [#192] added missing function to interface, Thanks to [@digitalkaoz]
- [#18] [softDeletable] added method to restore a deleted entity, Thanks to [@inoryy]

### Changed

- [#85] [FEATURE] Refactor \Knp\DoctrineBehaviors\ORM\Tree\Tree::getRootNodes to support QueryBuilder customization, Thanks to [@MisatoTremor]
- [#187] Call generateSlug from SluggableSubscriber, Thanks to [@EmmanuelVella]
- [#174] Fix ability to choose the class translation name, Thanks to [@asprega]
- [#31] README update, Thanks to [@eillarra]
- [#217] Change allowed_falures to allow_failures in travis.yml config, Thanks to [@akovalyov]
- [#216] Attemt to put vendor folder to cache to prevent composer failures, Thanks to [@akovalyov]
- [#56] Error serializing the AbstractToken in Symfony2, Thanks to [@patxi1980]
- [#199] run tests with the lowest possible versions, Thanks to [@greg0ire]
- [#36] Update TimestampableListener.php, Thanks to [@trsteel88]
- [#114] ClassAnalyzer::hasTrait returns false if $parentClass is NULL
- [#115] option to prevent default translation search, Thanks to [@DerekRoth]
- [#119] Update README.md, Thanks to [@Mondane]
- [#185] Update README.md, Thanks to [@JoydS]
- [#126] Tests should be green., Thanks to [@akovalyov]
- [#176] Yaml-Lint for travis., Thanks to [@kuczek]
- [#162] Slug generation from cyrillic strings, Thanks to [@MAXakaWIZARD]
- [#167] Allow locale as an association entity, Thanks to [@burci]
- [#225] Highlight PHP code syntax, Thanks to [@bocharsky-bw]
- [#161] Try to use getters when getting non-existent field values for Sluggable, Thanks to [@MAXakaWIZARD]
- [#43] Update child methods so they contain 'Node', Thanks to [@trsteel88]
- [#131] Ease contributions, Thanks to [@greg0ire]
- [#157] Translation fallback, Thanks to [@kuczek]
- [#133] use PSR-4 autoloading, Thanks to [@greg0ire]
- [#149] TranslatableSubscriber change undefined property $this->em to $em, Thanks to [@adrienrusso]
- [#44] Semantic versioning, Thanks to [@jankramer]
- [#134] rename listener to subscriber, Thanks to [@greg0ire]
- [#52] Update the geocodable documentation, Thanks to [@josselinh]
- [#144] Improve the translatable documentation, Thanks to [@roukmoute]
- [#135] Replace most occurences of listener with subscribers, Thanks to [@greg0ire]
- [#136] Use event system, Thanks to [@greg0ire]
- [#221] Register it as a bundle, Thanks to [@akovalyov]
- [#226] Use PropertyAccessor for get translations, Thanks to [@bocharsky-bw]
- [#55] Update README.md, Thanks to [@jalopezcar]
- [#228] Change mysite.com with example.com, Thanks to [@bocharsky-bw]
- [#2] Use non-locale aware type modifier %F in sprintf(), Thanks to [@jsor]
- [#3] require php >= 5.4.0 since you use traits :), Thanks to [@pminnieur]
- [#78] Make timestampable and blameable setters fluent, Thanks to [@EmmanuelVella]
- [#6] rename entity traits to Model namespace, Thanks to [@docteurklein]
- [#7] Auto metadata, Thanks to [@docteurklein]
- [#13] Modify SoftDeletable, Thanks to [@akia]
- [#14] Update README.md, Thanks to [@michelsalib]
- [#290] Test against three last doctrine common versions, Thanks to [@akovalyov]
- [#288] TranslatableMethods: use late static bindings, Thanks to [@meyerbaptiste]
- [#284] Microseconds, Thanks to [@boekkooi]
- [#276] Update UserCallable.php, Thanks to [@adampiotrowski]
- [#89] Parametrized translatable and translation Traits, Thanks to [@alch]
- [#274] Translatable: enable cascade persist and merge on the owning side, Thanks to [@jonasgoderis]
- [#102] Parametrized traits, Thanks to [@gaydarov]
- [#231] use psr logger instead of symfony logger, Thanks to [@digitalkaoz]
- [#232] Make Blameable respect the isRecursive setting, Thanks to [@jdachtera]
- [#240] Set current and default locale on prePersist event, Thanks to [@MAXakaWIZARD]
- [#111] Rename of parameter to comply with the rest of the parameters, Thanks to [@bobvandevijver]
- [#250] Reorder list of behaviors with ASC order in docs, Thanks to [@bocharsky-bw]
- [#252] composer: bump to PHPUnit ~4.8
- [#141] test different versions of doctrine, Thanks to [@greg0ire]
- [#266] Make Behaviors configurable., Thanks to [@NiR-]
- [#254] Update tree documentation, Thanks to [@ksom]
- [#93] issue Bug (typo+) in softDeletable doc [#90], Thanks to [@siciarek]
- [#21] Refactoring/listener, Thanks to [@PedroTroller]
- [#265] Language only fallback, Thanks to [@DerekRoth]

### Fixed

- [#88] [RFC] fix for TranslatableListener, to ignore entities that have translations as properties, Thanks to [@theodorDiaconu]
- [#132] fix path, Thanks to [@greg0ire]
- [#83] Fixed error with deletedBy field update on entity persist, Thanks to [@dmishh]
- [#146] Fixed support for PointType with Mysql., Thanks to [@kuczek]
- [#77] Fix phpdoc type hint, Thanks to [@EmmanuelVella]
- [#30] Fixed bug with isDeleted() return true, Thanks to [@dmishh]
- [#4] Fixes generate:doctrine:entities errors, Thanks to [@fixe]
- [#300] Fix beforeNormalization anonymous function, Thanks to [@NiR-]
- [#298] Fix if no config specified all behaviors are enabled, Thanks to [@NiR-]
- [#279] Fixes Travis checks, Thanks to [@tobias-93]
- [#275] Fixes compatibility for Symfony 3.0, Thanks to [@tobias-93]
- [#24] Fixed removal of translations, Thanks to [@jankramer]
- [#26] Fix/travis, Thanks to [@PedroTroller]
- [#32] Fix DI mistake, Thanks to [@NicolasBadey]
- [#143] Fixed typo in variable $fetchMode name, Thanks to [@cblegare]
- [#37] Fix typos, Thanks to [@trsteel88]
- [#40] Fixed SoftDeletable behavior when using inheritance, Thanks to [@jankramer]
- [#68] Debug instead of log, fix for DateTime, Thanks to [@kuczek]
- [#41] Fix Geocodable listener isEntitySupported check, Thanks to [@EmmanuelVella]
- [#155] This PR is fixing [#122] and [#150], Thanks to [@pmontoya]
- [#152] Fix for array values in log, Thanks to [@kuczek]
- [#147] Fix missing EntityManager in TranslatableSubscriber

### Removed

- [#60] Remove [@constructor] annotations, Thanks to [@jankramer]
- [#158] Removed Id from EnittyTranslation fixture., Thanks to [@kuczek]
- [#142] remove unneeded dependency, Thanks to [@greg0ire]

[#469]: https://github.com/KnpLabs/DoctrineBehaviors/pull/469
[#468]: https://github.com/KnpLabs/DoctrineBehaviors/pull/468
[#467]: https://github.com/KnpLabs/DoctrineBehaviors/pull/467
[#464]: https://github.com/KnpLabs/DoctrineBehaviors/pull/464
[#463]: https://github.com/KnpLabs/DoctrineBehaviors/pull/463
[#461]: https://github.com/KnpLabs/DoctrineBehaviors/pull/461
[#460]: https://github.com/KnpLabs/DoctrineBehaviors/pull/460
[#459]: https://github.com/KnpLabs/DoctrineBehaviors/pull/459
[#458]: https://github.com/KnpLabs/DoctrineBehaviors/pull/458
[#457]: https://github.com/KnpLabs/DoctrineBehaviors/pull/457
[#453]: https://github.com/KnpLabs/DoctrineBehaviors/pull/453
[#452]: https://github.com/KnpLabs/DoctrineBehaviors/pull/452
[#451]: https://github.com/KnpLabs/DoctrineBehaviors/pull/451
[#450]: https://github.com/KnpLabs/DoctrineBehaviors/pull/450
[#449]: https://github.com/KnpLabs/DoctrineBehaviors/pull/449
[#448]: https://github.com/KnpLabs/DoctrineBehaviors/pull/448
[#447]: https://github.com/KnpLabs/DoctrineBehaviors/pull/447
[#445]: https://github.com/KnpLabs/DoctrineBehaviors/pull/445
[#444]: https://github.com/KnpLabs/DoctrineBehaviors/pull/444
[#443]: https://github.com/KnpLabs/DoctrineBehaviors/pull/443
[#442]: https://github.com/KnpLabs/DoctrineBehaviors/pull/442
[#441]: https://github.com/KnpLabs/DoctrineBehaviors/pull/441
[#440]: https://github.com/KnpLabs/DoctrineBehaviors/pull/440
[#439]: https://github.com/KnpLabs/DoctrineBehaviors/pull/439
[#438]: https://github.com/KnpLabs/DoctrineBehaviors/pull/438
[#436]: https://github.com/KnpLabs/DoctrineBehaviors/pull/436
[#435]: https://github.com/KnpLabs/DoctrineBehaviors/pull/435
[#433]: https://github.com/KnpLabs/DoctrineBehaviors/pull/433
[#425]: https://github.com/KnpLabs/DoctrineBehaviors/pull/425
[#423]: https://github.com/KnpLabs/DoctrineBehaviors/pull/423
[#411]: https://github.com/KnpLabs/DoctrineBehaviors/pull/411
[#392]: https://github.com/KnpLabs/DoctrineBehaviors/pull/392
[#390]: https://github.com/KnpLabs/DoctrineBehaviors/pull/390
[#389]: https://github.com/KnpLabs/DoctrineBehaviors/pull/389
[#382]: https://github.com/KnpLabs/DoctrineBehaviors/pull/382
[#365]: https://github.com/KnpLabs/DoctrineBehaviors/pull/365
[#363]: https://github.com/KnpLabs/DoctrineBehaviors/pull/363
[#361]: https://github.com/KnpLabs/DoctrineBehaviors/pull/361
[#360]: https://github.com/KnpLabs/DoctrineBehaviors/pull/360
[#358]: https://github.com/KnpLabs/DoctrineBehaviors/pull/358
[#353]: https://github.com/KnpLabs/DoctrineBehaviors/pull/353
[#350]: https://github.com/KnpLabs/DoctrineBehaviors/pull/350
[#338]: https://github.com/KnpLabs/DoctrineBehaviors/pull/338
[#332]: https://github.com/KnpLabs/DoctrineBehaviors/pull/332
[#328]: https://github.com/KnpLabs/DoctrineBehaviors/pull/328
[#326]: https://github.com/KnpLabs/DoctrineBehaviors/pull/326
[#317]: https://github.com/KnpLabs/DoctrineBehaviors/pull/317
[#316]: https://github.com/KnpLabs/DoctrineBehaviors/pull/316
[#304]: https://github.com/KnpLabs/DoctrineBehaviors/pull/304
[#300]: https://github.com/KnpLabs/DoctrineBehaviors/pull/300
[#298]: https://github.com/KnpLabs/DoctrineBehaviors/pull/298
[#290]: https://github.com/KnpLabs/DoctrineBehaviors/pull/290
[#288]: https://github.com/KnpLabs/DoctrineBehaviors/pull/288
[#284]: https://github.com/KnpLabs/DoctrineBehaviors/pull/284
[#279]: https://github.com/KnpLabs/DoctrineBehaviors/pull/279
[#276]: https://github.com/KnpLabs/DoctrineBehaviors/pull/276
[#275]: https://github.com/KnpLabs/DoctrineBehaviors/pull/275
[#274]: https://github.com/KnpLabs/DoctrineBehaviors/pull/274
[#266]: https://github.com/KnpLabs/DoctrineBehaviors/pull/266
[#265]: https://github.com/KnpLabs/DoctrineBehaviors/pull/265
[#262]: https://github.com/KnpLabs/DoctrineBehaviors/pull/262
[#256]: https://github.com/KnpLabs/DoctrineBehaviors/pull/256
[#254]: https://github.com/KnpLabs/DoctrineBehaviors/pull/254
[#253]: https://github.com/KnpLabs/DoctrineBehaviors/pull/253
[#252]: https://github.com/KnpLabs/DoctrineBehaviors/pull/252
[#250]: https://github.com/KnpLabs/DoctrineBehaviors/pull/250
[#243]: https://github.com/KnpLabs/DoctrineBehaviors/pull/243
[#240]: https://github.com/KnpLabs/DoctrineBehaviors/pull/240
[#236]: https://github.com/KnpLabs/DoctrineBehaviors/pull/236
[#232]: https://github.com/KnpLabs/DoctrineBehaviors/pull/232
[#231]: https://github.com/KnpLabs/DoctrineBehaviors/pull/231
[#228]: https://github.com/KnpLabs/DoctrineBehaviors/pull/228
[#226]: https://github.com/KnpLabs/DoctrineBehaviors/pull/226
[#225]: https://github.com/KnpLabs/DoctrineBehaviors/pull/225
[#221]: https://github.com/KnpLabs/DoctrineBehaviors/pull/221
[#220]: https://github.com/KnpLabs/DoctrineBehaviors/pull/220
[#217]: https://github.com/KnpLabs/DoctrineBehaviors/pull/217
[#216]: https://github.com/KnpLabs/DoctrineBehaviors/pull/216
[#206]: https://github.com/KnpLabs/DoctrineBehaviors/pull/206
[#199]: https://github.com/KnpLabs/DoctrineBehaviors/pull/199
[#192]: https://github.com/KnpLabs/DoctrineBehaviors/pull/192
[#191]: https://github.com/KnpLabs/DoctrineBehaviors/pull/191
[#189]: https://github.com/KnpLabs/DoctrineBehaviors/pull/189
[#187]: https://github.com/KnpLabs/DoctrineBehaviors/pull/187
[#185]: https://github.com/KnpLabs/DoctrineBehaviors/pull/185
[#179]: https://github.com/KnpLabs/DoctrineBehaviors/pull/179
[#176]: https://github.com/KnpLabs/DoctrineBehaviors/pull/176
[#174]: https://github.com/KnpLabs/DoctrineBehaviors/pull/174
[#167]: https://github.com/KnpLabs/DoctrineBehaviors/pull/167
[#162]: https://github.com/KnpLabs/DoctrineBehaviors/pull/162
[#161]: https://github.com/KnpLabs/DoctrineBehaviors/pull/161
[#160]: https://github.com/KnpLabs/DoctrineBehaviors/pull/160
[#158]: https://github.com/KnpLabs/DoctrineBehaviors/pull/158
[#157]: https://github.com/KnpLabs/DoctrineBehaviors/pull/157
[#155]: https://github.com/KnpLabs/DoctrineBehaviors/pull/155
[#152]: https://github.com/KnpLabs/DoctrineBehaviors/pull/152
[#150]: https://github.com/KnpLabs/DoctrineBehaviors/pull/150
[#149]: https://github.com/KnpLabs/DoctrineBehaviors/pull/149
[#148]: https://github.com/KnpLabs/DoctrineBehaviors/pull/148
[#147]: https://github.com/KnpLabs/DoctrineBehaviors/pull/147
[#146]: https://github.com/KnpLabs/DoctrineBehaviors/pull/146
[#144]: https://github.com/KnpLabs/DoctrineBehaviors/pull/144
[#143]: https://github.com/KnpLabs/DoctrineBehaviors/pull/143
[#142]: https://github.com/KnpLabs/DoctrineBehaviors/pull/142
[#141]: https://github.com/KnpLabs/DoctrineBehaviors/pull/141
[#138]: https://github.com/KnpLabs/DoctrineBehaviors/pull/138
[#136]: https://github.com/KnpLabs/DoctrineBehaviors/pull/136
[#135]: https://github.com/KnpLabs/DoctrineBehaviors/pull/135
[#134]: https://github.com/KnpLabs/DoctrineBehaviors/pull/134
[#133]: https://github.com/KnpLabs/DoctrineBehaviors/pull/133
[#132]: https://github.com/KnpLabs/DoctrineBehaviors/pull/132
[#131]: https://github.com/KnpLabs/DoctrineBehaviors/pull/131
[#126]: https://github.com/KnpLabs/DoctrineBehaviors/pull/126
[#122]: https://github.com/KnpLabs/DoctrineBehaviors/pull/122
[#119]: https://github.com/KnpLabs/DoctrineBehaviors/pull/119
[#115]: https://github.com/KnpLabs/DoctrineBehaviors/pull/115
[#114]: https://github.com/KnpLabs/DoctrineBehaviors/pull/114
[#112]: https://github.com/KnpLabs/DoctrineBehaviors/pull/112
[#111]: https://github.com/KnpLabs/DoctrineBehaviors/pull/111
[#102]: https://github.com/KnpLabs/DoctrineBehaviors/pull/102
[#98]: https://github.com/KnpLabs/DoctrineBehaviors/pull/98
[#93]: https://github.com/KnpLabs/DoctrineBehaviors/pull/93
[#90]: https://github.com/KnpLabs/DoctrineBehaviors/pull/90
[#89]: https://github.com/KnpLabs/DoctrineBehaviors/pull/89
[#88]: https://github.com/KnpLabs/DoctrineBehaviors/pull/88
[#85]: https://github.com/KnpLabs/DoctrineBehaviors/pull/85
[#83]: https://github.com/KnpLabs/DoctrineBehaviors/pull/83
[#78]: https://github.com/KnpLabs/DoctrineBehaviors/pull/78
[#77]: https://github.com/KnpLabs/DoctrineBehaviors/pull/77
[#76]: https://github.com/KnpLabs/DoctrineBehaviors/pull/76
[#68]: https://github.com/KnpLabs/DoctrineBehaviors/pull/68
[#62]: https://github.com/KnpLabs/DoctrineBehaviors/pull/62
[#60]: https://github.com/KnpLabs/DoctrineBehaviors/pull/60
[#57]: https://github.com/KnpLabs/DoctrineBehaviors/pull/57
[#56]: https://github.com/KnpLabs/DoctrineBehaviors/pull/56
[#55]: https://github.com/KnpLabs/DoctrineBehaviors/pull/55
[#52]: https://github.com/KnpLabs/DoctrineBehaviors/pull/52
[#44]: https://github.com/KnpLabs/DoctrineBehaviors/pull/44
[#43]: https://github.com/KnpLabs/DoctrineBehaviors/pull/43
[#41]: https://github.com/KnpLabs/DoctrineBehaviors/pull/41
[#40]: https://github.com/KnpLabs/DoctrineBehaviors/pull/40
[#37]: https://github.com/KnpLabs/DoctrineBehaviors/pull/37
[#36]: https://github.com/KnpLabs/DoctrineBehaviors/pull/36
[#32]: https://github.com/KnpLabs/DoctrineBehaviors/pull/32
[#31]: https://github.com/KnpLabs/DoctrineBehaviors/pull/31
[#30]: https://github.com/KnpLabs/DoctrineBehaviors/pull/30
[#27]: https://github.com/KnpLabs/DoctrineBehaviors/pull/27
[#26]: https://github.com/KnpLabs/DoctrineBehaviors/pull/26
[#25]: https://github.com/KnpLabs/DoctrineBehaviors/pull/25
[#24]: https://github.com/KnpLabs/DoctrineBehaviors/pull/24
[#21]: https://github.com/KnpLabs/DoctrineBehaviors/pull/21
[#20]: https://github.com/KnpLabs/DoctrineBehaviors/pull/20
[#18]: https://github.com/KnpLabs/DoctrineBehaviors/pull/18
[#14]: https://github.com/KnpLabs/DoctrineBehaviors/pull/14
[#13]: https://github.com/KnpLabs/DoctrineBehaviors/pull/13
[#10]: https://github.com/KnpLabs/DoctrineBehaviors/pull/10
[#7]: https://github.com/KnpLabs/DoctrineBehaviors/pull/7
[#6]: https://github.com/KnpLabs/DoctrineBehaviors/pull/6
[#4]: https://github.com/KnpLabs/DoctrineBehaviors/pull/4
[#3]: https://github.com/KnpLabs/DoctrineBehaviors/pull/3
[#2]: https://github.com/KnpLabs/DoctrineBehaviors/pull/2
[#1]: https://github.com/KnpLabs/DoctrineBehaviors/pull/1
[v2.0.0-alpha4]: https://github.com/KnpLabs/DoctrineBehaviors/compare/v2.0.0-alpha3...v2.0.0-alpha4
[v2.0.0-alpha3]: https://github.com/KnpLabs/DoctrineBehaviors/compare/v2.0.0-alpha2...v2.0.0-alpha3
[v2.0.0-alpha2]: https://github.com/KnpLabs/DoctrineBehaviors/compare/v2.0.0-alpha1...v2.0.0-alpha2
[@webda2l]: https://github.com/webda2l
[@trsteel88]: https://github.com/trsteel88
[@tobias-93]: https://github.com/tobias-93
[@theodorDiaconu]: https://github.com/theodorDiaconu
[@tarlepp]: https://github.com/tarlepp
[@siciarek]: https://github.com/siciarek
[@shieldo]: https://github.com/shieldo
[@roukmoute]: https://github.com/roukmoute
[@pmontoya]: https://github.com/pmontoya
[@pminnieur]: https://github.com/pminnieur
[@patxi1980]: https://github.com/patxi1980
[@ossinkine]: https://github.com/ossinkine
[@nykopol]: https://github.com/nykopol
[@michelsalib]: https://github.com/michelsalib
[@meyerbaptiste]: https://github.com/meyerbaptiste
[@martinprihoda]: https://github.com/martinprihoda
[@lopsided]: https://github.com/lopsided
[@lemoinem]: https://github.com/lemoinem
[@l3l0]: https://github.com/l3l0
[@kuczek]: https://github.com/kuczek
[@ksom]: https://github.com/ksom
[@kimlai]: https://github.com/kimlai
[@jsor]: https://github.com/jsor
[@josselinh]: https://github.com/josselinh
[@jordisala1991]: https://github.com/jordisala1991
[@jonasgoderis]: https://github.com/jonasgoderis
[@jerome-fix]: https://github.com/jerome-fix
[@jdachtera]: https://github.com/jdachtera
[@jankramer]: https://github.com/jankramer
[@jalopezcar]: https://github.com/jalopezcar
[@inoryy]: https://github.com/inoryy
[@hanovruslan]: https://github.com/hanovruslan
[@greg0ire]: https://github.com/greg0ire
[@giuliapellegrini]: https://github.com/giuliapellegrini
[@gaydarov]: https://github.com/gaydarov
[@fixe]: https://github.com/fixe
[@eillarra]: https://github.com/eillarra
[@docteurklein]: https://github.com/docteurklein
[@dmishh]: https://github.com/dmishh
[@digitalkaoz]: https://github.com/digitalkaoz
[@corentinheadoo]: https://github.com/corentinheadoo
[@constructor]: https://github.com/constructor
[@cblegare]: https://github.com/cblegare
[@burci]: https://github.com/burci
[@boekkooi]: https://github.com/boekkooi
[@bocharsky-bw]: https://github.com/bocharsky-bw
[@bobvandevijver]: https://github.com/bobvandevijver
[@asprega]: https://github.com/asprega
[@andreybolonin]: https://github.com/andreybolonin
[@alexpozzi]: https://github.com/alexpozzi
[@alch]: https://github.com/alch
[@akovalyov]: https://github.com/akovalyov
[@akia]: https://github.com/akia
[@adrienrusso]: https://github.com/adrienrusso
[@adampiotrowski]: https://github.com/adampiotrowski
[@PedroTroller]: https://github.com/PedroTroller
[@Nyholm]: https://github.com/Nyholm
[@NicolasBadey]: https://github.com/NicolasBadey
[@NiR-]: https://github.com/NiR-
[@Mondane]: https://github.com/Mondane
[@MisatoTremor]: https://github.com/MisatoTremor
[@MAXakaWIZARD]: https://github.com/MAXakaWIZARD
[@Lusitanian]: https://github.com/Lusitanian
[@JoydS]: https://github.com/JoydS
[@EmmanuelVella]: https://github.com/EmmanuelVella
[@Einenlum]: https://github.com/Einenlum
[@DerekRoth]: https://github.com/DerekRoth
[1.6.0]: https://github.com/KnpLabs/DoctrineBehaviors/compare/1.5.0...1.6.0
[1.5.0]: https://github.com/KnpLabs/DoctrineBehaviors/compare/1.4.1...1.5.0
[1.4.1]: https://github.com/KnpLabs/DoctrineBehaviors/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/KnpLabs/DoctrineBehaviors/compare/v2.0.0-alpha4...1.4.0
[#478]: https://github.com/KnpLabs/DoctrineBehaviors/pull/478
[#477]: https://github.com/KnpLabs/DoctrineBehaviors/pull/477
[#475]: https://github.com/KnpLabs/DoctrineBehaviors/pull/475
[#474]: https://github.com/KnpLabs/DoctrineBehaviors/pull/474
[#473]: https://github.com/KnpLabs/DoctrineBehaviors/pull/473
[#472]: https://github.com/KnpLabs/DoctrineBehaviors/pull/472
[#470]: https://github.com/KnpLabs/DoctrineBehaviors/pull/470
[v2.0.0-beta1]: https://github.com/KnpLabs/DoctrineBehaviors/compare/v2.0.0-alpha4...v2.0.0-beta1
[v2.0.0-alpha1]: https://github.com/KnpLabs/DoctrineBehaviors/compare/1.6.0...v2.0.0-alpha1
[@hermann8u]: https://github.com/hermann8u
[@StanislavUngr]: https://github.com/StanislavUngr
