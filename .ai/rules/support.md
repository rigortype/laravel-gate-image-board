---
paths:
  - app/Support/LspSurface.php
---

# Support

## Unknown helper keys stay on LspSurface
env/route/view/config/__ unknown keys and env('') belong only in app/Support/LspSurface.php. Do not scatter fake keys on HTTP paths. Do not call surface() at runtime: route('gate.missing') throws. The env() calls are ignored in phpstan for this file on purpose.
