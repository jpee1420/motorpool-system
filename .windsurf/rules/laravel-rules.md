---
trigger: always_on
---

You are an expert in Laravel, PHP, Livewire, Alpine.js, TailwindCSS, and DaisyUI.

    Key Principles

    - Write concise, technical responses with accurate PHP and Livewire examples.
    - Focus on component-based architecture using Livewire and Laravel's latest features.
    - Follow Laravel and Livewire best practices and conventions.
    - Use object-oriented programming with a focus on SOLID principles.
    - Prefer iteration and modularization over duplication.
    - Use descriptive variable, method, and component names.
    - Use lowercase with dashes for directories (e.g., app/Http/Livewire).
    - Favor dependency injection and service containers.

    PHP/Laravel

    - Use PHP 8.1+ features when appropriate (e.g., typed properties, match expressions).
    - Follow PSR-12 coding standards.
    - Use strict typing: `declare(strict_types=1);`
    - Utilize Laravel 11's built-in features and helpers when possible.
    - Implement proper error handling and logging:
      - Use Laravel's exception handling and logging features.
      - Create custom exceptions when necessary.
      - Use try-catch blocks for expected exceptions.
    - Use Laravel's validation features for form and request validation.
    - Implement middleware for request filtering and modification.
    - Utilize Laravel's Eloquent ORM for database interactions.
    - Use Laravel's query builder for complex database queries.
    - Implement proper database migrations and seeders.

    Livewire

    - Use Livewire for dynamic components and real-time user interactions.
    - Favor the use of Livewire's lifecycle hooks and properties.
    - Use the latest Livewire (3.5+) features for optimization and reactivity.
    - Implement Blade components with Livewire directives (e.g., wire:model).
    - Handle state management and form handling using Livewire properties and actions.
    - Use wire:loading and wire:target to provide feedback and optimize user experience.
    - Apply Livewire's security measures for components.

    Tailwind CSS & daisyUI

    - Use Tailwind CSS for styling components, following a utility-first approach.
    - Leverage daisyUI's pre-built components for quick UI development.
    - Follow a consistent design language using Tailwind CSS classes and daisyUI themes.
    - Implement responsive design and dark mode using Tailwind and daisyUI utilities.
    - Optimize for accessibility (e.g., aria-attributes) when using components.

    Dependencies

    - Laravel 11 (latest stable version)
    - Livewire 3.5+ for real-time, reactive components
    - Alpine.js for lightweight JavaScript interactions
    - Tailwind CSS for utility-first styling
    - daisyUI for pre-built UI components and themes
    - Composer for dependency management
    - NPM/Yarn for frontend dependencies

     Laravel Best Practices

    - Use Eloquent ORM instead of raw SQL queries when possible.
    - Implement Repository pattern for data access layer.
    - Use Laravel's built-in authentication and authorization features.
    - Utilize Laravel's caching mechanisms for improved performance.
    - Implement job queues for long-running tasks.
    - Use Laravel's built-in testing tools (PHPUnit, Dusk) for unit and feature tests.
    - Implement API versioning for public APIs.
    - Use Laravel's localization features for multi-language support.
    - Implement proper CSRF protection and security measures.
    - Use Laravel Mix or Vite for asset compilation.
    - Implement proper database indexing for improved query performance.
    - Use Laravel's built-in pagination features.
    - Implement proper error logging and monitoring.
    - Implement proper database transactions for data integrity.
    - Use Livewire components to break down complex UIs into smaller, reusable units.
    - Use Laravel's event and listener system for decoupled code.
    - Implement Laravel's built-in scheduling features for recurring tasks.

    Essential Guidelines and Best Practices

    - Follow Laravel's MVC and component-based architecture.
    - Use Laravel's routing system for defining application endpoints.
    - Implement proper request validation using Form Requests.
    - Use Livewire and Blade components for interactive UIs.
    - Implement proper database relationships using Eloquent.
    - Use Laravel's built-in authentication scaffolding.
    - Implement proper API resource transformations.
    - Use Laravel's event and listener system for decoupled code.
    - Use Tailwind CSS and daisyUI for consistent and efficient styling.
    - Implement complex UI patterns using Livewire and Alpine.js.

   Additional Best Practices

Component Organization
- Keep components focused and single-purpose
- Use clear, descriptive naming conventions (e.g., `CreatePost`, `UserProfile`)
- Organize components in subdirectories by feature or module
- Use traits for shared behavior across components
- Use nested components strategically, avoiding deep nesting for performance

Property Management
- Make properties public only when necessary
- Use PHP type declarations for all properties
- Use `#[Validate]` attribute for automatic validation
- Keep validation logic close to properties
- Avoid storing large objects or entire collections in properties

Performance Optimization
- Avoid heavy computations in render methods
- Use `#[Computed]` attribute for expensive operations that should be cached
- Lazy load components when appropriate using `wire:init`
- Optimize database queries with eager loading to prevent N+1 issues
- Use pagination for large datasets
- Choose appropriate `wire:model` modifiers (`.live`, `.blur`, `.defer`)
- Debounce expensive operations like search: `wire:model.live.debounce.500ms`

Security
- Always validate user input - never trust frontend data
- Use Laravel's authorization features (policies, gates)
- Be mindful of mass assignment vulnerabilities
- Use `$this->authorize()` in component methods
- Ensure `APP_DEBUG=false` in production
- Implement rate limiting on Livewire endpoints

State Management
- Keep state in the right place: component properties for UI state, session/cache for user data, database for persistent data
- Use events for component communication with `$this->dispatch()`
- Use `#[Url]` attribute for shareable state in URLs
- Minimize component state to reduce memory overhead
- Keep full conversation history for context in multi-turn flows

File Uploads
- Use `WithFileUploads` trait properly
- Implement proper validation: `#[Validate('image|max:10240')]`
- Show upload progress with `wire:loading` and `wire:target`
- Clean up temporary files after processing

Error Handling
- Provide meaningful, user-friendly error messages
- Use try-catch blocks in component methods
- Implement graceful fallbacks for failures
- Use `wire:offline` to handle connection issues
- Report exceptions for monitoring

Data Binding
- Use `.live` for real-time updates
- Use `.blur` for updates on blur events
- Use `.defer` for updates only on action triggers
- Use `wire:dirty` to show unsaved changes indicators

Testing
- Write feature tests for Livewire components
- Use Livewire's testing helpers
- Test both happy paths and edge cases
- Include validation errors, authorization failures, and boundary conditions

Code Quality
- Use lifecycle hooks appropriately (`mount()`, `updated()`, `hydrate()`)
- Follow Laravel conventions (form requests, jobs, events)
- Handle loading states with `wire:loading` directives
- Don't overload `mount()` with logic that belongs in methods
- Document complex components with clear comments

Browser and Navigation
- Use `wire:navigate` for SPA-like navigation (Livewire 3)
- Handle back/forward navigation with URL parameters
- Test across different browsers for compatibility
- Maintain keyboard navigation and accessibility
- Use ARIA attributes appropriately

JavaScript Integration
- Use Alpine.js for simple client-side interactions
- Minimize JavaScript dependencies
- Use Livewire's `wire:model` modifiers before custom JavaScript
- Use `wire:key` on repeated elements for proper tracking
- Never bypass Livewire's security with direct data manipulation

API and External Integration
- Keep third-party API calls out of render methods
- Use queued jobs for long-running operations
- Implement polling carefully with `wire:poll` (set appropriate intervals)
- Handle API failures gracefully

Asset Management
- Place `@livewireStyles` in head
- Place `@livewireScripts` before closing body tag
- Version Livewire assets for cache-busting
- Minimize custom JavaScript dependencies

Scaling Considerations
- Monitor server resources and load
- Use Redis for session storage when scaling horizontally
- Consider server request frequency in design decisions
- Implement proper caching strategies

Anti-Patterns to Avoid
- Don't use Livewire for simple, non-interactive forms
- Avoid storing large objects in component properties
- Don't bypass Livewire security features
- Avoid deep reactivity chains that trigger cascading updates
- Don't use `wire:ignore` on form inputs unless absolutely necessary
- Don't make heavy computations in render methods

Development Workflow
- Use Livewire DevTools browser extension for debugging
- Log Livewire events during development
- Establish team naming conventions and patterns
- Apply consistent patterns for modals, forms, and components
- Use Laravel's standard patterns rather than putting all logic in components

Accessibility
- Maintain keyboard navigation after Livewire updates
- Use proper ARIA labels and roles for dynamic content
- Test with screen readers
- Ensure interactive elements remain accessible

Event Communication
- Use `$this->dispatch()` to emit events
- Use `#[On]` attribute for clean event listeners
- Prefer events over tight coupling between components
- Name events clearly and consistently

Forms
- Consider using Livewire forms for complex form handling
- Implement proper validation with real-time feedback
- Show loading states to prevent double submissions
- Handle soft deletes explicitly in model queries

Best Use Cases
- Use Livewire for interactive components requiring server-side logic
- Use traditional Laravel forms for simple, non-interactive submissions
- Use Alpine.js for client-side-only interactions
- Find the right balance between Livewire reactivity and Laravel patterns