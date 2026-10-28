$ErrorActionPreference='Stop'
function Decode([string]$s) { [System.Net.WebUtility]::HtmlDecode($s).Trim() }
function Encode([string]$s) { [System.Net.WebUtility]::HtmlEncode($s) }
$brands=@('Tokyo Marui','Double Bell','Double Eagle','Golden Eagle','King Arms','KJ Works','Classic Army','Asia Hobby','Army Armament','Angry Gun','Maple Leaf','Green Walther','Snow Wolf','Snow Peak','Bow Master','RA Tech','Stark Arms','Strike Industries','Action Army','Smith & Wesson','Smith&Wesson','KUNG FU','Guai Guai','Guns Modify','Lambda Defence','Hoone Gear','HOONE GEAR','TTI AIRSOFT','North East','Northeast','Kral Arms','T-Eagle','Z-Tactical','VFC/Umarex','Umarex/VFC','SRC/Umarex','VFC/SIG','GHK/Umarex','VFC','Umarex','WE','Marui','KWC','KSC','KWA','GHK','G&G','CYMA','A&K','APS','Ares','ASG','ICS','LCT','SRC','HFC','VORSK','Raven','RWA','PTS','EMG','APFG','MARUYAMA','Tanaka','Marushin','Maruzen','WELL PRO','WELLPRO','WELL','WINGUN','WG','GAMO','Diana','Evanix','Hatsan','Reximex','Snowpeak','SPA','COMETA','BSA','Baikal','Daisy','AirForce','EDgun','AEA','JSB','H&N','Skenco','ZAN','Funko','Mezco','Nintendo','Nerf','Bid Toy','PXS','CTM','COWCOW','Guarder','AIP','5KU','ACETECH','Eshooter','E-Shooter','OLIGHT','HOLOSUN','Novritsch','Novus','Vector Optics','Vector','Vortex','WADSN','SOTAC','SOETAC','WST','Wosport','FMA','IDOGEAR','AMOMAX','CYTAC','FOBUS','EARMOR','TOSHIBA','UNICORN','Parabellum','Crusader','AMG','Samoon','Showguns','SHOWSGUN','SAVIA','USHOT','TTI','40MAX','T238','BPW','NW','NorthWind','BOW','ETEE','SALVO','Advantage Airsoft','Advantage_Airsoft','Advantage','N77','PROW','HWO','TASK','BO','FCW','Raptor','Rossi','IMI','STRATOS','PDI','MILBRO','BBT','AKRIVA','SEAL','ELEMENT','LISTONE','GCTAC','AW','HLR','REBEL','MULIFANG','Huasheng','Guuun','UltraForce','ARMORED','UMX','DMGEAR','LDT','K&H','JULIET','LANCER','Recover','Hexmag','JDG','Irevalebat','KIZUNA','KRAKEN','Farsan','FALCON','DIAMONDBACK','ESD','ELKO','BOLT','BRS','ARCTURUS','B&T','FLUX','Modify','XYL','WASHAN','WASHAN/FARSAN','RAREARMS','Poseidon','UDL','UHC','WA','TA.A.Custom','Viper','Viva','Vomz','RGW','SIIS','CANiK','Battleaxe','DoublePow','DNA','Armytek','4UAD','LeHui','Mafioso','Mafio','Ferro','FAB','FireFox','Kampak','HooneGear','Magpul','MAGPUL®','Kingdom','Ceondoi','Evolution','Krytac','Hwasan','HuasHan') | Sort-Object Length -Descending
$pattern='^(?:'+(($brands | ForEach-Object {[regex]::Escape($_)}) -join '|')+')(?=\s|/|\(|-|$)'
$rows=[Collections.Generic.List[object]]::new()
$bad=[Collections.Generic.List[string]]::new()
foreach($f in Get-ChildItem catalog-data/product-*.html){
 $h=Get-Content $f -Raw
 $name=Decode ([regex]::Match($h,'class="product-name-text">([^<]+)').Groups[1].Value)
 $sku=Decode ([regex]::Match($h,'class="product-model-text">([^<]+)').Groups[1].Value)
 if(!$name -or !$sku){$bad.Add($f.Name);continue}
 $desc=[regex]::Match($h,'(?s)class="product-description-content">(.*?)</div>\s*</div>\s*</div>').Groups[1].Value
 $plain=Decode ($desc -replace '(?i)<br\s*/?>|</(?:div|p|li|tr)>',"`n" -replace '<[^>]+>','')
 $m=[regex]::Match($plain,'(?im)^[ \t]*(?:Brand|Manufacturer)[ \t]*[:：][ \t]*([^\r\n]+)')
 $brand='';$basis=''
 if($m.Success){$brand=$m.Groups[1].Value.Trim();$basis='Product description'}
 if(!$brand){
  $meta=[regex]::Match($h,'(?s)<meta name="description" content="(.*?)">').Groups[1].Value
  $meta=Decode $meta
  $m=[regex]::Match(($meta -replace '<[^>]+>',"`n"),'(?im)^[ \t]*(?:Brand|Manufacturer)[ \t]*[:：][ \t]*([^\r\n]+)')
  if($m.Success){$brand=$m.Groups[1].Value.Trim();$basis='Product description'}
 }
 if(!$brand){$clean=$name -replace '^(?i)(?:%OFF\s+|Pre-order\s*[:：-]?\s*)','';$m=[regex]::Match($clean,$pattern,'IgnoreCase');if($m.Success){$brand=$m.Value;$basis='Product name'}else{$brand='Not stated';$basis='Brand could not be identified reliably'}}
 $brand=($brand -split '(?i)(?:Capacity|Shooting Mode|Fire Mode|Material|Caliber|System|Weight|Model|Color|Colour|Length|Hop-Up|Power Source|Compatibility)\s*[:：]')[0].Trim()
 if(!$brand){$brand='Not stated';$basis='Brand could not be identified reliably'}
 $brand=$brand -replace '\s+',' '
 $rows.Add([pscustomobject]@{Brand=$brand;Product=$name;SKU=$sku;Basis=$basis;URL=('https://www.paradiseonlinestorehk.com/'+$f.BaseName)})
}
$rows=@($rows | Sort-Object Brand,Product,SKU)
$rows | ConvertTo-Json -Depth 4 | Set-Content -Encoding utf8 catalog-data/final-data.json
$bad | Set-Content catalog-data/missing-fields.txt
$known=@{};Get-ChildItem catalog-data/*.html | Where-Object {$_.Name -notlike 'product-*'} | ForEach-Object {$h=[string](Get-Content $_ -Raw);foreach($m in [regex]::Matches($h,'class="card product-card[^>]+href="(/product-\d+)" alt="([^"]*)"')){$known[$m.Groups[1].Value]=Decode $m.Groups[2].Value}}
$missing=@($known.Keys | Where-Object {!(Test-Path ('catalog-data/'+$_.Trim('/')+'.html'))})
$missing | Set-Content catalog-data/missing-pages.txt
$known | ConvertTo-Json | Set-Content catalog-data/all-discovered.json
$body=[Text.StringBuilder]::new()
foreach($r in $rows){[void]$body.AppendLine('<tr><td>'+ (Encode $r.Brand)+'</td><td><a href="'+$r.URL+'" target="_blank" rel="noopener">'+(Encode $r.Product)+'</a></td><td class="sku">'+(Encode $r.SKU)+'</td><td>'+ (Encode $r.Basis)+'</td></tr>')}
$note="Checked 26 October 2026. $($rows.Count) products with verified store SKUs. Product names are preserved as listed. Brand source identifies whether the brand comes from the description or product name. 'Not stated' means the brand could not be identified reliably. The store limits each listing/search to five pages; completeness is subject to the coverage note below."
$html=@'
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Paradise Hobby product catalog</title><style>
*{box-sizing:border-box}body{font:15px/1.5 system-ui,sans-serif;margin:0;color:#17232f;background:#f4f6f8}header{padding:32px 4vw 22px;background:#173b45;color:#fff}h1{font-size:28px;margin:0 0 10px}header p{max-width:1050px;color:#d5e6e9;margin:0}main{padding:24px 4vw}nav{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px}input,select,button{font:inherit;padding:10px 12px;border:1px solid #b6c5cc;border-radius:6px;background:white}input{width:min(560px,100%)}button{cursor:pointer}#count{margin-left:auto;color:#48606a}.wrap{overflow:auto;background:white;border:1px solid #d9e1e6;border-radius:8px;max-height:74vh}table{border-collapse:collapse;width:100%;font-size:14px}th{position:sticky;top:0;background:#e4edef;text-align:left;padding:12px 15px;cursor:pointer;white-space:nowrap}td{padding:11px 15px;border-top:1px solid #e8edf0;vertical-align:top}td:first-child{min-width:180px;max-width:320px}td:nth-child(2){min-width:400px}td:nth-child(4){font-size:12px;color:#58717c;min-width:130px}tbody tr:nth-child(even){background:#f8fafb}a{color:#136f83;text-decoration:none}a:hover{text-decoration:underline}.sku{font-family:ui-monospace,monospace;white-space:nowrap}footer{font-size:13px;color:#586e78;margin-top:16px;max-width:1050px}@media print{nav{display:none}.wrap{max-height:none;overflow:visible}th{position:static}header{color:black;background:white}header p{color:#444}}
</style></head><body><header><h1>Paradise Hobby · Product catalog</h1><p>__NOTE__</p></header><main><nav><input id="search" type="search" placeholder="Search brand, product name or SKU" aria-label="Search catalog"><select id="source" aria-label="Brand source"><option value="">All brand sources</option>Product description</option><option>Product name</option><option>Brand could not be identified reliably</option></select><button id="reset">Reset</button><span id="count"></span></nav><div class="wrap"><table><thead><tr><th>Brand ↕</th><th>Product name ↕</th><th>SKU ↕</th><th>Brand source ↕</th></tr></thead><tbody>__ROWS__</tbody></table></div><footer><p>Source: <a href="https://www.paradiseonlinestorehk.com/Category">Paradise Hobby public catalog</a>. Click any product name to view its source page. SKUs are the store's displayed SKU identifiers, which may differ from manufacturer model numbers.</p><p id="coverage">__COVERAGE__</p></footer></main><script>
const body=document.querySelector('tbody'),rows=[...body.rows],search=document.querySelector('#search'),source=document.querySelector('#source'),count=document.querySelector('#count');const texts=new Map(rows.map(r=>[r,r.textContent.toLowerCase()]));function filter(){const q=search.value.trim().toLowerCase();let n=0;for(const r of rows){r.hidden=!(texts.get(r).includes(q)&&(!source.value||r.cells[3].textContent===source.value));if(!r.hidden)n++}count.textContent=n.toLocaleString()+' of '+rows.length.toLocaleString()+' products'}search.addEventListener('input',filter);source.addEventListener('change',filter);document.querySelector('#reset').onclick=()=>{search.value='';source.value='';filter()};document.querySelectorAll('th').forEach((th,i)=>{let asc=true;th.onclick=()=>{rows.sort((a,b)=>a.cells[i].textContent.localeCompare(b.cells[i].textContent,undefined,{numeric:true})*(asc?1:-1));body.append(...rows);asc=!asc}});filter();
</script></body></html>
'@
$coverage="Enumerated $($known.Count) unique product links across category and search listings. $($missing.Count) discovered product pages remain unread; $($bad.Count) downloaded pages lacked a product name or SKU. The store's five-page listing cap means undiscovered products may remain; this is not a certified complete inventory."
$html=$html.Replace('__NOTE__',(Encode $note)).Replace('__ROWS__',$body.ToString()).Replace('__COVERAGE__',(Encode $coverage))
New-Item -ItemType Directory -Force outputs | Out-Null
$html | Set-Content -Encoding utf8 outputs/paradise-hobby-products.html
Write-Output $coverage
Write-Output "Rows: $($rows.Count); Not stated: $(@($rows | Where-Object Brand -eq 'Not stated').Count)"
$rows | Select-Object -First 10 | Format-Table Brand,Product,SKU

