$ErrorActionPreference = "Stop"

$base       = "i:\Work\SIMPLE-CREATIVE\Wordpress\Airsoft\wp-content\themes\betheme\functions\builder"
$wooHelper  = "$base\class-mfn-builder-woo-helper.php"
$fieldsFile = "$base\class-mfn-builder-fields.php"
$skuBlock   = "$base\_sku_block.txt"

function Write-Status([string]$msg, [string]$type = "INFO") {
    $color = switch ($type) { "OK" {"Green"} "SKIP" {"Yellow"} "FAIL" {"Red"} default {"Cyan"} }
    Write-Host "[$type] $msg" -ForegroundColor $color
}

# ---------------------------------------------------------------
# PATCH A  — woo-helper: insert SKU block after show_category block
# ---------------------------------------------------------------
Write-Host ""
Write-Host "=== Patch A: woo-helper.php ===" -ForegroundColor White

$lines1 = [System.IO.File]::ReadAllLines($wooHelper, [System.Text.Encoding]::UTF8)

if (($lines1 | Where-Object { $_ -match "show_sku" }).Count -gt 0) {
    Write-Status "Already patched - skipping." "SKIP"
} else {
    # Find closing "}" of the show_category if-block
    # Pattern: line contains "show_category" and "!== '0'" => then scan for next standalone closing brace at indent-2
    $insertAfterIndex = -1
    for ($i = 0; $i -lt $lines1.Length; $i++) {
        if ($lines1[$i] -match "show_category" -and $lines1[$i] -match "!== .0.") {
            for ($j = $i + 1; $j -lt $lines1.Length; $j++) {
                $trimmed = $lines1[$j].TrimEnd()
                if ($trimmed -eq "`t`t}") {
                    $insertAfterIndex = $j
                    break
                }
            }
            break
        }
    }

    if ($insertAfterIndex -ge 0) {
        $skuLines = [System.IO.File]::ReadAllLines($skuBlock, [System.Text.Encoding]::UTF8)
        $before = $lines1[0..$insertAfterIndex]
        $after  = $lines1[($insertAfterIndex + 1)..($lines1.Length - 1)]
        $result = $before + $skuLines + $after
        [System.IO.File]::WriteAllLines($wooHelper, $result, [System.Text.Encoding]::UTF8)
        Write-Status "SKU block inserted after line $($insertAfterIndex + 1)." "OK"
    } else {
        Write-Status "Could not find show_category closing brace. Manual review needed." "FAIL"
    }
}

# ---------------------------------------------------------------
# PATCH B  — fields: insert show_sku switch after show_category array
# ---------------------------------------------------------------
Write-Host ""
Write-Host "=== Patch B: fields.php ===" -ForegroundColor White

$lines2 = [System.IO.File]::ReadAllLines($fieldsFile, [System.Text.Encoding]::UTF8)

if (($lines2 | Where-Object { $_ -match "'id' => 'show_sku'" }).Count -gt 0) {
    Write-Status "Already patched - skipping." "SKIP"
} else {
    # Find the closing ")," of the show_category field array (6 tabs + ),)
    $insertAfterIndex2 = -1
    for ($i = 0; $i -lt $lines2.Length; $i++) {
        if ($lines2[$i] -match "'id' => 'show_category'") {
            for ($j = $i + 1; $j -lt $lines2.Length; $j++) {
                $trimmed = $lines2[$j].TrimEnd()
                if ($trimmed -eq ("`t" * 6 + "),")) {
                    $insertAfterIndex2 = $j
                    break
                }
            }
            break
        }
    }

    if ($insertAfterIndex2 -ge 0) {
        $t6 = "`t" * 6
        $t7 = "`t" * 7
        $t8 = "`t" * 8

        # Build field lines using Format-String to avoid PS quote issues
        $f1  = $t6 + "array("
        $f2  = $t7 + [char]39 + "id" + [char]39 + " => " + [char]39 + "show_sku" + [char]39 + ","
        $f3  = $t7 + [char]39 + "attr_id" + [char]39 + " => " + [char]39 + "show_sku" + [char]39 + ","
        $f4  = $t7 + [char]39 + "re_render" + [char]39 + " => true,"
        $f5  = $t7 + [char]39 + "type" + [char]39 + " => " + [char]39 + "switch" + [char]39 + ","
        $f6  = $t7 + [char]39 + "title" + [char]39 + " => __(" + [char]39 + "Product SKU" + [char]39 + ", " + [char]39 + "mfn-opts" + [char]39 + "),"
        $f7  = $t7 + [char]39 + "options" + [char]39 + " => array("
        $f8  = $t8 + [char]39 + [char]39  + " => __(" + [char]39 + "Default" + [char]39 + ", " + [char]39 + "mfn-opts" + [char]39 + "),"
        $f9  = $t8 + [char]39 + "0" + [char]39 + " => __(" + [char]39 + "Hide" + [char]39 + ", " + [char]39 + "mfn-opts" + [char]39 + "),"
        $f10 = $t8 + [char]39 + "1" + [char]39 + " => __(" + [char]39 + "Show" + [char]39 + ", " + [char]39 + "mfn-opts" + [char]39 + "),"
        $f11 = $t7 + "),"
        $f12 = $t7 + [char]39 + "std" + [char]39 + " => " + [char]39 + [char]39 + ","
        $f13 = $t6 + "),"

        $newField = @("", $f1, $f2, $f3, $f4, $f5, $f6, $f7, $f8, $f9, $f10, $f11, $f12, $f13)

        $before2 = $lines2[0..$insertAfterIndex2]
        $after2  = $lines2[($insertAfterIndex2 + 1)..($lines2.Length - 1)]
        $result2 = $before2 + $newField + $after2
        [System.IO.File]::WriteAllLines($fieldsFile, $result2, [System.Text.Encoding]::UTF8)
        Write-Status "show_sku field inserted after line $($insertAfterIndex2 + 1)." "OK"
    } else {
        Write-Status "Could not find show_category closing ),. Manual review needed." "FAIL"
    }
}

# Cleanup temp files
Remove-Item "$base\_sku_block.txt"   -Force -ErrorAction SilentlyContinue
Remove-Item "$base\add_show_sku.ps1" -Force -ErrorAction SilentlyContinue
Remove-Item "$base\find_lines.ps1"   -Force -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "=== Done ===" -ForegroundColor White
