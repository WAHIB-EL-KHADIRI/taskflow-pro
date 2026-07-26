# Changelog

All notable changes to TaskFlow Pro are documented here.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/);
this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- MIT `LICENSE` file — the README claimed MIT but no license file existed, which
  legally left the project all-rights-reserved.
- CI workflow (`.github/workflows/ci.yml`) running `composer check`
  (PSR-12 lint + PHPStan + PHPUnit) on every push and pull request.
- `CONTRIBUTING.md`, `SECURITY.md`, and `CODE_OF_CONDUCT.md`.

### Changed

- Repository renamed from `Gestionnaire-de-T-ches-en-PHP` to `taskflow-pro`
  (the old name had a mangled character and was not URL-friendly).
- Default branch renamed from `master` to `main`.
- Stripped UTF-8 BOM and trailing whitespace from source files (PSR-1/PSR-12
  violations that blocked the lint step).
