<a href="https://simpleanalytics.com/">
  <img src="https://assets.simpleanalytics.com/images/logos/logo-github-readme.png" alt="Simple Analytics logo" align="right" height="62" />
</a>

# WordPress plugin

[Read our docs](https://docs.simpleanalytics.com/install-simple-analytics-on-wordpress) on the official Simple Analytics WordPress plugin.

You need a Simple Analytics account. Start with the [free plan](https://www.simpleanalytics.com/signup) (no credit card required) or upgrade later if you need more.

## Resources

### Client IP addresses behind a proxy

IP exclusions and **Add Current IP** both use the validated `REMOTE_ADDR` supplied by your web server. Forwarded headers are no longer trusted automatically. Configure your server's trusted proxy handling to populate the visitor's address in `REMOTE_ADDR`.

If your deployment needs a different resolver, the `simpleanalytics_client_ip` filter receives that address (or `null` when unavailable). Return one visitor IP only after validating the connecting proxy and its headers. The plugin validates and normalizes the returned IPv4 or IPv6 address and uses it consistently for tracking and the settings UI. Never pass an untrusted `X-Forwarded-For` header directly through this filter.

### Links

-   [WordPress plugin page](https://wordpress.org/plugins/simpleanalytics/)
-   [Create a Simple Analytics account](https://www.simpleanalytics.com/signup)
