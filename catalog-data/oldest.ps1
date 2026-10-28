$cats=Get-ChildItem catalog-data/c-*-1.html | ForEach-Object {$h=[string](Get-Content $_ -Raw);if($h -match 'data-total="180"'){($_.BaseName -replace '^c-','' -replace '-1$','')}}
foreach($id in $cats){
 if(Test-Path catalog-data/pause.flag){break}
 $s=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
 $s.Cookies.Add([System.Net.Cookie]::new("pagination[5][$id][page_size]",'36','/','www.paradiseonlinestorehk.com'))
 $s.Cookies.Add([System.Net.Cookie]::new('gridsort[5][field]','date','/','www.paradiseonlinestorehk.com'))
 $s.Cookies.Add([System.Net.Cookie]::new('gridsort[5][sort]','asc','/','www.paradiseonlinestorehk.com'))
 for($p=1;$p -le 5;$p++){
 $s.Cookies.Add([System.Net.Cookie]::new("pagination[5][$id][page_from]",[string]$p,'/','www.paradiseonlinestorehk.com'))
 try{$r=Invoke-WebRequest ('https://www.paradiseonlinestorehk.com/Category/'+$id) -WebSession $s;$r.Content | Set-Content -Encoding utf8 "catalog-data/old-$id-$p.html"}catch{Write-Output ('Error '+$id);break}
 }
}
