# Database Rules

## Database portability (MySQL, PostgreSQL, SQLite)

Migrations, indexes and queries must work the same on MySQL 8, PostgreSQL and SQLite. Locally, tests run on SQLite. CI (`.github/workflows/tests.yml`) runs the migrations (up and down) and the full suite against SQLite, MySQL 8.4 and PostgreSQL 17.

- Use only the schema builder: `index()`, `unique()`, `primary()`, `foreignId()->constrained()`. No `DB::statement`, raw SQL or `rawIndex`.
- **Don't use driver-specific index features:** no partial/filtered indexes, expression or functional indexes, `fullText()`, `spatialIndex()`, `->algorithm()`, `->language()`, or index prefix lengths.
- **Explicitly index every foreign key column** that isn't the leading column of another index. MySQL creates FK indexes automatically, but PostgreSQL and SQLite don't.
- **Never index `text`, `json` or `blob` columns.** MySQL can't index them without a prefix length. Index a `string` column with an explicit, sensible length instead. Keep composite indexes under MySQL's 3072-byte key limit (utf8mb4 means 4 bytes per character).
- **Name custom indexes no longer than 63 characters** (PostgreSQL's limit), and prefix them with the table name. Index names are unique per schema in PostgreSQL, not per table. Laravel's default names already follow this.
- **Unique constraints and case:** MySQL's default collation compares case-insensitively, while PostgreSQL and SQLite compare case-sensitively. Normalize values such as emails and slugs (e.g. lowercase) before storing them, so unique indexes behave the same everywhere. Emails are already handled: `User` and `TeamInvitation` lowercase `email` in a mutator, and form requests that accept an email use the `App\Http\Requests\Concerns\LowercasesEmail` trait. So query with plain `where('email', …)` (which uses the index), never `LOWER(email)`.
- **In queries,** don't use driver-specific SQL or functions (e.g. `ILIKE`, `JSON_*` operators, `GROUP_CONCAT`, MySQL `FIELD()`). Use Eloquent and query builder methods, and check that any `whereJsonContains` or date functions you use are supported on all three drivers.
