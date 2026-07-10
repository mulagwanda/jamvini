/**
 * JamVini Builder
 *
 * A schema-driven page builder for CMS pages. The saved data format is kept
 * compatible with the existing frontend renderer:
 * [{ type, data, styles }]
 */
(function () {
    "use strict";

    const BLOCKS = {
        row: {
            label: "Columns",
            icon: "C",
            description: "Multi-column layout",
            defaults: {
                type: "row",
                data: {
                    columns: [
                        { width: "50%", blocks: [] },
                        { width: "50%", blocks: [] },
                    ],
                },
                styles: sectionStyles("24px 0", "transparent", false),
            },
            fields: [
                { kind: "notice", text: "Columns can contain other blocks. Click a column on the canvas to edit its width and background." },
            ],
        },
        hero: {
            label: "Hero Section",
            icon: "H",
            description: "Full-width banner with CTA",
            defaults: {
                type: "hero",
                data: {
                    eyebrow: "Trusted by businesses across Tanzania",
                    heading: "Hosting that actually flies.",
                    subtitle: "SSD-powered servers, free SSL & domain, and a 99.9% uptime guarantee.",
                    showDomainSearch: true,
                    primaryBtnText: "Get Started",
                    primaryBtnLink: "/hosting",
                    secondaryBtnText: "",
                    secondaryBtnLink: "/contact",
                    bgType: "gradient",
                    bgImage: "",
                    overlayOpacity: "0.4",
                    align: "center",
                    fullHeight: false,
                },
                styles: sectionStyles("0", "transparent", true),
            },
            fields: [
                text("eyebrow", "Eyebrow"),
                text("heading", "Heading"),
                textarea("subtitle", "Subtitle", 2),
                toggle("showDomainSearch", "Show domain search"),
                divider("Buttons"),
                text("primaryBtnText", "Primary button text"),
                text("primaryBtnLink", "Primary button link"),
                text("secondaryBtnText", "Secondary button text"),
                text("secondaryBtnLink", "Secondary button link"),
                divider("Background"),
                select("bgType", "Background type", [["gradient", "Gradient"], ["image", "Image"]]),
                text("bgImage", "Background image URL"),
                range("overlayOpacity", "Image overlay opacity", 0, 1, 0.05),
                select("align", "Alignment", [["left", "Left"], ["center", "Center"]]),
                toggle("fullHeight", "Full viewport height"),
            ],
        },
        heading: {
            label: "Heading",
            icon: "T",
            description: "Section title",
            defaults: {
                type: "heading",
                data: {
                    eyebrow: "",
                    content: "Section Heading",
                    subheading: "",
                    tag: "h2",
                    fontSize: "32",
                    color: "#0f172a",
                    align: "center",
                },
                styles: sectionStyles("8px 0", "transparent", false),
            },
            fields: [
                text("eyebrow", "Eyebrow"),
                text("content", "Heading"),
                textarea("subheading", "Subheading", 2),
                select("tag", "HTML tag", [["h1", "H1"], ["h2", "H2"], ["h3", "H3"], ["h4", "H4"]]),
                number("fontSize", "Font size", 12, 120),
                color("color", "Color"),
                align(),
            ],
        },
        text: {
            label: "Text",
            icon: "P",
            description: "Paragraph content",
            defaults: {
                type: "text",
                data: {
                    content: "Your text here...",
                    tag: "p",
                    fontSize: "16",
                    color: "#1e293b",
                    align: "left",
                },
                styles: sectionStyles("16px 0", "transparent", false),
            },
            fields: [
                select("tag", "HTML tag", [["p", "Paragraph"], ["h2", "H2"], ["h3", "H3"], ["h4", "H4"]]),
                textarea("content", "Content", 5),
                number("fontSize", "Font size", 8, 100),
                color("color", "Color"),
                align(),
            ],
        },
        "feature-card": {
            label: "Feature Card",
            icon: "F",
            description: "Icon, title, description",
            defaults: {
                type: "feature-card",
                data: {
                    icon: "⚡",
                    title: "Feature Title",
                    description: "Feature description goes here.",
                    iconStyle: "filled",
                    iconColor: "#6C5CE7",
                    iconPosition: "top",
                    align: "center",
                },
                styles: sectionStyles("16px", "#ffffff", false),
            },
            fields: [
                text("icon", "Icon"),
                text("title", "Title"),
                textarea("description", "Description", 3),
                select("iconStyle", "Icon style", [["filled", "Filled"], ["outline", "Outline"], ["plain", "Plain"]]),
                color("iconColor", "Icon color"),
                select("iconPosition", "Icon position", [["top", "Top"], ["left", "Left"]]),
                align(),
            ],
        },
        "pricing-table": {
            label: "Pricing Table",
            icon: "$",
            description: "Pulls plans from Services",
            defaults: {
                type: "pricing-table",
                data: {
                    eyebrow: "Pricing",
                    heading: "Plans for every stage",
                    subheading: "Start small and scale as you grow.",
                    showToggle: true,
                    services: [],
                },
                styles: sectionStyles("80px 0", "#ffffff", false),
            },
            fields: [
                text("eyebrow", "Eyebrow"),
                text("heading", "Heading"),
                textarea("subheading", "Subheading", 2),
                toggle("showToggle", "Show monthly/annual toggle"),
                { kind: "notice", text: "Plans are pulled from the Services plugin on the public page." },
            ],
        },
        cta: {
            label: "Call to Action",
            icon: "A",
            description: "Conversion banner",
            defaults: {
                type: "cta",
                data: {
                    heading: "Ready to launch?",
                    text: "Join businesses already running on our platform.",
                    buttonText: "Get Started Today",
                    buttonLink: "/hosting",
                    buttonStyle: "light",
                    bgColor: "#6C5CE7",
                },
                styles: sectionStyles("60px 0", "#6C5CE7", true),
            },
            fields: [
                text("heading", "Heading"),
                textarea("text", "Text", 3),
                text("buttonText", "Button text"),
                text("buttonLink", "Button link"),
                select("buttonStyle", "Button style", [["light", "Light"], ["primary", "Primary"], ["outline", "Outline"]]),
                color("bgColor", "Background color"),
            ],
        },
        image: {
            label: "Image",
            icon: "I",
            description: "Image with alt text",
            defaults: {
                type: "image",
                data: { src: "", alt: "", width: "100%", align: "center" },
                styles: sectionStyles("16px 0", "transparent", false),
            },
            fields: [
                text("src", "Image URL"),
                text("alt", "Alt text"),
                text("width", "Width"),
                align(),
            ],
        },
        quote: {
            label: "Quote",
            icon: "Q",
            description: "Customer quote or testimonial",
            defaults: {
                type: "quote",
                data: {
                    quote: "JamVini gives our team one calm place to manage hosting, domains, billing, and support.",
                    author: "Happy Client",
                    role: "Business Owner",
                    accentColor: "#2f6f73",
                    textColor: "#0f172a",
                    align: "left",
                },
                styles: sectionStyles("28px", "#ffffff", false),
            },
            fields: [
                textarea("quote", "Quote", 4),
                text("author", "Author"),
                text("role", "Role or company"),
                color("accentColor", "Accent color"),
                color("textColor", "Text color"),
                align(),
            ],
        },
        tabs: {
            label: "Tabs",
            icon: "Tb",
            description: "Tabbed content panel",
            defaults: {
                type: "tabs",
                data: {
                    heading: "Helpful details",
                    activeColor: "#6C5CE7",
                    items: [
                        { title: "Overview", content: "Explain the service, product, or process in a short paragraph." },
                        { title: "Features", content: "List important benefits or technical details here." },
                        { title: "Support", content: "Tell visitors how your team will help them." },
                    ],
                },
                styles: sectionStyles("32px 0", "transparent", false),
            },
            fields: [
                text("heading", "Heading"),
                color("activeColor", "Active color"),
                { kind: "tabs-items", key: "items", label: "Tab items" },
            ],
        },
        video: {
            label: "Video",
            icon: "V",
            description: "YouTube/Vimeo embed",
            defaults: {
                type: "video",
                data: { url: "", provider: "youtube", autoplay: false },
                styles: sectionStyles("24px 0", "transparent", false),
            },
            fields: [
                text("url", "Video URL"),
                select("provider", "Provider", [["youtube", "YouTube"], ["vimeo", "Vimeo"]]),
                toggle("autoplay", "Autoplay"),
            ],
        },
        button: {
            label: "Button",
            icon: "B",
            description: "CTA button",
            defaults: {
                type: "button",
                data: { text: "Click Me", link: "#", style: "primary", size: "md", align: "center" },
                styles: sectionStyles("16px 0", "transparent", false),
            },
            fields: [
                text("text", "Button text"),
                text("link", "Link URL"),
                select("style", "Style", [["primary", "Primary"], ["outline", "Outline"], ["dark", "Dark"], ["white", "White"]]),
                select("size", "Size", [["sm", "Small"], ["md", "Medium"], ["lg", "Large"]]),
                align(),
            ],
        },
        shortcode: {
            label: "Shortcode",
            icon: "[]",
            description: "Render plugin output",
            defaults: {
                type: "shortcode",
                data: { shortcode: "[pricing]" },
                styles: sectionStyles("24px 0", "transparent", false),
            },
            fields: [
                text("shortcode", "Shortcode"),
                { kind: "notice", text: "Examples: [pricing], [slider slug=\"home\"], [form id=\"1\"]" },
            ],
        },
        "domain-search": {
            label: "Domain Search",
            icon: "D",
            description: "Domain checker",
            defaults: {
                type: "domain-search",
                data: {},
                styles: sectionStyles("32px 0", "#f1f5f9", true),
            },
            fields: [
                { kind: "notice", text: "This widget renders the public domain search form automatically." },
            ],
        },
        "page-hero": {
            label: "Page Hero",
            icon: "Pg",
            description: "Page title banner",
            defaults: {
                type: "page-hero",
                data: {
                    title: "Page Title",
                    subtitle: "",
                    showBreadcrumbs: true,
                    bgColor: "#0F172A",
                    textColor: "#ffffff",
                    height: "normal",
                    align: "center",
                },
                styles: sectionStyles("60px 0", "#0F172A", true),
            },
            fields: [
                text("title", "Title"),
                text("subtitle", "Subtitle"),
                toggle("showBreadcrumbs", "Show breadcrumbs"),
                color("bgColor", "Background color"),
                color("textColor", "Text color"),
                select("height", "Height", [["small", "Small"], ["normal", "Normal"], ["large", "Large"]]),
                select("align", "Alignment", [["left", "Left"], ["center", "Center"]]),
            ],
        },
        spacer: {
            label: "Spacer",
            icon: "S",
            description: "Vertical space",
            defaults: {
                type: "spacer",
                data: { height: "40" },
                styles: sectionStyles("0", "transparent", false),
            },
            fields: [
                number("height", "Height", 0, 320),
            ],
        },
    };

    class JamViniBuilder {
        constructor(options) {
            this.saveUrl = options.saveUrl;
            this.previewUrl = options.previewUrl || "";
            this.builderCssUrl = options.builderCssUrl || "";
            this.frontendCssUrl = options.frontendCssUrl || "/themes/default/assets/css/frontend.css";
            this.csrfToken = options.csrfToken;
            this.blocks = this.normalizeBlocks(options.initialBlocks || []);
            this.selectedPath = null;
            this.selectedColumn = null;
            this.previewMode = "desktop";
            this.history = [];
            this.future = [];
            this.isDirty = false;
            this.isSaving = false;
            this.lastSavedJson = JSON.stringify(this.blocks);

            this.dom = {
                canvas: document.getElementById("canvas"),
                sidebar: document.querySelector(".block-list"),
                settings: document.getElementById("settingsPanel"),
                settingsTitle: document.getElementById("settingsTitle"),
                settingsBody: document.getElementById("settingsBody"),
                saveBtn: document.getElementById("saveBtn"),
                previewBtn: document.getElementById("previewBtn"),
            };

            this.init();
            this.render();
        }

        init() {
            this.enhanceToolbar();
            this.bindLibrary();
            this.bindCanvas();
            this.bindSettings();
            this.bindKeyboard();
            this.bindBeforeUnload();

            this.dom.saveBtn?.addEventListener("click", () => this.save());
            this.dom.previewBtn?.addEventListener("click", () => this.preview());

            document.querySelectorAll(".preview-toggle").forEach((button) => {
                button.addEventListener("click", () => this.setPreviewMode(button.dataset.mode || "desktop"));
            });
        }

        enhanceToolbar() {
            const toolbarActions = this.dom.saveBtn?.parentElement;
            if (!toolbarActions || document.getElementById("undoBtn")) return;

            toolbarActions.insertAdjacentHTML("afterbegin", `
                <button id="undoBtn" class="builder-btn builder-btn-outline" type="button" title="Undo">${icon("undo-2")} Undo</button>
                <button id="redoBtn" class="builder-btn builder-btn-outline" type="button" title="Redo">${icon("redo-2")} Redo</button>
            `);

            document.getElementById("undoBtn").addEventListener("click", () => this.undo());
            document.getElementById("redoBtn").addEventListener("click", () => this.redo());
        }

        bindLibrary() {
            document.querySelectorAll(".block-item").forEach((item) => {
                item.setAttribute("draggable", "true");
                item.addEventListener("dragstart", (event) => {
                    event.dataTransfer.setData("application/x-jamvini-block", item.dataset.type);
                    event.dataTransfer.setData("text/plain", item.dataset.type);
                    event.dataTransfer.effectAllowed = "copy";
                });
                item.addEventListener("click", () => this.addBlock(item.dataset.type));
            });
        }

        bindCanvas() {
            this.dom.canvas.addEventListener("click", (event) => {
                const action = event.target.closest("[data-action]");
                if (action) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.handleAction(action.dataset.action, action.dataset);
                    return;
                }

                const column = event.target.closest("[data-column-path]");
                if (column) {
                    event.stopPropagation();
                    this.selectColumn(column.dataset.columnPath, Number(column.dataset.columnIndex));
                    return;
                }

                const block = event.target.closest("[data-block-path]");
                if (block) {
                    event.stopPropagation();
                    this.selectBlock(block.dataset.blockPath);
                }
            });

            this.dom.canvas.addEventListener("dragover", (event) => {
                event.preventDefault();
                const dropZone = event.target.closest("[data-drop-path]");
                this.setDropTarget(dropZone);
            });

            this.dom.canvas.addEventListener("dragleave", (event) => {
                if (!this.dom.canvas.contains(event.relatedTarget)) this.setDropTarget(null);
            });

            this.dom.canvas.addEventListener("drop", (event) => {
                event.preventDefault();
                const type = event.dataTransfer.getData("application/x-jamvini-block") || event.dataTransfer.getData("text/plain");
                const dropZone = event.target.closest("[data-drop-path]");
                this.setDropTarget(null);
                if (!type) return;

                if (dropZone) {
                    this.addBlock(type, dropZone.dataset.dropPath);
                } else {
                    this.addBlock(type);
                }
            });
        }

        bindSettings() {
            this.dom.settingsBody.addEventListener("input", (event) => this.handleFieldChange(event));
            this.dom.settingsBody.addEventListener("change", (event) => this.handleFieldChange(event));
            this.dom.settingsBody.addEventListener("click", (event) => {
                const action = event.target.closest("[data-settings-action]");
                const blockAction = event.target.closest("[data-action]");

                if (action) {
                    event.preventDefault();
                    this.handleSettingsAction(action.dataset.settingsAction, action.dataset);
                    return;
                }

                if (blockAction) {
                    event.preventDefault();
                    this.handleAction(blockAction.dataset.action, blockAction.dataset);
                }
            });
        }

        bindKeyboard() {
            document.addEventListener("keydown", (event) => {
                const target = event.target;
                const isTyping = ["INPUT", "TEXTAREA", "SELECT"].includes(target?.tagName);
                const mod = event.metaKey || event.ctrlKey;

                if (mod && event.key.toLowerCase() === "s") {
                    event.preventDefault();
                    this.save();
                    return;
                }

                if (!isTyping && mod && event.key.toLowerCase() === "z" && !event.shiftKey) {
                    event.preventDefault();
                    this.undo();
                    return;
                }

                if (!isTyping && (mod && event.key.toLowerCase() === "y" || mod && event.shiftKey && event.key.toLowerCase() === "z")) {
                    event.preventDefault();
                    this.redo();
                    return;
                }

                if (!isTyping && event.key === "Delete" && this.selectedPath) {
                    event.preventDefault();
                    this.removeBlock(this.selectedPath);
                }
            });
        }

        bindBeforeUnload() {
            window.addEventListener("beforeunload", (event) => {
                if (!this.isDirty) return;
                event.preventDefault();
                event.returnValue = "";
            });
        }

        handleAction(action, dataset) {
            const path = dataset.path;
            if (action === "select") this.selectBlock(path);
            if (action === "duplicate") this.duplicateBlock(path);
            if (action === "remove") this.removeBlock(path);
            if (action === "move-up") this.moveBlock(path, -1);
            if (action === "move-down") this.moveBlock(path, 1);
        }

        handleSettingsAction(action, dataset) {
            if (action === "close") this.closeSettings();
            if (action === "add-column") this.addColumn(dataset.path);
            if (action === "remove-column") this.removeColumn(dataset.path, Number(dataset.columnIndex));
            if (action === "select-column") this.selectColumn(dataset.path, Number(dataset.columnIndex));
        }

        handleFieldChange(event) {
            const field = event.target.closest("[data-field]");
            if (!field) return;

            const path = field.dataset.path;
            const group = field.dataset.group || "data";
            const key = field.dataset.field;
            const value = field.type === "checkbox" ? field.checked : field.value;

            if (field.dataset.live === "false" && event.type === "input") return;
            this.updateValue(path, group, key, value);
        }

        addBlock(type, destinationPath = null) {
            if (!BLOCKS[type]) type = "text";
            this.captureHistory();
            const block = this.createBlock(type);

            if (destinationPath) {
                const column = this.getColumnByDropPath(destinationPath);
                if (column) {
                    column.blocks = Array.isArray(column.blocks) ? column.blocks : [];
                    column.blocks.push(block);
                    this.selectedPath = `${destinationPath}.blocks.${column.blocks.length - 1}`;
                }
            } else {
                this.blocks.push(block);
                this.selectedPath = String(this.blocks.length - 1);
            }

            this.selectedColumn = null;
            this.markDirty();
            this.render();
            this.openSettings(this.selectedPath);
        }

        createBlock(type) {
            return this.normalizeBlock(clone(BLOCKS[type].defaults));
        }

        duplicateBlock(path) {
            const parent = this.getParentList(path);
            if (!parent) return;
            this.captureHistory();
            const index = Number(path.split(".").pop());
            parent.splice(index + 1, 0, clone(parent[index]));
            this.selectedPath = this.pathWithNewIndex(path, index + 1);
            this.selectedColumn = null;
            this.markDirty();
            this.render();
            this.openSettings(this.selectedPath);
        }

        removeBlock(path) {
            const parent = this.getParentList(path);
            if (!parent) return;
            this.captureHistory();
            const index = Number(path.split(".").pop());
            parent.splice(index, 1);
            this.selectedPath = null;
            this.selectedColumn = null;
            this.markDirty();
            this.render();
            this.closeSettings();
        }

        moveBlock(path, direction) {
            const parent = this.getParentList(path);
            if (!parent) return;
            const index = Number(path.split(".").pop());
            const next = index + direction;
            if (next < 0 || next >= parent.length) return;

            this.captureHistory();
            const [block] = parent.splice(index, 1);
            parent.splice(next, 0, block);
            this.selectedPath = this.pathWithNewIndex(path, next);
            this.selectedColumn = null;
            this.markDirty();
            this.render();
            this.openSettings(this.selectedPath);
        }

        addColumn(path) {
            const block = this.getBlock(path);
            if (!block || block.type !== "row") return;
            this.captureHistory();
            block.data.columns = Array.isArray(block.data.columns) ? block.data.columns : [];
            block.data.columns.push({ width: `${Math.round(100 / (block.data.columns.length + 1))}%`, blocks: [] });
            this.balanceColumns(block);
            this.markDirty();
            this.render();
            this.openSettings(path);
        }

        removeColumn(path, columnIndex) {
            const block = this.getBlock(path);
            if (!block || block.type !== "row") return;
            this.captureHistory();
            block.data.columns.splice(columnIndex, 1);
            if (!block.data.columns.length) block.data.columns.push({ width: "100%", blocks: [] });
            this.balanceColumns(block);
            this.selectedColumn = null;
            this.markDirty();
            this.render();
            this.openSettings(path);
        }

        selectBlock(path) {
            this.selectedPath = path;
            this.selectedColumn = null;
            this.render();
            this.openSettings(path);
        }

        selectColumn(rowPath, columnIndex) {
            this.selectedPath = rowPath;
            this.selectedColumn = { rowPath, columnIndex };
            this.render();
            this.openColumnSettings(rowPath, columnIndex);
        }

        updateValue(path, group, key, value) {
            const block = this.getBlock(path);
            if (!block) return;

            this.captureHistory();
            if (!block[group] || typeof block[group] !== "object") block[group] = {};
            setNestedValue(block[group], key, value);

            if (block.type === "cta" && key === "bgColor") block.styles.background = value;
            if (block.type === "page-hero" && key === "bgColor") block.styles.background = value;

            this.markDirty();
            this.render();
        }

        updateColumn(rowPath, columnIndex, key, value) {
            const block = this.getBlock(rowPath);
            if (!block?.data?.columns?.[columnIndex]) return;
            this.captureHistory();
            block.data.columns[columnIndex][key] = value;
            this.markDirty();
            this.render();
        }

        render(options = {}) {
            this.dom.canvas.className = `canvas-container preview-${this.previewMode}`;

            if (!this.blocks.length) {
                this.dom.canvas.innerHTML = `
                    <div class="canvas-empty">
                        <div class="empty-icon">JV</div>
                        <h3>Start Building Your Page</h3>
                        <p>Drag blocks from the left panel or click a block to add it.</p>
                    </div>
                `;
                this.updateToolbarState();
                return;
            }

            this.dom.canvas.innerHTML = this.blocks.map((block, index) => this.renderCanvasBlock(block, String(index), index, this.blocks.length)).join("");
            this.updateToolbarState();

            if (!options.keepSettings) return;
            if (this.selectedColumn) {
                this.openColumnSettings(this.selectedColumn.rowPath, this.selectedColumn.columnIndex);
            } else if (this.selectedPath) {
                this.openSettings(this.selectedPath);
            }
        }

        renderCanvasBlock(block, path, index, total) {
            const styles = block.styles || {};
            const isSelected = this.selectedPath === path && !this.selectedColumn;
            const fullWidth = Boolean(styles.fullWidth);
            const background = safeCss(styles.background || "transparent");
            const padding = safeCss(styles.padding || "0");
            const type = escapeHtml(block.type || "text");
            const label = escapeHtml(BLOCKS[block.type]?.label || block.type || "Block");

            return `
                <section class="canvas-block ${isSelected ? "selected" : ""} ${fullWidth ? "is-full-width" : ""}"
                    data-block-path="${escapeAttr(path)}"
                    data-action="select"
                    data-path="${escapeAttr(path)}"
                    style="background:${background}; padding:${padding};">
                    <div class="block-toolbar">
                        <span class="block-chip">${label}</span>
                        <button class="block-action-btn" type="button" data-action="move-up" data-path="${escapeAttr(path)}" ${index <= 0 ? "disabled" : ""} title="Move up">${icon("arrow-up", 14)}</button>
                        <button class="block-action-btn" type="button" data-action="move-down" data-path="${escapeAttr(path)}" ${index >= total - 1 ? "disabled" : ""} title="Move down">${icon("arrow-down", 14)}</button>
                        <button class="block-action-btn" type="button" data-action="duplicate" data-path="${escapeAttr(path)}" title="Duplicate">${icon("copy", 14)}</button>
                        <button class="block-action-btn delete" type="button" data-action="remove" data-path="${escapeAttr(path)}" title="Remove">${icon("trash-2", 14)}</button>
                    </div>
                    <div class="canvas-block-inner" data-block-type="${type}">
                        ${this.renderBlockContent(block, path)}
                    </div>
                </section>
            `;
        }

        renderBlockContent(block, path) {
            const data = block.data || {};

            switch (block.type) {
                case "text":
                    return renderText(data);
                case "heading":
                    return renderHeading(data);
                case "spacer":
                    return `<div class="jvb-spacer" style="height:${num(data.height, 40)}px"></div>`;
                case "image":
                    return renderImage(data);
                case "quote":
                    return renderQuote(data);
                case "tabs":
                    return renderTabs(data);
                case "video":
                    return renderVideo(data);
                case "button":
                    return renderButton(data);
                case "shortcode":
                    return `<div class="shortcode-placeholder"><strong>Shortcode</strong><span>${escapeHtml(data.shortcode || "")}</span></div>`;
                case "domain-search":
                    return renderDomainSearch();
                case "row":
                    return this.renderRow(block, path);
                case "hero":
                    return renderHero(data);
                case "feature-card":
                    return renderFeatureCard(data);
                case "pricing-table":
                    return renderPricing(data);
                case "cta":
                    return renderCta(data);
                case "page-hero":
                    return renderPageHero(data);
                default:
                    return `<div class="shortcode-placeholder">Unknown block: ${escapeHtml(block.type)}</div>`;
            }
        }

        renderRow(block, path) {
            const columns = Array.isArray(block.data?.columns) ? block.data.columns : [];
            return `
                <div class="builder-row">
                    ${columns.map((column, columnIndex) => {
                        const dropPath = `${path}.data.columns.${columnIndex}`;
                        const selected = this.selectedColumn?.rowPath === path && this.selectedColumn?.columnIndex === columnIndex;
                        const width = safeCss(column.width || `${100 / Math.max(columns.length, 1)}%`);
                        const bg = safeCss(column.bg || "rgba(255,255,255,0.6)");
                        const blocks = Array.isArray(column.blocks) ? column.blocks : [];

                        return `
                            <div class="builder-column ${selected ? "selected" : ""}"
                                data-column-path="${escapeAttr(path)}"
                                data-column-index="${columnIndex}"
                                data-drop-path="${escapeAttr(dropPath)}"
                                style="flex-basis:${width}; background:${bg};">
                                <div class="column-label">
                                    <span>Column ${columnIndex + 1}</span>
                                    <span>${escapeHtml(column.width || width)}</span>
                                </div>
                                <div class="column-block-list">
                                    ${blocks.length
                                        ? blocks.map((inner, innerIndex) => this.renderCanvasBlock(inner, `${dropPath}.blocks.${innerIndex}`, innerIndex, blocks.length)).join("")
                                        : `<div class="column-empty">Drop blocks here</div>`}
                                </div>
                            </div>
                        `;
                    }).join("")}
                </div>
            `;
        }

        openSettings(path) {
            const block = this.getBlock(path);
            if (!block) return;

            const meta = BLOCKS[block.type] || BLOCKS.text;
            this.dom.settingsTitle.textContent = `${meta.label} Settings`;
            this.dom.settingsBody.innerHTML = `
                ${this.renderBlockFields(block, path)}
                ${this.renderStyleFields(block, path)}
            `;
            this.dom.settings.classList.add("open");
        }

        openColumnSettings(rowPath, columnIndex) {
            const row = this.getBlock(rowPath);
            const column = row?.data?.columns?.[columnIndex];
            if (!column) return;

            this.dom.settingsTitle.textContent = `Column ${columnIndex + 1} Settings`;
            this.dom.settingsBody.innerHTML = `
                <div class="settings-section">
                    <h4>Layout</h4>
                    <div class="builder-form-group">
                        <label>Width</label>
                        <input type="text" value="${escapeAttr(column.width || "")}" data-column-field="width">
                    </div>
                    <div class="builder-form-group">
                        <label>Background</label>
                        <input type="color" value="${colorValue(column.bg || "#ffffff")}" data-column-field="bg">
                    </div>
                    <div class="settings-actions">
                        <button class="builder-btn builder-btn-danger" type="button" data-settings-action="remove-column" data-path="${escapeAttr(rowPath)}" data-column-index="${columnIndex}">${icon("trash-2", 14)} Remove Column</button>
                        <button class="builder-btn builder-btn-outline" type="button" data-settings-action="select-column" data-path="${escapeAttr(rowPath)}" data-column-index="${columnIndex}">${icon("plus", 14)} Refresh</button>
                    </div>
                </div>
                <div class="settings-section">
                    <h4>Blocks</h4>
                    <p class="settings-help">${(column.blocks || []).length} block(s) in this column. Drag blocks from the left panel into the column.</p>
                </div>
            `;

            this.dom.settingsBody.querySelectorAll("[data-column-field]").forEach((field) => {
                field.addEventListener("input", () => this.updateColumn(rowPath, columnIndex, field.dataset.columnField, field.value));
                field.addEventListener("change", () => this.updateColumn(rowPath, columnIndex, field.dataset.columnField, field.value));
            });
            this.dom.settings.classList.add("open");
        }

        renderBlockFields(block, path) {
            const meta = BLOCKS[block.type] || BLOCKS.text;
            const fields = meta.fields || [];

            const content = fields.map((field) => this.renderField(field, block.data || {}, "data", path)).join("");

            if (block.type !== "row") return `<div class="settings-section"><h4>Content</h4>${content}</div>`;

            const columns = block.data.columns || [];
            return `
                <div class="settings-section">
                    <h4>Columns</h4>
                    <p class="settings-help">${columns.length} column(s). Click a column on the canvas to edit it.</p>
                    <button class="builder-btn builder-btn-primary btn-block" type="button" data-settings-action="add-column" data-path="${escapeAttr(path)}">${icon("plus", 14)} Add Column</button>
                    <div class="column-settings-list">
                        ${columns.map((column, index) => `
                            <button class="column-settings-item" type="button" data-settings-action="select-column" data-path="${escapeAttr(path)}" data-column-index="${index}">
                                <span>Column ${index + 1}</span>
                                <small>${escapeHtml(column.width || "auto")} · ${(column.blocks || []).length} block(s)</small>
                            </button>
                        `).join("")}
                    </div>
                    ${content}
                </div>
            `;
        }

        renderStyleFields(block, path) {
            const styles = block.styles || {};
            return `
                <div class="settings-section">
                    <h4>Style</h4>
                    ${this.renderField(color("background", "Background"), styles, "styles", path)}
                    ${this.renderField(text("padding", "Padding"), styles, "styles", path)}
                    ${this.renderField(toggle("fullWidth", "Full width section"), styles, "styles", path)}
                </div>
                <div class="settings-section">
                    <h4>Actions</h4>
                    <div class="settings-actions">
                        <button class="builder-btn builder-btn-outline" type="button" data-action="duplicate" data-path="${escapeAttr(path)}">${icon("copy", 14)} Duplicate</button>
                        <button class="builder-btn builder-btn-danger" type="button" data-action="remove" data-path="${escapeAttr(path)}">${icon("trash-2", 14)} Delete</button>
                    </div>
                </div>
            `;
        }

        renderField(field, source, group, path) {
            if (field.kind === "divider") return `<div class="settings-divider">${escapeHtml(field.label)}</div>`;
            if (field.kind === "notice") return `<p class="settings-help">${escapeHtml(field.text)}</p>`;

            const value = source[field.key];
            const base = `data-field="${escapeAttr(field.key)}" data-group="${escapeAttr(group)}" data-path="${escapeAttr(path)}"`;
            const label = `<label>${escapeHtml(field.label)}</label>`;

            if (field.kind === "tabs-items") {
                const items = Array.isArray(value) && value.length ? value : [
                    { title: "Overview", content: "Explain this section here." },
                    { title: "Details", content: "Add useful details here." },
                    { title: "Support", content: "Tell visitors how your team will help them." },
                ];

                return `
                    <div class="builder-form-group">
                        ${label}
                        <div style="display:grid;gap:12px;">
                            ${items.slice(0, 6).map((item, index) => `
                                <div style="display:grid;gap:8px;padding:10px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;">
                                    <input type="text" value="${escapeAttr(item.title || "")}" data-field="${escapeAttr(`${field.key}.${index}.title`)}" data-group="${escapeAttr(group)}" data-path="${escapeAttr(path)}" placeholder="Tab title">
                                    <textarea rows="3" data-field="${escapeAttr(`${field.key}.${index}.content`)}" data-group="${escapeAttr(group)}" data-path="${escapeAttr(path)}" placeholder="Tab content">${escapeHtml(item.content || "")}</textarea>
                                </div>
                            `).join("")}
                        </div>
                    </div>
                `;
            }

            if (field.kind === "textarea") {
                return `<div class="builder-form-group">${label}<textarea rows="${field.rows || 3}" ${base}>${escapeHtml(value || "")}</textarea></div>`;
            }

            if (field.kind === "select") {
                return `
                    <div class="builder-form-group">${label}
                        <select ${base}>
                            ${field.options.map(([optionValue, optionLabel]) => `<option value="${escapeAttr(optionValue)}" ${String(value) === String(optionValue) ? "selected" : ""}>${escapeHtml(optionLabel)}</option>`).join("")}
                        </select>
                    </div>
                `;
            }

            if (field.kind === "toggle") {
                return `
                    <div class="builder-form-group">
                        <label class="builder-check">
                            <input type="checkbox" ${base} ${value ? "checked" : ""}>
                            <span>${escapeHtml(field.label)}</span>
                        </label>
                    </div>
                `;
            }

            if (field.kind === "range") {
                return `<div class="builder-form-group">${label}<input type="range" min="${field.min}" max="${field.max}" step="${field.step}" value="${escapeAttr(value ?? "")}" ${base}></div>`;
            }

            const type = field.kind === "number" ? "number" : field.kind === "color" ? "color" : "text";
            const extra = field.kind === "number" ? `min="${field.min}" max="${field.max}"` : "";
            const fieldValue = field.kind === "color" ? colorValue(value || "#ffffff") : value ?? "";
            return `<div class="builder-form-group">${label}<input type="${type}" value="${escapeAttr(fieldValue)}" ${extra} ${base}></div>`;
        }

        closeSettings() {
            this.dom.settings.classList.remove("open");
            this.selectedColumn = null;
        }

        setPreviewMode(mode) {
            this.previewMode = ["desktop", "tablet", "mobile"].includes(mode) ? mode : "desktop";
            document.querySelectorAll(".preview-toggle").forEach((button) => {
                button.classList.toggle("active", button.dataset.mode === this.previewMode);
            });
            this.render({ keepSettings: true });
        }

        async save() {
            if (this.isSaving) return false;
            this.isSaving = true;
            this.dom.saveBtn.disabled = true;
            this.dom.saveBtn.textContent = "Saving...";

            const blocks = this.serializeBlocks();
            try {
                const response = await fetch(this.saveUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": this.csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({ blocks }),
                });

                if (!response.ok) throw new Error(`Save failed with status ${response.status}`);

                this.blocks = this.normalizeBlocks(blocks);
                this.lastSavedJson = JSON.stringify(this.blocks);
                this.isDirty = false;
                this.showToast("Saved", "Page builder content saved successfully.", "success");
                this.render({ keepSettings: true });
                return true;
            } catch (error) {
                console.error(error);
                this.showToast("Save failed", "Please check your connection and try again.", "error");
                return false;
            } finally {
                this.isSaving = false;
                this.dom.saveBtn.disabled = false;
                this.dom.saveBtn.textContent = "Save";
                this.updateToolbarState();
            }
        }

        async preview() {
            if (this.previewUrl) {
                if (this.isDirty) {
                    const saved = await this.save();
                    if (!saved) return;
                }

                const previewTab = window.open(this.previewUrl, "_blank", "noopener,noreferrer");
                if (!previewTab) {
                    this.showToast("Preview blocked", "Please allow popups for this site.", "error");
                }
                return;
            }

            const html = this.blocks.map((block, index) => this.renderCanvasBlock(block, String(index), index, this.blocks.length)).join("");
            const preview = window.open("", "_blank");
            if (!preview) {
                this.showToast("Preview blocked", "Please allow popups for this site.", "error");
                return;
            }

            preview.document.write(`
                <!doctype html>
                <html>
                    <head>
                        <meta charset="utf-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <title>JamVini Builder Preview</title>
                        <link rel="stylesheet" href="${escapeHtml(this.frontendCssUrl)}">
                        ${this.builderCssUrl ? `<link rel="stylesheet" href="${escapeHtml(this.builderCssUrl)}">` : ""}
                        <style>
                            body { margin: 0; background: #fff; }
                            .canvas-container { max-width: none; box-shadow: none; padding: 0; border-radius: 0; }
                            .canvas-block { border: 0 !important; margin: 0; }
                            .block-toolbar { display: none !important; }
                        </style>
                    </head>
                    <body><main class="canvas-container">${html}</main></body>
                </html>
            `);
            preview.document.close();
        }

        undo() {
            if (!this.history.length) return;
            this.future.push(JSON.stringify(this.blocks));
            this.blocks = JSON.parse(this.history.pop());
            this.selectedPath = null;
            this.selectedColumn = null;
            this.markDirty();
            this.render();
            this.closeSettings();
        }

        redo() {
            if (!this.future.length) return;
            this.history.push(JSON.stringify(this.blocks));
            this.blocks = JSON.parse(this.future.pop());
            this.selectedPath = null;
            this.selectedColumn = null;
            this.markDirty();
            this.render();
            this.closeSettings();
        }

        captureHistory() {
            const snapshot = JSON.stringify(this.blocks);
            if (this.history[this.history.length - 1] !== snapshot) {
                this.history.push(snapshot);
                if (this.history.length > 60) this.history.shift();
            }
            this.future = [];
        }

        markDirty() {
            this.isDirty = JSON.stringify(this.blocks) !== this.lastSavedJson;
            this.updateToolbarState();
        }

        updateToolbarState() {
            const undo = document.getElementById("undoBtn");
            const redo = document.getElementById("redoBtn");
            if (undo) undo.disabled = this.history.length === 0;
            if (redo) redo.disabled = this.future.length === 0;
            if (this.dom.saveBtn && !this.isSaving) this.dom.saveBtn.textContent = this.isDirty ? "Save Changes" : "Saved";
        }

        showToast(title, message, type = "success") {
            document.querySelectorAll(".builder-toast").forEach((toast) => toast.remove());
            const toast = document.createElement("div");
            toast.className = `builder-toast builder-toast-${type}`;
            toast.innerHTML = `<strong>${escapeHtml(title)}</strong><span>${escapeHtml(message)}</span>`;
            document.body.appendChild(toast);
            window.setTimeout(() => toast.classList.add("show"), 10);
            window.setTimeout(() => {
                toast.classList.remove("show");
                window.setTimeout(() => toast.remove(), 250);
            }, 3200);
        }

        serializeBlocks() {
            return this.normalizeBlocks(this.blocks).map((block) => ({
                type: block.type,
                data: clone(block.data || {}),
                styles: clone(block.styles || {}),
            }));
        }

        normalizeBlocks(blocks) {
            return (Array.isArray(blocks) ? blocks : []).map((block) => this.normalizeBlock(block));
        }

        normalizeBlock(block) {
            const type = BLOCKS[block?.type] ? block.type : "text";
            const defaults = clone(BLOCKS[type].defaults);
            const normalized = {
                type,
                data: { ...defaults.data, ...(isPlainObject(block?.data) ? block.data : {}) },
                styles: { ...defaults.styles, ...(isPlainObject(block?.styles) ? block.styles : {}) },
            };

            if (type === "row") {
                normalized.data.columns = Array.isArray(normalized.data.columns) ? normalized.data.columns : defaults.data.columns;
                normalized.data.columns = normalized.data.columns.map((column) => ({
                    width: column.width || "50%",
                    bg: column.bg || "",
                    blocks: this.normalizeBlocks(column.blocks || []),
                }));
            }

            return normalized;
        }

        getBlock(path) {
            if (!path && path !== "0") return null;
            const parts = String(path).split(".");
            let current = { blocks: this.blocks };
            for (let i = 0; i < parts.length; i += 1) {
                const part = parts[i];
                if (part === "data" || part === "columns" || part === "blocks") {
                    current = current?.[part];
                } else if (Array.isArray(current)) {
                    current = current[Number(part)];
                } else if (i === 0) {
                    current = this.blocks[Number(part)];
                } else {
                    current = current?.[part];
                }
            }
            return current || null;
        }

        getParentList(path) {
            const parts = String(path).split(".");
            parts.pop();
            if (!parts.length) return this.blocks;
            const parent = this.getBlock(parts.join("."));
            return Array.isArray(parent) ? parent : null;
        }

        getColumnByDropPath(path) {
            const column = this.getBlock(path);
            return column && typeof column === "object" ? column : null;
        }

        pathWithNewIndex(path, nextIndex) {
            const parts = String(path).split(".");
            parts[parts.length - 1] = String(nextIndex);
            return parts.join(".");
        }

        balanceColumns(rowBlock) {
            const columns = rowBlock.data.columns || [];
            const width = `${(100 / Math.max(columns.length, 1)).toFixed(2)}%`;
            columns.forEach((column) => {
                if (!column.width || column.width === "auto") column.width = width;
            });
        }

        setDropTarget(target) {
            document.querySelectorAll(".builder-column.drop-target").forEach((el) => el.classList.remove("drop-target"));
            if (target) target.classList.add("drop-target");
        }
    }

    function renderText(data) {
        const tag = allowedTag(data.tag, "p");
        return `<${tag} class="jvb-text" style="font-size:${num(data.fontSize, 16)}px;color:${safeCss(data.color || "#1e293b")};text-align:${alignValue(data.align)}">${nl2br(data.content || "")}</${tag}>`;
    }

    function renderHeading(data) {
        const tag = allowedTag(data.tag, "h2");
        return `
            <div class="jvb-heading" style="text-align:${alignValue(data.align)}">
                ${data.eyebrow ? `<span class="jvb-eyebrow">${escapeHtml(data.eyebrow)}</span>` : ""}
                <${tag} style="font-size:${num(data.fontSize, 32)}px;color:${safeCss(data.color || "#0f172a")}">${escapeHtml(data.content || "Section Heading")}</${tag}>
                ${data.subheading ? `<p>${escapeHtml(data.subheading)}</p>` : ""}
            </div>
        `;
    }

    function renderImage(data) {
        if (!data.src) {
            return `<div class="jvb-placeholder">Choose an image from Media Library or paste a media URL in the settings panel.</div>`;
        }
        return `
            <div style="text-align:${alignValue(data.align)}">
                <img src="${escapeAttr(data.src)}" alt="${escapeAttr(data.alt || "")}" style="width:${safeCss(data.width || "100%")};max-width:100%;border-radius:8px;">
            </div>
        `;
    }

    function renderQuote(data) {
        const align = alignValue(data.align);
        return `
            <figure class="jvb-quote" style="border-left:4px solid ${safeCss(data.accentColor || "#2f6f73")};text-align:${align};color:${safeCss(data.textColor || "#0f172a")}">
                <blockquote>${nl2br(data.quote || "Add a customer quote or highlighted statement.")}</blockquote>
                ${data.author ? `<figcaption><strong>${escapeHtml(data.author)}</strong>${data.role ? `<span>${escapeHtml(data.role)}</span>` : ""}</figcaption>` : ""}
            </figure>
        `;
    }

    function renderTabs(data) {
        const items = Array.isArray(data.items) && data.items.length ? data.items : [
            { title: "Overview", content: "Explain this section here." },
            { title: "Details", content: "Add useful details here." },
        ];

        return `
            <div class="jvb-tabs" style="--tab-active:${safeCss(data.activeColor || "#6C5CE7")}">
                ${data.heading ? `<h3>${escapeHtml(data.heading)}</h3>` : ""}
                <div class="jvb-tab-list">
                    ${items.map((item, index) => `<button type="button" class="${index === 0 ? "active" : ""}">${escapeHtml(item.title || `Tab ${index + 1}`)}</button>`).join("")}
                </div>
                <div class="jvb-tab-panel">${nl2br(items[0]?.content || "")}</div>
            </div>
        `;
    }

    function renderVideo(data) {
        if (!data.url) return `<div class="jvb-placeholder">Enter a video URL in the settings panel.</div>`;
        const embedUrl = videoEmbedUrl(data.url, data.provider, data.autoplay);
        return `<div class="jvb-video"><iframe src="${escapeAttr(embedUrl)}" allowfullscreen loading="lazy"></iframe></div>`;
    }

    function renderButton(data) {
        const size = { sm: "jvb-btn-sm", md: "jvb-btn-md", lg: "jvb-btn-lg" }[data.size] || "jvb-btn-md";
        const style = { primary: "primary", outline: "outline", dark: "dark", white: "white" }[data.style] || "primary";
        return `
            <div style="text-align:${alignValue(data.align)}">
                <a class="jvb-button ${size} ${style}" href="${escapeAttr(data.link || "#")}">${escapeHtml(data.text || "Click Me")}</a>
            </div>
        `;
    }

    function renderDomainSearch() {
        return `
            <div class="jvb-domain-search">
                <strong>Domain Search Widget</strong>
                <div class="jvb-domain-row"><span>example</span><span>.com</span><button type="button">Search</button></div>
                <small>Live domain search appears on the public page.</small>
            </div>
        `;
    }

    function renderHero(data) {
        const bg = data.bgType === "image" && data.bgImage
            ? `url(${safeCssUrl(data.bgImage)}) center/cover`
            : "linear-gradient(135deg, #0F172A 0%, #1a1f3a 50%, #2d1b4e 100%)";
        const overlay = data.bgType === "image" ? `rgba(15,23,42,${Number(data.overlayOpacity || 0.4)})` : "transparent";
        const align = alignValue(data.align);

        return `
            <div class="jvb-hero" style="background:${bg};${data.fullHeight ? "min-height:100vh;" : ""}">
                <div class="jvb-hero-overlay" style="background:${overlay}"></div>
                <div class="jvb-hero-content" style="text-align:${align}">
                    ${data.eyebrow ? `<span class="jvb-hero-eyebrow">${escapeHtml(data.eyebrow)}</span>` : ""}
                    <h1>${escapeHtml(data.heading || "Hero Heading")}</h1>
                    ${data.subtitle ? `<p>${escapeHtml(data.subtitle)}</p>` : ""}
                    ${data.showDomainSearch !== false ? `<div class="jvb-hero-search"><input placeholder="Find your domain..."><button type="button">Search</button></div>` : ""}
                    <div class="jvb-hero-actions" style="justify-content:${align === "left" ? "flex-start" : align === "right" ? "flex-end" : "center"}">
                        ${data.primaryBtnText ? `<a class="jvb-button primary jvb-btn-lg" href="${escapeAttr(data.primaryBtnLink || "#")}">${escapeHtml(data.primaryBtnText)}</a>` : ""}
                        ${data.secondaryBtnText ? `<a class="jvb-button ghost jvb-btn-lg" href="${escapeAttr(data.secondaryBtnLink || "#")}">${escapeHtml(data.secondaryBtnText)}</a>` : ""}
                    </div>
                </div>
            </div>
        `;
    }

    function renderFeatureCard(data) {
        const iconColor = data.iconColor || "#6C5CE7";
        const style = data.iconStyle || "filled";
        const isHorizontal = data.iconPosition === "left";
        const iconClass = style === "outline" ? "outline" : style === "plain" ? "plain" : "filled";
        return `
            <article class="jvb-feature-card ${isHorizontal ? "horizontal" : ""}" style="text-align:${alignValue(data.align)}">
                <div class="jvb-feature-icon ${iconClass}" style="--feature-color:${safeCss(iconColor)}">${escapeHtml(data.icon || "⚡")}</div>
                <div>
                    <h3>${escapeHtml(data.title || "Feature Title")}</h3>
                    <p>${escapeHtml(data.description || "")}</p>
                </div>
            </article>
        `;
    }

    function renderPricing(data) {
        return `
            <div class="jvb-pricing">
                ${data.eyebrow ? `<span class="jvb-eyebrow">${escapeHtml(data.eyebrow)}</span>` : ""}
                <h2>${escapeHtml(data.heading || "Pricing Plans")}</h2>
                ${data.subheading ? `<p>${escapeHtml(data.subheading)}</p>` : ""}
                ${data.showToggle !== false ? `<div class="jvb-pricing-toggle"><span>Monthly</span><span>Annual</span></div>` : ""}
                <div class="jvb-pricing-grid">
                    ${["Starter", "Business", "Premium"].map((name, index) => `
                        <article class="jvb-price-card ${index === 1 ? "featured" : ""}">
                            ${index === 1 ? "<small>Popular</small>" : ""}
                            <h3>${name}</h3>
                            <strong>TZS ${index === 0 ? "25,000" : index === 1 ? "50,000" : "100,000"}</strong>
                            <span>/mo</span>
                            <a href="/hosting">Get Started</a>
                        </article>
                    `).join("")}
                </div>
            </div>
        `;
    }

    function renderCta(data) {
        const btnClass = data.buttonStyle === "outline" ? "ghost" : data.buttonStyle === "primary" ? "primary" : "white";
        return `
            <div class="jvb-cta" style="background:${safeCss(data.bgColor || "#6C5CE7")}">
                <h2>${escapeHtml(data.heading || "Ready to launch?")}</h2>
                ${data.text ? `<p>${escapeHtml(data.text)}</p>` : ""}
                ${data.buttonText ? `<a class="jvb-button ${btnClass} jvb-btn-lg" href="${escapeAttr(data.buttonLink || "#")}">${escapeHtml(data.buttonText)}</a>` : ""}
            </div>
        `;
    }

    function renderPageHero(data) {
        const padding = data.height === "small" ? "40px 0" : data.height === "large" ? "100px 0" : "60px 0";
        return `
            <div class="jvb-page-hero" style="background:${safeCss(data.bgColor || "#0F172A")};color:${safeCss(data.textColor || "#ffffff")};padding:${padding};text-align:${alignValue(data.align)}">
                ${data.showBreadcrumbs !== false ? `<div class="jvb-breadcrumb">Home / ${escapeHtml(data.title || "Page")}</div>` : ""}
                <h1 style="color:${safeCss(data.textColor || "#ffffff")}">${escapeHtml(data.title || "Page Title")}</h1>
                ${data.subtitle ? `<p>${escapeHtml(data.subtitle)}</p>` : ""}
            </div>
        `;
    }

    function sectionStyles(padding, background, fullWidth) {
        return { padding, background, fullWidth };
    }

    function text(key, label) { return { kind: "text", key, label }; }
    function textarea(key, label, rows) { return { kind: "textarea", key, label, rows }; }
    function number(key, label, min, max) { return { kind: "number", key, label, min, max }; }
    function color(key, label) { return { kind: "color", key, label }; }
    function toggle(key, label) { return { kind: "toggle", key, label }; }
    function range(key, label, min, max, step) { return { kind: "range", key, label, min, max, step }; }
    function select(key, label, options) { return { kind: "select", key, label, options }; }
    function divider(label) { return { kind: "divider", label }; }
    function align() { return select("align", "Alignment", [["left", "Left"], ["center", "Center"], ["right", "Right"]]); }

    function clone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function isPlainObject(value) {
        return value !== null && typeof value === "object" && !Array.isArray(value);
    }

    function setNestedValue(target, key, value) {
        const parts = String(key || "").split(".");
        let current = target;
        while (parts.length > 1) {
            const part = parts.shift();
            const nextPart = parts[0];
            if (current[part] === undefined) current[part] = /^\d+$/.test(nextPart) ? [] : {};
            current = current[part];
        }
        current[parts[0]] = value;
    }

    function escapeHtml(value) {
        return String(value ?? "").replace(/[&<>"']/g, (char) => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;",
        }[char]));
    }

    function escapeAttr(value) {
        return escapeHtml(value).replace(/`/g, "&#096;");
    }

    function icon(name, size = 16) {
        const icons = {
            "undo-2": '<path d="M9 14 4 9l5-5"/><path d="M4 9h10a5 5 0 0 1 0 10h-1"/>',
            "redo-2": '<path d="m15 14 5-5-5-5"/><path d="M20 9H10a5 5 0 0 0 0 10h1"/>',
            "arrow-up": '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
            "arrow-down": '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
            "copy": '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
            "trash-2": '<path d="M3 6h18"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><path d="M19 6l-1 14c0 1-1 2-2 2H8c-1 0-2-1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>',
            "plus": '<path d="M5 12h14"/><path d="M12 5v14"/>',
        };
        return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${icons[name] || icons.plus}</svg>`;
    }

    function nl2br(value) {
        return escapeHtml(value).replace(/\n/g, "<br>");
    }

    function allowedTag(tag, fallback) {
        return ["p", "h1", "h2", "h3", "h4", "h5", "h6"].includes(tag) ? tag : fallback;
    }

    function alignValue(value) {
        return ["left", "center", "right"].includes(value) ? value : "left";
    }

    function num(value, fallback) {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    function colorValue(value) {
        return /^#[0-9a-f]{6}$/i.test(String(value || "")) ? value : "#ffffff";
    }

    function safeCss(value) {
        return String(value ?? "").replace(/[;"<>]/g, "");
    }

    function safeCssUrl(value) {
        return String(value ?? "").replace(/[()"<>]/g, "");
    }

    function videoEmbedUrl(url, provider, autoplay) {
        const cleanUrl = String(url || "");
        let id = "";
        if (provider === "vimeo") {
            id = cleanUrl.split("/").filter(Boolean).pop() || "";
            return `https://player.vimeo.com/video/${encodeURIComponent(id)}${autoplay ? "?autoplay=1" : ""}`;
        }

        try {
            const parsed = new URL(cleanUrl);
            id = parsed.searchParams.get("v") || parsed.pathname.split("/").filter(Boolean).pop() || "";
        } catch (error) {
            id = cleanUrl.split("/").filter(Boolean).pop() || "";
        }

        return `https://www.youtube.com/embed/${encodeURIComponent(id)}${autoplay ? "?autoplay=1" : ""}`;
    }

    window.JamViniBuilder = JamViniBuilder;
})();
