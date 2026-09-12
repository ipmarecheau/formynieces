#!/usr/bin/env python3
"""Inventory SEA source PDFs and extract any existing text layer.

This is deliberately a staging step: scanned pages are marked for OCR and are
never treated as empty papers. The manifest is safe to rerun and is the input
for the future page OCR/LLM extraction worker.
"""
from __future__ import annotations
import argparse, hashlib, json, re
from datetime import datetime, timezone
from pathlib import Path

try:
    from pypdf import PdfReader
except ImportError:
    PdfReader = None

def classify(name: str) -> tuple[str, str]:
    upper = name.upper()
    subject = 'ELA' if ('ELA' in upper or 'ENGLISH' in upper or upper.startswith('CW-')) else 'Math'
    paper_type = 'creative_writing' if upper.startswith('CW-') or 'WRITING' in upper else 'multiple_choice'
    return subject, paper_type

def year(name: str) -> int | None:
    match = re.search(r'(20\d{2})', name)
    return int(match.group(1)) if match else None

def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument('source', type=Path)
    parser.add_argument('--output', type=Path, required=True)
    args = parser.parse_args()
    files = sorted(args.source.rglob('*.pdf'))
    records = []
    for path in files:
        digest = hashlib.sha256(path.read_bytes()).hexdigest()
        pages, text_pages, chars, error = 0, 0, 0, None
        text_path = None
        if PdfReader is None:
            error = 'pypdf is not installed; install it for text-layer extraction.'
        else:
            try:
                reader = PdfReader(str(path))
                pages = len(reader.pages)
                chunks = []
                for page in reader.pages:
                    text = (page.extract_text() or '').strip()
                    chunks.append(text)
                    if text:
                        text_pages += 1
                        chars += len(text)
                if chars:
                    text_path = str(path.with_suffix('.txt').relative_to(args.source))
                    target = args.output.parent / 'text' / text_path
                    target.parent.mkdir(parents=True, exist_ok=True)
                    target.write_text('\n\n'.join(chunks), encoding='utf-8')
            except Exception as exc:
                error = str(exc)
        subject, paper_type = classify(path.name)
        records.append({
            'source_ref': digest,
            'filename': path.name,
            'relative_path': str(path.relative_to(args.source)),
            'subject': subject,
            'paper_type': paper_type,
            'year': year(path.name),
            'pages': pages,
            'text_pages': text_pages,
            'ocr_pages': max(0, pages - text_pages),
            'text_path': text_path,
            'sha256': digest,
            'status': 'text_extracted' if text_pages else ('needs_ocr' if pages else 'error'),
            'error': error,
        })
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(json.dumps({'version': 1, 'generated_at': datetime.now(timezone.utc).isoformat(), 'files': records}, indent=2), encoding='utf-8')
    print(f'inventoried {len(records)} PDFs')
    print(f"text available: {sum(r['text_pages'] > 0 for r in records)}; needs OCR: {sum(r['status'] == 'needs_ocr' for r in records)}")

if __name__ == '__main__':
    main()
