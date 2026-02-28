# alma – Development Execution Plan

**Context:** Bedrock + Sage + Acorn + Livewire 4 already installed
**Goal:** Transform theme into a Laravel-like, FSE-compatible, block-first WordPress starter kit

---

# GLOBAL RULES (Agent Must Enforce)

0. Always use Roots guidelines (available in .ai/guidelines.md) and the substrate MCP. 
1. Do not place business logic inside Blade files.
2. Do not call `wp_*` functions inside views.
3. All domain logic must live in `app/`.
4. All reusable UI must be implemented as WordPress blocks.
5. Dynamic blocks must mount Livewire components.
6. FSE compatibility is mandatory.
7. Controllers must delegate logic to Services.
8. Block registration must be auto-discovered.
9. No duplicate asset loading.
10. Code must be clean, modular, and testable.

# AGENT SKILLS

1. fluxui-development (available in alma/.agents/skills/fluxui-development)
2. livewire-development (available in alma/.agents/skills/livewire-development)

---

# PHASE 1 – Convert Theme to Full Site Editing (FSE)

### 1.1 Create Required FSE Files

Ensure existence of:

```
theme.json
templates/
parts/
```

If missing, generate them.

---

### 1.2 Configure `theme.json`

* Define color palette aligned with Flux UI tokens.
* Define typography scale.
* Define spacing scale.
* Disable core color presets.
* Enable appearance tools.
* Ensure layout content width and wide width are defined.

Do not hardcode Tailwind classes inside theme.json.

---

### 1.3 Create Core Templates

Create:

```
templates/index.html
templates/front-page.html
templates/page.html
templates/single.html
templates/archive.html
```

Each template must:

* Use block markup
* Include header and footer template parts
* Avoid inline styling

---

### 1.4 Create Template Parts

Create:

```
parts/header.html
parts/footer.html
parts/navigation.html
```

Use core blocks where possible.
Allow alma custom blocks inside templates.

---

# PHASE 2 – Establish Block Architecture

### 2.1 Create Block Base Interface

Create:

```
app/Blocks/Contracts/Block.php
```

Define required methods:

* name(): string
* title(): string
* description(): string
* render(array $attributes): string
* supports(): array
* authorize(): bool

---

### 2.2 Create Block Base Abstract Class

Create:

```
app/Blocks/BaseBlock.php
```

Responsibilities:

* Provide default supports
* Provide default authorize() = true
* Register block type via WordPress API
* Define render_callback

---

### 2.3 Implement Auto-Discovery

Inside `AlmaServiceProvider`:

* Scan `app/Blocks`
* Instantiate each block class
* Call register() method
* Ensure no manual block registration required

---

# PHASE 3 – Implement Core Blocks

Create the following blocks:

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

---

# PHASE 4 – Livewire Integration Layer

### 4.1 Define Livewire Component Structure

Create:

```
resources/livewire/auth/
resources/livewire/dashboard/
resources/livewire/profile/
resources/livewire/security/
```

All interactive UI must live here.

---

### 4.2 Mount Livewire Inside Dynamic Blocks

In block render view:

```
resources/views/blocks/{block-name}.blade.php
```

Mount:

```
<livewire:component-name :attributes="$attributes" />
```

Ensure:

* Works without breaking block editor
* Does not load Livewire scripts inside admin editor

---

### 4.3 Configure Livewire Asset Loading

Agent must:

* Enqueue Livewire only on frontend
* Ensure scripts load once
* Prevent duplication in block preview

---

# PHASE 5 – Routing & Middleware Layer

### 5.1 Define Routes

Inside `routes/web.php`, define:

* /
* /login
* /register
* /dashboard
* /settings
* /two-factor-challenge

Routes must use controllers.

No closure routes.

---

### 5.2 Create Controllers

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

### 5.3 Implement Middleware

Create:

```
Authenticate
RedirectIfAuthenticated
EnsureTwoFactorVerified
```

Register aliases in service provider.

Apply middleware to protected routes.

---

# PHASE 6 – Authentication Service Layer

### 6.1 Create AuthService

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

Internally may use WordPress APIs.

Controllers must never directly call wp_*.

---

# PHASE 7 – Two-Factor Authentication

### 7.1 Install and Use TOTP Library

Use `pragmarx/google2fa`.

---

### 7.2 Create TwoFactorService

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

Store secret encrypted in user meta.

---

### 7.3 Enforce 2FA Flow

After successful login:

* If 2FA enabled
* Store pending session
* Redirect to challenge route
* Validate TOTP
* Complete login

Middleware must block dashboard until verified.

---

# PHASE 8 – Dashboard System

### 8.1 Make Dashboard Block-Based

Create FSE template:

```
templates/page-dashboard.html
```

Insert alma dashboard blocks.

---

### 8.2 Implement Widget System

Create:

```
app/Dashboard/Contracts/Widget.php
```

Allow blocks to register dashboard widgets.

Render widgets dynamically inside DashboardStatsBlock.

---

# PHASE 9 – Role & Capability Layer

### 9.1 Create RoleService

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

### 9.2 Theme Activation Hook

On `after_switch_theme`:

* Create default roles
* Create Home page
* Create Dashboard page
* Assign page templates
* Set front page
* Set permalinks

No manual admin configuration required.

---

# PHASE 10 – Editor Experience

### 10.1 Create Custom Block Category

Register category:

```
alma
```

All custom blocks must belong to it.

---

### 10.2 Editor Styling

Ensure:

* Editor loads theme styles
* Flux UI renders correctly
* Tailwind classes preserved in build

---

# PHASE 11 – Quality & Safety

Agent must verify:

* No wp_* in views
* No business logic in block callbacks
* Livewire scripts load once
* Unauthorized users cannot access protected routes
* Blocks hide if authorize() returns false
* No duplicate registrations
* No global state leakage

---

# FINAL STATE

After completion:

* Theme fully FSE compatible
* Blocks reusable across editor
* Dynamic UI powered by Livewire
* Authentication abstracted
* 2FA enforced
* Dashboard modular
* WordPress reduced to infrastructure

This is no longer “just a theme.”
It is a structured application that happens to run on WordPress.

If the agent deviates from these instructions, it’s wrong.
