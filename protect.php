#!/usr/bin/env php
<?php
/**
 * NCE Virtualize CLI – Irreversible PHP virtualization for any PHP environment
 *
 * @author reyzee
 * @github reyzee0
 *
 * Usage:
 *   php protect.php [command] [directory]
 *
 * Commands:
 *   virtualize   Convert PHP source into custom .vm bytecode and replace originals
 *   help         Display this help message
 *
 * Features:
 *   - Auto-generates vm.php loader with integrity checks (guarded against redeclaration)
 *   - Excludes directories (vendor, storage, node_modules)
 *   - Terminal colors and hyperlinks for enhanced UX
 *   - Loader indicator showing when processing
 *   - Executes virtualized code in global scope preserving globals
 */

// Run in script directory
@chdir(__DIR__);

// Exclude these directories
$excludeDirs = ['vendor', 'storage', 'node_modules'];

// Paths
define('VM_DIR', __DIR__ . '/.nce-bytecode');
define('VM_FILE', __DIR__ . '/vm.php');

// ANSI coloring and hyperlinks
function color($text, $code) { return "\e[{$code}m{$text}\e[0m"; }
function hyperlink($text, $url) { return "\e]8;;{$url}\a{$text}\e]8;;\a"; }

// Loader indicator
function loaderStart() { echo color("Working...", '33') . "\n"; }
function loaderEnd()   { echo color("Done.",    '32') . "\n\n"; }

// Auto-generate vm.php loader if missing
if (!file_exists(VM_FILE)) {
    loaderStart();
    $vmCode = <<<'PHP'
<?php
/**
 * vm.php – Custom VM Interpreter for NCE Virtualized Bytecode with integrity checks
 *
 * @author reyzee
 * @github reyzee0
 */
if (!class_exists('VM')) {
    class VM {
        public static function run($vmFile) {
            // Load and verify bytecode
            if (!file_exists($vmFile)) {
                throw new Exception("VM error: Bytecode file not found: $vmFile");
            }
            $hashFile = $vmFile . '.hash';
            if (!file_exists($hashFile) || file_get_contents($hashFile) !== hash('sha256', file_get_contents($vmFile))) {
                throw new Exception("VM error: Integrity check failed for $vmFile");
            }
            $ops = @unserialize(file_get_contents($vmFile));
            if ($ops === false) {
                throw new Exception("VM error: Failed to unserialize bytecode.");
            }
            // Reconstruct source
            $php = '';
            foreach ($ops as $op) {
                if ($op[0] === 'TOK') {
                    list(, $id, $text) = $op;
                    if (in_array($id, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG])) continue;
                    if ($id === T_INLINE_HTML) {
                        $php .= 'echo ' . var_export($text, true) . ';';
                    } else {
                        $php .= $text;
                    }
                } else {
                    $php .= $op[1];
                }
            }
            // Execute in global scope preserving globals
            $runner = \Closure::bind(function() use ($php) {
                extract($GLOBALS, EXTR_SKIP);
                eval('?>' . $php);
            }, null, null);
            $runner();
        }
    }
}
PHP;
    file_put_contents(VM_FILE, $vmCode);
    loaderEnd();
    echo color("Generated vm.php loader with closure-based global execution", '32') . "\n";
}

// CLI args
global $argc, $argv;
$cmd = $argv[1] ?? 'help';

if ($cmd === 'help' || $argc < 2) {
    echo color("NCE Virtualize CLI", '36') . " by " . color("reyzee (github: reyzee0)", '33') . "\n";
    echo color("Usage:", '34') . " php protect.php [command] [directory]\n";
    echo color("Commands:", '34') . "\n";
    echo "  " . color("virtualize", '32') . "   Convert PHP to .vm bytecode and replace originals\n";
    echo "  " . color("help", '32') . "         Display this help message\n";
    $repo = 'https://github.com/reyzee0/nce-virtualize';
    echo "\n" . color("Support:", '34') . " " . hyperlink('⭐ Star on GitHub', $repo) . " ($repo)\n";
    exit;
}

// Ensure VM_DIR exists
if (!is_dir(VM_DIR) && !mkdir(VM_DIR, 0700, true)) {
    die(color("Error: Cannot create VM_DIR: " . VM_DIR, '31') . "\n");
}

if ($cmd === 'virtualize') {
    loaderStart();
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($argv[2] ?? __DIR__, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        $fp = $file->getPathname();
        if (!$file->isFile() || pathinfo($fp, PATHINFO_EXTENSION) !== 'php') continue;
        if (strpos($fp, VM_DIR) === 0) continue;
        if (basename($fp) === basename(__FILE__) || basename($fp) === basename(VM_FILE)) continue;
        foreach ($excludeDirs as $ex) {
            if (strpos($fp, DIRECTORY_SEPARATOR.$ex.DIRECTORY_SEPARATOR) !== false) continue 2;
        }
        $cont = @file_get_contents($fp);
        if (strpos($cont, '// VM loader stub for') === 0) continue;
        // Tokenize and serialize
        $src = file_get_contents($fp);
        $tokens = token_get_all("<?php\n" . $src);
        $ops = [];
        foreach ($tokens as $t) {
            if (is_array($t)) {
                list($id, $text) = $t;
                $ops[] = ['TOK', $id, $text];
            } else {
                $ops[] = ['CHR', $t];
            }
        }
        $base = basename($fp, '.php');
        $vmf = VM_DIR . "/{$base}.vm";
        file_put_contents($vmf, serialize($ops));
        file_put_contents($vmf . '.hash', hash('sha256', file_get_contents($vmf)));
        // Stub
        $stub = <<<PHP
<?php
// VM loader stub for {$base}.php
\$vmDir = __DIR__;
while (!file_exists(\$vmDir . '/vm.php')) {
    \$par = dirname(\$vmDir);
    if (\$par === \$vmDir) throw new Exception("VM loader error: vm.php not found");
    \$vmDir = \$par;
}
require_once \$vmDir . '/vm.php';
VM::run(\$vmDir . '/.nce-bytecode/{$base}.vm');
PHP;
        file_put_contents($fp, $stub);
        echo color("Virtualized: {$base}.php", '36') . " -> " . color(".nce-bytecode/{$base}.vm", '35') . color(" (+hash)", '33') . "\n";
    }
    loaderEnd();
    echo color("Virtualization complete.", '32') . "\n";
    echo color("Please", '36') . " " .'⭐ Star our GitHub repo:', ' https://github.com/reyzee0/nce-virtualize'. "\n";
    exit;
}

echo color("Unknown command: $cmd", '31') . "\n";
exit(1);
