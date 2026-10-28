$existing=Get-Content catalog-data/products-expanded.json -Raw | ConvertFrom-Json -AsHashtable
$extra=@{}
Get-ChildItem catalog-data/s-*.html | ForEach-Object {$h=Get-Content $_ -Raw; foreach($m in [regex]::Matches($h,'class="card product-card[^>]+href="(/product-\d+)" alt="([^"]*)"')){$url=$m.Groups[1].Value;if(!$existing.ContainsKey($url)){$extra[$url]=[System.Net.WebUtility]::HtmlDecode($m.Groups[2].Value).Trim()}}}
$extra | ConvertTo-Json | Set-Content catalog-data/extra.json
Write-Output ('Additional products: '+$extra.Count)
$extra.Keys | ForEach-Object -Parallel {$u=$_;$f='catalog-data/'+$u.Trim('/')+'.html';if(!(Test-Path $f)){try{$r=Invoke-WebRequest ('https://www.paradiseonlinestorehk.com'+$u);$r.Content | Set-Content -Encoding utf8 $f}catch{Write-Output ('FAILED '+$u)}}} -ThrottleLimit 6
