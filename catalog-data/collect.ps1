$base='https://www.paradiseonlinestorehk.com'
1..117 | ForEach-Object -Parallel {
 $p=$_; $s=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
 $s.Cookies.Add([System.Net.Cookie]::new('pagination[5][Category][page_size]','36','/','www.paradiseonlinestorehk.com'))
 $s.Cookies.Add([System.Net.Cookie]::new('pagination[5][Category][page_from]',[string]$p,'/','www.paradiseonlinestorehk.com'))
 $r=Invoke-WebRequest 'https://www.paradiseonlinestorehk.com/Category' -WebSession $s
 $r.Content | Set-Content -Encoding utf8 ('catalog-data/list-'+$p+'.html')
} -ThrottleLimit 4
$items=@{}; Get-ChildItem catalog-data/list-*.html | ForEach-Object {
 $h=Get-Content $_ -Raw
 foreach($m in [regex]::Matches($h,'class="card product-card[^>]+href="(/product-\d+)" alt="([^"]*)"')) { $items[$m.Groups[1].Value]=[System.Net.WebUtility]::HtmlDecode($m.Groups[2].Value).Trim() }
}
$items | ConvertTo-Json | Set-Content catalog-data/products.json
Write-Output ('Unique products: '+$items.Count)
$items.Keys | ForEach-Object -Parallel {
 $url=$_; $f='catalog-data/'+$url.Trim('/')+'.html'
 if(!(Test-Path $f)) {
  for($try=0;$try -lt 3;$try++) { try { $r=Invoke-WebRequest ('https://www.paradiseonlinestorehk.com'+$url); $r.Content | Set-Content -Encoding utf8 $f; break } catch { if($try -eq 2) {Write-Output ('FAILED '+$url)} } }
 }
} -ThrottleLimit 4
Write-Output 'Download complete'
