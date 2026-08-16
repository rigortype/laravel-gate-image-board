# Gate Imageboard

A team-scoped imageboard: members post images and replies on boards they belong to.

## Language

**Board**:
A named topic area on a team. Identified by a slug unique within that team.
_Avoid_: Forum, channel, category

**Thread**:
A conversation on a board, started by an opening post and ordered by last bump.
_Avoid_: Topic, topic thread, discussion

**Post**:
A message in a thread. The first post is the opening post; later posts are replies.
_Avoid_: Comment, reply (as the noun for the record), message

**Image**:
A single uploaded picture attached to a post.
_Avoid_: Attachment, file, media

**Opening post**:
The first post in a thread. It starts the thread and must include an image.
_Avoid_: OP (in code), original post (as a type name)

**Reply**:
A later post in a thread. Body or image is enough; both are allowed.
_Avoid_: Comment, follow-up

**Bump**:
Updating a thread's last-activity time when a reply is added, so it rises on the board.
_Avoid_: Bump order as a separate entity

**Visibility**:
Who may find and read a thread: public, team only, unlisted, or private.
_Avoid_: Privacy, permission, publication status

**Public**:
A thread anyone can find on the board and read, including guests.
_Avoid_: 一般公開 as a code name

**Team only**:
A thread only members of the board's team can find and read.
_Avoid_: チーム限定公開 as a code name

**Unlisted**:
A thread omitted from catalogs. Anyone with the share link can read it.
_Avoid_: URL限定公開 as a code name, secret

**Private**:
A thread only the author (and team moderators) can find and read.
_Avoid_: 非公開 as a code name

**Share token**:
The unguessable identifier in an unlisted thread's share URL.
_Avoid_: Password, slug, secret key

**Explore**:
The guest-readable catalog of public threads at `/explore`, served by Folio. Copy lives in `lang/en/boards.php` as dotted `__()` keys.
_Avoid_: Discover, browse, public index
