(function () {
    "use strict";

    const state = {
        slides: (window.JamViniSliderStudio?.slides || []).map(normalizeSlide),
        activeSlideId: null,
        selectedLayerId: null,
        dragging: null,
        preview: false,
    };

    const dom = {};

    document.addEventListener("DOMContentLoaded", init);

    function init() {
        dom.app = document.querySelector(".jvs-app");
        dom.stage = document.getElementById("jvsStage");
        dom.tabs = document.getElementById("jvsSlideTabs");
        dom.layers = document.getElementById("jvsLayerList");
        dom.inspector = document.getElementById("jvsInspector");
        dom.layerCount = document.getElementById("jvsLayerCount");
        dom.slideName = document.getElementById("jvsSlideName");
        dom.save = document.getElementById("jvsSaveBtn");
        dom.preview = document.getElementById("jvsPreviewBtn");
        dom.settings = document.getElementById("jvsSettingsPanel");

        state.activeSlideId = state.slides[0]?.id || null;

        dom.tabs?.addEventListener("click", (event) => {
            const button = event.target.closest("[data-slide-id]");
            if (!button) return;
            state.activeSlideId = Number(button.dataset.slideId);
            state.selectedLayerId = null;
            render();
        });

        document.querySelectorAll("[data-add-layer]").forEach((button) => {
            button.addEventListener("click", () => addLayer(button.dataset.addLayer));
        });

        dom.save?.addEventListener("click", saveCurrentSlide);
        dom.preview?.addEventListener("click", togglePreview);
        document.getElementById("jvsAddSlideBtn")?.addEventListener("click", createSlide);
        document.getElementById("jvsSettingsBtn")?.addEventListener("click", () => dom.settings.classList.add("is-open"));
        document.getElementById("jvsCloseSettings")?.addEventListener("click", () => dom.settings.classList.remove("is-open"));

        render();
    }

    function normalizeSlide(slide) {
        const layers = Array.isArray(slide.layers) && slide.layers.length ? slide.layers : defaultLayers(slide.name || "Slide");
        return { ...slide, background: slide.background || {}, layers: layers.map(normalizeLayer) };
    }

    function normalizeLayer(layer) {
        return {
            id: layer.id || uid("layer"),
            type: layer.type || "text",
            name: layer.name || labelFor(layer.type || "text"),
            content: layer.content || "",
            x: Number(layer.x ?? 10),
            y: Number(layer.y ?? 20),
            width: Number(layer.width ?? 30),
            height: Number(layer.height ?? 10),
            style: layer.style || {},
            link: layer.link || "",
            target: layer.target || "_self",
            src: layer.src || "",
            alt: layer.alt || "",
        };
    }

    function defaultLayers(title) {
        return [
            { id: uid("heading"), type: "heading", name: "Heading", content: title, x: 9, y: 28, width: 56, height: 16, style: { fontSize: 60, color: "#ffffff", fontWeight: 800, align: "left" } },
            { id: uid("text"), type: "text", name: "Text", content: "Add a supporting message for this slide.", x: 9, y: 48, width: 48, height: 12, style: { fontSize: 20, color: "#dbeafe", fontWeight: 400, align: "left" } },
            { id: uid("button"), type: "button", name: "Button", content: "Get Started", x: 9, y: 65, width: 16, height: 8, style: { fontSize: 16, color: "#0f172a", background: "#ffffff", radius: 12, fontWeight: 800, align: "center" }, link: "/" },
        ];
    }

    function activeSlide() {
        return state.slides.find((slide) => Number(slide.id) === Number(state.activeSlideId));
    }

    function selectedLayer() {
        return activeSlide()?.layers.find((layer) => layer.id === state.selectedLayerId);
    }

    function render() {
        renderTabs();
        renderStage();
        renderLayerList();
        renderInspector();
    }

    function renderTabs() {
        const slide = activeSlide();
        const addButton = document.getElementById("jvsAddSlideBtn");
        dom.tabs.querySelectorAll("[data-slide-id]").forEach((button) => button.remove());
        state.slides.forEach((item, index) => {
            const button = document.createElement("button");
            button.type = "button";
            button.dataset.slideId = item.id;
            button.textContent = `Slide ${index + 1}`;
            button.classList.toggle("is-active", Number(item.id) === Number(state.activeSlideId));
            dom.tabs.insertBefore(button, addButton);
        });
        if (dom.slideName) dom.slideName.textContent = slide?.name || "Slide";
    }

    function renderStage() {
        const slide = activeSlide();
        if (!slide) return;
        const bg = slide.background || {};
        dom.stage.style.backgroundImage = bg.image ? `url("${bg.image.replace(/"/g, '\\"')}")` : "";
        dom.stage.style.backgroundPosition = bg.position || "center center";
        dom.stage.style.setProperty("--jvs-overlay", bg.overlay || "rgba(15,23,42,.58)");
        dom.stage.innerHTML = slide.layers.map(layerHtml).join("");
        dom.stage.querySelectorAll(".jvs-layer").forEach((el) => {
            el.addEventListener("mousedown", startDrag);
            el.addEventListener("click", (event) => {
                event.stopPropagation();
                state.selectedLayerId = el.dataset.layerId;
                render();
            });
        });
        dom.stage.addEventListener("click", () => {
            state.selectedLayerId = null;
            render();
        }, { once: true });
    }

    function layerHtml(layer) {
        const style = layer.style || {};
        const css = [
            `left:${layer.x}%`, `top:${layer.y}%`, `width:${layer.width}%`, `height:${layer.height}%`,
            `font-size:${Number(style.fontSize || 18)}px`, `color:${style.color || "#ffffff"}`,
            `font-weight:${style.fontWeight || 400}`, `text-align:${style.align || "left"}`,
            `border-radius:${Number(style.radius || 0)}px`,
            layer.type === "button" || layer.type === "shape" ? `background:${style.background || (layer.type === "button" ? "#ffffff" : "rgba(255,255,255,.18)")}` : "",
            `justify-content:${alignToFlex(style.align || "left")}`,
        ].filter(Boolean).join(";");
        const selected = layer.id === state.selectedLayerId ? " is-selected" : "";
        const content = layer.type === "image"
            ? `<img src="${escapeAttr(layer.src || layer.content || "")}" alt="${escapeAttr(layer.alt || "")}">`
            : escapeHtml(layer.content || labelFor(layer.type));
        return `<div class="jvs-layer${selected}" data-layer-id="${escapeAttr(layer.id)}" data-type="${escapeAttr(layer.type)}" style="${escapeAttr(css)}">${content}</div>`;
    }

    function renderLayerList() {
        const slide = activeSlide();
        if (!slide) return;
        dom.layerCount.textContent = slide.layers.length;
        dom.layers.innerHTML = slide.layers.map((layer, index) => `
            <button type="button" class="jvs-layer-row ${layer.id === state.selectedLayerId ? "is-selected" : ""}" data-layer-row="${escapeAttr(layer.id)}">
                <span>${escapeHtml(layer.name || labelFor(layer.type))}</span>
                <small>${index + 1}</small>
            </button>
        `).join("");
        dom.layers.querySelectorAll("[data-layer-row]").forEach((button) => {
            button.addEventListener("click", () => {
                state.selectedLayerId = button.dataset.layerRow;
                render();
            });
        });
    }

    function renderInspector() {
        const slide = activeSlide();
        const layer = selectedLayer();
        if (!slide) return;
        if (!layer) {
            dom.inspector.innerHTML = `
                <label>Background Image <input data-slide-field="image" value="${escapeAttr(slide.background.image || "")}" placeholder="/storage/media/..."></label>
                <label>Overlay <input data-slide-field="overlay" value="${escapeAttr(slide.background.overlay || "rgba(15,23,42,.58)")}"></label>
                <label>Position <input data-slide-field="position" value="${escapeAttr(slide.background.position || "center center")}"></label>
                <p>Use an image saved in Media Library. Select a layer to edit text, colors, links, and position.</p>
            `;
            bindSlideInspector();
            return;
        }
        dom.inspector.innerHTML = `
            <label>Name <input data-layer-field="name" value="${escapeAttr(layer.name)}"></label>
            <label>Content <textarea data-layer-field="content">${escapeHtml(layer.content)}</textarea></label>
            ${layer.type === "image" ? `<label>Media Library URL <input data-layer-field="src" value="${escapeAttr(layer.src || layer.content)}" placeholder="/storage/media/..."></label><label>Alt Text <input data-layer-field="alt" value="${escapeAttr(layer.alt)}"></label>` : ""}
            ${(layer.type === "button") ? `<label>Link <input data-layer-field="link" value="${escapeAttr(layer.link)}"></label><label>Target <select data-layer-field="target"><option value="_self"${layer.target !== "_blank" ? " selected" : ""}>Same tab</option><option value="_blank"${layer.target === "_blank" ? " selected" : ""}>New tab</option></select></label>` : ""}
            <label>X <input type="number" data-layer-field="x" value="${layer.x}"></label>
            <label>Y <input type="number" data-layer-field="y" value="${layer.y}"></label>
            <label>Width <input type="number" data-layer-field="width" value="${layer.width}"></label>
            <label>Height <input type="number" data-layer-field="height" value="${layer.height}"></label>
            <label>Font Size <input type="number" data-style-field="fontSize" value="${Number(layer.style.fontSize || 18)}"></label>
            <label>Text Color <input type="color" data-style-field="color" value="${colorValue(layer.style.color || "#ffffff")}"></label>
            <label>Background <input ${colorInputType(layer.style.background)} data-style-field="background" value="${escapeAttr(layer.style.background || "#ffffff")}"></label>
            <label>Radius <input type="number" data-style-field="radius" value="${Number(layer.style.radius || 0)}"></label>
            <label>Align <select data-style-field="align"><option value="left"${(layer.style.align || "left") === "left" ? " selected" : ""}>Left</option><option value="center"${layer.style.align === "center" ? " selected" : ""}>Center</option><option value="right"${layer.style.align === "right" ? " selected" : ""}>Right</option></select></label>
            <button type="button" class="jvs-icon-btn" id="jvsDuplicateLayer">${icon("copy")} Duplicate Layer</button>
            <button type="button" class="jvs-icon-btn" id="jvsDeleteLayer">${icon("trash-2")} Delete Layer</button>
        `;
        bindLayerInspector(layer);
    }

    function bindSlideInspector() {
        dom.inspector.querySelectorAll("[data-slide-field]").forEach((input) => {
            input.addEventListener("input", () => {
                const slide = activeSlide();
                slide.background[input.dataset.slideField] = input.value;
                renderStage();
            });
        });
    }

    function bindLayerInspector(layer) {
        dom.inspector.querySelectorAll("[data-layer-field]").forEach((input) => {
            input.addEventListener("input", () => {
                const value = ["x", "y", "width", "height"].includes(input.dataset.layerField) ? Number(input.value) : input.value;
                layer[input.dataset.layerField] = value;
                if (layer.type === "image" && input.dataset.layerField === "src") layer.content = input.value;
                renderStage();
                renderLayerList();
            });
        });
        dom.inspector.querySelectorAll("[data-style-field]").forEach((input) => {
            input.addEventListener("input", () => {
                const key = input.dataset.styleField;
                layer.style[key] = ["fontSize", "radius"].includes(key) ? Number(input.value) : input.value;
                renderStage();
            });
        });
        document.getElementById("jvsDeleteLayer")?.addEventListener("click", () => {
            const slide = activeSlide();
            slide.layers = slide.layers.filter((item) => item.id !== layer.id);
            state.selectedLayerId = null;
            render();
        });
        document.getElementById("jvsDuplicateLayer")?.addEventListener("click", () => duplicateLayer(layer));
    }

    function addLayer(type) {
        const slide = activeSlide();
        const layer = normalizeLayer({
            id: uid(type),
            type,
            name: labelFor(type),
            content: defaultContent(type),
            src: type === "image" ? "" : undefined,
            x: 18,
            y: 20 + slide.layers.length * 6,
            width: type === "button" ? 18 : type === "image" ? 28 : 34,
            height: type === "heading" ? 14 : type === "button" ? 8 : type === "shape" ? 18 : 12,
            style: defaultStyle(type),
            link: type === "button" ? "/" : "",
        });
        slide.layers.push(layer);
        state.selectedLayerId = layer.id;
        render();
    }

    function duplicateLayer(layer) {
        const slide = activeSlide();
        const copy = normalizeLayer(JSON.parse(JSON.stringify(layer)));
        copy.id = uid(layer.type || "layer");
        copy.name = `${layer.name || labelFor(layer.type)} Copy`;
        copy.x = clamp(Number(layer.x || 0) + 3, 0, Math.max(0, 100 - Number(layer.width || 30)));
        copy.y = clamp(Number(layer.y || 0) + 3, 0, Math.max(0, 100 - Number(layer.height || 10)));
        slide.layers.push(copy);
        state.selectedLayerId = copy.id;
        render();
    }

    function startDrag(event) {
        event.preventDefault();
        event.stopPropagation();
        const layer = activeSlide().layers.find((item) => item.id === event.currentTarget.dataset.layerId);
        state.selectedLayerId = layer.id;
        const rect = dom.stage.getBoundingClientRect();
        state.dragging = { layer, rect, offsetX: event.clientX - rect.left - (layer.x / 100) * rect.width, offsetY: event.clientY - rect.top - (layer.y / 100) * rect.height };
        window.addEventListener("mousemove", dragMove);
        window.addEventListener("mouseup", dragEnd, { once: true });
        render();
    }

    function dragMove(event) {
        if (!state.dragging) return;
        const { layer, rect, offsetX, offsetY } = state.dragging;
        layer.x = clamp(((event.clientX - rect.left - offsetX) / rect.width) * 100, 0, 100 - layer.width);
        layer.y = clamp(((event.clientY - rect.top - offsetY) / rect.height) * 100, 0, 100 - layer.height);
        renderStage();
    }

    function dragEnd() {
        window.removeEventListener("mousemove", dragMove);
        state.dragging = null;
        render();
    }

    async function saveCurrentSlide() {
        const slide = activeSlide();
        if (!slide) return;
        await fetch(slide.saveUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.JamViniSliderStudio.csrfToken, Accept: "application/json" },
            body: JSON.stringify({ layers: slide.layers, background: slide.background }),
        });
        await saveSettings();
        toast("Studio saved");
    }

    async function createSlide() {
        const response = await fetch(window.JamViniSliderStudio.createSlideUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.JamViniSliderStudio.csrfToken, Accept: "application/json" },
            body: JSON.stringify({}),
        });
        const payload = await response.json();
        if (!payload.success) return;
        state.slides.push(normalizeSlide(payload.slide));
        state.activeSlideId = payload.slide.id;
        state.selectedLayerId = null;
        render();
        toast("Slide added");
    }

    async function saveSettings() {
        const settings = {
            height: Number(document.getElementById("jvsSettingHeight")?.value || 620),
            delay: Number(document.getElementById("jvsSettingDelay")?.value || 5500),
            speed: Number(document.getElementById("jvsSettingSpeed")?.value || 700),
            autoplay: document.getElementById("jvsSettingAutoplay")?.checked || false,
            navigation: document.getElementById("jvsSettingNavigation")?.checked || false,
            pagination: document.getElementById("jvsSettingPagination")?.checked || false,
        };
        await fetch(window.JamViniSliderStudio.settingsUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": window.JamViniSliderStudio.csrfToken, Accept: "application/json" },
            body: JSON.stringify({ settings }),
        });
    }

    function togglePreview() {
        state.preview = !state.preview;
        dom.app.classList.toggle("jvs-preview", state.preview);
        dom.preview.innerHTML = state.preview ? `${icon("pencil")} Edit` : `${icon("eye")} Preview`;
    }

    function toast(message) {
        const el = document.createElement("div");
        el.className = "jvs-toast";
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 1800);
    }

    function defaultStyle(type) {
        if (type === "button") return { fontSize: 16, color: "#0f172a", background: "#ffffff", radius: 12, fontWeight: 800, align: "center" };
        if (type === "shape") return { background: "rgba(255,255,255,.18)", radius: 18 };
        if (type === "heading") return { fontSize: 54, color: "#ffffff", fontWeight: 800, align: "left" };
        return { fontSize: 20, color: "#dbeafe", fontWeight: 400, align: "left" };
    }

    function defaultContent(type) {
        return { heading: "New Heading", text: "New text layer", button: "Button", image: "", shape: "" }[type] || "Layer";
    }

    function labelFor(type) {
        return { heading: "Heading", text: "Text", button: "Button", image: "Image", shape: "Shape" }[type] || "Layer";
    }

    function uid(prefix) {
        return `${prefix}-${Math.random().toString(36).slice(2, 9)}`;
    }

    function alignToFlex(align) {
        return align === "right" ? "flex-end" : align === "center" ? "center" : "flex-start";
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function escapeHtml(value) {
        return String(value ?? "").replace(/[&<>"']/g, (char) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" }[char]));
    }

    function escapeAttr(value) {
        return escapeHtml(value);
    }

    function colorValue(value) {
        return /^#[0-9a-f]{6}$/i.test(String(value || "")) ? value : "#ffffff";
    }

    function colorInputType(value) {
        return /^#[0-9a-f]{6}$/i.test(String(value || "")) ? 'type="color"' : 'type="text"';
    }

    function icon(name, size = 16) {
        const icons = {
            eye: '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
            pencil: '<path d="M21.2 8.4 8.6 21H3v-5.6L15.6 2.8a2 2 0 0 1 2.8 0l2.8 2.8a2 2 0 0 1 0 2.8Z"/><path d="m14 5 5 5"/>',
            copy: '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
            "trash-2": '<path d="M3 6h18"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><path d="M19 6l-1 14c0 1-1 2-2 2H8c-1 0-2-1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
        };
        return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${icons[name] || icons.eye}</svg>`;
    }
})();
