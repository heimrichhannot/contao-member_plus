# Changelog
All notable changes to this project will be documented in this file.

## [2.1.4] - 2018-03-05

### Changed
- renamed `tl_member` operation `content` to `editContent` for css purposes

## [2.1.3] - 2018-01-16

### Added
- MemberAddressModel

## [2.1.2] - 20017-12-19

### Fixed
- `MemberRegistrationPlusForm` did not provide `$arrDca` within `parent::setDefaults()` callback 

## [2.1.1] - 20017-10-24

### Fixed
- contao 4 compatibility (`tl_member.title` -> `tl_member.imageTitle`)

## [2.1.0] - 20017-10-24

### Fixed
- contao 4 compatibility (`tl_member.title` -> `tl_member.imageTitle`)

### Changed
- dropped orphan dependency `heimrichhannot/contao-bootstrapper`
- dropped requirement for `heimrichhannot/contao-formhybrid` and make usage optional (added composer `suggest` note)

### Added
- field `nobilityTitle` to `tl_member`
- field `academicDegree` to `tl_member`
- field `jobTitles` to `tl_member`
- field `facebookProfile` to `tl_member`
- field `twitterProfile` to `tl_member`
- field `googlePlusProfile` to `tl_member`
- field `foreignLanguages` to `tl_member`
- field `additionalAddresses` to `tl_member` and provide multiple addresses within `tl_member_address` table

## [2.0.36] - 20017-08-28

### Fixed

- #3 (Cannot declare class tl_content_member_plus, because the name is already in use)

### Changed

- moved inputType `password_noConfirm` from bootstrapper to member_plus

## [2.0.35] - 20017-08-28

### Changed

- reformated
- match modifyDC to parent

## [2.0.34] - 20017-08-08

### Added

- login_registration_plus -> login and registration in one form with formhybrid

## [2.0.33] - 2017-08-02

### Added

- tl_member -> xingProfile and linkedinProfile

## [2.0.32] - 2017-08-01

### Added

- MemberPlusMemberModel::getContent() and MemberPlusMemberModel::getParsedContent()

## [2.0.31] - 2017-06-12

### Fixed

- contao 4 bug in tl_content

## [2.0.30] - 2017-04-12

### Added

- new tag

## [2.0.29] - 2017-04-06

### Changed
- added php7 support. fixed contao-core dependency

## [2.0.28] - 2017-03-13

### Added
- street2 field

## [2.0.27] - 2016-02-09

### Fixed
- Contao 3.5.0 support, where no \Contao\StringUtil class did exist

## [2.0.26] - 2016-02-09

### Fixed
- removed faulty integration of protected_homedirs

## [2.0.25] - 2016-02-08

### Added
- basic protected_homedirs support
