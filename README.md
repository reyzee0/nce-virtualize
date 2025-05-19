# NCE Virtualize CLI

Irreversible PHP virtualization into custom bytecode for production, with built-in integrity checks and AES encryption support for your PHP projects.

---

## 📜 Overview

`protect.php` focuses on **critical PHP files**, such as configuration files (`config.php`, `database.php`), environment files (`.env`), and other sensitive scripts. It converts these files into irreversible bytecode (`.vm`) or AES-encrypted blobs, replacing the originals with lightweight loader stubs.

> **Limitation:** This tool is **not** yet designed to virtualize or encrypt an entire large project in one pass. For now, target only your most _important_ PHP files to protect credentials and business logic. Full project-wide support is planned for future releases.

## 🛠️ Requirements

- PHP 7.4+ (CLI)  
- `token_get_all` & `openssl` PHP extensions enabled  

## ⚙️ Installation

1. Copy `protect.php` into your project root.  
2. (Optional) Commit `protect.php` to your repository.  
3. Ensure `openssl` is enabled in your `php.ini`.

## 🎯 Usage

### 1. Show Help

```bash
php protect.php help
