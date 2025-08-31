# HeartfeltDagger

A DaggerHeart TTRPG companion website that allows for character creation and more features coming in the future.

## Tech Stack

- **Laravel 12** - PHP framework
- **TailwindCSS** - Styling
- **Livewire** - Dynamic frontend components
- **AlpineJS** - Client-side JavaScript interactions
- **Spatie Laravel Data** - DTOs and data objects

## Development Setup

This project uses Laravel Sail for local development:

```bash
# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Generate application key
./vendor/bin/sail artisan key:generate

# Start the development environment
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Build assets
npm run dev

# Playwright browsers are automatically set up on first container start
```

## Browser Testing Setup

sail root-shell
  npx playwright install-deps
sail npx playwright install

## Contributing

We welcome contributions! Please follow these guidelines:

### Architecture & Code Standards

- **Domain-Driven Design**: All business logic goes in `domain/` folder, never in `app/Domain`
- **PSR-12 Compliance**: Strict adherence to PSR-12 coding standards
- **snake_case** for variables, **camelCase** for functions/methods
- **No `@php` directives** in Blade templates

### Code Organization

- **Models**: Located in `domain/*/Models` (never in `app` namespace)
- **Actions**: Business logic in `domain/*/Actions` with single `execute()` method
- **Repositories**: Data retrieval only, returning DTOs (no data modification)
- **Data Objects**: DTOs in `domain/*/Data` using Spatie Laravel Data
- **Livewire Components**: In `app/Livewire/` for UI components

### Development Patterns

- Use `./vendor/bin/sail` for all Artisan commands
- Actions executed as: `(new Action())->execute(...)`
- Create DTOs with `DataClass::from(...)`
- Use Laravel collections, not data collections
- Prefer client-side JavaScript (AlpineJS) for simple interactions

### Testing Requirements

- **All new code must include tests**
- Use `#[Test]` attributes (not `@test`)
- **No mocks** except for external APIs
- Use **Playwright via Pest** for browser testing
- Use factories for test data setup
- No skipped tests allowed
- Run tests with `./vendor/bin/sail pest`

### Before Contributing

1. **Check existing code** before creating new files/classes
2. **Search the codebase** for similar functionality
3. **Ask for clarification** if unsure about implementation
4. **Follow existing patterns** and naming conventions

### Pull Request Guidelines

- Ensure all tests pass
- Follow PSR-12 coding standards
- Include tests for new functionality
- Keep commits focused and well-described
- Reference any related issues

## Credits

Thanks to https://github.com/NandayDev/DaggerheartJsonSRD for the JSON files.

## Legal Disclaimer

This repository includes materials from the Daggerheart System Reference Document © Critical Role, LLC. under the terms of the Darrington Press Community Gaming (DPCGL) License. More information can be found at https://www.daggerheart.com/. There are minor modifications to format and structure.

Daggerheart and all related marks are trademarks of Critical Role, LLC and used with permission. This project is not affiliated with, endorsed, or sponsored by Critical Role or Darrington Press.

For full license terms, see: https://www.daggerheart.com/