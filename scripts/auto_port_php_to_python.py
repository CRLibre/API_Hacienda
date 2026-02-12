#!/usr/bin/env python3
"""
Automatic, non-refactor PHP -> Python raw port generator.

Purpose:
- Read PHP source files from this repository.
- Generate Python mirror modules with:
  - inferred function signatures
  - original PHP body embedded as docstring (for faithful reference)
  - TODO placeholders for behavior completion

This is intentionally mechanical (lift-and-shift baseline), not an idiomatic refactor.
"""

from __future__ import annotations

import argparse
import re
from dataclasses import dataclass
from pathlib import Path


PHP_FUNCTION_RE = re.compile(r"^\s*function\s+([A-Za-z_][A-Za-z0-9_]*)\s*\((.*?)\)\s*\{", re.MULTILINE)


@dataclass
class FunctionBlock:
    name: str
    params_raw: str
    start: int
    end: int
    body: str


def sanitize_param_name(raw: str) -> str:
    name = raw.strip()
    name = re.sub(r"^&", "", name)
    name = re.sub(r"^\$", "", name)
    name = name.strip()
    name = re.sub(r"[^A-Za-z0-9_]", "_", name)
    if not name:
        name = "arg"
    if name[0].isdigit():
        name = f"p_{name}"
    return name


def normalize_php_default(default: str) -> str:
    d = default.strip()
    d = d.replace("NULL", "None").replace("null", "None")
    d = d.replace("true", "True").replace("false", "False")
    if d == "":
        return "None"
    return d


def convert_params(params_raw: str) -> str:
    raw = params_raw.strip()
    if not raw:
        return ""
    parts = [p.strip() for p in raw.split(",")]
    out: list[str] = []
    for part in parts:
        if not part:
            continue
        if "=" in part:
            left, right = part.split("=", 1)
            name = sanitize_param_name(left)
            out.append(f"{name}={normalize_php_default(right)}")
        else:
            name = sanitize_param_name(part)
            out.append(name)
    return ", ".join(out)


def find_function_blocks(content: str) -> list[FunctionBlock]:
    blocks: list[FunctionBlock] = []
    for match in PHP_FUNCTION_RE.finditer(content):
        name = match.group(1)
        params_raw = match.group(2)
        fn_start = match.start()
        brace_start = content.find("{", match.end() - 1)
        if brace_start == -1:
            continue
        depth = 0
        i = brace_start
        end = -1
        while i < len(content):
            ch = content[i]
            if ch == "{":
                depth += 1
            elif ch == "}":
                depth -= 1
                if depth == 0:
                    end = i
                    break
            i += 1
        if end == -1:
            continue
        body = content[brace_start + 1 : end]
        blocks.append(FunctionBlock(name=name, params_raw=params_raw, start=fn_start, end=end + 1, body=body))
    return blocks


def make_python_module(source_rel: Path, php_content: str) -> tuple[str, int]:
    blocks = find_function_blocks(php_content)

    lines: list[str] = []
    lines.append('"""')
    lines.append(f"AUTO-PORTED FROM: {source_rel.as_posix()}")
    lines.append("Mode: mechanical baseline (no manual refactor).")
    lines.append('"""')
    lines.append("")
    lines.append("from __future__ import annotations")
    lines.append("")

    if not blocks:
        lines.append("# No function blocks were detected automatically.")
        lines.append("# Original PHP source is kept below for manual completion.")
        lines.append("ORIGINAL_PHP_SOURCE = r'''")
        lines.append(php_content.rstrip())
        lines.append("'''")
        return ("\n".join(lines) + "\n", 0)

    for block in blocks:
        params = convert_params(block.params_raw)
        lines.append(f"def {block.name}({params}):")
        lines.append('    """')
        lines.append("    AUTO-PORTED PHP BODY (verbatim)")
        lines.append("    --------------------------------")
        body = block.body.rstrip("\n")
        if body.strip():
            for raw_line in body.splitlines():
                lines.append(f"    {raw_line}")
        else:
            lines.append("    <empty>")
        lines.append('    """')
        lines.append("    raise NotImplementedError('Auto-port placeholder: complete behavior port here.')")
        lines.append("")

    return ("\n".join(lines).rstrip() + "\n", len(blocks))


def should_skip(path: Path) -> bool:
    parts = set(path.parts)
    if "vendor" in parts:
        return True
    if "mysql-data" in parts:
        return True
    return False


def discover_php_sources(root: Path) -> list[Path]:
    sources: list[Path] = []
    for base in (root / "api", root / "www"):
        if not base.exists():
            continue
        for path in base.rglob("*.php"):
            rel = path.relative_to(root)
            if should_skip(rel):
                continue
            sources.append(path)
    return sorted(sources)


def generate(root: Path, out_dir: Path) -> tuple[int, int]:
    sources = discover_php_sources(root)
    manifest_lines: list[str] = ["source_php\ttarget_py\tfunction_count"]
    total_functions = 0

    for source in sources:
        rel = source.relative_to(root)
        target_rel = rel.with_suffix(".py")
        target = out_dir / target_rel
        target.parent.mkdir(parents=True, exist_ok=True)

        content = source.read_text(encoding="utf-8", errors="replace")
        py_content, fn_count = make_python_module(rel, content)
        target.write_text(py_content, encoding="utf-8")

        total_functions += fn_count
        manifest_lines.append(f"{rel.as_posix()}\t{target_rel.as_posix()}\t{fn_count}")

    (out_dir / "PORT_MANIFEST.tsv").write_text("\n".join(manifest_lines) + "\n", encoding="utf-8")
    return (len(sources), total_functions)


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", default=".", help="Repository root")
    parser.add_argument(
        "--out",
        default="python-api/auto_port",
        help="Output directory for generated Python mirror files",
    )
    args = parser.parse_args()

    root = Path(args.root).resolve()
    out = (root / args.out).resolve()
    out.mkdir(parents=True, exist_ok=True)

    files_count, functions_count = generate(root, out)
    print(f"Generated Python mirror for {files_count} PHP files.")
    print(f"Detected function blocks: {functions_count}.")
    print(f"Manifest: {(out / 'PORT_MANIFEST.tsv').as_posix()}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
