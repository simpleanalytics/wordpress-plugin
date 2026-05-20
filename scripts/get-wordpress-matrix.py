#!/usr/bin/env python3
"""Generate a WordPress + PHP test matrix for GitHub Actions.

Includes the WP_LATEST_COUNT most recent WP versions plus the first and
last minor of every WP major still in the API. Each entry uses the PHP
version WordPress recommends for that release (major.minor).
"""
import json
import os
import urllib.request

VERSIONS_API_URL = "https://api.wordpress.org/core/version-check/1.7/"
DEFAULT_WP_LATEST_COUNT = 2
# wp-env uses official wordpress:phpX images; PHP < 7.2 is on Debian Stretch
# whose archive repos now have expired signing keys (apt exit 100 in CI).
DEFAULT_WP_ENV_MIN_PHP = "7.2"


def branch_sort_key(branch: str):
    major, minor = branch.split(".")
    return int(major), int(minor)


def major_minor(v: str) -> str:
    parts = v.split(".")
    return f"{parts[0]}.{parts[1]}"


def version_tuple(v: str) -> tuple[int, ...]:
    return tuple(int(part) for part in v.split(".") if part.isdigit())


def php_for_wp_env(recommended: str, min_php: str) -> str:
    """Use WordPress-recommended PHP unless below wp-env's practical minimum."""
    php = major_minor(recommended) if recommended else min_php
    if version_tuple(php) < version_tuple(min_php):
        return min_php
    return php


def fetch_offers():
    with urllib.request.urlopen(VERSIONS_API_URL) as response:
        return json.load(response).get("offers", [])


def latest_per_branch(offers):
    """Return {branch: (latest_version, recommended_php)}."""
    info = {}
    for offer in offers:
        version = offer.get("version", "")
        if not version or version.count(".") < 1:
            continue
        parts = version.split(".")
        branch = ".".join(parts[:2])
        patch = int(parts[2]) if len(parts) > 2 and parts[2].isdigit() else 0
        php = offer.get("php_version", "")
        current = info.get(branch)
        if current is None or patch > current[0]:
            info[branch] = (patch, version, php)
    return {b: (v, p) for b, (_, v, p) in info.items()}


def build_matrix(wp_latest_count: int):
    branch_info = latest_per_branch(fetch_offers())
    branches = sorted(branch_info.keys(), key=branch_sort_key, reverse=True)
    if not branches:
        return {"include": []}

    # Group branches by major; sort each group ascending so [0]=first, [-1]=last
    majors = {}
    for branch in branches:
        major = int(branch.split(".")[0])
        majors.setdefault(major, []).append(branch)
    for major in majors:
        majors[major].sort(key=branch_sort_key)

    selected = set(branches[:wp_latest_count])           # most recent overall
    for group in majors.values():
        selected.add(group[0])                            # first of major
        selected.add(group[-1])                           # last of major

    min_php = os.getenv("WP_ENV_MIN_PHP", DEFAULT_WP_ENV_MIN_PHP)
    include = []
    for branch in sorted(selected, key=branch_sort_key, reverse=True):
        wp, recommended_php = branch_info[branch]
        php = php_for_wp_env(recommended_php, min_php)
        include.append({"wp": wp, "php": php})
    return {"include": include}


if __name__ == "__main__":
    count = int(os.getenv("WP_LATEST_COUNT", DEFAULT_WP_LATEST_COUNT))
    print(json.dumps(build_matrix(count)))
