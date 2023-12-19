# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.4.0] - 2023-12-19

### Added

- Tablestore instance management API (#25).
- **[INTERNAL]** Static analysis workflow on Github Actions. (#26).
- **[INTERNAL]** Initialize Git attributes file. (#26).

## [1.3.0] - 2023-12-14

### Added

- Delete table API (#17).
- List table API (#18).
- Get table information API (#19).
- API to update an existing table (#20).
- Table existence check API (#22).
- **[INTERNAL]** Integration test on Github Actions (#23).

### Changed

- Build throughput reservations and table options on the fly (#21).

## [1.2.0] - 2023-12-13

### Added

- Create table (#15).

## [1.1.0] - 2023-12-08

### Added

- Supports STS token (#12).
- Configure request options (#13).

## [1.0.0] - 2023-12-04

### Added

- Basic CRUD against the table.
- Column selection from the result by column names or column range.
- Where API to filter or apply condition update.
- Filter columns by data version.

[unreleased]: https://github.com/dew-serverless/tablestore-php/compare/v1.4.0...HEAD
[1.4.0]: https://github.com/dew-serverless/tablestore-php/compare/v1.3.0...v1.4.0
[1.3.0]: https://github.com/dew-serverless/tablestore-php/compare/v1.2.0...v1.3.0
[1.2.0]: https://github.com/dew-serverless/tablestore-php/compare/v1.1.0...v1.2.0
[1.1.0]: https://github.com/dew-serverless/tablestore-php/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/dew-serverless/tablestore-php/releases/tag/v1.0.0
