#!/usr/bin/env python3
"""Render private source PDFs to page PNGs used by the admin review screen."""
import argparse, hashlib
from pathlib import Path
import fitz

parser = argparse.ArgumentParser(); parser.add_argument('source', type=Path); parser.add_argument('--output', type=Path, required=True)
args = parser.parse_args(); files = sorted(args.source.rglob('*.pdf'))
for path in files:
    target = args.output / hashlib.sha1(path.name.encode()).hexdigest() / 'page-{:03d}.png'
    target.parent.mkdir(parents=True, exist_ok=True)
    with fitz.open(path) as pdf:
        for index, page in enumerate(pdf, 1):
            out = Path(str(target).format(index))
            if not out.exists(): page.get_pixmap(matrix=fitz.Matrix(1.5, 1.5), alpha=False).save(out)
print(f'rendered {len(files)} PDFs')
