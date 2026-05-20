#!/usr/bin/env python3
"""Generate a WordPress + PHP test matrix for GitHub Actions.

WordPress versions (from wordpress.org API + release zip probing):
  - Latest release branch: min, middle, and max patch versions
  - Second-latest release branch: last two patch versions

PHP versions (parsed from the WordPress PHP compatibility handbook table):
  - For each WordPress version, run lowest and highest supported PHP
"""
import json
import re
import urllib.error
import urllib.request

VERSIONS_API_URL = "https://api.wordpress.org/core/version-check/1.7/"
RELEASE_URL = "https://wordpress.org/wordpress-{version}.tar.gz"
HANDBOOK_URL = (
    "https://make.wordpress.org/core/handbook/references/"
    "php-compatibility-and-wordpress-versions/"
)
CHART_ID = "supported-version-chart"
OLDER_SECTION_ID = "older-wordpress-versions"


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


def strip_html(cell_html: str) -> str:
    text = re.sub(r"<[^>]+>", "", cell_html)
    return re.sub(r"\s+", " ", text).strip()


def parse_php_column(header_html: str) -> str | None:
    text = strip_html(header_html)
    if not text or "WP / PHP" in text:
        return None
    match = re.match(r"^(\d+\.\d+)", text)
    return match.group(1) if match else None


def parse_wp_row_label(cell_html: str) -> str | None:
    text = strip_html(cell_html)
    match = re.match(r"^(\d+\.\d+)", text)
    return match.group(1) if match else None


def is_supported(cell_html: str) -> bool:
    """Only full support (Y). Beta (Y*) is excluded."""
    text = strip_html(cell_html).upper()
    return text == "Y"


def extract_primary_compatibility_table(html: str) -> str:
    """Match: #supported-version-chart -> next table in the page."""
    anchor = f'id="{CHART_ID}"'
    start = html.find(anchor)
    if start == -1:
        raise ValueError(f'Could not find #{CHART_ID} on {HANDBOOK_URL}')

    end = html.find(f'id="{OLDER_SECTION_ID}"', start)
    section = html[start:end] if end != -1 else html[start:]

    table_start = section.find("<table")
    if table_start == -1:
        raise ValueError("Could not find compatibility table after #supported-version-chart")

    table_end = section.find("</table>", table_start)
    if table_end == -1:
        raise ValueError("Compatibility table is missing a closing tag")

    return section[table_start : table_end + len("</table>")]


def parse_compatibility_table(table_html: str) -> dict[str, list[tuple[str, str]]]:
    """
    Parse the handbook matrix into {wp_branch: [(php_version, cell_text), ...]}.
    Column order is highest PHP -> lowest PHP (left to right).
    """
    rows = re.findall(r"<tr[^>]*>(.*?)</tr>", table_html, flags=re.S | re.I)
    if not rows:
        raise ValueError("Compatibility table has no rows")

    header_cells = re.findall(r"<t[dh][^>]*>(.*?)</t[dh]>", rows[0], flags=re.S | re.I)
    php_columns = [parse_php_column(cell) for cell in header_cells[1:]]
    php_columns = [php for php in php_columns if php]

    matrix: dict[str, list[tuple[str, str]]] = {}
    for row_html in rows[1:]:
        cells = re.findall(r"<t[dh][^>]*>(.*?)</t[dh]>", row_html, flags=re.S | re.I)
        if len(cells) < 2:
            continue

        wp_branch = parse_wp_row_label(cells[0])
        if not wp_branch:
            continue

        entries = []
        for php, cell in zip(php_columns, cells[1:]):
            entries.append((php, strip_html(cell)))
        matrix[wp_branch] = entries

    if not matrix:
        raise ValueError("No WordPress rows found in compatibility table")

    return matrix


def fetch_compatibility_matrix() -> dict[str, list[tuple[str, str]]]:
    request = urllib.request.Request(
        HANDBOOK_URL,
        headers={"User-Agent": "simpleanalytics-wordpress-plugin-ci"},
    )
    with urllib.request.urlopen(request, timeout=30) as response:
        html = response.read().decode("utf-8", errors="replace")

    table_html = extract_primary_compatibility_table(html)
    return parse_compatibility_table(table_html)


def php_bounds_for_wp(
    wp_version: str, matrix: dict[str, list[tuple[str, str]]]
) -> tuple[str, str]:
    branch = major_minor(wp_version)
    row = matrix.get(branch)
    if not row:
        raise ValueError(
            f"No PHP compatibility row for WordPress {wp_version} (branch {branch}) "
            f"in {HANDBOOK_URL}"
        )

    supported = [php for php, status in row if is_supported(status)]
    if not supported:
        raise ValueError(f"No supported PHP versions for WordPress branch {branch}")

    supported.sort(key=version_tuple)
    return supported[0], supported[-1]


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

    compat_matrix = fetch_compatibility_matrix()
    include = []
    seen: set[tuple[str, str]] = set()

    for wp in sorted(set(wp_versions), key=version_tuple, reverse=True):
        min_php, max_php = php_bounds_for_wp(wp, compat_matrix)
        for php in (min_php, max_php):
            key = (wp, php)
            if key in seen:
                continue
            seen.add(key)
            include.append({"wp": wp, "php": php})

    return {"include": include}


def format_requires_php(php: str) -> str:
    """readme.txt uses a three-part PHP version (e.g. 7.2.0)."""
    if php.count(".") == 1:
        return f"{php}.0"
    return php


def tested_versions_metadata() -> dict:
    """Bounds of the CI matrix for release/readme metadata."""
    matrix = build_matrix()
    wp_versions = sorted(
        {entry["wp"] for entry in matrix["include"]},
        key=version_tuple,
    )
    php_versions = sorted(
        {entry["php"] for entry in matrix["include"]},
        key=version_tuple,
    )
    min_php = php_versions[0]
    return {
        "tested_up_to": wp_versions[-1],
        "requires_at_least": wp_versions[0],
        "requires_php": format_requires_php(min_php),
        "wp_versions": wp_versions,
        "php_versions": php_versions,
    }


if __name__ == "__main__":
    import sys

    if "--metadata" in sys.argv:
        print(json.dumps(tested_versions_metadata()))
    else:
        print(json.dumps(build_matrix()))
