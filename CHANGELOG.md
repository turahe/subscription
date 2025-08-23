# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.2.0] - 2024-12-19

### Added
- New `HasConfigurablePrimaryKey` concern for flexible primary key configuration
- Enhanced subscription models with improved primary key handling
- Updated database migrations for better structure and relationships

### Changed
- Replaced `HasUlids` with `HasConfigurablePrimaryKey` in all models for better flexibility
- Updated subscription models, migrations, and related components
- Improved model relationships and database schema

### Fixed
- Enhanced PlanSubscriptionUsage model functionality
- Updated CI workflow configurations
- Improved test coverage and model testing

## [1.1.0] - 2024-07-31

### Added
- Configuration option to use timestamps or unit time
- Enhanced subscription management capabilities

## [1.0.5] - 2024-07-31

### Changed
- Set interval as enums for better type safety and consistency

## [1.0.4] - 2024-07-31

### Removed
- Removed ledger functionality

### Added
- Added documentation for models
- Added PHP badge to project

## [1.0.0] - 2024-07-31

### Added
- Initial release of the subscription management system
- Core subscription functionality
- Plan and feature management
- Subscription usage tracking
- Laravel integration and service provider
