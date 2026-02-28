First: build a **solid Laravel-like starter kit on top of Bedrock + Sage + Acorn + Livewire**.
Then, when everything is stable and modular, we layer FSE compatibility on top as a final integration phase.

---

# alma – Development Execution Plan (Reordered Architecture)

**Context:** Bedrock + Sage + Acorn + Livewire 4 already installed
**Goal:** Build a Laravel-like, block-first WordPress starter kit.
**FSE compatibility will be implemented at the end.**

---

# GLOBAL RULES (Agent Must Enforce)

0. Always use Roots guidelines (available in .ai/guidelines.md) and the substrate MCP.
1. Do not place business logic inside Blade files.
2. Do not call `wp_*` functions inside views.
3. All domain logic must live in `app/`.
4. All reusable UI must be implemented as WordPress blocks.
5. Dynamic blocks must mount Livewire components.
6. Controllers must delegate logic to Services.
7. Block registration must be auto-discovered.
8. No duplicate asset loading.
9. Code must be clean, modular, and testable.
10. FSE compatibility will be implemented in the final phase only.

---

# AGENT SKILLS

1. fluxui-development (alma/.agents/skills/fluxui-development)
2. livewire-development (alma/.agents/skills/livewire-development)

---

# PHASE 1 – Establish Block Architecture

We begin with architecture, not editor rendering.

---

### 1.1 Create Block Base Interface

Create:

```
app/Blocks/Contracts/Block.php
```

Define:

* name(): string
* title(): string
* description(): string
* render(array $attributes): string
* supports(): array
* authorize(): bool

---

### 1.2 Create Block Base Abstract Class

Create:

```
app/Blocks/BaseBlock.php
```

Responsibilities:

* Provide default supports
* Provide default authorize() = true
* Register block type via WordPress API
* Define render_callback
* Map block.json automatically

---

### 1.3 Implement Auto-Discovery

Inside `AlmaServiceProvider`:

* Scan `app/Blocks`
* Instantiate each block class
* Call register()
* Ensure zero manual block registration

---

# PHASE 2 – Implement Core Blocks

Create:

```
HeroBlock
LoginFormBlock
RegisterFormBlock
DashboardStatsBlock
ProfileSummaryBlock
SecurityPanelBlock
```

Each block must:

* Have block.json
* Support editor preview
* Define supports configuration
* Use render callback
* Mount Livewire component when dynamic

Do not depend on FSE yet.

Blocks must render correctly inside standard templates.

---

# PHASE 3 – Livewire Integration Layer

---

### 3.1 Define Livewire Component Structure

Create:

```
resources/livewire/auth/
resources/livewire/dashboard/
resources/livewire/profile/
resources/livewire/security/
```

All interactive UI lives here.

---

### 3.2 Mount Livewire Inside Dynamic Blocks

In:

```
resources/views/blocks/{block-name}.blade.php
```

Mount:

```
<livewire:component-name :attributes="$attributes" />
```

Ensure:

* Works in frontend
* Does not break block editor
* Does not load Livewire scripts in admin

---

### 3.3 Configure Livewire Asset Loading

Agent must:

* Enqueue Livewire only on frontend
* Ensure scripts load once
* Prevent duplication in previews

---

# PHASE 4 – Routing & Middleware Layer

---

### 4.1 Define Routes

Inside `routes/web.php`:

* /
* /login
* /register
* /dashboard
* /settings
* /two-factor-challenge

No closure routes.

---

### 4.2 Create Controllers

Create:

```
app/Http/Controllers/
  HomeController
  Auth/LoginController
  Auth/RegisterController
  DashboardController
  SettingsController
  TwoFactorController
```

Controllers must:

* Validate input
* Call Services
* Return Blade views or redirects

---

### 4.3 Implement Middleware

Create:

```
Authenticate
RedirectIfAuthenticated
EnsureTwoFactorVerified
```

Register aliases.

Apply to protected routes.

---

# PHASE 5 – Authentication Service Layer

---

### 5.1 Create AuthService

Location:

```
app/Services/AuthService.php
```

Implement:

* login(array $credentials)
* register(array $data)
* logout()
* user()
* check()

Controllers must never call wp_* directly.

---

# PHASE 6 – Two-Factor Authentication

---

### 6.1 Install TOTP Library

Use:

```
pragmarx/google2fa
```

---

### 6.2 Create TwoFactorService

Location:

```
app/Services/TwoFactorService.php
```

Implement:

* generateSecret()
* getQrCode()
* verify()
* enable()
* disable()

Encrypt secret in user meta.

---

### 6.3 Enforce 2FA Flow

After login:

* If 2FA enabled
* Store pending session
* Redirect to challenge
* Validate TOTP
* Complete login

Middleware blocks dashboard until verified.

---

# PHASE 7 – Dashboard System

---

### 7.1 Make Dashboard Block-Based

Create standard Blade template for dashboard first.

Do NOT depend on FSE yet.

Blocks must render inside the Blade layout.

---

### 7.2 Implement Widget System

Create:

```
app/Dashboard/Contracts/Widget.php
```

Allow blocks to register widgets.

Render widgets dynamically inside DashboardStatsBlock.

---

# PHASE 8 – Role & Capability Layer

---

### 8.1 Create RoleService

Location:

```
app/Services/RoleService.php
```

Implement:

* createRole()
* assignRole()
* hasRole()
* hasPermission()

---

### 8.2 Theme Activation Hook

On `after_switch_theme`:

* Create default roles
* Create Home page
* Create Dashboard page
* Assign templates
* Set front page
* Set permalinks

No manual admin setup required.

---

# PHASE 9 – Editor Experience (Non-FSE)

---

### 9.1 Create Custom Block Category

Register:

```
alma
```

All custom blocks must belong to it.

---

### 9.2 Editor Styling

Ensure:

* Editor loads theme styles
* Flux UI renders correctly
* Tailwind preserved in build

Do not implement FSE templates yet.

---

# PHASE 10 – Convert Theme to Full Site Editing (FINAL INTEGRATION)

Only after all systems work correctly.

---

### 10.1 Create Required FSE Structure

Ensure existence of:

```
theme.json
templates/
parts/
```

Generate if missing.

---

### 10.2 Configure theme.json

* Define color palette aligned with Flux tokens
* Define typography scale
* Define spacing scale
* Disable core presets
* Enable appearance tools
* Define layout content width + wide width

No Tailwind classes inside theme.json.

---

### 10.3 Create Core Templates

Create:

```
templates/index.html
templates/front-page.html
templates/page.html
templates/single.html
templates/archive.html
templates/page-dashboard.html
```

Each must:

* Use block markup
* Include header + footer parts
* Avoid inline styles
* Allow alma blocks

---

### 10.4 Create Template Parts

Create:

```
parts/header.html
parts/footer.html
parts/navigation.html
```

Use core blocks when possible.
Allow alma custom blocks.

---

# PHASE 11 – Quality & Safety

Agent must verify:

* No wp_* in views
* No business logic in block callbacks
* Livewire scripts load once
* Middleware properly blocks unauthorized users
* Blocks respect authorize()
* No duplicate block registration
* No asset duplication
* FSE templates correctly render alma blocks

---

# FINAL STATE

After completion:

* Modular Laravel-like architecture
* Block-first UI
* Livewire-powered dynamic behavior
* Auth + 2FA abstracted
* Dashboard modular
* Roles and permissions isolated
* FSE compatibility layered cleanly at the end
* WordPress reduced to infrastructure

Now it’s not chaos pretending to be structure.
It’s structure pretending to be WordPress.

And that’s exactly how it should be.
