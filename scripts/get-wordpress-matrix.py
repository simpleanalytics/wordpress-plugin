#!/usr/bin/env python3
import json
import os
import urllib.request

VERSIONS_API_URL = "https://api.wordpress.org/core/version-check/1.7/"
DEFAULT_BRANCH_COUNT = 2


def branch_sort_key(branch: str):
    major, minor = branch.split(".")
    return int(major), int(minor)


def latest_versions_from_api(branch_count: int):
    with urllib.request.urlopen(VERSIONS_API_URL) as response:
        data = json.load(response)

    offers = data.get("offers", [])
    branch_latest = {}

    for offer in offers:
        version = offer.get("version", "")
        if not version or version.count(".") < 1:
            continue

        parts = version.split(".")
        branch = ".".join(parts[:2])
        patch = int(parts[2]) if len(parts) > 2 and parts[2].isdigit() else 0

        current = branch_latest.get(branch)
        if current is None or patch > current[0]:
            branch_latest[branch] = (patch, version)

    latest_branches = sorted(branch_latest.keys(), key=branch_sort_key, reverse=True)[:branch_count]
    return [branch_latest[branch][1] for branch in latest_branches]


if __name__ == "__main__":
    count = int(os.getenv("WP_MATRIX_BRANCH_COUNT", DEFAULT_BRANCH_COUNT))
    versions = latest_versions_from_api(count)
    print(json.dumps(versions))
