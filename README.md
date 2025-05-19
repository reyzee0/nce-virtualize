# rce-virtualize

# NCE Virtualize CLI

Irreversible PHP virtualization into custom bytecode for production, with built-in integrity checks and AES encryption support for your PHP projects.

---

## 📜 Overview

`protect.php` focuses on **critical PHP files**, such as configuration files (`config.php`, `database.php`), environment files (`.env`), and other sensitive scripts. It converts these files into irreversible bytecode (`.vm`) or AES-encrypted blobs, replacing the originals with lightweight loader stubs.

> **Limitation:** This tool is **not** yet designed to virtualize or encrypt an entire large project in one pass. For now, target only your most _important_ PHP files to protect credentials and business logic. Full project-wide support is planned for future releases.

## 🛠️ Requirements

- PHP 7.4+ (CLI)

-`token_get_all` & `openssl` PHP extensions enabled

## ⚙️ Installation

1. Copy `protect.php` into your project root.
2. (Optional) Commit `protect.php` to your repository.
3. Ensure `openssl` is enabled in your `php.ini`.

## 🎯 Usage

### 1. Show Help

```bash

phpprotect.phphelp

```

### 2. Virtualize Critical Files

Converts specified PHP files under a directory into bytecode and replaces originals:

```bash

phpprotect.phpvirtualizeconfig

```

-**Output**: `.nce-bytecode/*.vm` and updated loader stubs.

### 3. Encrypt Config Files

AES-256-CBC encrypt specified files into `.nce-config/`:

```bash

phpprotect.phpencrypt-configconfig/database.php.env

```

-**Key**: Stored once as `.nce-config-key` (base64).

-**Output**: `.nce-config/database.php.enc`, etc.

## 🔧 Customization

-**Exclude additional folders**: edit `$excludeDirs` in `protect.php`.

-**Change bytecode folder**: modify the `VM_DIR` constant.

-**Use a random IV**: replace the fixed IV logic in `encrypt-config`.

## ⚠️ Important

- Always keep your `.nce-config-key` safe and out of version control.
- Back up your original PHP files before virtualization.
- Virtualized code cannot be reversed back to PHP source.

## ❤️ Contribute & Support

If you find this tool useful, please ⭐️ [star on GitHub](https://github.com/reyzee0/nce-virtualize)!
>>>>>>> a24e5c2 (Initial commit: scaffold NCE Virtualize project)
