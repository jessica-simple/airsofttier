foreach($id in @('category-10','category-22','category-31','category-33','category-35','category-53')){
foreach($direction in @('asc','desc')){
 if(Test-Path catalog-data/pause.flag){break}
 $s=[Microsoft.PowerShell.Commands.WebRequestSession]::new()
 $s.Cookies.Add([System.Net.Cookie]::new("pagination[5][$id][page_size]",'36','/','www.paradiseonlinestorehk.com'))
 $s.Cookies.Add([System.Net.Cookie]::new('gridsort[5][field]','name','/','www.paradiseonlinestorehk.com'))
 $s.Cookies.Add([System.Net.Cookie]::new('gridsort[5][sort]',$direction,'/','www.paradiseonlinestorehk.com'))
 for($p=1;$p -le 5;$p++){$s.Cookies.Add([System.Net.Cookie]::new("pagination[5][$id][page_from]",[string]$p,'/','www.paradiseonlinestorehk.com'));try{$r=Invoke-WebRequest ('https://www.paradiseonlinestorehk.com/Category/'+$id) -WebSession $s;$r.Content | Set-Content -Encoding utf8 "catalog-data/name-$direction-$id-$p.html"}catch{break}}
}}
