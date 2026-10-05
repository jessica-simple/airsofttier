# ============================================================
#  BeTheme Patch: Product Category Toggle
#  Run this after every BeTheme update to restore customisations.
#  Safe to run multiple times (idempotent).
#  Stored in: betheme-child/ (never overwritten by theme updates)
# ============================================================

$ErrorActionPreference = "Stop"

$childTheme = $PSScriptRoot
$parentTheme = Join-Path (Split-Path -Parent $childTheme) "betheme"

$wooHelper  = Join-Path $parentTheme "functions\builder\class-mfn-builder-woo-helper.php"
$fieldsFile = Join-Path $parentTheme "functions\builder\class-mfn-builder-fields.php"

function Write-Status([string]$msg, [string]$type = "INFO") {
    $color = "Cyan"
    if ($type -eq "OK")   { $color = "Green" }
    if ($type -eq "SKIP") { $color = "Yellow" }
    if ($type -eq "FAIL") { $color = "Red" }
    Write-Host "[$type] $msg" -ForegroundColor $color
}

function Backup-File([string]$path) {
    $ts     = Get-Date -Format "yyyyMMdd_HHmmss"
    $backup = "$path.bak_$ts"
    Copy-Item $path $backup
    Write-Status "Backup: $backup"
}

# ---------------------------------------------------------------
# PATCH 1 — class-mfn-builder-woo-helper.php
# Replace original bare category block with the toggled version.
# ---------------------------------------------------------------

Write-Host ""
Write-Host "=== Patch 1: class-mfn-builder-woo-helper.php ===" -ForegroundColor White

$content1 = [System.IO.File]::ReadAllText($wooHelper, [System.Text.Encoding]::UTF8)

if ($content1.Contains("show_category") -and $content1.Contains("!== '0'")) {
    Write-Status "Already patched - skipping." "SKIP"
}
else {
    # Anchor: unique string from BeTheme's original (un-patched) code
    $anchor1 = "wc_get_product_category_list" + '($product->get_id(), ' + "', ');"

    if ($content1.Contains($anchor1)) {
        # Build the replacement block line by line using tab char
        $t2 = [string][char]9 + [string][char]9
        $t3 = $t2 + [string][char]9

        $oldLines = @(
            $t2 + '// Category name at the end of each product item',
            $t3 + '$cat_list = wc_get_product_category_list($product->get_id(), ' + "', '" + ');',
            $t2 + 'if (!empty($cat_list)) {',
            $t3 + '$output .= ' + "'<div class=" + '"mfn-li-product-row mfn-li-product-row-category">' + "'" + ' . $cat_list . ' + "'</div>" + "';",
            $t2 + '}'
        )

        $newLines = @(
            $t2 + "// Category name at the end of each product item",
            $t2 + "// ''=Default(show), '1'=show, '0'=hide",
            $t2 + "if (!isset(" + '$attr[' + "'show_category'" + ']) || $attr[' + "'show_category'" + "] !== '0') {",
            $t3 + '$cat_list = wc_get_product_category_list($product->get_id(), ' + "', '" + ');',
            $t3 + 'if (!empty($cat_list)) {',
            $t3 + [string][char]9 + '$output .= ' + "'<div class=" + '"mfn-li-product-row mfn-li-product-row-category">' + "'" + ' . $cat_list . ' + "'</div>" + "';",
            $t3 + '}',
            $t2 + '}'
        )

        # Try replacing by searching for the anchor line and rebuilding the block
        $lines1 = [System.IO.File]::ReadAllLines($wooHelper, [System.Text.Encoding]::UTF8)
        $insertIdx = -1
        for ($i = 0; $i -lt $lines1.Length; $i++) {
            if ($lines1[$i] -match [regex]::Escape("wc_get_product_category_list")) {
                # Found the line - go back to find the comment above it
                $blockStart = $i - 1
                if ($blockStart -ge 0 -and $lines1[$blockStart] -match "Category name") {
                    $insertIdx = $blockStart
                    break
                }
            }
        }

        if ($insertIdx -ge 0) {
            # Determine how many lines the old block spans (comment + cat_list + if + output + closing brace = 5 lines)
            $oldBlockSize = 5
            $before = $lines1[0..($insertIdx - 1)]
            $after  = $lines1[($insertIdx + $oldBlockSize)..($lines1.Length - 1)]
            $result = $before + $newLines + $after

            Backup-File $wooHelper
            [System.IO.File]::WriteAllLines($wooHelper, $result, [System.Text.Encoding]::UTF8)
            Write-Status "Patch applied." "OK"
        }
        else {
            Write-Status "Could not locate the exact block start - manual review needed." "FAIL"
        }
    }
    else {
        Write-Status "Anchor string not found in woo-helper. BeTheme may have changed code - manual review needed." "FAIL"
    }
}

# ---------------------------------------------------------------
# PATCH 2 — class-mfn-builder-fields.php
# Insert show_category switch field after the 'button' field.
# ---------------------------------------------------------------

Write-Host ""
Write-Host "=== Patch 2: class-mfn-builder-fields.php ===" -ForegroundColor White

$lines2 = [System.IO.File]::ReadAllLines($fieldsFile, [System.Text.Encoding]::UTF8)

$alreadyPatched2 = ($lines2 | Select-String -SimpleMatch "'id' => 'show_category'").Count -gt 0

if ($alreadyPatched2) {
    Write-Status "Already patched - skipping." "SKIP"
}
else {
    $insertAfterIndex = -1

    for ($i = 1; $i -lt $lines2.Length; $i++) {
        # Find: line is   <tabs>'std' => '1',
        #  AND next line  <tabs>),
        if ($lines2[$i] -match "^\s+'std' => '1',$" -and $lines2[$i + 1] -match "^\s+\),$") {
            # Confirm this is actually the button field by looking back
            $lookback = [Math]::Max(0, $i - 20)
            $ctx = $lines2[$lookback..$i] -join " "
            if ($ctx -match "'id' => 'button'") {
                $insertAfterIndex = $i + 1   # insert after the "),"
                break
            }
        }
    }

    if ($insertAfterIndex -ge 0) {
        $t6 = [string][char]9 * 6
        $t7 = [string][char]9 * 7
        $t8 = [string][char]9 * 8

        $newField = @(
            "",
            "${t6}array(",
            "${t7}'id' => 'show_category',",
            "${t7}'attr_id' => 'show_category',",
            "${t7}'re_render' => true,",
            "${t7}'type' => 'switch',",
            "${t7}'title' => __(" + "'Product Category', 'mfn-opts'),",
            "${t7}'options' => array(",
            "${t8}'' => __(" + "'Default', 'mfn-opts'),",
            "${t8}'0' => __(" + "'Hide', 'mfn-opts'),",
            "${t8}'1' => __(" + "'Show', 'mfn-opts'),",
            "${t7}),",
            "${t7}'std' => '',",
            "${t6}),"
        )

        $before = $lines2[0..$insertAfterIndex]
        $after  = $lines2[($insertAfterIndex + 1)..($lines2.Length - 1)]
        $result = $before + $newField + $after

        Backup-File $fieldsFile
        [System.IO.File]::WriteAllLines($fieldsFile, $result, [System.Text.Encoding]::UTF8)
        Write-Status "Patch applied after line $($insertAfterIndex + 1)." "OK"
    }
    else {
        Write-Status "Could not locate button field anchor. BeTheme may have changed code - manual review needed." "FAIL"
    }
}

Write-Host ""
Write-Host "=== All done ===" -ForegroundColor White
