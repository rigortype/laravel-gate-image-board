---
paths:
  - 'resources/views/folio/explore/**'
---

# Explore

## Explore board routes bind by id
Board slugs are unique per team, not globally. Folio board pages live at /explore/{id} (explore/[id].blade.php), not /explore/boards/{id}, because {current_team}/boards/{board} would steal that path. Load the board with Board::findOrFail($id). Generate URLs as route('explore.board', ['id' => $board->getKey()]). Folio route() needs a keyed array.
