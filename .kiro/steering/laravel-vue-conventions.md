---
name: laravel-vue-conventions
description: Apply full-stack development conventions for Laravel (PHP) backends and Vue 3 / Nuxt 3 / Inertia frontends. Use this skill whenever building, scaffolding, or extending a feature in any Laravel, Vue, or Nuxt project — new endpoints, models, services, components, stores, authentication flows, real-time features, or background jobs. Trigger it even when the user only says things like "add a feature", "create a CRUD", "build an API", "set up auth", or "add a component", as long as the project uses this stack. The goal is consistent, production-ready code that matches the established architecture without re-explaining preferences each time.
---

# Laravel + Vue/Nuxt Conventions

This skill captures a consistent set of full-stack conventions so that new features come out matching the established architecture on the first try. The aim is **complete, production-ready code** — not partial snippets or "you could do X" examples.

## Core principles (always apply)

1. **Production-ready, not illustrative.** Deliver code that compiles, passes static analysis, and could ship. Include imports, type hints, error handling, and validation. No `// TODO` placeholders unless the user asks to stub something.

2. **Strict typing everywhere.** PHP files start with `declare(strict_types=1);`. Type every parameter, property, and return. In Vue, prefer typed props and typed composable/store returns. Avoid implicit `mixed`.

3. **Layered architecture.** Controllers stay thin. Business logic lives in single-purpose **Actions** or **Services** behind **Contracts** (interfaces). HTTP shaping happens in **Resources**. Authorization lives in **Policies**.

4. **Document as you go.** Add concise PHPDoc where it adds information beyond the type signature (array shapes, thrown exceptions, generics). API endpoints get Scribe annotations.

5. **Static analysis must pass.** Write code that satisfies PHPStan/Larastan at the project's configured level. Avoid patterns that require baseline suppression.

6. **Convention over surprise.** Match existing naming, folder layout, and patterns already in the project. When unsure, inspect a sibling file before inventing a new approach.

7. **Communicate in French, code in English.** Explanations and discussion in French; identifiers, in-code comments, commit messages, and doc strings in English.

## Backend conventions (Laravel)

- **IDs**: models use **ULID** (`HasUlids`), not auto-increment, unless the project says otherwise.
- **Structure**: `app/Actions`, `app/Services`, `app/Contracts`, `app/Http/Resources`, `app/Http/Requests`, `app/Policies`. Bind contracts to implementations in a service provider.
- **Validation**: always via Form Request classes, never inline in controllers.
- **API output**: always return `JsonResource` / `ResourceCollection` — never raw Eloquent models (avoids payload bloat and circular references). Load only the relations the endpoint needs.
- **Auth**: Sanctum for tokens, with an OTP login flow where required.
- **Concurrency**: use `lockForUpdate()` inside transactions for state changes that can race (orders, wallets, stock).
- **Background work**: long or sequential side-effects (notifications, external calls) go through queued Jobs.
- **Docs**: annotate endpoints for Scribe.

### Default order for a new backend feature
migration/model → Action/Service (+ Contract) → Form Request → thin Controller → Resource → route → Policy → Scribe annotations → tests (if the project has them).

## Frontend conventions (Vue 3 / Nuxt 3 / Inertia)

- **Vue 3 Composition API** with `<script setup>`. Extract reusable logic into composables.
- **State**: Pinia stores, one concern per store; typed state, getters, and actions.
- **Types first**: define TS interfaces/types for API payloads before wiring the UI.
- **Inertia**: use Inertia pages/forms where the project is Inertia-based; otherwise consume the API via a typed client.
- **Feedback**: consistent loading/error states; user feedback via the project's existing toast/alert mechanism (e.g. SweetAlert2).

## Integrations

- **Real-time**: Laravel Reverb or Pusher for WebSockets; broadcast typed events.
- **Push notifications**: OneSignal; keep payloads small (under the ~10KB transport limit) by sending IDs/keys, not full objects.
- **Media/files**: Spatie MediaLibrary for uploads and conversions.
- **Permissions/roles**: Spatie laravel-permission.

## What to avoid

- Fat controllers containing business logic.
- Returning raw models instead of Resources.
- Auto-increment IDs where the standard is ULID.
- Untyped code that breaks static analysis.
- Partial answers — finish the implementation.
