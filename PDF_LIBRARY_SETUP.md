# PDF Generation Library Setup

## Option 1: TCPDF (Recommended)

TCPDF is a popular PHP library for generating PDF documents.

### Installation via Composer (Recommended)

```bash
composer require tecnickcom/tcpdf
```

### Manual Installation


## Option 2: FPDF (Lightweight Alternative)

FPDF is a simpler, lightweight PDF library.

### Installation

1. Download FPDF from: http://www.fpdf.org/
2. Extract fpdf.php to `includes/` directory
3. Include in your code:

```php
require_once '../includes/fpdf.php';
```

## Recommended: TCPDF

For this payroll system, we recommend **TCPDF** because it supports:
- UTF-8 (for Malay language)
- HTML to PDF conversion
- Better styling options
- Professional layouts
This file was moved to the `docs/` folder during repository tidy-up.

See: [docs/PDF_LIBRARY_SETUP.md](docs/PDF_LIBRARY_SETUP.md)

The original content has been preserved in `docs/` to keep history.
Windows:
