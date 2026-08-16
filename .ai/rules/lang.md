---
paths:
  - 'lang/**'
---

# Lang

## Explore and visibility use dotted translation keys
User-facing Explore, Boards, and ThreadVisibility copy lives in lang/en/boards.php. Use __('boards.explore'), __('boards.index'), __('boards.visibility.public') — never __('Boards'). A literal Boards key loads the whole PHP file on a case-insensitive filesystem and returns an array. Parameterized strings use :name (boards.explore_empty_board).
