<a href="https://simpleanalytics.com/">
  <img src="https://assets.simpleanalytics.com/images/logos/logo-github-readme.png" alt="Simple Analytics logo" align="right" height="62" />
</a>

# WordPress plugin

[Read our docs](https://docs.simpleanalytics.com/install-simple-analytics-on-wordpress) on the official Simple Analytics WordPress plugin.

You need a Simple Analytics account. Start with the [free plan](https://www.simpleanalytics.com/signup) (no credit card required) or upgrade later if you need more.

## Resources

-   [WordPress plugin page](https://wordpress.org/plugins/simpleanalytics/)
-   [Create a Simple Analytics account](https://www.simpleanalytics.com/signup)

## Script pinning

Enable **Pin analytics scripts** under **Settings → Simple Analytics → Advanced** to run the v12 analytics and automated-events scripts bundled with the plugin, instead of the latest scripts from the CDN. Every script tag carries a Subresource Integrity (SRI) hash, so a browser refuses changed bytes. Pinning is off by default. Script updates arrive with plugin releases. Clear page caches after changing this setting or updating the plugin, so visitors receive the new script tags, and exclude pinned files from JavaScript rewriting or minification.

With a custom domain, the plugin fills the domain into the bundled custom-domain build, as the custom-domain server does for `https://YOUR.DOMAIN/sri/v12.js`, and saves it as `wp-content/uploads/simpleanalytics/v12-YOUR.DOMAIN.js`. That script sends data to the custom domain. If the uploads folder is not writable, visitors get the bundled script for the default domain and administrators see a warning.

### Updating pinned scripts

- Pinned scripts follow the releases of [simpleanalytics/scripts](https://github.com/simpleanalytics/scripts). `VERSION` in `src/Support/ScriptPinning.php` is the release tag (`v12`), and `resources/js/pinned/v12/` holds `dist/v12/app.js`, `dist/v12/custom/app.js`, `dist/v12/auto-events.js`, `dist/v12/app.js.map` and `dist/v12/auto-events.js.map` from that release's `scripts-v12.tar.gz`, unmodified. Browsers request a script's source map when developer tools are open, so a missing map logs an error. The custom-domain map is valid only after its server fills in the hostname, so it is not bundled, and the script written for a custom domain drops its source map comment. `app.js` equals `https://scripts.simpleanalyticscdn.com/sri/v12.js` except for its source map comment. The MIT license is retained in `resources/js/pinned/LICENSE`.
- For a new release such as `v13`: review the upstream changes, put the same five files from `scripts-v13.tar.gz` in `resources/js/pinned/v13/`, remove `v12/`, and change `VERSION`, `INTEGRITY`, `CUSTOM_DOMAIN_INTEGRITY` and `EVENTS_INTEGRITY` together. Calculate each hash with `echo "sha384-$(openssl dgst -sha384 -binary FILE | openssl base64 -A)"`. Never calculate the expected hashes at runtime.
- Run the PHP regressions and browser checks, including altered script responses, a custom domain, and event collection.
