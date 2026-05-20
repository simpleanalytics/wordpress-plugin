#!/usr/bin/env python3
"""Generate a WordPress + PHP test matrix for GitHub Actions.

WordPress versions (from wordpress.org API):
  - Latest release branch: min, middle, and max patch versions
  - Second-latest release branch: last two patch versions

PHP versions (from scripts/wp-php-compatibility.json, based on the WordPress
PHP compatibility handbook):
  - For each WordPress version, run lowest and highest supported PHP
"""
import json
import os
import pathlib
import urllib.request

VERSIONS_API_URL = "https://api.wordpress.org/core/version-check/1.7/"
RELEASE_URL = "https://wordpress.org/wordpress-{version}.tar.gz"
COMPAT_FILE = pathlib.Path(__file__).with_name("wp-php-compatibility.json")


def branch_sort_key(branch: str):
    major, minor = branch.split(".")
    return int(major), int(minor)


def version_tuple(v: str) -> tuple[int, ...]:
    return tuple(int(part) for part in v.split(".") if part.isdigit())


def major_minor(v: str) -> str:
    parts = v.split(".")
    return f"{parts[0]}.{parts[1]}"


def fetch_offers():
    with urllib.request.urlopen(VERSIONS_API_URL) as response:
        return json.load(response).get("offers", [])


def latest_version_per_branch(offers) -> dict[str, str]:
    """API offers only include the current patch per branch."""
    latest: dict[str, tuple[int, str]] = {}
    for offer in offers:
        version = offer.get("version", "")
        if not version or version.count(".") < 1:
            continue
        parts = version.split(".")
        branch = ".".join(parts[:2])
        patch = int(parts[2]) if len(parts) > 2 and parts[2].isdigit() else 0
        current = latest.get(branch)
        if current is None or patch > current[0]:
            latest[branch] = (patch, version)
    return {branch: version for branch, (_, version) in latest.items()}


def release_exists(version: str) -> bool:
    request = urllib.request.Request(
        RELEASE_URL.format(version=version),
        method="HEAD",
    )
    try:
        with urllib.request.urlopen(request, timeout=15) as response:
            return response.status == 200
    except urllib.error.HTTPError as error:
        return error.code == 200
    except urllib.error.URLError:
        return False


def discover_versions_for_branch(branch: str, latest: str) -> list[str]:
    """Discover published .tar.gz releases from wordpress.org (API has latest only)."""
    parts = latest.split(".")
    if len(parts) == 2:
        return [latest] if release_exists(latest) else []

    max_patch = int(parts[2])
    versions = []
    for patch in range(max_patch + 1):
        version = f"{branch}.{patch}"
        if release_exists(version):
            versions.append(version)
    return versions


def select_min_middle_max(versions: list[str]) -> list[str]:
    """Pick three patch versions: earliest, middle, and latest."""
    if not versions:
        return []
    if len(versions) == 1:
        return [versions[0]]
    if len(versions) == 2:
        return [versions[0], versions[0], versions[1]]
    return [versions[0], versions[len(versions) // 2], versions[-1]]


def select_last_two(versions: list[str]) -> list[str]:
    if len(versions) >= 2:
        return versions[-2:]
    return versions


def load_php_compatibility() -> dict[str, dict[str, str]]:
    with COMPAT_FILE.open() as handle:
        data = json.load(handle)
    return {key: value for key, value in data.items() if not key.startswith("_")}


def php_bounds_for_wp(wp_version: str, compat: dict[str, dict[str, str]]) -> tuple[str, str]:
    branch = major_minor(wp_version)
    bounds = compat.get(branch)
    if not bounds:
        raise ValueError(
            f"No PHP compatibility for WordPress {wp_version} (branch {branch}). "
            f"Update {COMPAT_FILE.name} from "
            "https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/"
        )
    return bounds["min"], bounds["max"]


def build_matrix() -> dict:
    latest_by_branch = latest_version_per_branch(fetch_offers())
    branches = sorted(latest_by_branch.keys(), key=branch_sort_key, reverse=True)
    if len(branches) < 1:
        return {"include": []}

    latest_branch = branches[0]
    second_branch = branches[1] if len(branches) > 1 else None

    latest_versions = discover_versions_for_branch(
        latest_branch, latest_by_branch[latest_branch]
    )
    wp_versions: list[str] = []
    wp_versions.extend(select_min_middle_max(latest_versions))

    if second_branch:
        second_versions = discover_versions_for_branch(
            second_branch, latest_by_branch[second_branch]
        )
        for version in select_last_two(second_versions):
            if version not in wp_versions:
                wp_versions.append(version)

    compat = load_php_compatibility()
    include = []
    seen: set[tuple[str, str]] = set()

    for wp in sorted(set(wp_versions), key=version_tuple, reverse=True):
        min_php, max_php = php_bounds_for_wp(wp, compat)
        for php in (min_php, max_php):
            key = (wp, php)
            if key in seen:
                continue
            seen.add(key)
            include.append({"wp": wp, "php": php})

    return {"include": include}


if __name__ == "__main__":
    print(json.dumps(build_matrix()))
