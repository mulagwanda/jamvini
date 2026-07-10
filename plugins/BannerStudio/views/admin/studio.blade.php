<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Banner Studio - {{ $banner->title }}</title>
    <style>
        *{box-sizing:border-box} body{margin:0;font-family:Inter,ui-sans-serif,system-ui;background:#eef2f7;color:#0f172a}.bs-app{height:100vh;display:grid;grid-template-columns:78px minmax(0,1fr)340px;grid-template-rows:64px 1fr}.bs-top{grid-column:1/-1;background:#fff;border-bottom:1px solid #dbe3ee;display:flex;align-items:center;justify-content:space-between;padding:0 16px}.bs-brand{display:flex;align-items:center;gap:12px}.bs-brand a,.bs-icon{width:36px;height:36px;border:1px solid #dbe3ee;border-radius:8px;display:grid;place-items:center;background:#fff;color:#0f172a;text-decoration:none}.bs-tools{background:#fff;border-right:1px solid #dbe3ee;padding:10px;display:grid;align-content:start;gap:8px}.bs-tools button{border:1px solid #dbe3ee;background:#fff;border-radius:8px;min-height:56px;display:grid;place-items:center;gap:3px;font-weight:800;font-size:11px;cursor:pointer}.bs-stage-wrap{padding:22px;display:grid;grid-template-rows:auto minmax(0,1fr);gap:12px;overflow:auto}.bs-stage-toolbar{display:flex;justify-content:space-between;align-items:center;color:#64748b;font-weight:700}.bs-stage{position:relative;min-height:var(--bs-height,620px);border-radius:var(--bs-radius,0);overflow:hidden;background:#0f172a center/cover no-repeat;box-shadow:0 24px 70px rgba(15,23,42,.18);isolation:isolate}.bs-stage:before{content:"";position:absolute;inset:0;background:var(--bs-overlay,rgba(15,23,42,.35));z-index:0}.bs-layer{position:absolute;z-index:1;display:flex;align-items:center;min-width:24px;min-height:20px;padding:4px;cursor:move;outline:1px solid transparent;overflow:hidden}.bs-layer.is-selected{outline:2px solid #7a5cff;background:rgba(122,92,255,.08)}.bs-layer img{width:100%;height:100%;object-fit:contain;display:block}.bs-layer[data-type=button]{text-decoration:none}.bs-layer[data-type=domain-search] input{width:68%;height:100%;border:0;border-radius:8px 0 0 8px;padding:0 12px}.bs-layer[data-type=domain-search] button{width:32%;height:100%;border:0;border-radius:0 8px 8px 0;background:#7a5cff;color:#fff;font-weight:800}.bs-right{background:#fff;border-left:1px solid #dbe3ee;overflow:auto}.bs-panel{border-bottom:1px solid #dbe3ee}.bs-panel-head{padding:14px 16px;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;align-items:center}.bs-panel-body{padding:14px 16px;display:grid;gap:10px}.bs-layer-row{border:1px solid #dbe3ee;background:#fff;border-radius:8px;padding:10px;display:flex;justify-content:space-between;cursor:pointer}.bs-layer-row.is-selected{border-color:#7a5cff;color:#5b21b6;background:#f5f3ff}.bs-field{display:grid;gap:5px;font-size:12px;font-weight:800;color:#475569}.bs-field input,.bs-field textarea,.bs-field select{width:100%;border:1px solid #dbe3ee;border-radius:8px;padding:9px;font:inherit;font-weight:500}.bs-actions{display:flex;gap:8px}.bs-primary,.bs-outline{border:0;border-radius:8px;padding:10px 14px;font-weight:900;cursor:pointer}.bs-primary{background:#2f6f73;color:#fff}.bs-outline{background:#fff;border:1px solid #dbe3ee;color:#0f172a}.bs-danger{background:#fee2e2;color:#991b1b}@media(max-width:980px){.bs-app{grid-template-columns:64px 1fr;grid-template-rows:64px minmax(0,1fr)380px}.bs-right{grid-column:1/-1;border-left:0;border-top:1px solid #dbe3ee}.bs-stage{min-height:460px}}
    </style>
</head>
<body>
<div class="bs-app">
    <header class="bs-top">
        <div class="bs-brand">
            <a href="{{ route('admin.banner-studio.index') }}">{{ jv_icon('arrow-left', '', 16) }}</a>
            <div><strong>{{ $banner->title }}</strong><br><small>Shortcode: [banner slug="{{ $banner->slug }}"]</small></div>
        </div>
        <div class="bs-actions">
            <a href="{{ route('banner-studio.render', $banner->slug) }}" target="_blank" class="bs-outline" style="text-decoration:none;">Preview</a>
            <button class="bs-primary" id="bsSave">Save Banner</button>
        </div>
    </header>
    <aside class="bs-tools">
        <button data-add="heading">{{ jv_icon('heading-2', '', 18) }}<span>Heading</span></button>
        <button data-add="text">{{ jv_icon('type', '', 18) }}<span>Text</span></button>
        <button data-add="button">{{ jv_icon('square-mouse-pointer', '', 18) }}<span>Button</span></button>
        <button data-add="image">{{ jv_icon('image', '', 18) }}<span>Image</span></button>
        <button data-add="shape">{{ jv_icon('shapes', '', 18) }}<span>Shape</span></button>
        <button data-add="domain-search">{{ jv_icon('search', '', 18) }}<span>Search</span></button>
    </aside>
    <main class="bs-stage-wrap">
        <div class="bs-stage-toolbar"><span>Drag layers on the banner</span><span id="bsStatus">Ready</span></div>
        <section class="bs-stage" id="bsStage"></section>
    </main>
    <aside class="bs-right">
        <section class="bs-panel">
            <div class="bs-panel-head"><strong>Banner</strong></div>
            <div class="bs-panel-body" id="bsBannerPanel"></div>
        </section>
        <section class="bs-panel">
            <div class="bs-panel-head"><strong>Layers</strong><span id="bsLayerCount">0</span></div>
            <div class="bs-panel-body" id="bsLayerList"></div>
        </section>
        <section class="bs-panel">
            <div class="bs-panel-head"><strong>Inspector</strong></div>
            <div class="bs-panel-body" id="bsInspector"></div>
        </section>
    </aside>
</div>
<script>
window.JamViniBannerStudio = {
    csrfToken: @json(csrf_token()),
    saveUrl: @json(route('admin.banner-studio.studio.save', $banner)),
    settings: @json($banner->settings ?: []),
    layers: @json($banner->layers ?: []),
};
</script>
<script>
(() => {
    const state = {
        settings: Object.assign({height:620,radius:0,backgroundType:"gradient",backgroundColor:"#0f172a",backgroundGradient:"linear-gradient(135deg, #0f172a 0%, #214f54 48%, #7a5cff 100%)",backgroundImage:"",backgroundPosition:"center center",overlay:"rgba(15,23,42,.35)"}, window.JamViniBannerStudio.settings || {}),
        layers: (window.JamViniBannerStudio.layers || []).map(normalizeLayer),
        selected: null,
        dragging: null,
    };
    const stage = document.getElementById("bsStage"), list = document.getElementById("bsLayerList"), inspector = document.getElementById("bsInspector"), bannerPanel = document.getElementById("bsBannerPanel"), status = document.getElementById("bsStatus");
    document.querySelectorAll("[data-add]").forEach(btn => btn.addEventListener("click", () => addLayer(btn.dataset.add)));
    document.getElementById("bsSave").addEventListener("click", save);
    render();
    function normalizeLayer(layer){return {id:layer.id||uid("layer"),type:layer.type||"text",name:layer.name||label(layer.type||"text"),content:layer.content||"",link:layer.link||"",target:layer.target||"_self",src:layer.src||"",alt:layer.alt||"",x:Number(layer.x??10),y:Number(layer.y??20),width:Number(layer.width??34),height:Number(layer.height??10),style:Object.assign({fontSize:18,color:"#fff",fontWeight:500,align:"left",background:"",radius:0,letterSpacing:0},layer.style||{})}}
    function render(){renderStage();renderList();renderBannerPanel();renderInspector()}
    function renderStage(){stage.style.setProperty("--bs-height", `${Number(state.settings.height||620)}px`);stage.style.setProperty("--bs-radius", `${Number(state.settings.radius||0)}px`);stage.style.setProperty("--bs-overlay", state.settings.overlay||"transparent");stage.style.backgroundImage = state.settings.backgroundType==="image"&&state.settings.backgroundImage ? `url("${state.settings.backgroundImage.replace(/"/g,'\\"')}")` : state.settings.backgroundGradient;stage.style.backgroundColor=state.settings.backgroundColor||"#0f172a";stage.style.backgroundPosition=state.settings.backgroundPosition||"center center";stage.innerHTML=state.layers.map(layerHtml).join("");stage.querySelectorAll(".bs-layer").forEach(el=>{el.addEventListener("mousedown",startDrag);el.addEventListener("click",e=>{e.stopPropagation();state.selected=el.dataset.id;render()})});stage.onclick=()=>{state.selected=null;render()}}
    function layerHtml(l){const s=l.style||{};const css=`left:${l.x}%;top:${l.y}%;width:${l.width}%;height:${l.height}%;font-size:${Number(s.fontSize||18)}px;color:${s.color||"#fff"};font-weight:${s.fontWeight||500};text-align:${s.align||"left"};justify-content:${flex(s.align)};background:${(l.type==="button"||l.type==="shape")?(s.background||(l.type==="button"?"#fff":"rgba(255,255,255,.16)")):"transparent"};border-radius:${Number(s.radius||0)}px;letter-spacing:${Number(s.letterSpacing||0)}px;`;let html=escape(l.content||label(l.type));if(l.type==="image") html=`<img src="${attr(l.src||l.content)}" alt="${attr(l.alt)}">`;if(l.type==="domain-search") html=`<input placeholder="${attr(l.content||"Search domain...")}"><button>Search</button>`;return `<div class="bs-layer ${l.id===state.selected?"is-selected":""}" data-id="${attr(l.id)}" data-type="${attr(l.type)}" style="${attr(css)}">${html}</div>`}
    function renderList(){document.getElementById("bsLayerCount").textContent=state.layers.length;list.innerHTML=state.layers.map((l,i)=>`<button class="bs-layer-row ${l.id===state.selected?"is-selected":""}" data-row="${attr(l.id)}"><span>${escape(l.name||label(l.type))}</span><small>${i+1}</small></button>`).join("");list.querySelectorAll("[data-row]").forEach(btn=>btn.onclick=()=>{state.selected=btn.dataset.row;render()})}
    function renderBannerPanel(){bannerPanel.innerHTML=`${field("height","Height","number",state.settings.height)}${field("radius","Radius","number",state.settings.radius)}${select("backgroundType","Background Type",state.settings.backgroundType,[["gradient","Gradient"],["image","Image"],["color","Color"]])}${colorField("backgroundColor","Color",state.settings.backgroundColor)}${field("backgroundGradient","Gradient","text",state.settings.backgroundGradient)}${field("backgroundImage","Media Library URL","text",state.settings.backgroundImage)}${field("backgroundPosition","Position","text",state.settings.backgroundPosition)}${field("overlay","Overlay","text",state.settings.overlay)}`;bannerPanel.querySelectorAll("[data-setting]").forEach(input=>input.oninput=()=>{state.settings[input.dataset.setting]=input.value;renderStage()})}
    function renderInspector(){const l=state.layers.find(x=>x.id===state.selected);if(!l){inspector.innerHTML="<p>Select a layer to edit it. Use images saved in Media Library for image layers.</p>";return}inspector.innerHTML=`${field("name","Name","text",l.name,"layer")}${textarea("content","Content",l.content)}${l.type==="image"?field("src","Media Library URL","text",l.src,"layer")+field("alt","Alt Text","text",l.alt,"layer"):""}${l.type==="button"?field("link","Link","text",l.link,"layer")+select("target","Target",l.target,[["_self","Same tab"],["_blank","New tab"]],"layer"):""}${field("x","X","number",l.x,"layer")}${field("y","Y","number",l.y,"layer")}${field("width","Width","number",l.width,"layer")}${field("height","Height","number",l.height,"layer")}${field("fontSize","Font Size","number",l.style.fontSize,"style")}${colorField("color","Text Color",l.style.color,"style")}${colorField("background","Background",l.style.background,"style")}${field("fontWeight","Weight","number",l.style.fontWeight,"style")}${field("radius","Radius","number",l.style.radius,"style")}${field("letterSpacing","Letter Spacing","number",l.style.letterSpacing,"style")}${select("align","Align",l.style.align,[["left","Left"],["center","Center"],["right","Right"]],"style")}<button class="bs-primary" id="bsDuplicate">Duplicate Layer</button><button class="bs-primary bs-danger" id="bsDelete">Delete Layer</button>`;inspector.querySelectorAll("[data-layer]").forEach(input=>input.oninput=()=>{const key=input.dataset.layer;l[key]=numKey(key)?Number(input.value):input.value;renderStage();renderList()});inspector.querySelectorAll("[data-style]").forEach(input=>input.oninput=()=>{const key=input.dataset.style;l.style[key]=["fontSize","fontWeight","radius","letterSpacing"].includes(key)?Number(input.value):input.value;renderStage()});document.getElementById("bsDelete").onclick=()=>{state.layers=state.layers.filter(x=>x.id!==l.id);state.selected=null;render()};document.getElementById("bsDuplicate").onclick=()=>duplicateLayer(l)}
    function addLayer(type){const l=normalizeLayer({id:uid(type),type,name:label(type),content:defaultContent(type),x:16,y:18+state.layers.length*6,width:type==="button"?17:type==="image"?30:type==="domain-search"?42:34,height:type==="heading"?14:type==="shape"?18:8,style:defaultStyle(type),link:type==="button"?"/":""});state.layers.push(l);state.selected=l.id;render()}
    function duplicateLayer(layer){const copy=normalizeLayer(JSON.parse(JSON.stringify(layer)));copy.id=uid(copy.type||"layer");copy.name=`${copy.name||label(copy.type)} Copy`;copy.x=Math.max(0,Math.min(100-copy.width,copy.x+3));copy.y=Math.max(0,Math.min(100-copy.height,copy.y+3));state.layers.push(copy);state.selected=copy.id;render()}
    function startDrag(e){e.preventDefault();const l=state.layers.find(x=>x.id===e.currentTarget.dataset.id);state.dragging={l,startX:e.clientX,startY:e.clientY,x:l.x,y:l.y,w:stage.clientWidth,h:stage.clientHeight};document.addEventListener("mousemove",drag);document.addEventListener("mouseup",stopDrag,{once:true})}
    function drag(e){if(!state.dragging)return;const d=state.dragging;d.l.x=Math.max(0,Math.min(100,d.x+((e.clientX-d.startX)/d.w)*100));d.l.y=Math.max(0,Math.min(100,d.y+((e.clientY-d.startY)/d.h)*100));renderStage()}
    function stopDrag(){state.dragging=null;document.removeEventListener("mousemove",drag);renderInspector()}
    async function save(){status.textContent="Saving...";const res=await fetch(window.JamViniBannerStudio.saveUrl,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":window.JamViniBannerStudio.csrfToken,Accept:"application/json"},body:JSON.stringify({settings:state.settings,layers:state.layers})});status.textContent=res.ok?"Saved":"Save failed";setTimeout(()=>status.textContent="Ready",2000)}
    function field(k,label,type,value,scope="setting"){return `<label class="bs-field">${label}<input type="${type}" data-${scope}="${attr(k)}" value="${attr(value??"")}"></label>`}
    function colorField(k,label,value,scope="setting"){const v=/^#[0-9a-f]{6}$/i.test(String(value||""))?value:"#ffffff";return `<label class="bs-field">${label}<input type="color" data-${scope}="${attr(k)}" value="${attr(v)}"></label>`}
    function textarea(k,label,value){return `<label class="bs-field">${label}<textarea data-layer="${attr(k)}">${escape(value??"")}</textarea></label>`}
    function select(k,label,value,options,scope="setting"){return `<label class="bs-field">${label}<select data-${scope}="${attr(k)}">${options.map(o=>`<option value="${attr(o[0])}"${value===o[0]?" selected":""}>${escape(o[1])}</option>`).join("")}</select></label>`}
    function defaultStyle(t){if(t==="button")return{fontSize:16,color:"#0f172a",background:"#ffffff",fontWeight:800,align:"center",radius:8,letterSpacing:0};if(t==="shape")return{background:"rgba(255,255,255,.16)",radius:16};if(t==="heading")return{fontSize:52,color:"#ffffff",fontWeight:900,align:"left",letterSpacing:0};return{fontSize:18,color:"#dbeafe",fontWeight:500,align:"left",letterSpacing:0}}
    function defaultContent(t){return{heading:"Big hosting banner",text:"Supporting banner text",button:"Get Started",image:"",shape:"", "domain-search":"Search your domain..."}[t]||"Layer"}
    function label(t){return{"domain-search":"Domain Search",heading:"Heading",text:"Text",button:"Button",image:"Image",shape:"Shape"}[t]||"Layer"}
    function flex(a){return a==="center"?"center":a==="right"?"flex-end":"flex-start"} function numKey(k){return["x","y","width","height"].includes(k)} function uid(p){return `${p}-${Math.random().toString(36).slice(2,8)}`} function escape(s){return String(s??"").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[m]))} function attr(s){return escape(s)}
})();
</script>
</body>
</html>
