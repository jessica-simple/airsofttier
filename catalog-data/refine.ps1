$x=Get-Content catalog-data/all-discovered.json -Raw | ConvertFrom-Json -AsHashtable
$terms=@($x.Values | Where-Object {$_ -match '^(?i)(VFC|WE)\s'} | ForEach-Object {(($_ -split '\s+')[0..1] -join ' ')} | Sort-Object -Unique)
foreach($term in $terms){
 if(Test-Path catalog-data/pause.flag){break}
 $tag=[Convert]::ToHexString([Text.Encoding]::UTF8.GetBytes($term));$s=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
 $s.Cookies.Add([System.Net.Cookie]::new('pagination[5][Category][page_size]','36','/','www.paradiseonlinestorehk.com'))
 $s.Cookies.Add([System.Net.Cookie]::new('search[5][product_name]',[uri]::EscapeDataString($term),'/','www.paradiseonlinestorehk.com'))
 for($p=1;$p -le 5;$p++){$s.Cookies.Add([System.Net.Cookie]::new('pagination[5][Category][page_from]',[string]$p,'/','www.paradiseonlinestorehk.com'));try{$r=Invoke-WebRequest 'https://www.paradiseonlinestorehk.com/Category' -WebSession $s;$r.Content | Set-Content -Encoding utf8 "catalog-data/s-$tag-$p.html"}catch{break};$n=[int][regex]::Match($r.Content,'data-page-total="(\d+)"').Groups[1].Value;if($p -ge $n){break}}
}
