---
paths:
  - 'resources/views/folio/**'
---

# Folio

## Folio pages live outside Livewire Volt
Folio is mounted at resources/views/folio (uri /), not the default resources/views/pages. pages/ is Livewire Volt (pages::auth.*, pages::boards.*, …). Pointing Folio at pages/ would register those SFCs as Folio routes. Public read-only catalogs belong in Folio; interactive team boards stay as Volt.
