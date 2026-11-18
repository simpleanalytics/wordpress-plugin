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

Enable **Pin analytics scripts** under **Settings → Simple Analytics → Advanced** to use the approved v12 analytics script with Subresource Integrity (SRI), and a bundled automated-events script. Pinning is off by default. Script changes are reviewed and shipped with plugin releases; the plugin never fetches or automatically trusts new hashes. A hash mismatch or unavailable script stops the affected tracking, without an unpinned fallback. Clear page caches after changing this setting or updating pinned scripts, so visitors receive the new script tags.

For a custom domain, save the domain first. Review its `https://YOUR.DOMAIN/sri/v12.js` script and obtain its SHA-384 hash using the [Simple Analytics SRI documentation](https://docs.simpleanalytics.com/sri). Paste the complete `sha384-…` value into **Custom domain script hash** and save again. This requires `unfiltered_html` permission. Approval is tied to the site, domain, script version, and hash. Changing the domain clears approval; a new version requires a new approval. Tracking is paused, with an administrator notice, until approval is complete. The plugin does not download custom scripts to calculate hashes.

### Updating pinned scripts

- Review the upstream script changes before changing the version or fixed hashes in `src/Support/ScriptPinning.php`. Check the published main-script hash against the actual bytes and verify CORS support.
- Pinned scripts follow the releases of [simpleanalytics/scripts](https://github.com/simpleanalytics/scripts): `VERSION` is the release tag (`v12`), the main script is `sri/v12.js`, and the bundled `resources/js/pinned/v12/auto-events.js` is the unmodified [`src/auto-events.js` at tag v12](https://github.com/simpleanalytics/scripts/blob/v12/src/auto-events.js). Its MIT license is retained in `resources/js/pinned/LICENSE`. It calls the main script’s event function and does not download additional code.
- When a new release such as `v13` is tagged, change `VERSION`, `INTEGRITY` and `EVENTS_INTEGRITY` together, add `resources/js/pinned/v13/auto-events.js` from that tag, and remove the old directory. Do not calculate the expected hash at runtime, or enable JavaScript rewriting/minification for pinned files.
- Run the PHP regressions and browser checks, including altered main-script and event-script responses, custom-domain approval changes, and event collection. Custom-domain endpoint availability and CORS must also be checked for each actual domain before enabling pinning there.
