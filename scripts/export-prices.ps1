# XLSX -> JSON for the prices post type. Requires PowerShell 5.1+, no Excel/modules.
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)][string]$InputPath,
    [string]$OutputPath = '.\prices-export.json',
    [string]$SheetName
)
$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Read-ZipXml([string]$Name) {
    $entry = $archive.GetEntry($Name)
    if (-not $entry) { throw "Missing XLSX entry: $Name" }
    $reader = [IO.StreamReader]::new($entry.Open())
    try { return [xml]$reader.ReadToEnd() } finally { $reader.Dispose() }
}
function Read-Cell($Cell) {
    if ($Cell.t -eq 'e') { throw "Excel error in cell $($Cell.r): $($Cell.v)" }
    if ($Cell.f -and $null -eq $Cell.v) { throw "Formula has no cached value: $($Cell.r). Save in Excel first." }
    if ($Cell.t -eq 's') { return $strings[[int]$Cell.v] }
    if ($Cell.t -eq 'inlineStr') { return $Cell.is.InnerText }
    return [string]$Cell.v
}
function Read-Number([string]$Text, [int]$RowNumber, [string]$Header) {
    $clean = ($Text -replace '\s', '') -replace ',', '.'
    $number = 0.0
    if (-not [double]::TryParse($clean, [Globalization.NumberStyles]::Float,
        [Globalization.CultureInfo]::InvariantCulture, [ref]$number) -or
        [double]::IsNaN($number) -or [double]::IsInfinity($number) -or $number -lt 0) {
        throw "Row ${RowNumber}: invalid number in '$Header': '$Text'"
    }
    return $number
}
function Read-Choice($Choices, [string]$Text, [int]$RowNumber, [string]$Header) {
    if (-not $Choices.ContainsKey($Text)) { throw "Row ${RowNumber}: unknown '$Header': '$Text'" }
    return $Choices[$Text]
}
$types = @{ 'Значение' = 'value'; 'Таблица' = 'table' }
$additionalChoices = @{ 'Да' = 1; 'Нет' = 0 }
$displays = @{ 'Рубли' = 'rub'; 'Рубли от' = 'rub_from'; 'Рубли за шт' = 'rub_per_item'; 'Проценты' = 'percent' }
$source = (Resolve-Path -LiteralPath $InputPath).Path
$destination = [IO.Path]::GetFullPath($OutputPath)
if ($source -eq $destination) { throw 'Input and output paths must differ.' }
$archive = [IO.Compression.ZipFile]::OpenRead($source)
try {
    $strings = @()
    if ($archive.GetEntry('xl/sharedStrings.xml')) {
        $stringsXml = Read-ZipXml 'xl/sharedStrings.xml'
        $strings = @($stringsXml.sst.si | ForEach-Object { $_.InnerText })
    }
    $workbook = Read-ZipXml 'xl/workbook.xml'
    $sheets = @($workbook.workbook.sheets.sheet)
    $sheet = $sheets[0]
    if ($SheetName) {
        $selected = @($sheets | Where-Object { $_.name -eq $SheetName })
        if ($selected.Count -ne 1) { throw "Sheet not found: $SheetName" }
        $sheet = $selected[0]
    }
    $relationships = Read-ZipXml 'xl/_rels/workbook.xml.rels'
    $sheetId = $sheet.GetAttribute('id', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')
    $relationship = @($relationships.Relationships.Relationship | Where-Object { $_.Id -eq $sheetId })[0]
    if (-not $relationship -or $relationship.TargetMode -eq 'External') { throw 'Invalid worksheet relationship.' }
    $uri = [Uri]::new([Uri]'http://xlsx.local/xl/workbook.xml', [string]$relationship.Target)
    $worksheet = Read-ZipXml $uri.AbsolutePath.TrimStart('/')
    $rows = @($worksheet.worksheet.sheetData.row)
    if ($rows.Count -lt 2) { throw 'Worksheet contains no price records.' }
    $headers = @{}
    foreach ($cell in $rows[0].c) {
        $label = (Read-Cell $cell).Trim()
        if (-not $label) { continue }
        if ($headers.ContainsKey($label)) { throw "Duplicate header: $label" }
        $headers[$label] = $cell.r -replace '\d', ''
    }
    foreach ($header in @('Наименование', 'Услуга', 'Тип', 'Дополнительная', 'Тип вывода', 'Значение')) {
        if (-not $headers.ContainsKey($header)) { throw "Missing header: $header" }
    }
    $pairIndices = @($headers.Keys | ForEach-Object {
        if ($_ -match '^Название\[(\d+)\]$') { [int]$Matches[1] }
    } | Sort-Object)
    foreach ($header in $headers.Keys) {
        if ($header -match '^(Название|Значение)\[(\d+)\]$') {
            $index = $Matches[2]
            if (-not $headers.ContainsKey("Название[$index]") -or -not $headers.ContainsKey("Значение[$index]")) {
                throw "Incomplete table headers for pair $index"
            }
        }
    }
    $records = [Collections.Generic.List[object]]::new()
    $identities = @{}
    $hashAlgorithm = [Security.Cryptography.SHA256]::Create()
    try {
        foreach ($row in $rows | Select-Object -Skip 1) {
            $cells = @{}
            foreach ($cell in $row.c) { $cells[$cell.r -replace '\d', ''] = (Read-Cell $cell).Trim() }
            if (-not @($cells.Values | Where-Object { $_ -ne '' }).Count) { continue }
            $rowNumber = [int]$row.r
            $title = [string]$cells[$headers['Наименование']]
            $services = @($cells[$headers['Услуга']] -split ',' | ForEach-Object { $_.Trim() } | Where-Object { $_ } | Select-Object -Unique)
            if (-not $title -or -not $services.Count) { throw "Row ${rowNumber}: title and services are required." }
            $type = Read-Choice $types $cells[$headers['Тип']] $rowNumber 'Тип'
            $additional = Read-Choice $additionalChoices $cells[$headers['Дополнительная']] $rowNumber 'Дополнительная'
            $display = Read-Choice $displays $cells[$headers['Тип вывода']] $rowNumber 'Тип вывода'
            $priceRows = @()
            $value = $null
            if ($type -eq 'value') {
                $value = Read-Number $cells[$headers['Значение']] $rowNumber 'Значение'
            } else {
                foreach ($index in $pairIndices) {
                    $name = [string]$cells[$headers["Название[$index]"]]
                    $amount = [string]$cells[$headers["Значение[$index]"]]
                    if (-not $name -and -not $amount) { continue }
                    if (-not $name -or -not $amount) { throw "Row ${rowNumber}: incomplete table pair $index" }
                    $priceRows += [ordered]@{ name = $name; value = (Read-Number $amount $rowNumber "Значение[$index]") }
                }
                if (-not $priceRows.Count) { throw "Row ${rowNumber}: table has no entries." }
            }
            # Identity excludes amounts and row number, so price edits/reordering update existing posts.
            $identity = @($title.ToLowerInvariant()) + @($services | ForEach-Object { $_.ToLowerInvariant() } | Sort-Object)
            $identityJson = ConvertTo-Json -InputObject $identity -Compress
            $hash = $hashAlgorithm.ComputeHash([Text.Encoding]::UTF8.GetBytes($identityJson))
            $key = ([BitConverter]::ToString($hash)).Replace('-', '').ToLowerInvariant()
            if ($identities.ContainsKey($key)) { throw "Duplicate price identity on rows $($identities[$key]) and $rowNumber" }
            $identities[$key] = $rowNumber
            $records.Add([ordered]@{
                source_key = $key; source_row = $rowNumber; post_title = $title; service_titles = $services
                acf = [ordered]@{
                    price_type = $type; price_additional = $additional; price_display_type = $display
                    price_value = $value; price_rows = $priceRows
                }
            })
        }
    } finally { $hashAlgorithm.Dispose() }
    if (-not $records.Count) { throw 'No price records found.' }
    $export = [ordered]@{
        format = 'shina-prices-v1'; source_file = [IO.Path]::GetFileName($source)
        sheet = [string]$sheet.name; records = @($records.ToArray())
    }
    [IO.File]::WriteAllText($destination, (ConvertTo-Json -InputObject $export -Depth 10), [Text.UTF8Encoding]::new($false))
    Write-Host "Exported $($records.Count) prices: $destination"
} finally { $archive.Dispose() }
