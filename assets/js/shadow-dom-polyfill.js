// Just in case the browser doesn't support shadowRootMode
// https://developer.chrome.com/docs/css-ui/declarative-shadow-dom#polyfill
// https://caniuse.com/mdn-html_elements_template_shadowrootmode
(function attachShadowRoots(root) {
    root.querySelectorAll("template[shadowrootmode]").forEach(template => {
        const mode = template.getAttribute("shadowrootmode");
        const shadowRoot = template.parentNode.attachShadow({ mode });
        shadowRoot.appendChild(template.content);
        template.remove();
        attachShadowRoots(shadowRoot);
    });
})(document);
