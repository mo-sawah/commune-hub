# Commune Hub

A modern community platform plugin for WordPress featuring:

- Communities (custom post type)
- Discussion posts (custom post type)
- Tag taxonomy
- Voting (up/down) with hot/top/rising sorting algorithms
- Membership joins
- REST API (WP-JSON) namespace: `commune-hub/v1`
- React front-end SPA embedded via shortcode `[commune_hub]`
- Secure nonces, capability checks, sanitized inputs
- Performance-friendly (indexes, transients, lean queries)

## Shortcode

Add `[commune_hub]` to any page to mount the application.

## Build (optional)

If you want to modify the React source in `src/`:

```bash
cd wp-content/plugins/commune-hub
npm install
npm run build
```

This outputs an updated `assets/js/app.js`.

## REST Highlights

- GET /wp-json/commune-hub/v1/communities
- POST /wp-json/commune-hub/v1/communities
- GET /wp-json/commune-hub/v1/posts
- POST /wp-json/commune-hub/v1/posts
- POST /wp-json/commune-hub/v1/vote
- POST /wp-json/commune-hub/v1/membership (join/leave)
- GET /wp-json/commune-hub/v1/comments?post_id=ID
- POST /wp-json/commune-hub/v1/comments

All write endpoints require authentication and nonce `communeHub.nonce`.

## Security

- Capability checks on create/update
- Nonce verification
- Prepared SQL statements
- Rate-limited voting (1 row per user/post)

## Sorting Algorithms

- `new`: post_date DESC
- `top`: net score (up - down)
- `hot`: `(netScore / (hours+2)^1.5)` approximate ranking
- `rising`: votes in last 6 hours (burst detection)

## Extend

Hook examples:

```php
add_filter( 'commune_hub_hot_score_modifier', function( $score, $post_id ){ return $score; }, 10, 2 );
```

## License

MIT
